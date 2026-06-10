<?php

namespace app\modules\upload\uploader;

use app\common\exception\BusinessException;
use app\modules\upload\config\S3StorageConfig;
use app\modules\upload\config\UploadLimit;
use app\modules\upload\Input;
use app\modules\upload\Output;
use app\modules\upload\UploaderInterface;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Throwable;

class S3Uploader implements UploaderInterface
{
    private const STORAGE_KEY = 's3';

    private S3StorageConfig $config;
    private S3Client $client;

    public function __construct(?S3StorageConfig $config = null)
    {
        try {
            $this->config = $config ?? S3StorageConfig::fromSystemConfig();
            $this->client = new S3Client($this->config->clientConfig());
        } catch (BusinessException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new BusinessException('S3文件存储配置不正确：' . $e->getMessage());
        }
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
        $realPath = $input->content->getRealPath();
        if (!$realPath || !is_file($realPath)) {
            throw new BusinessException('上传文件不存在或不可读取');
        }

        $fileHash = md5_file($realPath);
        if ($fileHash === false) {
            throw new BusinessException('上传文件Hash计算失败');
        }

        $objectKey = $this->config->objectKey($savePath);
        $body = fopen($realPath, 'rb');
        if ($body === false) {
            throw new BusinessException('上传文件读取失败');
        }

        try {
            $params = [
                'Bucket' => $this->config->bucket,
                'Key' => $objectKey,
                'Body' => $body,
                'ContentType' => $input->mimeType ?: 'application/octet-stream',
            ];

            if ($this->config->acl !== '') {
                $params['ACL'] = $this->config->acl;
            }

            $this->client->putObject($params);
        } catch (AwsException $e) {
            $message = $e->getAwsErrorMessage() ?: $e->getMessage();
            throw new BusinessException('S3文件上传失败：' . $message);
        } catch (Throwable $e) {
            throw new BusinessException('S3文件上传失败：' . $e->getMessage());
        } finally {
            if (is_resource($body)) {
                fclose($body);
            }
        }

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
