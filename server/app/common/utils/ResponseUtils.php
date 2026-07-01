<?php declare(strict_types=1);

namespace app\common\utils;

use support\Response;

class ResponseUtils
{
    private const int JSON_FLAGS = JSON_UNESCAPED_UNICODE
        | JSON_PRETTY_PRINT
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE;

    public static function json(array $data, int $status = 200, array $headers = []): Response
    {
        $body = json_encode($data, self::JSON_FLAGS);
        if ($body === false) {
            $body = json_encode([
                'code' => 500,
                'message' => 'JSON encode error: ' . json_last_error_msg(),
                'data' => null,
            ], self::JSON_FLAGS);
        }

        return new Response($status, array_merge([
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ], $headers), $body ?: '');
    }
}
