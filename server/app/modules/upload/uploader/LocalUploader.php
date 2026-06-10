<?php

namespace app\modules\upload\uploader;

use app\modules\upload\config\LocalStorageConfig;
use app\modules\upload\config\UploadLimit;
use app\modules\upload\Input;
use app\modules\upload\Output;
use app\modules\upload\UploaderInterface;

class LocalUploader implements UploaderInterface
{
    private const STORAGE_KEY = 'local';

    private string $baseDir;
    private LocalStorageConfig $config;

    public function __construct(LocalStorageConfig $config)
    {
        $this->config = $config;
        $this->baseDir = public_path();
    }

    public function key(): string
    {
        return self::STORAGE_KEY;
    }

    public function limit(): UploadLimit
    {
        return $this->config->limit;
    }

    public function upload(Input $input, string $savePath): Output
    {
        // 含 root 前缀的相对存储路径
        $objectKey = $this->config->objectKey($savePath);
        $fullPath = $this->baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $objectKey);

        // 获取目录部分并创建所有目录
        $fullDir = dirname($fullPath);
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0755, true) && !is_dir($fullDir)) {
                throw new \RuntimeException("Failed to create directory: {$fullDir}");
            }
        }

        $realPath = $input->content->getRealPath();

        // 计算文件的MD5哈希
        $fileHash = md5_file($realPath);

        // 创建文件并复制内容
        if (!copy($realPath, $fullPath)) {
            throw new \RuntimeException("Failed to copy file to: {$objectKey}");
        }

        // 创建并返回Output对象
        $output = new Output();
        $output->url = $this->config->publicUrl($objectKey);
        $output->path = $objectKey;
        $output->ext = $input->ext;
        $output->fileName = $input->fileName;
        $output->hash = $fileHash;
        $output->storage = self::STORAGE_KEY;

        return $output;
    }
}
