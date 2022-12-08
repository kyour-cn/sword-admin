<?php declare (strict_types = 1);

namespace app\common\service;

use app\common\model\RuleModel;

class RuleService
{

    /**
     * 获取数据
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $model = (new RuleModel())
            ->order('sort asc, id asc');

        if(!empty($params['appid'])){
            $model = $model->where('appid', $params['appid']);
        }

        $list = $model->column('*','id');

        return self::recursionRule($list, 0);
    }

    /**
     * 递归数组
     * @param $arr
     * @param $pid
     * @return array
     */
    public static function recursionRule(&$arr, $pid): array
    {
        $data = [];
        foreach($arr as $id => $value){
            if($value['pid'] == $pid){

                $menu = [
                    'parentId' => $value['pid'],
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'alias' => $value['alias'],
                    'path' => $value['path'],
                    'sort' => $value['sort'],
                    'status' => $value['status'],
                ];

                $children = self::recursionRule($arr, $id);
                if(count($children)){
                    $menu['children'] = $children;
                }
                $data[] = $menu;
            }
        }
        return $data;
    }

    /**
     * 新增菜单
     */
    public function add($data): RuleModel
    {
        $model = new RuleModel();

        //新增
        $menu = [
            'appid' => $data['appid']?? 0,
            'pid' => $data['parentId']?: 0,
            'name' => $data['name'],
            'path' => $data['path']
        ];
        $model->save($menu);

        return $model;
    }

    /**
     * 修改菜单
     */
    public function edit($data): RuleModel
    {
        $model = new RuleModel();

        $menu = [];
        empty($data['parentId']) and $data['parentId'] = 0;
        is_array($data['parentId']) and $data['parentId'] = $data['parentId'][count($data['parentId']) -1]; //适配编辑页
        isset($data['appid']) and $menu['appid'] = $data['appid'];
        isset($data['parentId']) and $menu['pid'] = $data['parentId'];
        isset($data['name']) and $menu['name'] = $data['name'];
        isset($data['alias']) and $menu['alias'] = $data['alias'];
        isset($data['path']) and $menu['path'] = $data['path'];
        isset($data['sort']) and $menu['sort'] = $data['sort'];

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
        return RuleModel::whereIn('id', $ids)
            ->delete();
    }
}