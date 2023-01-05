<?php

namespace app\common\service;

use app\common\exception\MsgException;
use app\common\model\RoleModel;
use app\common\model\UserModel;
use think\db\exception\DbException;

class UserService
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

        $model = new UserModel();

        if(!empty($params['keyword'])){
            $model = $model->where('realname|username|mobile', 'like', "%{$params['keyword']}%");
        }

        $list = $model->order('id', 'desc')
            ->paginate($pageSize)
            ->each(function($item){
                $item['role_name'] = RoleModel::where('id', $item['role_id'])->value('name');
                return $item;
            });

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
     * @return UserModel
     * @throws \Exception
     */
    public function add(array $data): UserModel
    {
        //密码转md5
        if(!empty($data['password'])){
            $data['password'] = md5($data['password']);
        }else{
            unset($data['password']);
        }

        $model = new UserModel();
        //验证用户是否已存在
        if($check = $this->checkExits($data)){
            throw new MsgException('用户已存在，请勿重复添加：'. $check['field']);
        }

        $model->save($data);
        return $model;
    }

    /**
     * 编辑或新增
     * @param array $data
     * @return UserModel
     * @throws \Exception
     */
    public function edit(array $data): UserModel
    {
        //密码转md5
        if(!empty($data['password'])){
            $data['password'] = md5($data['password']);
        }else{
            unset($data['password']);
        }

        $model = new UserModel();

        //验证用户是否已存在
        if($check = $this->checkExits($data, $data['id'])){
            throw new MsgException($check['field']. '已存在，请检测数据是否正确：');
        }

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
        if(is_array($ids)){
            foreach ($ids as $id){
                UserModel::destroy($id);
            }
        }else{
            UserModel::destroy($ids);
        }
        return true;
    }

    /**
     * 判断用户是否已存在
     * @param array $param 检测数据
     * @param int|null $uid 修改用户时传入uid
     * @return ?array
     */
    public function checkExits(array $param, int $uid = null): ?array
    {
        if($uid){
            if(!empty($param['username'])){
                $check = UserModel::where('username', $param['username'])
                    ->where('id', '<>', $uid) //非本用户
                    ->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'username'];
            }

            if(!empty($param['mobile'])){
                $check = UserModel::where('mobile', $param['mobile'])
                    ->where('id', '<>', $uid) //非本用户
                    ->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'mobile'];
            }
        }else{
            if(!empty($param['username'])){
                $check = UserModel::where('username', $param['username'])->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'username'];
            }

            if(!empty($param['mobile'])){
                $check = UserModel::where('mobile', $param['mobile'])->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'mobile'];
            }
        }

        return null;
    }
}