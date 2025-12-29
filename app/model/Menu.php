<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use support\Model;

class Menu extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 关联MenuApi
     */
    public function menuApi(): HasMany
    {
        return $this->hasMany(MenuApi::class, 'menu_id', 'id');
    }

}