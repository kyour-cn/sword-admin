# 操作审计日志重构方案

## 背景

当前数据库中的 `log` / `log_type` / `DbLog` 同时承载代码运行日志和业务操作记录，语义混杂：

- `debug`、`info`、`warn`、`error` 等代码层日志不应入库，应交给 Monolog 写入 `runtime/logs`。
- 数据库日志应只用于可查询、可追责的操作审计日志。
- 旧表字段围绕“日志级别”设计，无法清晰表达操作主体、动作、资源、结果和请求上下文。

本次重构不兼容历史数据，不做旧数据迁移，直接按新的审计日志模型落地。

## 重构目标

- 规范化：统一命名为 `audit_log`，字段围绕审计语义设计，接口、菜单、页面文案统一使用“操作审计”。
- 先进：后端使用 PHP 8.4 可用的新语法和强类型风格，前端使用 Vue 3 `<script setup>` 与组合式 API，查询条件保持显式、可组合。
- 简约：采用单表审计模型，先不引入复杂事件总线、异步队列、审计类型字典表等额外层级。

## 边界划分

### 代码层日志

用途：

- 调试信息
- 异常堆栈
- 框架启动、进程、任务运行等技术日志

落点：

- `support\Log`
- `runtime/logs/webman.log`
- `runtime/logs/workerman.log`
- `runtime/logs/stdout.log`

处理原则：

- 删除 `DbLog::debug()`、`DbLog::info()`、`DbLog::warning()`、`DbLog::error()` 这类数据库写入能力。
- 代码异常或调试信息统一使用 `support\Log`。
- 审计写入失败只记录文件日志，不应阻断主业务流程。

### 操作审计日志

用途：

- 用户登录、退出
- 新增、编辑、删除、导入、导出
- 权限、配置、关键业务数据变更
- 后续需要追责或安全审计的后台操作

落点：

- 数据库 `audit_log` 表
- 后台“系统管理 / 操作审计”页面

处理原则：

- 审计记录只描述“谁在什么时间，从哪里，对什么资源做了什么操作，结果如何”。
- 不写入 token、密码、密钥、完整请求体等敏感信息。
- `context` 只存必要补充信息，避免把审计表变成通用日志桶。

## 数据库设计

废弃：

- `log`
- `log_type`

新增：

`audit_log`

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | integer unsigned | 主键 |
| `app_id` | integer unsigned | 应用 ID，0 表示未知 |
| `actor_id` | integer unsigned | 操作人 ID，0 表示匿名或系统 |
| `actor_name` | string(64) | 操作人名称 |
| `action` | string(64) | 操作动作，如 `login`、`create`、`update`、`delete`、`export` |
| `module` | string(64) | 业务模块，如 `auth`、`user`、`role`、`config` |
| `resource_type` | string(64) | 资源类型，如 `user`、`role`、`config` |
| `resource_id` | string(64) | 资源 ID，兼容数字 ID、字符串 key 或组合标识 |
| `title` | string(255) | 审计标题，用于列表展示 |
| `description` | string(500) | 操作摘要 |
| `method` | string(10) | HTTP 方法 |
| `path` | string(255) | 请求路径 |
| `ip` | string(64) | 请求 IP |
| `user_agent` | string(500) | 浏览器或客户端信息 |
| `status` | tinyinteger unsigned | 结果状态：1 成功，0 失败 |
| `context` | json nullable | 必要的结构化补充信息 |
| `created_at` | datetime | 创建时间 |

推荐索引：

- `created_at`
- `actor_id, created_at`
- `module, action, created_at`
- `resource_type, resource_id`
- `status, created_at`

迁移策略：

- 修改基线结构迁移，移除 `createLogTypeTable()` 和 `createLogTable()`，新增 `createAuditLogTable()`。
- 修改基线数据迁移，移除 `log_type` 初始化数据。
- 菜单初始化将“系统日志”改为“操作审计”，权限 tag 改为 `admin.system.auditLog.*`。
- 由于明确不保留历史数据，不编写 `log` 到 `audit_log` 的数据搬迁逻辑。

## 后端重构

### 模型

新增：

- `app\model\AuditLog`

移除：

- `app\model\Log`
- `app\model\LogType`

模型保持简单：

- `$table = 'audit_log'`
- `$timestamps = false`
- `$guarded = ['id']`
- `context` 使用模型 cast 转数组（如果当前 BaseModel 支持 casts，则启用；否则在服务层 JSON 编解码）

