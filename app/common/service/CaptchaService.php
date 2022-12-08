<?php

namespace App\common\service;

use support\Response;
use think\facade\Cache;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;

class CaptchaService
{

    /**
     * 输出验证码图像
     */
    public function captcha($uid): Response
    {
        $phraseBuilder = new PhraseBuilder(4, '0123456789');

        // 初始化验证码类
        $builder = new CaptchaBuilder(null, $phraseBuilder);

        $builder->setBackgroundColor(255, 255, 255);

        // 生成验证码
        $builder->build();

        // 将验证码的值存储到缓存中
        $code = strtolower($builder->getPhrase());

        Cache::set("captcha_code:{$uid}", $code, 300);

        // 获得验证码图片二进制数据
        $img_content = $builder->get();

        // 输出验证码二进制数据
        return new Response(200, ['Content-Type' => 'image/jpeg'], $img_content);
    }

    /**
     * @param $uid
     * @param string $input
     * @return bool
     */
    public function check($uid, string $input, bool $isClean = true)
    {
        $key = "captcha_code:{$uid}";
        // 对比缓存中的code值
        $code = Cache::get($key);

        if($isClean) Cache::delete($key);

        return strtolower($input) == $code;
    }

}