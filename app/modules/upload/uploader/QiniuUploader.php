<?php

namespace app\modules\upload\uploader;

use app\modules\upload\Input;
use app\modules\upload\Output;

use app\modules\upload\UploaderInterface;

class QiniuUploader implements UploaderInterface
{

    public function upload(Input $input, string $savePath): Output
    {
        // TODO: Implement upload() method.

        $output = new Output();

        return $output;
    }
}