<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\model\Menu;
use app\model\Role;
use app\model\RolePermission;
use app\model\UserRole;
use support\Db;

class RoleService extends BaseService
{
    /**
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $conds = [];

        if (!empty($params['keyword'])) {
            $conds[] = ['name', 'like', "%{$params['keyword']}%"];
        }

        if (!empty($params['app_id'])) {
            $conds[] = ['app_id', '=', $params['app_id']];
        }

        $row = Role::where($conds)
            ->with('permissions:id,role_id,menu_id')
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        $rows = array_map(static function (Role $role): array {
            $item = $role->toArray();
            $item['permission_ids'] = array_column($item['permissions'], 'menu_id');
            unset($item['permissions']);
            return $item;
        }, $row->items());

        return [
            'rows' => $rows,
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage(),
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
        $role = new Role();
        $role->fill($this->filterRoleData($data));
        $role->save();
    }

    public function update(array $data): void
    {
        $role = Role::find((int)($data['id'] ?? 0));
        if (!$role) {
            throw new BusinessException('角色不存在');
        }

        $role->fill($this->filterRoleData($data));
        $role->save();
    }

    /**
     * 覆盖角色的菜单及操作权限。
     */
    public function updatePermissions(int $roleId, array $menuIds): void
    {
        $menuIds = $this->formatMenuIds($menuIds);

        Db::transaction(function () use ($roleId, $menuIds) {
            $role = Role::where('id', $roleId)->lockForUpdate()->first();
            if (!$role) {
                throw new BusinessException('角色不存在');
            }

            if ($menuIds !== []) {
                $validMenuCount = Menu::where('app_id', $role->app_id)
                    ->whereIn('id', $menuIds)
                    ->count();
                if ($validMenuCount !== count($menuIds)) {
                    throw new BusinessException('权限节点不存在或不属于当前应用');
                }
            }

            RolePermission::where('role_id', $role->id)->delete();
            if ($menuIds === []) {
                return;
            }

            $now = date('Y-m-d H:i:s');
            $rows = array_map(static fn (int $menuId): array => [
                'role_id' => $role->id,
                'menu_id' => $menuId,
                'created_at' => $now,
            ], $menuIds);
            RolePermission::insert($rows);
        });
    }

    public function delete(array $ids): void
    {
        Db::transaction(function () use ($ids) {
            // 角色使用软删除，需同步删除关联数据，数据库外键级联不会生效。
            UserRole::whereIn('role_id', $ids)->delete();
            RolePermission::whereIn('role_id', $ids)->delete();
            Role::whereIn('id', $ids)->delete();
        });
    }

    private function filterRoleData(array $data): array
    {
        $fields = ['app_id', 'name', 'remark', 'status', 'sort', 'is_admin'];
        return array_intersect_key($data, array_flip($fields));
    }

    private function formatMenuIds(array $menuIds): array
    {
        $ids = [];
        foreach ($menuIds as $menuId) {
            if (!is_int($menuId) && !(is_string($menuId) && ctype_digit($menuId))) {
                throw new BusinessException('权限节点参数不正确');
            }

            $menuId = (int)$menuId;
            if ($menuId > 0) {
                $ids[$menuId] = $menuId;
            }
        }

        return array_values($ids);
    }
}
