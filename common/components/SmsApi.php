<?php
namespace common\components;
use common\models\logs\SmsLog;

/**
 * @Copyright (c) 2015
 * @Author Laurence Chen <laurence@lulutrip.com>
 * ============================
 * @Descriptions SMS API 短信系统 平台地址：http://m.5c.com.cn
 */

class SmsApi {

    /* 大汉三通短信云配置信息 */
    private static $_url = "http://www.dh3t.com/json/sms";
    private static $_account = "jt740601";
    private static $_password = "k3X0v687";
    private static $_sign = "【路路行旅游网】";
    private static $_cnMobileReg = '/^((00)?86)?1[34578]{1}\d{9}$/';

    /**
     * 判断手机号是否是国内的
     * @param $mobile 手机号
     * @return bool
     */
    private function _isMobileInChina($mobile) {
        preg_match_all('/\d+/', $mobile, $arr);
        $orig = $arr[0][0];
        $orig && $suffix = substr($orig, 0, 2);
        $suffixArr = array("86", "13", "14", "15", "17", "18");
        if (!($suffix && in_array($suffix, $suffixArr))) {
            if (substr($orig, 0, 4) != "0086") {
                return false;
            }
        }
        $str = "";
        foreach ((array)$arr[0] as $value) {
            $str .= $value;
        }
        $isInChina = preg_match(self::$_cnMobileReg, $str) ? true : false;
        return $isInChina;
    }

    public function sendSMS($mobile, $content) {
        // 非国内电话不发送短信
        if (!$this->_isMobileInChina($mobile)) {
            return false;
        }
        $data = array(
            'account'   => self::$_account,
            'password'  => strtolower(md5(self::$_password)),
            'phones'    => $mobile,
            'content'   => $content,
            'sign'      => self::$_sign
        );
        $ret = self::_http_post_json(__FUNCTION__, self::$_url . "/Submit", json_encode($data));
        self::log2db($mobile, $content, $ret);
        return $ret;
    }

    private function _http_post_json($functionName, $url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    private function log2db($mobile, $content, $res) {
        $smsLog = new SmsLog();
        $smsLog->mobile = $mobile;
        $smsLog->content = $content;
        $smsLog->response = $res;
        $smsLog->timestamp = time();
        $smsLog->save();
    }
}