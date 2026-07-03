<?php declare(strict_types=1);

namespace app\common\services;

use AltchaOrg\Altcha\V1\Altcha;
use AltchaOrg\Altcha\V1\ChallengeOptions;
use AltchaOrg\Altcha\V1\Hasher\Algorithm;
use app\common\exception\BusinessException;
use support\Cache;

class CaptchaService extends BaseService
{
    private const string CACHE_PREFIX = 'altcha_challenge_';
    private const int EXPIRE_SECONDS = 300;
    private const int MAX_NUMBER = 100000;

    /**
     * 生成 ALTCHA challenge，前端组件会自动完成 PoW 并在登录时提交 payload。
     */
    public function challenge(): array
    {
        $expiresAt = time() + self::EXPIRE_SECONDS;
        $challenge = $this->altcha()->createChallenge(new ChallengeOptions(
            algorithm: Algorithm::SHA256,
            maxNumber: self::MAX_NUMBER,
            expires: (new \DateTimeImmutable())->setTimestamp($expiresAt),
        ));

        Cache::set($this->cacheKey($challenge->salt), true, self::EXPIRE_SECONDS);

        return [
            'algorithm' => $challenge->algorithm,
            'challenge' => $challenge->challenge,
            'maxNumber' => $challenge->maxNumber,
            'maxnumber' => $challenge->maxNumber,
            'salt' => $challenge->salt,
            'signature' => $challenge->signature,
        ];
    }

    /**
     * 校验 ALTCHA payload，并删除 challenge 缓存，避免同一个 payload 重放登录。
     */
    public function verify(string $payload): void
    {
        $data = $this->decodePayload($payload);
        if ($data === null) {
            throw new BusinessException('请完成人机验证');
        }

        $cacheKey = $this->cacheKey((string)$data['salt']);
        if (!Cache::get($cacheKey)) {
            throw new BusinessException('人机验证已过期，请重新验证');
        }
        Cache::delete($cacheKey);

        if (!$this->altcha()->verifySolution($data)) {
            throw new BusinessException('人机验证失败，请重新验证');
        }
    }

    private function altcha(): Altcha
    {
        return new Altcha($this->secret());
    }

    private function secret(): string
    {
        return getenv('ALTCHA_SECRET') ?: config('jwt.secret') ?: 'sword-admin-altcha';
    }

    private function cacheKey(string $salt): string
    {
        return self::CACHE_PREFIX . hash('sha256', $salt);
    }

    private function decodePayload(string $payload): ?array
    {
        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return null;
        }

        try {
            $data = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($data)
            || !isset($data['algorithm'], $data['challenge'], $data['number'], $data['salt'], $data['signature'])
            || !is_string($data['algorithm'])
            || !is_string($data['challenge'])
            || !is_int($data['number'])
            || !is_string($data['salt'])
            || !is_string($data['signature'])
        ) {
            return null;
        }

        return $data;
    }
}
