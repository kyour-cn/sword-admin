<?php

namespace app\modules\upload\config;

/**
 * 本地上传配置，读取配置中心 uploader_local。
 */
class LocalStorageConfig
{
    public const CONFIG_KEY = 'uploader_local';

    public bool $enabled;
    public string $domain;
    public string $root;
    public UploadLimit $limit;

    private function __construct(array $config)
    {
        $this->enabled = filter_var($config['enabled'] ?? false, FILTER_VALIDATE_BOOL);
        $this->domain = $this->normalizeDomain((string)($config['domain'] ?? ''));
        $this->root = trim((string)($config['root'] ?? ''), '/');
        $this->limit = UploadLimit::fromConfig($config);
    }

    public static function fromSystemConfig(): self
    {
        return new self(ConfigLoader::load(self::CONFIG_KEY));
    }

    /**
     * 计算文件相对存储路径（含 root 前缀），用于落盘与记录 path。
     */
    public function objectKey(string $savePath): string
    {
        $path = ltrim(str_replace('\\', '/', $savePath), '/');
        if ($this->root === '') {
            return $path;
        }
        return $this->root . '/' . $path;
    }

    /**
     * 生成可访问链接。配置域名时返回绝对地址，否则返回相对路径。
     */
    public function publicUrl(string $objectKey): string
    {
        $key = ltrim($objectKey, '/');
        if ($this->domain === '') {
            return $key;
        }
        return rtrim($this->domain, '/') . '/' . $key;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        if ($domain === '') {
            return '';
        }
        if (!preg_match('/^https?:\/\//i', $domain)) {
            return 'https://' . $domain;
        }
        return $domain;
    }
}
