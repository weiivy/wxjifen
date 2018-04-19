<?php
/**
 * 微信类
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */
namespace  common\components\weixin;

use common\models\WxAccessToken;
use common\models\WxQrcodes;
use linslin\yii2\curl\Curl;
use Yii;

class Wx extends \yii\base\Component
{
    public static $Tqrcodes = "wx_qrcodes";
    public $access_token_table;
    public $appid;
    public $appsecret;
    public $Access_token_url;
    public static $wx_llt_authorize_url;
    public $wx_url;
    public static $access_token;
    public $wx_menus;
    public static $developer;
    public $wxDebug = true;

    public function init()
    {
        $config = Yii::$app->params['wx'];
        $this->appid = $config['developer']["AppId"];
        $this->appsecret = $config['developer']["appsecret"];
        if ($this->wxDebug || !in_array($_SERVER['SERVER_NAME'], array("www.lulutrip.com", "app.lulutrip.com", "car.lulutrip.com"))) {
            $this->appid = $config['developer']["DebugAppId"];
            $this->appsecret = $config['developer']["Debugappsecret"];
        }
        $this->wx_url = $config['developer']["wx_url"];
        $this->Access_token_url = $config['developer']["Access_token_url"];
        $this->wx_menus = $config['wxmenus']['button'];
        $this->access_token_table = "wx_access_token";
    }

    public function getQrCodeTicket($memberid)
    {
        return \common\models\WxQrcodes::find()->where('memberid=:memberid', ['memberid' => $memberid])->one()->toArray();
    }


    public function checkAuth()
    {
        $flag = false;
        $token = $this->getLastAccessToken('base');
        if ($token) {
            if (time() > $token['expires_in'] + $token['time']) {	//access_token已经过期
                $flag = true;
            } else {
                static::$access_token = $token['access_token'];
            }
        } else {
            $flag = true;
        }
        if($flag){
            $this->updateAccessToken();
        }
        return static::$access_token;
    }

    public function updateAccessToken() {
        //调用access_token接口
        $curl = new Curl;
        $url_get = array("grant_type"=>"client_credential", "appid"=>$this->appid, "secret"=>$this->appsecret);
        $url     = $this->Access_token_url.'?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
        $curl->get($url);
        $access_token_json = $curl->response;
        $access_token_decode = json_decode($access_token_json, true);
        $access_token_decode['time'] = time();
        $access_token_decode['type'] = "base";
        static::$access_token = $access_token_decode["access_token"];
        //ss("wxAccessToken", $this->access_token);
        //ss("wxAccessToken", static::$access_token);
        $this->insertAccessTokenInfo($access_token_decode);
    }

    public function getLastAccessToken($type)
    {
        return WxAccessToken::find()->select('access_token,expires_in,time')
            ->where('`type` =:type', ['type' => $type])
            ->orderBy('id')
            ->one()->toArray();
    }

    public function insertAccessTokenInfo($access_token_decode)
    {
        $model = new WxAccessToken;
        foreach ($access_token_decode as $k => $v) {
            $model->$k = $v;
        }

        $model->refresh_token = '';
        $model->openid        = '';
        $model->scope         = '';
        $model->save();
        return true;
    }

    public function insertQrCodeTicket($memberid, $ticket)
    {
        $model            = new WxQrcodes;
        $model->memberid  = $memberid;
        $model->ticket    = $ticket;
        $model->timestamp = time();
        return $model->save();
    }

    public function updateQrCodeTicket($id, $ticket)
    {
        $model = WxQrcodes::find()->where('id=:id', ['id' => $id])->one();
        $model->ticket = $ticket;
        $model->timestamp = time();
        return $model->save();
    }
}