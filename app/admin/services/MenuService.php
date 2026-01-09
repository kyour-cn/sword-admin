<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\services\BaseService;
use app\model\Menu;

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
     * @return void
     */
    public function create(array $data): void
    {
        $app = new Menu();
        $app->fill($data);
        $app->save();
    }

    public function update(array $data): void
    {
        $app = Menu::find($data['id']);
        $app->fill($data);
        $app->save();
    }

    public function delete(array $ids): void
    {
        Menu::whereIn('id', $ids)->delete();
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