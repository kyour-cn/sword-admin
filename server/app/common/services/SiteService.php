<?php declare(strict_types=1);

namespace app\common\services;

use app\admin\services\ConfigService;

class SiteService extends BaseService
{

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return ConfigService::instance()->getByKey('site');
    }

}
