<?php

namespace app\admin\service;

use app\model\system\AppModel;
use think\db\exception\DbException;

class AppService
{

    /**
     * 获取表格数据
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function getList(array $params): array
    {
        $pageSize = $params['pageSize']?? 10;

        $model = (new AppModel())
            ->order('sort');

        $list = $model->paginate($pageSize);

        return [
            'total' => $list->total(),
            'page' => $list->currentPage(),
            'pageSize' => $pageSize,
            'rows' => $list->toArray()['data']
        ];
    }

    /**
     * 编辑或新增App
     * @param array $data
     * @return AppModel
     */
    public function editOrAdd(array $data): AppModel
    {
        $model = new AppModel();
        if (!empty($data['id'])) {
            $model->where('id', $data['id'])->save($data);
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
        return AppModel::whereIn('id', $ids)
            ->delete();
    }

}