~~# Webman

High performance HTTP Service Framework for PHP based on [Workerman](https://github.com/walkor/workerman).

# Manual (文档)

https://www.workerman.net/doc/webman

# Benchmarks （压测）

![image](https://user-images.githubusercontent.com/6073368/96447814-120fc980-1245-11eb-938d-6ea408716c72.png)

## 安装运行

安装依赖包
```bash
commposer install
```

启动
```bash
php server start
```

常驻内存启动（后台）
```bash
php server start -d
```

## 开发说明

控制器中间件

全局函数

Nginx反向代理
