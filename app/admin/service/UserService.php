<?php

namespace app\admin\service;

use app\exception\MsgException;
use app\model\system\RoleModel;
use app\model\system\UserModel;
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
        $pageSize = input('pageSize', 10);

        $model = UserModel::newQuery();

        $list = $model->paginate($pageSize)->each(function($item){
            $item['role_name'] = RoleModel::where('id', $item['role'])->value('name');
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
     * 编辑或新增
     * @param array $data
     * @return UserModel
     * @throws \Exception
     */
    public function editOrAdd(array $data): UserModel
    {
        //密码转md5
        if(!empty($data['password'])){
            $data['password'] = md5($data['password']);
        }

        $model = new UserModel();
        if (!empty($data['id'])) {

            //验证用户是否已存在
            if($check = $this->checkExits($data, $data['id'])){
                throw new MsgException($check['field']. '已存在，请检测数据是否正确：');
            }

            $model->where('id', $data['id'])
                ->save($data);
        }else{

            //验证用户是否已存在
            if($check = $this->checkExits($data)){
                throw new MsgException('用户已存在，请勿重复添加：'. $check['field']);
            }

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