<?php

namespace app\modules\upload;

use app\common\exception\BusinessException;
use app\model\FileStorage;
use app\modules\upload\uploader\LocalUploader;
use app\modules\upload\uploader\QiniuUploader;

class UploadUtils
{
    /**
     * 生成上传路径
     * @param string $group
     * @param string $ext
     * @return string
     */
    public static function genPath(string $group, string $ext): string
    {
        $root = "uploads";
        if ($group) {
            $root .= "/$group";
        }

        $dir = "$root/" . date("Ym/d");

        $fileName = date("Hi") . uniqid() . ".$ext";
        return $dir . "/$fileName";
    }

    public static function GetUploader(string $key) : UploaderInterface
    {
        $condition = [];

        if ($key) {
            $condition[] = ['key', '=', $key];
        }else{
            $condition[] = ['is_default', '=', 1];
        }

        $storage = FileStorage::where($condition)->first();
        if (!$storage) {
            throw new BusinessException(message: '文件存储方式不存在:'.$key);
        }

        return match ($storage->key) {
            'local' => new LocalUploader($storage),
            'qiniu' => new QiniuUploader($storage),
            default => throw new BusinessException(message: '文件存储方式不存在:' . $key),
        };
    }


}