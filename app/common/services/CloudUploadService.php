<?php

namespace app\common\services;

use app\modules\upload\Input;
use app\modules\upload\Output;
use app\modules\upload\UploadUtils;

class CloudUploadService extends BaseService
{

    public function upload(Input $input, string $group): Output
    {
        // 保存路径 按日期分目录，避免单目录文件过多
        $savePath = UploadUtils::genPath($group, $input->ext);

        // 获取上传器
        $uploader = UploadUtils::GetUploader('');

        // 上传文件
        return $uploader->upload($input, $savePath);
    }
}