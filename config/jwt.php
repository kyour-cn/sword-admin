<?php

return [
    'secret' => getenv('JWT_SECRET') ?: 'sword-admin-2025',
    'alg'    => getenv('JWT_ALG') ?: 'HS256',
    'exp'    => getenv('JWT_EXP') ?: 86400
];