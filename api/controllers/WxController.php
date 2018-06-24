<?php

namespace api\controllers;


use api\library\weixin\Api;
use yii\rest\Controller;
use Yii;

class WxController extends Controller
{
    public function actionGetSign()
    {
        $api = new Api(Yii::$app->params['wx']['developer']['appId'], Yii::$app->params['wx']['developer']['appSecret']);
        $data = $api->getSignPackage();
        return [
            'status' => 200,
            'data'   => $data
        ];
    }
}