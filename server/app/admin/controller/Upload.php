<?php

namespace app\admin\controller;

use app\admin\services\FileService;
use app\BaseController;
use app\common\utils\JwtUtils;
use support\Request;
use support\Response;

/**
 * 通用上传接口
 * @api
 */
class Upload extends BaseController
{
    public function image(Request $req): Response
    {
        return $this->save($req);
    }

    public function file(Request $req): Response
    {
        return $this->save($req);
    }

    private function save(Request $req): Response
    {
        $file = $req->file('file');
        if (!$file) {
            return $this->fail(message: '请选择上传文件');
        }

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
            'url' => $file->url,
            'src' => $file->url,
        ]);
    }
}
