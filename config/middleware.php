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

use App\common\middleware\AccessControlMiddleware;
use App\common\middleware\ControllerMiddleware;

return [
    '' => [
        //跨域中间件
        AccessControlMiddleware::class,

        //控制器中间件
        ControllerMiddleware::class

    ]
];