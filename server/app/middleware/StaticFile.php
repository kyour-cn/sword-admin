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

namespace app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * Class StaticFile
 * @package app\middleware
 */
class StaticFile implements MiddlewareInterface
{
    /**
     * 需要长期缓存的静态资源后缀。
     */
    protected array $cacheExtensions = [
        'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'
    ];

    public function process(Request $request, callable $handler): Response
    {
        $path = $request->path();

        /** @var Response $response */
        $response = $handler($request);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, $this->cacheExtensions, true)) {
            // 静态资源一般带 hash 或版本号，设置缓存可减少重复请求。
            $response->withHeaders([
                'Cache-Control' => 'public, max-age=604800',
            ]);
        }

        return $response;
    }
}
