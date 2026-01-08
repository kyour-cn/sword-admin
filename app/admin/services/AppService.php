<?php declare(strict_types=1);

namespace app\admin\services;

use app\model\App;

class AppService
{

    /**
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $conds = [];

        if (!empty($params['keyword'])) {
            $conds[] = ['key', 'like', "%{$params['keyword']}%"];
        }

        $row = App::where($conds)
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
        $app = new App();
        $app->fill($data);
        $app->save();
    }

    public function update(array $data): void
    {
        $app = App::find($data['id']);
        $app->fill($data);
        $app->save();
    }

    public function delete(array $ids): void
    {
        App::whereIn('id', $ids)->delete();
    }
}