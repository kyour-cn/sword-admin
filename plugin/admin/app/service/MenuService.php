<?php

namespace plugin\admin\app\service;

use app\common\model\RoleModel;
use plugin\admin\app\model\MenuModel;
use think\db\exception\DbException;

class MenuService
{

    /**
     * 获取数据
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $model = (new MenuModel())
            ->order('sort asc, id asc')
            ->where('status', 1);

        if(!empty($params['app_id'])){
            $model = $model->where('app_id', $params['app_id']);
        }

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
                    'rule_id' => $value['rule_id'],
                    'component' => $value['component'],
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
    public function add($data): MenuModel
    {
        $model = new MenuModel();

        //新增
        $menu = [
            'app_id' => $data['app_id']?? 0,
            'pid' => $data['parentId']?: 0,
            'name' => $data['name'],
            'path' => $data['path'],
            'component' => $data['component'],
            "meta" => json_encode($data['meta'], JSON_UNESCAPED_UNICODE),
            'title' => $data['meta']['title'],
        ];
        $model->save($menu);

        return $model;
    }

    /**
     * 修改菜单
     */
    public function edit($data): MenuModel
    {
        $model = new MenuModel();

        $menu = [];
        empty($data['parentId']) and $data['parentId'] = 0;
        is_array($data['parentId']) and $data['parentId'] = $data['parentId'][count($data['parentId']) -1]; //适配编辑页
        isset($data['app_id']) and $menu['app_id'] = $data['app_id'];
        isset($data['parentId']) and $menu['pid'] = $data['parentId'];
        isset($data['name']) and $menu['name'] = $data['name'];
        isset($data['rule_id']) and $menu['rule_id'] = $data['rule_id'];
        isset($data['path']) and $menu['path'] = $data['path'];
        isset($data['sort']) and $menu['sort'] = $data['sort'];
        isset($data['type']) and $menu['type'] = $data['type'];
        isset($data['component']) and $menu['component'] = $data['component'];
        if(isset($data['meta'])){
            $menu['meta'] = json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
            $menu['title'] = $data['meta']['title'];
        }

        $model->where('id', $data['id'])
            ->save($menu);

        return $model;
    }

    /**
     * 批量删除菜单
     * @param $ids
     * @return bool
     */
    public function delete($ids): bool
    {
        return MenuModel::whereIn('id', $ids)
            ->delete();
    }

    /**
     * 获取超级管理员的后台菜单
     * @param int $appId 应用ID
     * @param array $exclude 需要排除掉的菜单
     * @return array
     */
    public function getRootMenu(int $appId, array $exclude = []): array
    {
        $model = new MenuModel();

        //需要排除掉的菜单
        if($exclude)
            $model = $model->whereNotIn('id', $exclude);

        $list = $model->order('sort')
            ->where('status', 1)
            ->where('app_id', $appId)
            ->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

    /**
     * 获取用户的后台菜单
     * @param int $roleId 角色ID
     * @param array $exclude 需要排除掉的菜单
     * @return array
     * @throws DbException
     */
    public function getUserMenu(int $roleId, array $exclude = []): array
    {
        $model = new MenuModel();

        //取出role的权限树
        $role = RoleModel::where('id', $roleId)
            ->where('status', 1)
            ->field('id,rules,app_id')
            ->find();
        if($role){
            $rules = explode(',', $role->rules);
            $rules[] = 0; //权限为0的菜单无需验证权限
            $model = $model->whereIn('rule_id', $rules)
                ->where('app_id', $role->app_id);
        }else{
            return [];
        }

        //需要排除掉的菜单
        if($exclude)
            $model = $model->whereNotIn('id', $exclude);

        $list = $model->order('sort')
            ->where('status', 1)
            ->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

}