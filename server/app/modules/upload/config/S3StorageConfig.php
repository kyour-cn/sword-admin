<?php

namespace app\modules\upload\config;

use app\common\exception\BusinessException;

class S3StorageConfig
{
    public const CONFIG_KEY = 'uploader_s3';

    public bool $enabled;
    public string $accessKey;
    public string $secretKey;
    public string $sessionToken;
    public string $region;
    public string $bucket;
    public string $endpoint;
    public string $customDomain;
    public string $root;
    public string $acl;
    public bool $usePathStyleEndpoint;
    public UploadLimit $limit;

    private function __construct(array $config)
    {
        $this->enabled = $this->boolValue($config, ['enabled']);
        $this->limit = UploadLimit::fromConfig($config);
        $this->accessKey = $this->stringValue($config, ['access_key', 'accessKey', 'accessKeyId', 'key', 'ak']);
        $this->secretKey = $this->stringValue($config, ['secret_key', 'secretKey', 'secretAccessKey', 'secret', 'sk']);
        $this->sessionToken = $this->stringValue($config, ['session_token', 'sessionToken', 'token']);
        $this->region = $this->stringValue($config, ['region'], 'us-east-1');
        $this->bucket = $this->stringValue($config, ['bucket']);
        $this->endpoint = $this->normalizeUrl($this->stringValue($config, ['endpoint']));
        $this->customDomain = $this->normalizeUrl($this->stringValue($config, ['custom_domain', 'customDomain', 'domain', 'cdn_domain', 'url', 'base_url']));
        $this->root = trim($this->stringValue($config, ['root', 'prefix', 'path']), '/');
        $this->acl = $this->stringValue($config, ['acl']);
        $this->usePathStyleEndpoint = $this->boolValue($config, ['use_path_style_endpoint', 'usePathStyleEndpoint', 'path_style_endpoint', 'pathStyleEndpoint']);
    }

    public static function fromSystemConfig(): self
    {
        $config = ConfigLoader::load(self::CONFIG_KEY);
        $instance = new self($config);
        $instance->validate();
        return $instance;
    }

    public function objectKey(string $savePath): string
    {
        $path = ltrim(str_replace('\\', '/', $savePath), '/');
        if ($this->root === '') {
            return $path;
        }
        return $this->root . '/' . $path;
    }

    public function clientConfig(): array
    {
        $config = [
            'version' => 'latest',
            'region' => $this->region,
        ];

        if ($this->accessKey !== '' || $this->secretKey !== '') {
            if ($this->accessKey === '' || $this->secretKey === '') {
                throw new BusinessException('S3文件存储配置不完整：access_key 和 secret_key 需同时填写');
            }

            $config['credentials'] = [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ];

            if ($this->sessionToken !== '') {
                $config['credentials']['token'] = $this->sessionToken;
            }
        }

        if ($this->endpoint !== '') {
            $config['endpoint'] = $this->endpoint;
            $config['use_path_style_endpoint'] = $this->usePathStyleEndpoint;
        }

        return $config;
    }

    public function publicUrl(string $objectKey): string
    {
        $encodedKey = $this->encodeObjectKey($objectKey);

        if ($this->customDomain !== '') {
            return rtrim($this->customDomain, '/') . '/' . $encodedKey;
        }

        if ($this->endpoint !== '') {
            if ($this->usePathStyleEndpoint) {
                return rtrim($this->endpoint, '/') . '/' . rawurlencode($this->bucket) . '/' . $encodedKey;
            }
            return $this->virtualHostedEndpointUrl($encodedKey);
        }

        return 'https://' . $this->bucket . '.s3.' . $this->region . '.amazonaws.com/' . $encodedKey;
    }

    private function validate(): void
    {
        if ($this->bucket === '') {
            throw new BusinessException('S3文件存储配置不完整：bucket 不能为空');
        }
        if ($this->region === '') {
            throw new BusinessException('S3文件存储配置不完整：region 不能为空');
        }
        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $this->region)) {
            throw new BusinessException('S3文件存储配置不正确：region 只能包含小写字母、数字和中划线，且不能以中划线开头或结尾');
        }
    }

    private function virtualHostedEndpointUrl(string $encodedKey): string
    {
        $endpoint = parse_url($this->endpoint);
        if (empty($endpoint['scheme']) || empty($endpoint['host'])) {
            return rtrim($this->endpoint, '/') . '/' . $encodedKey;
        }

        $host = $this->bucket . '.' . $endpoint['host'];
        $port = isset($endpoint['port']) ? ':' . $endpoint['port'] : '';
        $path = isset($endpoint['path']) ? rtrim($endpoint['path'], '/') : '';
        return $endpoint['scheme'] . '://' . $host . $port . $path . '/' . $encodedKey;
    }

    private function encodeObjectKey(string $objectKey): string
    {
        return implode('/', array_map('rawurlencode', explode('/', ltrim($objectKey, '/'))));
    }

    private function normalizeUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }
        if (!preg_match('/^https?:\/\//i', $url)) {
            return 'https://' . $url;
        }
        return $url;
    }

    private function stringValue(array $config, array $keys, string $default = ''): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $config) && $config[$key] !== null) {
                return trim((string)$config[$key]);
            }
        }
        return $default;
    }

    private function boolValue(array $config, array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $config)) {
                return filter_var($config[$key], FILTER_VALIDATE_BOOL);
            }
        }
        return false;
    }
}
