<?php

namespace App\common\service;

use App\common\exception\MsgException;
use App\common\model\LogLevelModel;
use App\common\model\LogModel;
use think\db\exception\DbException;
use think\facade\Cache;
use Tinywan\Jwt\JwtToken;

class LogService
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
//            $date_list[date('Y-m-d', $startTime)] = date('w', $startTime);
            $startTime = strtotime('+1 day', $startTime);
        }

        return $date_list;
    }

    /**
     * 获取表格数据
     * @param array $params
     * @return array
     * @throws DbException|MsgException
     */
    public function getList(array $params): array
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
            $where[] = ['level_id', '=', $params['level_id']];
        }

        $model = new LogModel();
        $list = $model->where($where)
            ->order('id desc')
            ->paginate($pageSize);

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

    /**
     * 获取指定别名的日志级别
     * @param string $label 日志级别英文别名
     * @param int $expire 缓存过期时间
     * @return ?array
     * @throws \Throwable
     */
    public static function getLevel(string $label, int $expire = 3600) :?array
    {
        $cacheKey = __METHOD__. ":$label";
        return Cache::remember($cacheKey, function () use($label){
            $data = LogLevelModel::where('label', $label)->find();
            if(!$data) return null;
            return $data->toArray();
        }, $expire);
    }

    /**
     * @param array $data
     * @return LogModel
     */
    private static function writeLog(array $data): LogModel
    {
        $request = request();
        //自动填充请求来源信息
        if(empty($data['request_source'])){
            if($request){
                $data['request_source'] = UtilsService::getRequestPath($request);
            }else{
                $traces = UtilsService::getCallFunc(3);
                $data['request_source'] = $traces;
            }
        }

        //填充用户数据
        try{ //避免未登录报错
            if($request and (new AuthService)->checkLogin()){
                if($userId = JwtToken::getExtendVal('id')){
                    $data['request_user_id'] = $userId;
                }
                if($userName = JwtToken::getExtendVal('name')){
                    $data['request_user'] = $userName;
                }
            }
        }catch (\Throwable $e){}

        //填充IP地址
        if($request and $ip = $request->getRealIp()){
            $data['request_ip'] = $ip;
        }

        $model = new LogModel();
        $model->save($data);
        return $model;
    }

    /**
     * 记录日志: debug
     * @param string|array $titleOrData 标题或参数
     * @param string $value 日志数据
     * @param string $valueType 数据类型 text|json
     * @return LogModel
     * @throws DbException|\Throwable
     */
    public static function debug($titleOrData, string $value = '', string $valueType = 'text'): LogModel
    {
        $level = self::getLevel('debug');
        $data = [
            'level_id' => $level['id'],
            'level_name' => $level['name'],
            'title' => $titleOrData,
            'value' => $value,
            'value_type' => $valueType
        ];
        if(is_array($titleOrData)){
            $data = array_merge($data, $titleOrData);
        }
        return self::writeLog($data);
    }

    /**
     * 记录日志: info
     * @param string|array $titleOrData 标题或参数
     * @param string $value 日志数据
     * @param string $valueType 数据类型 text|json
     * @return LogModel
     * @throws DbException|\Throwable
     */
    public static function info($titleOrData, string $value = '', string $valueType = 'text'): LogModel
    {
        $level = self::getLevel('info');
        $data = [
            'level_id' => $level['id'],
            'level_name' => $level['name'],
            'title' => $titleOrData,
            'value' => $value,
            'value_type' => $valueType
        ];
        if(is_array($titleOrData)){
            $data = array_merge($data, $titleOrData);
        }
        return self::writeLog($data);
    }

    /**
     * 记录日志: warn
     * @param string|array $titleOrData 标题或参数
     * @param string $value 日志数据
     * @param string $valueType 数据类型 text|json
     * @return LogModel
     * @throws DbException|\Throwable
     */
    public static function warn($titleOrData, string $value = '', string $valueType = 'text'): LogModel
    {
        $level = self::getLevel('warn');
        $data = [
            'level_id' => $level['id'],
            'level_name' => $level['name'],
            'title' => $titleOrData,
            'value' => $value,
            'value_type' => $valueType
        ];
        if(is_array($titleOrData)){
            $data = array_merge($data, $titleOrData);
        }
        return self::writeLog($data);
    }

    /**
     * 记录日志: error
     * @param string|array $titleOrData 标题或参数
     * @param string $value 日志数据
     * @param string $valueType 数据类型 text|json
     * @return LogModel
     * @throws DbException|\Throwable
     */
    public static function error($titleOrData, string $value = '', string $valueType = 'text'): LogModel
    {
        $level = self::getLevel('error');
        $data = [
            'level_id' => $level['id'],
            'level_name' => $level['name'],
            'title' => $titleOrData,
            'value' => $value,
            'value_type' => $valueType
        ];
        if(is_array($titleOrData)){
            $data = array_merge($data, $titleOrData);
        }
        return self::writeLog($data);
    }

    /**
     * 记录日志: falal
     * @param string|array $titleOrData 标题或参数
     * @param string $value 日志数据
     * @param string $valueType 数据类型 text|json
     * @return LogModel
     * @throws DbException|\Throwable
     */
    public static function falal($titleOrData, string $value = '', string $valueType = 'text'): LogModel
    {
        $level = self::getLevel('falal');
        $data = [
            'level_id' => $level['id'],
            'level_name' => $level['name'],
            'title' => $titleOrData,
            'value' => $value,
            'value_type' => $valueType
        ];
        if(is_array($titleOrData)){
            $data = array_merge($data, $titleOrData);
        }
        return self::writeLog($data);
    }

    /**
     * 记录应用日志
     * @param string $name 日志别名(label)
     * @param string|array $titleOrData 标题或参数
     * @param string $value 日志数据
     * @param string $valueType 数据类型 text|json
     * @return LogModel
     * @throws DbException|\Throwable
     */
    public static function app(string $name, $titleOrData, string $value = '', string $valueType = 'text'): LogModel
    {
        $level = self::getLevel($name);
        $data = [
            'level_id' => $level['id'],
            'level_name' => $level['name'],
            'title' => $titleOrData,
            'value' => $value,
            'value_type' => $valueType
        ];
        if(is_array($titleOrData)){
            $data = array_merge($data, $titleOrData);
        }
        return self::writeLog($data);
    }
}