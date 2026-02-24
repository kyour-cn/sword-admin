<?php

namespace app\modules\upload;

use SplFileInfo;

class Input
{
    /**
     * @var string 上传的文件名
     */
    public string $fileName;

    /**
     * @var SplFileInfo 上传的文件内容
     */
    public SplFileInfo $content;

    /**
     * @var int 上传的文件大小
     */
    public int $size;

    /**
     * @var string 上传的文件扩展名
     */
    public string $ext;

     /**
     * @var string 上传的文件MIME类型
     */
    public string $mimeType;

}