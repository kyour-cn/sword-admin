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

        if(!empty($params['appid'])){
            $model = $model->where('appid', $params['appid']);
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
                    'rid' => $value['rid'],
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
            'appid' => $data['appid']?? 0,
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
        isset($data['appid']) and $menu['appid'] = $data['appid'];
        isset($data['parentId']) and $menu['pid'] = $data['parentId'];
        isset($data['name']) and $menu['name'] = $data['name'];
        isset($data['rid']) and $menu['rid'] = $data['rid'];
        isset($data['path']) and $menu['path'] = $data['path'];
        isset($data['sort']) and $menu['sort'] = $data['sort'];
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
            ->where('appid', $appId)
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
            ->field('id,rules,appid')
            ->find();
        if($role){
            $rules = explode(',', $role->rules);
            $model = $model->whereIn('rid', $rules)
                ->where('appid', $role->appid);
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