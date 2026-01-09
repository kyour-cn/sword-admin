<?php declare(strict_types=1);

namespace app\admin\services;

use app\model\User;

class UserService
{
    /**
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $conds = [];

        if (!empty($params['keyword'])) {
            $conds[] = ['username', 'like', "%{$params['keyword']}%"];
        }

        if (!empty($params['status'])) {
            $conds[] = ['status', '=', $params['status']];
        }

        $row = User::where($conds)
            ->paginate(perPage : $params['page_size'] ?? 10,page: $params['page'] ?? 1);

        return [
            'rows' => $row->items(),
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
        $user = new User();
        
        // 处理密码加密
        if (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        
        $user->fill($data);
        $user->save();
    }

    public function update(array $data): void
    {
        $user = User::find($data['id']);
        
        // 如果密码为空，则不更新密码字段
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        
        $user->fill($data);
        $user->save();
    }

    public function delete(array $ids): void
    {
        User::whereIn('id', $ids)->delete();
    }

    public function resetPassword(int $id, string $newPassword): void
    {
        $user = User::find($id);
        if ($user) {
            $user->password = md5($newPassword);
            $user->save();
        }
    }
}