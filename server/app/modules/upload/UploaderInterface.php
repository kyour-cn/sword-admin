<?php

namespace app\modules\upload;

interface UploaderInterface
{
    public function upload(Input $input, string $savePath): Output;


}