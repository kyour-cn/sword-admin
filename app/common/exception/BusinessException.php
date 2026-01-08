<?php

namespace app\common\exception;

use RuntimeException;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * Class BusinessException
 */
class BusinessException extends RuntimeException
{

    /**
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * @var bool
     */
    protected bool $debug = false;

    /**
     * Render an exception into an HTTP response.
     * @param Request $request
     * @return Response|null
     */
    public function render(Request $request): ?Response
    {
        if ($request->expectsJson()) {
            $code = $this->getCode();
            $res = ['code' => $code ?: 500, 'message' => $this->getMessage(), 'data' => $this->data];
            return new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
            ], json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        return new Response(200, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ], $this->getMessage());
    }

    /**
     * Set data.
     * @param array|null $data
     * @return $this
     */
    public function data(mixed $data = null): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data.
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set debug.
     * @param bool|null $value
     * @return $this|bool
     */
    public function debug(?bool $value = null): bool|static
    {
        if ($value === null) {
            return $this->debug;
        }
        $this->debug = $value;
        return $this;
    }

    /**
     * Translate message.
     * @param string $message
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    protected function trans(string $message, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $args = [];
        foreach ($parameters as $key => $parameter) {
            $args[":$key"] = $parameter;
        }
        try {
            $message = trans($message, $args, $domain, $locale);
        } catch (Throwable) {
        }
        foreach ($parameters as $key => $value) {
            $message = str_replace(":$key", $value, $message);
        }
        return $message;
    }

}