### 审计写入工具

新增：

- `app\common\utils\AuditLog`

建议 API：

- `AuditLog::record(array $data): ?AuditLogModel`
- `AuditLog::success(string $action, string $module, string $title, array $data = []): ?AuditLogModel`
- `AuditLog::fail(string $action, string $module, string $title, array $data = []): ?AuditLogModel`

设计原则：

- 自动从当前请求提取 `method`、`path`、`ip`、`user_agent`。
- 自动从 JWT 提取 `actor_id` 和 `actor_name`，允许调用方覆盖。
- 自动过滤敏感字段，如 `password`、`token`、`secret`、`access_key`、`secret_key`。
- 捕获自身异常并写入 `support\Log::error()`，不影响主业务流程。

### 服务与控制器

替换：

- `app\admin\services\LogService` -> `AuditLogService`
- `app\admin\controller\system\Log` -> `AuditLog`

接口建议：

- `GET /admin/system/auditLog/list`
- `GET /admin/system/auditLog/stat`
- `GET /admin/system/auditLog/actions`

列表筛选：

- `keyword`：匹配标题、操作人、资源 ID、请求路径
- `module`
- `action`
- `actor_id`
- `status`
- `start_time`
- `end_time`

统计：

- 按日期统计总量
- 按 `action` 或 `module` 汇总
- 不再依赖 `log_type` 颜色配置，前端使用固定 action 映射即可

### 登录审计

当前登录逻辑：

- `DbLog::login()` 写入 token 到 `value`

重构后：

- 登录成功写入 `AuditLog::success('login', 'auth', '用户登录', [...])`
- `resource_type = 'user'`
- `resource_id = 用户 ID`
- `context` 可记录登录账号、应用 ID，不记录 token

## 前端重构

替换页面：

- `admin/src/views/admin/system/log` -> `admin/src/views/admin/system/audit-log`

API 命名：

- `systemApi.log` -> `systemApi.auditLog`

菜单：

- 名称：`操作审计`
- 路由：`/admin/system/audit-log`
- 组件：`admin/system/audit-log`

页面结构：

- 顶部筛选区：关键词、模块、动作、结果、时间范围、查询、重置
- 顶部操作区：导出可后续补充，首版不强行加入
- 主体：`sc-table` 显式列
- 右侧抽屉：审计详情

列表字段：

- 时间
- 操作人
- 动作
- 模块
- 标题
- 资源
- 结果
- IP
- 请求路径

详情字段：

- 审计标题
- 操作人
- 模块 / 动作
- 资源类型 / 资源 ID
- 请求方法 / 请求路径 / IP / User-Agent
- 结果状态
- `context` 格式化展示

## 命名规范

数据库：

- 表名：`audit_log`
- 字段名：使用清晰审计语义，避免 `type_name`、`value` 这类泛化字段

PHP：

- 模型：`AuditLog`
- 服务：`AuditLogService`
- 控制器：`AuditLog`
- 工具：`app\common\utils\AuditLog`

前端：

- API 对象：`auditLog`
- 页面目录：`audit-log`
- 组件名：`auditLog`

权限：

- `admin.system.auditLog.list`
- `admin.system.auditLog.stat`
- `admin.system.auditLog.actions`

## 落地步骤

1. 调整数据库基线迁移，删除旧日志表，新增 `audit_log`。
2. 调整初始化数据，移除 `log_type`，更新菜单、路由和权限。
3. 新增 `AuditLog` 模型和写入工具，删除 `DbLog` 的数据库日志语义。
4. 将登录记录改为操作审计，并去除 token 入库。
5. 新增后台 `AuditLogService` 和控制器接口。
6. 重构前端 API 与页面，按“操作审计”展示列表、统计和详情。
7. 全仓搜索 `DbLog`、`LogService`、`systemApi.log`、`/admin/system/log`，清理旧引用。
8. 确认代码层日志全部走 `support\Log` 或框架已有日志配置。

## 取舍说明

- 不保留历史日志数据：符合“全面遗弃之前日志”的目标，避免为旧模型付出兼容成本。
- 不保留 `log_type`：操作审计不需要可配置日志级别，动作枚举在代码侧维护更简单。
- 不做异步写入：首版保持同步但吞掉审计写入异常，避免引入队列复杂度。
- 不记录完整请求参数：降低敏感信息泄露风险，也让审计数据更可读。

