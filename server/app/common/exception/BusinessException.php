<?php

namespace app\common\exception;

use app\common\utils\ResponseUtils;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * Class BusinessException
 */
class BusinessException extends \support\exception\BusinessException
{

    /**
     * @var mixed
     */
    protected $data = null {
        get {
            return $this->data;
        }
    }

    /**
     * @var bool
     */
    protected $debug = false;

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
            return ResponseUtils::json($res);
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
