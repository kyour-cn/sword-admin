<?php
/**
 * 自定义的全局助手函数定义文件
 * 该文件将在composer自动加载时执行
 * @author @kyour
 */

/**
 * 获取env配置信息
 * @param string $name
 * @param $default
 * @return array|false|mixed|string|null
 */
function env(string $name, $default = null) {
    $value = getenv($name);
    return $value?: $default;
}
