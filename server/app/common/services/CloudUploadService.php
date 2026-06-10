<?php

namespace app\common\services;

use app\modules\upload\Input;
use app\modules\upload\Output;
use app\modules\upload\UploadUtils;

class CloudUploadService extends BaseService
{

    public function upload(Input $input, string $group): Output
    {
        // 获取上传器（按配置中心启用的上传器选择）
        $uploader = UploadUtils::GetUploader('');

        // 上传前按所选上传器的限制校验文件大小与类型
        $uploader->limit()->validate($input);

        // 保存路径 按日期分目录，避免单目录文件过多
        $savePath = UploadUtils::genPath($group, $input->ext);

        // 上传文件
        return $uploader->upload($input, $savePath);
    }
}