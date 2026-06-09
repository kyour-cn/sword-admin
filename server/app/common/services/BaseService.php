<?php

namespace app\common\services;

class BaseService
{

    /**
     * 获取实例
     * @return static
     */
    public static function instance(): static
    {
        return new static();
    }

}