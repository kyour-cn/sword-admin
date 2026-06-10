<?php

namespace app\modules\upload\config;

use app\model\Config as ConfigModel;

/**
 * 从配置中心读取上传器配置（config 表按 form_key 存储生效配置值）。
 */
class ConfigLoader
{
    public static function load(string $key): array
    {
        $value = ConfigModel::where('form_key', $key)->where('status', 1)->first();
        return $value ? self::decode($value->value) : [];
    }

    public static function decode(mixed $config): array
    {
        if (is_array($config)) {
            return $config;
        }
        if (!is_string($config) || trim($config) === '') {
            return [];
        }

        $data = json_decode($config, true);
        return is_array($data) ? $data : [];
    }
}
