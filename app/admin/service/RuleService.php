<?php declare (strict_types = 1);

namespace app\admin\service;

use app\model\system\RuleModel;

class RuleService
{

    /**
     * 获取数据
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $model = RuleModel::newQuery()
            ->order('sort')
            ->where('status', 1);

        if(!empty($params['appid'])){
            $model->where('appid', $params['appid']);
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
     * 创建、修改菜单
     */
    public function editOrAdd($data): RuleModel
    {
        $model = new RuleModel();

        if(!empty($data['id'])){
            $menu = [];
            empty($data['parentId']) and $data['parentId'] = 0;
            is_array($data['parentId']) and $data['parentId'] = $data['parentId'][0]; //适配编辑页
            isset($data['appid']) and $menu['appid'] = $data['appid'];
            isset($data['parentId']) and $menu['pid'] = $data['parentId'];
            isset($data['name']) and $menu['name'] = $data['name'];
            isset($data['path']) and $menu['path'] = $data['path'];
            isset($data['sort']) and $menu['sort'] = $data['sort'];

            $model->where('id', $data['id'])->update($menu);
        }else{
            //新增
            $menu = [
                'appid' => $data['appid']?? 0,
                'pid' => $data['parentId']?: 0,
                'name' => $data['name'],
                'path' => $data['path']
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
    public function delete($ids): bool
    {
        return RuleModel::newQuery()
            ->whereIn('id', $ids)
            ->delete();
    }
}