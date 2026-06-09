<?php

namespace app\model;

use DateTimeInterface;
use support\Model;

/**
 * 基础模型
 */
class BaseModel extends Model
{

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The storage format of the model's date columns.
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Prepare a date for array / JSON serialization.
     * @param  DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat);
    }

}