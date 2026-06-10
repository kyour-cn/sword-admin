<?php

namespace app\process;

use Webman\App;
use Workerman\Connection\TcpConnection;

class Http extends App
{
    /**
     * 目录访问默认尝试响应 public 目录下的 index.html。
     *
     * 例如 / 会映射到 public/index.html，/admin/ 会映射到 public/admin/index.html。
     */
    protected static function findFile(TcpConnection $connection, string $path, string $key, $request): bool
    {
        if ($path === '/' || str_ends_with($path, '/')) {
            $path = rtrim($path, '/') . '/index.html';
        }

        return parent::findFile($connection, $path, $key, $request);
    }
}
