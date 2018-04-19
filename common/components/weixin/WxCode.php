<?php
/**
 * 微信类
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */
namespace  common\components\weixin;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Component;

class WxCode extends Component
{
    /**
     * @var Wx $wx
     */
    public $wx;
    private static $lifetime  = 86400;
    private static $cachetime = 3600;
    private static $baseUrl   = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=";

    public function init()
    {
        $this->wx = new Wx;
    }

    public function get($memberid)
    {
        if (empty($memberid)) {
            return false;
        }
        $row    = $this->wx->getQrCodeTicket($memberid);
        $id     = $row['id'];
        $ticket = $row['ticket'];
        if (!empty($ticket) && intval($row['timestamp']) > time() - self::$cachetime) {
            return self::$baseUrl . $ticket;
        }
        $ticket = $this->queryWxServer($memberid);
        if (empty($ticket)) {
            return false;
        }
        if (empty($id)) {
            $this->wx->insertQrCodeTicket($memberid, $ticket);
        } else {
            $this->wx->updateQrCodeTicket($id, $ticket);
        }
        return self::$baseUrl . $ticket;
    }

    private function queryWxServer($memberid)
    {
        $access_token = $this->wx->checkAuth();
        $wxurl = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
        $qr_code_data = array(
            'action_name' => 'QR_SCENE',
            'action_info' => array('scene' => array('scene_id' => $memberid)),
            'expire_seconds' => self::$lifetime
        );

        $curl = new Curl;
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            json_encode($qr_code_data))
            ->post($wxurl);

        $qr_code = $curl->response;
        $code_before = json_decode($qr_code, true);
        $ticket = $code_before['ticket'];
        return $ticket;
    }
}