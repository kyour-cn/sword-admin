<?php

namespace app\admin\service;

use app\model\MenuModel;
use app\model\RoleModel;

class RoleService
{
    public function getList()
    {
        $pageSize = input('pageSize');

        $model = RoleModel::newQuery()
            ->order('sort')
            ->where('status', 1);

        $list = $model->paginate($pageSize);

        return [
            'total' => $list->total(),
            'page' => $list->currentPage(),
            'pageSize' => $pageSize,
            'row' => $list->listRows()
        ];
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

            if(isset($data['meta'])){
                isset($data['meta']['title']) and $menu['title'] = $data['meta']['title'];
                isset($data['meta']['icon']) and $menu['icon'] = $data['meta']['icon'];
                isset($data['meta']['type']) and $menu['type'] = $data['meta']['type'];
                isset($data['meta']['hidden']) and $menu['hidden'] = $data['meta']['hidden'];
            }
            $model->where('id', $data['id'])->find();
            $model->save($menu);
        }else{

            $menu = [
                'pid' => $data['parentId']?: 0,
                'name' => $data['name'],
                'hidden' => (int) ($data['meta']['hidden']?? 0)
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