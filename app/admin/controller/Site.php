<?php


namespace app\admin\controller;

use app\BaseController;
use app\common\service\SiteService;
use support\Request;
use support\Response;

/**
 * @api
 */
class Site extends BaseController
{
    public function config(Request $req): Response
    {
        $res = new SiteService()->getConfig();
        if(empty($res)){
            return $this->withData(1, '配置不存在');
        }

        $conf = json_decode($res->value, true);

        return $this->withData(0, '', $conf);
    }
}
