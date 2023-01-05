<?php

namespace app\common\service;

use app\common\model\AppModel;
use think\facade\Cache;
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
     * 获取指定应用的id
     * @param string $key
     * @param int $expire 缓存过期时间
     * @return mixed
     * @throws \Throwable
     */
    public static function getId(string $key, int $expire = 3600)
    {
        $cacheKey = __METHOD__. ":{$key}_{$expire}";
        return Cache::remember($cacheKey, function () use($key){
            return AppModel::where('key', $key)->value('id');
        }, $expire);
    }

}