<?php

namespace app\admin\service;

use app\model\system\MenuModel;
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
        $pageSize = input('pageSize');

        $model = RoleModel::newQuery()
            ->order('sort')
            ->where('status', 1);

        $list = $model->paginate($pageSize);

        return [
            'total' => $list->total(),
            'page' => $list->currentPage(),
            'pageSize' => $pageSize,
            'rows' => $list->toArray()['data']
        ];
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