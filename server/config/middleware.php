<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    // 全局中间件
    '' => [
        // ... 这里省略其它中间件
        app\middleware\AccessControlMiddleware::class,
    ],
    // admin应用中间件
    'admin' => [
        // 顺序不可调整：先建立认证上下文，再计算接口权限和数据范围，最后记录业务审计。
        app\middleware\AuthJwtMiddleware::class,
        app\middleware\AuditLogMiddleware::class,
    ]
];
