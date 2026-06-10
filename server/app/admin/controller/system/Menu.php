<?php

namespace app\admin\controller\system;

use app\admin\services\MenuService;
use app\BaseController;
use app\common\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 菜单管理
 * @api
 */
class Menu extends BaseController
{
    public function list(Request $req): Response
    {
        $serv = new MenuService();

        $appID = $req->get('app_id');
        if(empty($appID)) {
            throw new BusinessException('请选择应用');
        }

        $res = $serv->getMenuFromApp($appID);
        return $this->success(data: $res);
    }

    public function add(Request $req): Response
    {
        $serv = new MenuService();
        $data = $this->formatSaveData($req->post());
        $menu = $serv->create($data);
        $menu->load('menuApi');

        $res = $menu->toArray();
        $res['menu_api'] = empty($res['menu_api']) ? null : $res['menu_api'];

        return $this->success('success', $res);
    }

    public function edit(Request $req): Response
    {
        $serv = new MenuService();
        $serv->update($this->formatSaveData($req->post()));
        return $this->success();
    }

    public function delete(Request $req): Response
    {
        $serv = new MenuService();
        $serv->delete($req->post('ids'));
        return $this->success();
    }

    private function formatSaveData(array $data): array
    {
        $meta = $data['meta'] ?? [];
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }

        $meta = array_merge([
            'title' => $data['name'] ?? '',
            'icon' => '',
            'active' => '',
            'color' => '',
            'type' => 'menu',
            'fullPage' => false,
            'tag' => '',
            'affix' => false,
            'hidden' => false,
            'hiddenBreadcrumb' => false,
        ], $meta);

        if (is_array($data['pid'] ?? null)) {
            $data['pid'] = empty($data['pid']) ? 0 : end($data['pid']);
        }
        $data['pid'] = empty($data['pid']) ? 0 : (int)$data['pid'];
        $data['sort'] = is_numeric($data['sort'] ?? null) ? (int)$data['sort'] : 0;
        $data['title'] = $meta['title'];
        $data['type'] = $meta['type'];
        $data['meta'] = json_encode($meta, JSON_UNESCAPED_UNICODE);

        unset($data['apiList'], $data['menu_api'], $data['children'], $data['appId']);

        return $data;
    }
}
