---
name: admin-crud-list-page
description: Standardize admin CRUD list pages in the sword-admin project. Use when Codex needs to create, adjust, or refactor backend/admin list pages with add, delete, search, export, import, edit, status, date filters, Element Plus tables, sc-table, or the product-management table layout style.
---

# 后台 CRUD 列表页模板

## 核心目标

将增删查改列表页统一成商品管理页风格：顶部两行工具区，第一行为筛选查询，第二行为操作按钮，主体为 `sc-table` 表格。优先参考项目现有页面，尤其是 `admin/src/views/admin/data/product/index.vue`。

## 工作流程

1. 先读取目标页面、相邻同类页面、API 定义和后端列表参数，确认已有字段、权限码和接口能力。
2. 保持现有业务逻辑、注释、弹窗组件和 API 调用方式，主要调整布局、筛选状态、表格列和样式。
3. 不主动新增测试文件、示例文件或执行测试命令；如需验证前端语法，优先使用项目已有构建命令，并说明结果。
4. 修改完成后给出一句 git message 概要。

## 标准结构

使用以下布局骨架，类名按业务前缀替换，例如 `user-page`、`user-table-header`：

```vue
<template>
  <el-container class="业务前缀-page">
    <el-header class="业务前缀-table-header">
      <div class="业务前缀-search-row">
        <!-- 搜索表单控件 -->
        <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
        <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
      </div>
      <div class="业务前缀-action-row">
        <!-- 新增、导入、批量删除、导出等操作按钮 -->
      </div>
    </el-header>
    <el-main class="nopadding">
      <sc-table ref="table" :apiObj="apiObj" :params="state.tableParams" row-key="id" @selection-change="selectionChange" stripe>
        <el-table-column type="selection" width="50"/>
        <!-- 显式表格列 -->
        <el-table-column label="操作" fixed="right" align="right" width="130">
          <template #default="scope">
            <el-button-group>
              <!-- 查看、编辑、删除 -->
            </el-button-group>
          </template>
        </el-table-column>
      </sc-table>
    </el-main>
  </el-container>
</template>
```

## 筛选区规范

- 常规关键词用 `state.search.keyword`，占位文案写清可匹配字段，例如“名称 / 编码 / 手机号”。
- 下拉筛选使用 `el-select`，日期筛选使用 `el-date-picker`；注册时间、创建时间、更新时间这类范围筛选优先使用 `type="daterange"`，并用后端已有参数名承接。
- 查询按钮调用 `upSearch`，重置按钮调用 `clearSearch`；`clearSearch` 必须同步清空 `state.search`，再 `table.value.reload(..., 1)`。
- 不臆造接口筛选字段。若后端当前不支持新增筛选参数，需要同步补服务端查询条件，或在总结中说明未改接口的原因。

## 操作区规范

- 操作区放新增、导入、批量删除、导出等列表级动作。
- 按钮文案必须明确：`新增用户`、`批量删除`、`导出`，不要只保留图标。
- 权限码沿用菜单数据或现有页面规则，例如 `v-auth="'admin.system.user.add'"`。
- 批量删除按钮使用 `:disabled="!state.selection.length"`，删除前使用 `ElMessageBox.confirm`。

## 表格规范

- 优先在模板中显式编写 `el-table-column`，便于展示头像、图片、角色、状态、金额、时间等特殊列。
- 状态列优先使用项目已有 `sc-status-indicator`。
- 图片和头像用项目工具函数处理资源地址，例如 `tool.resUrl(...)`。
- 长文本列加 `show-overflow-tooltip`，主要信息列用 `min-width`，固定操作列在右侧。
- 单行删除失败时也要展示后端错误信息，不要静默失败。

## 状态与方法规范

`state` 至少包含：

```js
const state = reactive({
  selection: [],
  search: {},
  tableParams: {}
})
```

常用方法保留项目命名：

- `selectionChange`
- `add`
- `tableEdit`
- `tableDel`
- `batchDel`
- `upSearch`
- `clearSearch`
- `exportData`
- `handleSaveSuccess`

## 样式规范

使用 scoped 样式，和商品管理页保持一致：

```css
.业务前缀-page {
  background: var(--el-bg-color);
}

.业务前缀-table-header {
  height: auto;
  display: block;
  padding: 0;
  border-bottom: 0;
}

.业务前缀-search-row,
.业务前缀-action-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
}

.业务前缀-search-row {
  flex-wrap: wrap;
  border-bottom: 1px solid var(--el-border-color-light);
}

.业务前缀-action-row {
  min-height: 44px;
}

@media (max-width: 768px) {
  .业务前缀-search-row :deep(.el-button),
  .业务前缀-action-row :deep(.el-button) {
    width: 100%;
  }

  .业务前缀-search-row,
  .业务前缀-action-row {
    align-items: stretch;
  }
}
```

为具体筛选控件补固定宽度类，例如 `.keyword-filter { width: 240px; }`、`.base-filter { width: 150px; }`，移动端统一置为 `width: 100%`。
