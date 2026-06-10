<?php

namespace app\admin\controller\system;

use app\admin\services\FileService;
use app\BaseController;
use app\common\utils\JwtUtils;
use support\Request;
use support\Response;

/**
 * 文件管理
 * @api
 */
class File extends BaseController
{
    public function menuList(Request $req): Response
    {
        $serv = new FileService();
        $res = $serv->getMenuList();
        return $this->success(data: $res);
    }

    public function menuAdd(Request $req): Response
    {
        $serv = new FileService();
        $serv->addMenu($req->post());
        return $this->success();
    }

    public function menuDelete(Request $req): Response
    {
        $id = $req->post('id');
        if (empty($id)) {
            return $this->fail(message: '请选择文件夹');
        }

        $serv = new FileService();
        if (!$serv->deleteMenu((int)$id)) {
            return $this->fail(message: '文件夹不存在');
        }
        return $this->success();
    }

    public function list(Request $req): Response
    {
        $serv = new FileService();
        $res = $serv->getList($req->get());
        return $this->success(data: $res);
    }

    public function upload(Request $req): Response
    {
        $file = $req->file('file');

        // 文件大小
        $size = $file->getSize();
        if ($size > 1024 * 1024 * 10) {
            return $this->fail(message: '文件大小不能超过10MB');
        }

        $claims = JwtUtils::decodeFromRequest($req);

        $serv = new FileService();
        $file = $serv->upload($req->post(), $file, $claims);
        return $this->success(data: [
            'fileName' => $file->file_name,
            'id' => $file->id,
            'src' => $file->url,
        ]);
    }

    public function delete(Request $req): Response
    {
        $serv = new FileService();
        $serv->delete($req->post('ids'));
        return $this->success();
    }
}
