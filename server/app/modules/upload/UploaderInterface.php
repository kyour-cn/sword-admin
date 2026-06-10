<?php

namespace app\modules\upload;

use app\modules\upload\config\UploadLimit;

interface UploaderInterface
{
    public function upload(Input $input, string $savePath): Output;

    /**
     * 上传器标识（local / s3），写入 file.storage_key。
     */
    public function key(): string;

    /**
     * 当前上传器的上传限制。
     */
    public function limit(): UploadLimit;
}
