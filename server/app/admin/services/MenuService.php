<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\model\Menu;
use app\model\MenuApi;
use support\Db;

class MenuService extends BaseService
{

    /**
     * @param int $appID
     * @return array
     */
    public function getMenuFromApp(int $appID): array
    {
        $row = Menu::where('app_id', $appID)
            ->with('menuApi')
            ->get()
            ->toArray();

        return $this->recursionMenu($row, 0);
    }

    /**
     * @param array $data
     * @return Menu
     */
    public function create(array $data): Menu
    {
        $apiList = array_key_exists('apiList', $data) ? $this->formatApiList($data['apiList']) : null;

        return Db::transaction(function () use ($data, $apiList) {
            $menu = new Menu();
            $menu->fill($this->filterMenuData($data));
            $menu->save();

            if ($apiList !== null) {
                $this->syncMenuApi($menu, $apiList);
            }

            return $menu->refresh();
        });
    }

    public function update(array $data): void
    {
        $apiList = array_key_exists('apiList', $data) ? $this->formatApiList($data['apiList']) : null;

        Db::transaction(function () use ($data, $apiList) {
            $menu = Menu::where('id', (int)($data['id'] ?? 0))->lockForUpdate()->first();
            if (!$menu) {
                throw new BusinessException('菜单不存在');
            }

            $menu->fill($this->filterMenuData($data));
            $menu->save();

            if ($apiList !== null) {
                $this->syncMenuApi($menu, $apiList);
            }
        });
    }

    public function delete(array $ids): void
    {
        Menu::whereIn('id', $ids)->delete();
    }

    /**
     * 仅保留菜单表字段，避免接口权限等前端展示字段参与主表更新。
     */
    private function filterMenuData(array $data): array
    {
        $fields = ['app_id', 'pid', 'name', 'title', 'type', 'path', 'component', 'sort', 'meta'];
        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * 过滤接口权限的关联字段，app_id 和 menu_id 统一由当前菜单生成。
     */
    private function formatApiList(mixed $apiList): array
    {
        if (!is_array($apiList)) {
            throw new BusinessException('接口权限参数不正确');
        }

        $rows = [];
        foreach ($apiList as $api) {
            if (!is_array($api)) {
                throw new BusinessException('接口权限参数不正确');
            }

            $rows[] = [
                'tag' => (string)($api['tag'] ?? ''),
                'path' => (string)($api['path'] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * 覆盖菜单关联的全部接口权限，保证主表与关联表同时保存成功或回滚。
     */
    private function syncMenuApi(Menu $menu, array $apiList): void
    {
        MenuApi::where('menu_id', $menu->id)->delete();
        if ($apiList === []) {
            return;
        }

        $rows = array_map(static fn (array $api): array => [
            'app_id' => $menu->app_id,
            'menu_id' => $menu->id,
            'tag' => $api['tag'],
            'path' => $api['path'],
        ], $apiList);
        MenuApi::insert($rows);
    }

    /**
     * 递归数组 -前端组件使用格式
     * @param $arr
     * @param $pid
     * @return array
     */
    public function recursionMenu(&$arr, $pid): array
    {
        $data = [];
        foreach($arr as $value){
            if($value['pid'] == $pid){
                $menu = [
                    'pid' => $value['pid'],
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'title' => $value['title'],
                    'path' => $value['path'],
                    'component' => $value['component'],
                    'sort' => $value['sort'],
                    'meta' => json_decode($value['meta'], true),
                    'appId' => $value['app_id'],
                    'apiList' => $value['menu_api'],
                ];

                $children = $this->recursionMenu($arr, $value['id']);
                if(!empty($children)){
                    $menu['children'] = $children;
                }
                $data[] = $menu;
            }
        }
        return $data;
    }
}
