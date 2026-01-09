<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\services\BaseService;
use app\model\Role;

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
        $role = new Role();
        $role->fill($data);
        $role->save();
    }

    public function update(array $data): void
    {
        $role = Role::find($data['id']);
        $role->fill($data);
        $role->save();
    }

    public function delete(array $ids): void
    {
        Role::whereIn('id', $ids)->delete();
    }
}