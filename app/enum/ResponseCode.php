<?php

namespace app\enum;

use app\exception\MsgException;

/**
 * 响应状态码
 */
enum ResponseCode: int
{
    //==============通用错误码================

    /**
     * 成功
     */
    case Success = 0;

    /**
     * 失败
     */
    case Error = 1;

    //==============系统级别错误码================

    /**
     * Token验证失败
     */
    case TokenError = 510;

    /**
     * Token过期
     */
    case TokenExpired = 511;

    /**
     * 无接口权限
     */
    case NoPermission = 512;

    /**
     * 数据加密失败
     */
    case EncryptError = 520;

    /**
     * 数据解密失败
     */
    case DecryptError = 521;

    //==============应用级别错误码================


    /**
     * 获取错误码对应的错误信息
     * @param ResponseCode $type
     * @return string
     */
    public static function getName(self $type): string
    {
        return match ($type) {
            self::Success => '成功',
            self::Error => '失败',

            self::TokenError => 'Token验证失败',
            self::TokenExpired => 'Token过期',
            self::NoPermission => '无接口权限',
            self::EncryptError => '数据加密失败',
            self::DecryptError => '数据解密失败',

        };
    }

    /**
     * 通过值获取错误信息
     * @param int $value
     * @return string
     */
    public static function getNameByValue(int $value): string
    {
        return self::getName(self::from($value));
    }

    /**
     * 将错误代码创建为 MsgException
     * @param ResponseCode $type
     * @param mixed $data
     * @return MsgException
     */
    public static function makeException(self $type, mixed $data = []): MsgException
    {
        $exception = new MsgException(self::getName($type), $type->value);
        $exception->setData($data);
        return $exception;
    }
}
