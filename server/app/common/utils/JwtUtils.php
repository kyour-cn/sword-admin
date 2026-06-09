<?php

namespace app\common\utils;

use Ahc\Jwt\JWT;
use app\common\exception\BusinessException;
use Webman\Http\Request;

class JwtUtils
{
    protected static function instance(): JWT
    {
        return new JWT(
            config('jwt.secret'),      // 密钥
            config('jwt.alg'),         // 算法
            config('jwt.exp')          // 过期时间（秒）
        );
    }

    /**
     * 生成 token
     * @param array $payload
     * @return string
     */
    public static function encode(array $payload): string
    {
        return self::instance()->encode($payload);
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
        $token = $request->header('Authorization');
        if(empty($token)){
            throw new BusinessException('token不能为空');
        }
        $token = str_replace('Bearer ', '', $token);
        return self::decode($token);
    }
}