<?php

namespace app\common\utils;

use Ahc\Jwt\JWT;
use app\common\exception\BusinessException;
use Webman\Http\Request;

class JwtUtils
{
    /**
     * 统一创建 JWT 实例，调用方可覆盖默认过期时间以匹配访问令牌 TTL。
     */
    protected static function instance(?int $expire = null): JWT
    {
        return new JWT(
            config('jwt.secret'),      // 密钥
            config('jwt.alg'),         // 算法
            $expire ?? config('jwt.exp')          // 过期时间（秒）
        );
    }

    /**
     * 生成 token
     * @param array $payload
     * @return string
     */
    public static function encode(array $payload, ?int $expire = null): string
    {
        return self::instance($expire)->encode($payload);
    }

     /**
      * 解析 token
      * @param string $token
      * @return array
      */
    public static function decode(string $token): array
    {
        return self::instance()->decode($token);
    }

    /**
     * 从请求中解析 token
     * @param Request $request
     * @return array
     */
    public static function decodeFromRequest(Request $request): array
    {
        $authorization = trim((string)$request->header('Authorization', ''));
        if (!preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)) {
            throw new BusinessException('访问令牌格式不正确');
        }
        $claims = self::decode($matches[1]);
        if (!isset($claims['id']) && isset($claims['sub'])) {
            // 兼容仍读取 id 的既有服务；id 不写入 JWT，只在解析结果中提供别名。
            $claims['id'] = (int)$claims['sub'];
        }
        return $claims;
    }
}
