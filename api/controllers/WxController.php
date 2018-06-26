<?php

namespace api\controllers;


use api\library\member\MemberService;
use api\library\weixin\Api;
use api\models\Contact;
use yii\rest\Controller;
use Yii;

class WxController extends Controller
{
    /**
     * 获取签名
     * @return array
     */
    public function actionGetSign()
    {
        $api = new Api(Yii::$app->params['wx']['developer']['appId'], Yii::$app->params['wx']['developer']['appSecret']);
        $data = $api->getSignPackage();
        return [
            'status' => 200,
            'data'   => $data
        ];
    }

    /**
     * 授权地址
     * @return array
     */
    public function actionGetAuth()
    {
        $url = Yii::$app->request->post('url');
        if(!$url)  $url = Yii::$app->params['h5Url'] . '/#/index';
        //跳转到微信oAuth授权页面
        $state = uniqid();
        $session = Yii::$app->session;
        $flashKey = 'oAuthAuthState';
        $session->set($flashKey, $state);
        $api = new Api(Yii::$app->params['wx']['developer']['appId'], Yii::$app->params['wx']['developer']['appSecret']);
        $reUrl = $api->getOauthAuthorizeUrl($url, $state, 'snsapi_userinfo');
        return [
            'status' => 200,
            'data'   => $reUrl
        ];

    }

    /**
     * 授权登录
     * @throws \Exception
     */
    public function actionUserInfo()
    {
        $flashKey = 'oAuthAuthState';
        $session = Yii::$app->session;
//        var_dump($session->get($flashKey));die;

        //从微信oAuth页面跳转回来

        $code = Yii::$app->request->post('code');
        $state = Yii::$app->request->post('state');
        if (!$code || !$state ) {
            Yii::error('网页授权获取用户基本信息错误，请检查appid等相关信息');
            return [
                'status' => 0,
                'message' => '网页授权获取用户基本信息错误，请检查appid等相关信息'
            ];
        }

        $api = new Api(Yii::$app->params['wx']['developer']['appId'], Yii::$app->params['wx']['developer']['appSecret']);

        //获取AccessToken
        $arr = $api->getOauthAccessToken($code);
        if(empty($arr)) {
            return [
                'status' => 0,
                'message' => '获取AccessToken失败'
            ];
        }


        //获取用户信息
        $userInfo = $api->getOauthUserInfo($arr['openid'], $arr['access_token']);

        if($userInfo ) {
            $post = [
                'openId' => $userInfo['openid'],
                'nickName' => $userInfo['nickname'],
                'avatarUrl' => $userInfo['headimgurl'],
                'city' => $userInfo['city'],
                'province' => $userInfo['province'],
                'country' => $userInfo['country'],
                'gender' => $userInfo['sex'],
            ];
            if(MemberService::saveContact($post)) {
                Yii::warning(json_encode($userInfo));
                return [
                    'status' => 200,
                    'message'=> 'success',
                    'data'   => $userInfo
                ];
            }

        }

        return [
            'status' => 0,
            'message' => '操作失败'
        ];

    }
}