<?php

namespace app\exception;

use Exception;
use Throwable;

/**
 * 响应消息异常类
 */
class MsgException extends Exception
{

    /**
     * @var mixed 响应数据
     */
    protected mixed $data = [];

    /**
     * 默认Code为1
     * @param $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message, int $code = 1, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return MsgException
     */
    public function setData(mixed $data): MsgException
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 立马抛出该异常
     * @return void
     * @throws MsgException
     */
    public function throw(): void
    {
        throw $this;
    }

}