<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\services\BaseService;
use app\common\services\CloudUploadService;
use app\model\File;
use app\model\FileMenu;
use app\modules\upload\Input;
use Illuminate\Database\Eloquent\Collection;
use Webman\Http\UploadFile;

class FileService extends BaseService
{

    public function getMenuList(): Collection|array
    {
        $menu = new FileMenu();
        return $menu->get();
    }

    public function addMenu(array $data): bool
    {
        $menu = new FileMenu();
        $menu->fill($data);
        return $menu->save();
    }

    public function deleteMenu(int $id): bool
    {
        $menu = FileMenu::find($id);
        if (!$menu) {
            return false;
        }
        return $menu->delete();
    }

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

        $row = File::where($conds)
            ->orderBy('id', 'desc')
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        return [
            'rows' => $row->items(),
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    public function upload(array $data, UploadFile $uploadFile, array $claims): File
    {
        $cloudUploadService = new CloudUploadService();

        $input = new Input();
        $input->ext = $uploadFile->getUploadExtension();
        $input->size = $uploadFile->getSize();
        $input->content = $uploadFile;
        $input->fileName = $uploadFile->getUploadName();
        $input->mimeType = $uploadFile->getUploadMimeType();

        // 上传文件
        $output = $cloudUploadService->upload($input, '');

        // 保存文件信息
        $file = new File();
        $file->fill([
            'file_size' => $input->size,
            'file_type' => $uploadFile->getUploadMimeType(),
            'file_name' => $uploadFile->getUploadName(),
            'file_ext' => $uploadFile->getUploadExtension(),
            'url' => $output->url,
            'file_path' => $output->path,
            'storage_id' => $output->storageID,
            'storage_key' => $output->storage,
            'hash_md5' => $output->hash,
            'user_id' => $claims['id'],
            'menu_id' => $data['menu_id'] ?? 0
        ]);
        $file->save();
        return $file;
    }

    public function delete(array $ids): void
    {
        File::whereIn('id', $ids)->delete();
    }
}
