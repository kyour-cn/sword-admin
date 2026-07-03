
基于PHP和Vue的管理后台系统
===============

## admin 自动发布

项目根目录提供 `Taskfile.yml`，用于编译 `admin` 并发布到 `server/public`。

使用前需要安装 `task`、`node`、`pnpm`、`git`。

常用命令：

```bash
# 安装依赖、编译并发布 admin
task admin:release

# 只发布已编译好的 admin/dist 文件
task admin:publish

# 检测远端更新，有更新时自动拉取代码并发布
task deploy:auto
```

`task deploy:auto` 默认检测 `origin/HEAD` 指向的分支，如需指定其他分支：

```bash
BRANCH=main task deploy:auto
```

Linux 服务器可使用 crontab 定时检测更新：

```bash
* * * * * cd /www/sword-admin && task deploy:auto >> /var/log/sword-admin-deploy.log 2>&1
```

Windows 开发环境可直接在项目根目录执行 `task admin:release`，或通过任务计划程序定时执行 `task deploy:auto`。
