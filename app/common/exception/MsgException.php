<?php

namespace app\common\exception;

use Throwable;

class MsgException extends \Exception
{

    private array $data = [];

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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): MsgException
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 立马抛出该异常
     * @return void
     * @throws MsgException
     */
    public function throw()
    {
        throw $this;
    }

}