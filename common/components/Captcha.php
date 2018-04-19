<?php

namespace common\components;

/**
 * Class Captcha
 * @package common\components
 */
class Captcha
{
    /**
     * 生成验证码
     * @return string
     */
    public static function generate() {
        $captchaCode = (string)rand(1000, 9999);
        $_SESSION['captcha'] = $captchaCode;
        $_SESSION['captcha_expire_time'] = strtotime('+5 minutes');

        return $captchaCode;
    }

    /**
     * 检查验证码是否正确
     * @param $captchaCode string 验证码
     * @return bool
     */
    public static function check($captchaCode) {
        if (time() >= $_SESSION['captcha_expire_time']) {
            return false;
        }

        if (strtolower($captchaCode) === strtolower($_SESSION['captcha'])) {
            return true;
        } else {
            return false;
        }
    }

}