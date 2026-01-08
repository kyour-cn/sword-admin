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
        $res = new SiteService()->getConfig();
        if (empty($res)) {
            return $this->fail(1, '配置不存在');
        }

        $conf = json_decode($res->value, true);

        return $this->success(data: $conf);
    }
}
