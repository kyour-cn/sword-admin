<?php

namespace app\common\service;

use app\model\User;
use support\Cache;

class AuthService
{

    /**
     * @param string $username
     * @param string $password
     * @return User
     * @throws \Exception
     */
    public function login(string $username, string $password): User
    {
        $key = "login_lock_$username";
        $lock = Cache::get($key);
        if (!$lock) {
            $lock = 0;
        }

        // 10秒登录失败次数超过3次，禁止登录
        if ($lock > 3) {
            throw new \Exception('登录失败次数过多，请稍后再试');
        }

        $model = new User();
        $user = $model->with(['userRole', 'userRole.role', 'userRole.role.app'])
            ->where('username', $username)
            ->where('password', $password)
            ->select(['id', 'nickname', 'username', 'avatar', 'created_at', 'status'])
            ->first();
        if (empty($user)) {
            Cache::set($key, $lock + 1, 10);
            throw new \Exception('用户不存在');
        }

        // 登录成功，清空锁
        Cache::delete($key);

        return $user;
    }
}