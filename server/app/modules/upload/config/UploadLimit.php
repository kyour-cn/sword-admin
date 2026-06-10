<?php

namespace app\modules\upload\config;

use app\common\exception\BusinessException;
use app\modules\upload\Input;

/**
 * 上传限制（大小、扩展名），各上传器各自配置。
 */
class UploadLimit
{
    /**
     * @var int 单文件最大字节数，0 表示不限制
     */
    public int $maxSizeBytes;

    /**
     * @var string[] 允许的扩展名（小写、不含点），空数组表示不限制
     */
    public array $allowExt;

    /**
     * @param int $maxSizeMb 大小限制（MB），0 表示不限制
     * @param string $allowExt 逗号分隔的扩展名白名单
     */
    public function __construct(int $maxSizeMb, string $allowExt)
    {
        $this->maxSizeBytes = max(0, $maxSizeMb) * 1024 * 1024;
        $this->allowExt = self::parseExtList($allowExt);
    }

    public static function fromConfig(array $config): self
    {
        $maxSize = (int)($config['max_size'] ?? 0);
        $allowExt = (string)($config['allow_ext'] ?? '');
        return new self($maxSize, $allowExt);
    }

    /**
     * 校验上传文件是否满足限制，不满足抛 BusinessException。
     */
    public function validate(Input $input): void
    {
        if ($this->maxSizeBytes > 0 && $input->size > $this->maxSizeBytes) {
            throw new BusinessException(sprintf(
                '文件大小超出限制：最大允许 %s',
                $this->humanSize($this->maxSizeBytes)
            ));
        }

        if (!empty($this->allowExt)) {
            $ext = strtolower(ltrim($input->ext, '.'));
            if ($ext === '' || !in_array($ext, $this->allowExt, true)) {
                throw new BusinessException('不支持的文件类型，仅允许：' . implode('、', $this->allowExt));
            }
        }
    }

    /**
     * @return string[]
     */
    private static function parseExtList(string $allowExt): array
    {
        $parts = preg_split('/[,，\s]+/u', $allowExt) ?: [];
        $result = [];
        foreach ($parts as $part) {
            $ext = strtolower(ltrim(trim($part), '.'));
            if ($ext !== '') {
                $result[$ext] = $ext;
            }
        }
        return array_values($result);
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . 'MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . 'KB';
        }
        return $bytes . 'B';
    }
}
