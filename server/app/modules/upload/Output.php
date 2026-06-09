<?php

namespace app\modules\upload;

class Output
{

    /**
     * @var string 可访问链接
     */
    public string $url;

    /**
     * @var string 存储路径
     */
    public string $path;

    /**
     * @var string 文件后缀
     */
    public string $ext;

    /**
     * @var string 文件名（带后缀）
     */
    public string $fileName;

    /**
     * @var string 文件hash值（如md5）
     */
    public string $hash;

    /**
     * @var string 存储类型（对应file_storage表的key）
     */
    public string $storage;

    /**
     * @var string 存储类型ID（对应file_storage表的id）
     */
    public string $storageID;

}