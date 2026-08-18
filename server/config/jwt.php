<?php

$debug = filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
$secret = trim((string)(getenv('JWT_SECRET') ?: ''));
// 生产环境拒绝短密钥，开发环境仅保留便于本地启动的兜底值。
if (!$debug && strlen($secret) < 32) {
    throw new RuntimeException('非调试环境必须配置至少32个字符的 JWT_SECRET');
}

return [
    'secret' => $secret !== '' ? $secret : 'development-only-jwt-secret-change-me',
    'alg'    => getenv('JWT_ALG') ?: 'HS256',
    'exp'    => getenv('JWT_EXP') ?: 86400
];
