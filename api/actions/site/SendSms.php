<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\library\sms\Ucpaas;
use Yii;
/**
 * 发送短信验证码
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class SendSms extends BaseAction
{
    public function run()
    {
        $mobile = Yii::$app->request->post('mobile');
        if(empty($mobile)) return ['status' => 0, 'message' => '请输入手机号'];

        try{
            $verifyCode = Yii::$app->cache->get($mobile.'verifyCode');
            if(!empty($verifyCode)) throw new \Exception('60秒内只能发送一次', 0);
            $ucPass = new Ucpaas(['accountsid' => Yii::$app->params['wx']['smsapi']['accountSid'], 'token' => Yii::$app->params['wx']['smsapi']['token']]);

            $code = rand(100000,999999);
            $result = $ucPass->SendSms(Yii::$app->params['wx']['smsapi']['appid'], Yii::$app->params['wx']['smsapi']['templateid'], $code, $mobile, '');

            if($result == '没有返回数据') return ['status' => 0, 'message' => '没有返回数据'];
            if(!$result) return ['status' => 0, 'message' => '非国内电话不发送短信'];
            $result = json_decode($result, true);
            Yii::$app->cache->set($mobile.'verifyCode', $code, 60);
            if($result['msg'] === 'OK') {
                Yii::$app->cache->set($mobile.'verifyCode', $code, 60);
                return ['status' => 200, 'message' => "验证码发送成功"];
            }
            throw new \Exception($result['msg'], 0);
        }catch(\Exception $e){
            return ['status' => 0, 'message' => $e->getMessage()];
        }


    }
} 