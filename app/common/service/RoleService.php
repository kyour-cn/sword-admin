<?php

namespace app\common\service;

use app\common\model\RoleModel;
use think\db\exception\DbException;

class RoleService
{

    /**
     * 获取表格数据
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function getList(array $params): array
    {
        $pageSize = $params['pageSize']??10;

        $model = (new RoleModel())
            ->order('sort');

        if(!empty($params['appid'])){
            $model = $model->where('appid', $params['appid']);
        }
        if(!empty($params['name'])){
            $model = $model->where('name', 'like', "%{$params['name']}%");
        }
        if(isset($params['is_auto'])){
            $model = $model->where('is_auto', '=', $params['is_auto']);
        }

        $list = $model->paginate($pageSize);

        return [
            'total' => $list->total(),
            'page' => $list->currentPage(),
            'pageSize' => $pageSize,
            'rows' => $list->toArray()['data']
        ];
    }

    /**
     * 新增
     * @param array $data
     * @return RoleModel
     */
    public function add(array $data): RoleModel
    {
        $model = new RoleModel();
        $model->save($data);
        return $model;
    }

    /**
     * 编辑或新增
     * @param array $data
     * @return RoleModel
     */
    public function edit(array $data): RoleModel
    {
        $model = new RoleModel();
        $model->where('id', $data['id'])
            ->save($data);
        return $model;
    }

    /**
     * 批量删除菜单
     * @param $ids
     * @return bool
     */
    public function delete($ids): bool
    {
        return RoleModel::whereIn('id', $ids)
            ->delete();
    }
}