<?php

namespace app\modules\upload\uploader;

use app\model\FileStorage;
use app\modules\upload\Input;
use app\modules\upload\Output;
use app\modules\upload\UploaderInterface;

class LocalUploader implements UploaderInterface
{
    private string $baseDir;
    private string $storeKey;
    private FileStorage $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
        $this->baseDir = public_path();
        $this->storeKey = $storage->key ?? 'local';
    }

    public function upload(Input $input, string $savePath): Output
    {
        // 获取目录部分并创建所有目录
        $fullDir = dirname($this->baseDir . DIRECTORY_SEPARATOR . $savePath);
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0755, true)) {
                throw new \RuntimeException("Failed to create directory: {$fullDir}");
            }
        }

        $realPath = $input->content->getRealPath();

        // 计算文件的MD5哈希
        $fileHash = md5_file($realPath);

        // 创建文件并复制内容
        if (!copy($realPath, $this->baseDir . DIRECTORY_SEPARATOR . $savePath)) {
            throw new \RuntimeException("Failed to copy file to: {$savePath}");
        }

        // 创建并返回Output对象
        $output = new Output();
        $output->url = $savePath;
        $output->path = $savePath;
        $output->ext = $input->ext;
        $output->fileName = $input->fileName;
        $output->hash = $fileHash;
        $output->storage = $this->storeKey;
        $output->storageID = $this->storage->id;
        
        return $output;
    }
}