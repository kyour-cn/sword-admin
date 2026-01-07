<?php declare(strict_types=1);

namespace app\common\service;

use app\model\Config;

class SiteService
{

    /**
     * @return Config|null
     */
    public function getConfig(): ?Config
    {
        return Config::where('key', 'site')->first();
    }

}