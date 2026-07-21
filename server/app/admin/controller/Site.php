<?php


namespace app\admin\controller;

use app\BaseController;
use app\common\services\SiteService;
use support\Response;

/**
 * @api
 */
class Site extends BaseController
{
    public function config(): Response
    {
        $conf = (new SiteService())->getConfig();
        if (empty($conf)) {
            return $this->fail(1, '配置不存在');
        }

        return $this->success(data: $conf);
    }
}
