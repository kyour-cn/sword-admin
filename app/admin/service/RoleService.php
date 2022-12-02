<?php

namespace app\admin\service;

use app\model\system\RoleModel;
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
        $pageSize = input('pageSize', 10);

        $model = RoleModel::newQuery()
            ->order('sort');

        if(!empty($params['appid'])){
            $model->where('appid', $params['appid']);
        }
        if(!empty($params['name'])){
            $model->where('name', 'like', "%{$params['name']}%");
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
     * 编辑或新增
     * @param array $data
     * @return RoleModel
     */
    public function editOrAdd(array $data): RoleModel
    {
        $model = new RoleModel();
        if (!empty($data['id'])) {
            $model->where('id', $data['id'])->update($data);
        }else{
            $model->save($data);
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
        return RoleModel::newQuery()
            ->whereIn('id', $ids)
            ->delete();
    }
}