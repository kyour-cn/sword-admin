<?php

namespace app\middleware;

use app\common\services\AuthService;
use app\common\utils\JwtUtils;
use app\common\utils\ResponseUtils;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * JWT鉴权中间件
 * 验证JWT token并检查接口权限
 * 参照Go版本AuthJwtMiddleware实现
 */
class AuthJwtMiddleware implements MiddlewareInterface
{
    /**
     * 不需要鉴权的路由白名单
     */
    protected array $whitelist = [
        '/admin/auth/login',
        '/admin/auth/register',
        '/admin/auth/captcha',
        '/admin/site/config',
    ];

    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        // 白名单路由直接放行
        if (in_array($request->path(), $this->whitelist)) {
            return $handler($request);
        }

        try {
            // 从请求头中解析JWT token
            $claims = JwtUtils::decodeFromRequest($request);
        } catch (\Exception $e) {
            return ResponseUtils::json([
                'code'    => 401,
                'message' => 'Invalid JWT token: ' . $e->getMessage(),
                'data'    => null,
            ], 401);
        }

        // 验证接口权限
        $authService = new AuthService();
        if (!$authService->checkPath($claims, $request)) {
            return ResponseUtils::json([
                'code'    => 403,
                'message' => 'Forbidden',
                'data'    => null,
            ], 403);
        }

        // 将JWT claims存入request属性中，供后续控制器使用
        $request->jwt = $claims;

        return $handler($request);
    }

}
