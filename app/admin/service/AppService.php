<?php

namespace app\admin\service;

use app\model\system\AppModel;
use think\db\exception\DbException;

class AppService
{
    /**
     * 获取表格数据
     * @return array
     * @throws DbException
     */
    public function getList(): array
    {
        $pageSize = input('pageSize', 10);

        $model = AppModel::newQuery()
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

    public function edit()
    {

    }

}