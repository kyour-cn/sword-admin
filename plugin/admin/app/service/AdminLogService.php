<?php

namespace plugin\admin\app\service;

use app\exception\MsgException;
use app\model\LogLevelModel;
use app\model\LogModel;
use think\db\exception\DbException;
use think\Paginator;

class AdminLogService
{

    /**
     * 返回日志级别数量
     * @return array
     * @throws DbException
     */
    public function getLevelList(): array
    {
        $model = new LogLevelModel();
        return $model->select()->toArray();
    }

    /**
     * 获取统计数据
     * @param array $params
     * @return array
     * @throws DbException|MsgException
     */
    public function getCount(array $params): array
    {
        $where = [];
        //时间筛选
        if(!empty($params['start_time']) and !empty($params['end_time'])){
            $times = [strtotime($params['start_time']), strtotime($params['end_time'])];
            if($times[1] - $times[0] > (86400 * 365 * 12)){
                throw new MsgException('筛选时间不能大于一年');
            }
            $where[] = ['create_time', 'between', $times];
        }else{
            throw new MsgException('时间必填');
        }

        if(isset($params['level_id']) and $params['level_id'] !== ''){
            $where[] = ['level_id', '=', $params['level_id']];
        }

        return LogModel::where($where)
            ->field("from_unixtime(create_time, '%Y-%m-%d') date, count(*) count,level_name,level_id")
            ->group("from_unixtime(create_time, '%Y-%m-%d'),level_name,level_id")
            ->select()
            ->toArray();
    }

    /**
     * 获取给定时间范围内的日期列表数组
     * @param $startTime
     * @param $endTime
     * @return array
     */
    public function getDateList($startTime, $endTime): array
    {
        $date_list = [];
        while ($startTime <= $endTime) {
            $date_list[] = date('Y-m-d', $startTime);
            $startTime = strtotime('+1 day', $startTime);
        }
        return $date_list;
    }

    /**
     * 获取表格数据
     * @param array $params
     * @return Paginator
     * @throws DbException|MsgException
     */
    public function getList(array $params): Paginator
    {
        $pageSize = $params['pageSize']?? 10;
        $where = [];

        //时间筛选
        if(!empty($params['start_time']) and !empty($params['end_time'])){
            $times = [strtotime($params['start_time']), strtotime($params['end_time'])];
            if($times[1] - $times[0] > (86400 * 365 * 12)){
                throw new MsgException('筛选时间不能大于一年');
            }
            $where[] = ['create_time', 'between', $times];
        }else{
            throw new MsgException('时间必填');
        }

        if(isset($params['level_id']) and $params['level_id'] !== ''){
            if($params['level_id'] == 'system'){
                $where[] = ['level_id', '<', 10];
            }elseif($params['level_id'] == 'app'){
                $where[] = ['level_id', '>=', 10];
            }else{
                $where[] = ['level_id', '=', $params['level_id']];
            }
        }

        $model = new LogModel();
        return $model->where($where)
            ->order('id desc')
            ->paginate($pageSize);
    }

    /**
     * 新增
     * @param array $data
     * @return LogModel
     */
    public function add(array $data): LogModel
    {
        $model = new LogModel();
        $model->save($data);
        return $model;
    }

    /**
     * 编辑
     * @param array $data
     * @return LogModel
     */
    public function edit(array $data): LogModel
    {
        $model = new LogModel();
        $model->where('id', $data['id'])
            ->save($data);

        return $model;
    }

    /**
     * 批量删除
     * @param $ids
     * @return bool
     */
    public function delete($ids): bool
    {
        return LogModel::whereIn('id', $ids)
            ->delete();
    }

}