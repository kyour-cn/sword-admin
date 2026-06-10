<?php

namespace app\modules\upload;

use app\common\exception\BusinessException;
use app\model\ConfigForm;
use app\modules\upload\config\ConfigLoader;
use app\modules\upload\config\LocalStorageConfig;
use app\modules\upload\config\S3StorageConfig;
use app\modules\upload\uploader\LocalUploader;
use app\modules\upload\uploader\S3Uploader;

class UploadUtils
{
    /**
     * 配置中心上传器配置的 key 前缀
     */
    private const UPLOADER_PREFIX = 'uploader_';

    /**
     * 未启用任何上传器时的兜底上传器
     */
    private const FALLBACK = 'local';

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

    /**
     * 获取上传器。
     * 不传 key 时扫描配置中心 uploader_ 前缀配置，取启用中的作为默认（多个按排序取第一个，全部未启用兜底 local）。
     * 传 key 时强制使用指定上传器（local / s3）。
     */
    public static function GetUploader(string $key = ''): UploaderInterface
    {
        if ($key !== '') {
            return self::makeUploader($key);
        }

        $forms = ConfigForm::where('status', 1)
            ->where('key', 'like', self::UPLOADER_PREFIX . '%')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        foreach ($forms as $form) {
            $config = ConfigLoader::load($form->key);
            if (filter_var($config['enabled'] ?? false, FILTER_VALIDATE_BOOL)) {
                return self::makeUploader(self::stripPrefix($form->key));
            }
        }

        return self::makeUploader(self::FALLBACK);
    }

    private static function makeUploader(string $driver): UploaderInterface
    {
        return match ($driver) {
            'local' => new LocalUploader(LocalStorageConfig::fromSystemConfig()),
            's3' => new S3Uploader(S3StorageConfig::fromSystemConfig()),
            default => throw new BusinessException(message: '不支持的上传方式：' . $driver),
        };
    }

    private static function stripPrefix(string $formKey): string
    {
        return str_starts_with($formKey, self::UPLOADER_PREFIX)
            ? substr($formKey, strlen(self::UPLOADER_PREFIX))
            : $formKey;
    }
}
