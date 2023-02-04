<?php

namespace app\common\service;

use think\facade\Cache;
use support\Response;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;

class CaptchaService
{

    /**
     * 输出验证码图像
     */
    public function captcha($key): Response
    {
        $phraseBuilder = new PhraseBuilder(4, '0123456789');

        // 初始化验证码类
        $builder = new CaptchaBuilder(null, $phraseBuilder);

        $builder->setBackgroundColor(255, 255, 255);

        // 生成验证码
        $builder->build();

        // 将验证码的值存储到缓存中
        $code = strtolower($builder->getPhrase());

        Cache::set("captcha_code:{$key}", $code, 300);

        // 获得验证码图片二进制数据
        $img_content = $builder->get();

        // 输出验证码二进制数据
        return new Response(200, ['Content-Type' => 'image/jpeg'], $img_content);
    }

    /**
     * 验证图形验证码是否正确
     * @param string $key 验证码
     * @param string $input 输入的验证码
     * @param bool $isClean 是否立即清除缓存
     * @return bool
     */
    public function check(string $key, string $input, bool $isClean = true): bool
    {
        $cacheKey = "captcha_code:{$key}";
        // 对比缓存中的code值
        $code = Cache::get($cacheKey);
        if($isClean) Cache::delete($cacheKey);

        return $code == $input;
    }

}