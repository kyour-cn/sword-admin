<?php

namespace app\exception;

use Throwable;

class MsgException extends \Exception
{

    private $data = [];

    /**
     * 默认Code为1
     * @param $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message, int $code = 1, Throwable $previous = null)
    {
        parent::__construct($message, $code);
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
    public function setData(array $data)
    {
        $this->data = $data;
    }

}