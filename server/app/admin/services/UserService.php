<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\common\utils\JwtUtils;
use app\model\Task;
use app\model\User;
use app\model\UserRole;
use support\Db;

class UserService extends BaseService
{
    /**
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $row = $this->buildQuery($params)
            ->with(['userRole.role.app'])
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        return [
            'rows' => $row->items(),
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    /**
     * 构建用户列表筛选查询
     * @param array $params
     * @return mixed
     */
    public function buildQuery(array $params): mixed
    {
        $query = User::query();

        if (!empty($params['keyword'])) {
            $keyword = trim((string)$params['keyword']);
            $query->where(function ($query) use ($keyword) {
                $query->where('username', 'like', "%{$keyword}%")
                    ->orWhere('nickname', 'like', "%{$keyword}%")
                    ->orWhere('mobile', 'like', "%{$keyword}%");
            });
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', '=', $params['status']);
        }

        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $query->whereBetween('created_at', [$params['start_time'], $params['end_time']]);
        }

        return $query;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function export(array $params): mixed
    {
        $claims = JwtUtils::decodeFromRequest(request());

        return Task::create([
            'title' => '用户列表导出',
            'group' => 'user',
            'user_id' => $claims['id']??0,
            'type' => 'export',
            'label' => 'export_user',
            'content' => json_encode($params, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 获取当前登录用户的任务列表
     * @return array
     */
    public function getTaskList(): array
    {
        $claims = JwtUtils::decodeFromRequest(request());

        return Task::where('group', 'user')
            ->where('user_id', $claims['id'] ?? 0)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
        $roleIds = $this->formatRoleIds($data['roles'] ?? []);
        $userData = $this->filterUserData($data);

        // 处理密码加密
        if (!empty($userData['password'])) {
            $userData['password'] = md5($userData['password']);
        }

        Db::transaction(function () use ($userData, $roleIds) {
            $user = new User();
            $user->fill($userData);
            $user->save();

            $this->syncRoles($user->id, $roleIds);
        });
    }

    public function update(array $data): void
    {
        $roleIds = array_key_exists('roles', $data) ? $this->formatRoleIds($data['roles']) : null;
        $userData = $this->filterUserData($data);

        // 如果密码为空，则不更新密码字段
        if (isset($userData['password']) && empty($userData['password'])) {
            unset($userData['password']);
        } elseif (!empty($userData['password'])) {
            $userData['password'] = md5($userData['password']);
        }

        Db::transaction(function () use ($data, $userData, $roleIds) {
            $user = User::find((int)($data['id'] ?? 0));
            if (!$user) {
                throw new BusinessException('用户不存在');
            }

            $user->fill($userData);
            $user->save();

            if ($roleIds !== null) {
                $this->syncRoles($user->id, $roleIds);
            }
        });
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

    private function filterUserData(array $data): array
    {
        $fields = ['nickname', 'username', 'mobile', 'avatar', 'password', 'status'];
        return array_intersect_key($data, array_flip($fields));
    }

    private function formatRoleIds(mixed $roles): array
    {
        if (!is_array($roles)) {
            return [];
        }

        $roleIds = [];
        foreach ($roles as $role) {
            $roleId = is_array($role) ? (int)($role['id'] ?? 0) : (int)$role;
            if ($roleId > 0) {
                $roleIds[$roleId] = $roleId;
            }
        }

        return array_values($roleIds);
    }

    private function syncRoles(int $userId, array $roleIds): void
    {
        UserRole::where('user_id', $userId)->delete();

        if ($roleIds === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = array_map(static fn (int $roleId) => [
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => $now,
        ], $roleIds);

        UserRole::insert($rows);
    }
}
