<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\library\ace\WXBizDataCrypt;
use api\library\member\MemberService;
use api\models\Contact;
use Yii;
/**
 * 登陆
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class WxLogin extends BaseAction
{
    public function run()
    {
        $code   =   Yii::$app->request->post('code');
        $encryptedData   =   Yii::$app->request->post('encryptedData');
        $iv   =   Yii::$app->request->post('iv');
        $appid  =  Yii::$app->params['wx']['developer']['appId'];
        $secret =   Yii::$app->params['wx']['developer']['appSecret'];
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        $apiData = $this->curlGet($URL);
        $apiData = json_decode($apiData, true);

        if(!isset($apiData['errcode'])){
            $sessionKey = $apiData['session_key'];
            $userifo = new WXBizDataCrypt($appid, $sessionKey);
            $errCode = $userifo->decryptData($encryptedData, $iv, $data );
            //todo存取用户信息
            if ($errCode == 0) {
                $data = json_decode($data, true);

                //检查用户信息是否存在
                if(Contact::findOne(['openid' => $data['openId']])) {
                    return [
                        'status' => 200,
                        'data' => $data,
                        'message' => '获取用户信息'
                    ];
                }

                //保存用户信息
                if(MemberService::saveContact($data)) {
                    return [
                        'status' => 200,
                        'data' => $data,
                        'message' => '获取用户信息'
                    ];
                }
            }
        }

        return [
            'status' => 0,
            'message' => '获取用户信息失败'
        ];
    }

    public function curlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);

        $output = curl_exec($ch);
        if( !$output)
        {
            echo curl_error($ch);die;
        }
        curl_close($ch);
        return $output;
    }
} 