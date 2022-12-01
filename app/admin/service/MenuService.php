<?php

namespace app\admin\service;

use app\model\MenuModel;

class MenuService
{
    public function getList()
    {
        $model = MenuModel::newQuery()
            ->order('sort')
            ->where('status', 1);

        $list = $model->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

    /**
     * 递归数组 -前端组件使用格式
     * @param $arr
     * @param $pid
     * @return array
     */
    public static function recursionMenu(&$arr, $pid): array
    {
        $data = [];
        foreach($arr as $id => $value){
            if($value['pid'] == $pid){

                $menu = [
                    'parentId' => $value['pid'],
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'path' => $value['path'],
                    'component' => $value['component']??'',
                    'sort' => $value['sort'],
                    'meta' => json_decode($value['meta'], true)
                ];

                $children = self::recursionMenu($arr, $id);
                if(count($children)){
                    $menu['children'] = $children;
                }
                $data[] = $menu;
            }
        }
        return $data;
    }

    /**
     * 创建菜单
     */
    public function createMenu($data)
    {
        $model = new MenuModel();

        if(!empty($data['id'])){

            $menu = [];
            empty($data['parentId']) and $data['parentId'] = 0;
            is_array($data['parentId']) and $data['parentId'] = $data['parentId'][0];
            isset($data['parentId']) and $menu['pid'] = $data['parentId'];
            isset($data['name']) and $menu['name'] = $data['name'];
            isset($data['path']) and $menu['path'] = $data['path'];
            isset($data['component']) and $menu['component'] = $data['component'];
            isset($data['component']) and $menu['component'] = $data['component'];
            if(isset($data['meta'])){
                $menu['meta'] = json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
                $menu['title'] = $data['meta']['title'];
            }

            $model->where('id', $data['id'])->update($menu);
        }else{

            $menu = [
                'pid' => $data['parentId']?: 0,
                'name' => $data['name'],
                'path' => $data['path'],
                'component' => $data['component'],
                "meta" => json_encode($data['meta'], JSON_UNESCAPED_UNICODE),
                'title' => $data['meta']['title'],
//                'type' => $data['meta']['type'],
//                'icon' => $data['meta']['icon']?? '',
//                'hidden' => (int) ($data['meta']['hidden']?? 0)
            ];
            $model->save($menu);
        }

        return $model;
    }

    /**
     * 批量删除菜单
     * @param $ids
     * @return bool
     */
    public function deleteMenu($ids): bool
    {
        return MenuModel::newQuery()
            ->whereIn('id', $ids)
            ->delete();
    }
}