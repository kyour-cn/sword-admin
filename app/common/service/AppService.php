<?php

namespace app\common\service;

use app\common\model\AppModel;
use think\db\exception\DbException;
use think\facade\Cache;

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
     * 新增App
     * @param array $data
     * @return AppModel
     */
    public function add(array $data): AppModel
    {
        $model = new AppModel();
        $model->save($data);
        return $model;
    }

    /**
     * 编辑App
     * @param array $data
     * @return AppModel
     */
    public function edit(array $data): AppModel
    {
        $model = new AppModel();
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
        return AppModel::whereIn('id', $ids)
            ->delete();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function getId(string $key)
    {
        //TODO: 应使用缓存，避免频繁的Model查询
//        $cacheKey = __METHOD__. "_{$key}";
//        $cache = Cache::get($cacheKey);
        return AppModel::where('key', $key)->value('id');
    }

}