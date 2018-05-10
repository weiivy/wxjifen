<?php

namespace api\library\wxpay;

/**
 * 微信支付
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class WxpayService
{
    protected $mchid;
    protected $appid;
    protected $key;

    public function __construct($mchid, $appid, $key)
    {
        $this->mchid = $mchid; // 微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
        $this->appid = $appid; //公众号APPID 通过微信支付商户资料审核后邮件发送
        $this->key = $key;     //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
    }

    /**
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     *      https://mp.weixin.qq.com/  微信支付-开发配置-测试目录
     *      测试目录 http://mp.izhanlue.com/paytest/    最后需要斜线，(需要精确到二级或三级目录)
     * @param $timestamp
     * @return array
     * @throws \Exception
     */
    public function createJsBizPackage($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp)
    {

        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );

        $unified = array(
            'appid' => $config['appid'],
            'attach' => '支付',                          //商家数据包，原样返回
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notifyUrl,
            'openid' => $openid,                        //rade_type=JSAPI，此参数必传
            'out_trade_no' => 'W'.$outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'total_fee' => intval($totalFee * 100),             //单位 转为分
            'trade_type' => 'JSAPI',
        );

        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        /*
        <xml>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <return_msg><![CDATA[OK]]></return_msg>
        <appid><![CDATA[wx00e5904efec77699]]></appid>
        <mch_id><![CDATA[1220647301]]></mch_id>
        <nonce_str><![CDATA[1LHBROsdmqfXoWQR]]></nonce_str>
        <sign><![CDATA[ACA7BC8A9164D1FBED06C7DFC13EC839]]></sign>
        <result_code><![CDATA[SUCCESS]]></result_code>
        <prepay_id><![CDATA[wx2015032016590503f1bcd9c30421762652]]></prepay_id>
        <trade_type><![CDATA[JSAPI]]></trade_type>
        </xml>
        */

        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($unifiedOrder === false) {
            throw new \Exception('parse xml error', 0);
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            throw new \Exception($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            throw new \Exception($unifiedOrder->err_code);
            /*
            NOAUTH  商户无此接口权限
            NOTENOUGH  余额不足
            ORDERPAID  商户订单已支付
            ORDERCLOSED  订单已关闭
            SYSTEMERROR  系统错误
            APPID_NOT_EXIST     APPID不存在
            MCHID_NOT_EXIST  MCHID不存在
            APPID_MCHID_NOT_MATCH appid和mch_id不匹配
            LACK_PARAMS 缺少参数
            OUT_TRADE_NO_USED 商户订单号重复
            SIGNERROR 签名错误
            XML_FORMAT_ERROR XML格式错误
            REQUIRE_POST_METHOD 请使用post方法
            POST_DATA_EMPTY post数据为空
            NOT_UTF8 编码格式错误
           */
        }

        //$unifiedOrder->trade_type  交易类型  调用接口提交的交易类型，取值如下：JSAPI，NATIVE，APP
        //$unifiedOrder->prepay_id  预支付交易会话标识 微信生成的预支付回话标识，用于后续接口调用中使用，该值有效期为2小时
        //$unifiedOrder->code_url 二维码链接 trade_type为NATIVE是有返回，可将该参数值生成二维码展示出来进行扫码支付

        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => $timestamp,
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
        );

        $arr['paySign'] = self::getSign($arr, $config['key']);

        return $arr;
    }

    public function notify()
    {

        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        \Yii::error($postStr);

        /*
        $postStr = '<xml>
        <appid><![CDATA[wx00e5904efec77699]]></appid>
        <attach><![CDATA[支付测试]]></attach>
        <bank_type><![CDATA[CMB_CREDIT]]></bank_type>
        <cash_fee><![CDATA[1]]></cash_fee>
        <fee_type><![CDATA[CNY]]></fee_type>
        <is_subscribe><![CDATA[Y]]></is_subscribe>
        <mch_id><![CDATA[1220647301]]></mch_id>
        <nonce_str><![CDATA[a0tZ41phiHm8zfmO]]></nonce_str>
        <openid><![CDATA[oU3OCt5O46PumN7IE87WcoYZY9r0]]></openid>
        <out_trade_no><![CDATA[550bf2990c51f]]></out_trade_no>
        <result_code><![CDATA[SUCCESS]]></result_code>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <sign><![CDATA[F6F519B4DD8DB978040F8C866C1E6250]]></sign>
        <time_end><![CDATA[20150320181606]]></time_end>
        <total_fee>1</total_fee>
        <trade_type><![CDATA[JSAPI]]></trade_type>
        <transaction_id><![CDATA[1008840847201503200034663980]]></transaction_id>
        </xml>';
        */

        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }
        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }

        $arr = (array)$postObj;
        unset($arr['sign']);

        if (self::getSign($arr, $config['key']) == $postObj->sign) {

            // $mch_id = $postObj->mch_id;  //微信支付分配的商户号
            // $appid = $postObj->appid; //微信分配的公众账号ID
            // $openid = $postObj->openid; //用户在商户appid下的唯一标识
            // $transaction_id = $postObj->transaction_id;//微信支付订单号
            // $out_trade_no = $postObj->out_trade_no;//商户订单号
            // $total_fee = $postObj->total_fee; //订单总金额，单位为分
            // $is_subscribe = $postObj->is_subscribe; //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
            // $attach = $postObj->attach;//商家数据包，原样返回
            // $time_end = $postObj->time_end;//支付完成时间

            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';

            return $postObj;
        }
    }

    /**
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $amount 收款总费用 单位元
     * @return array
     * @throws \Exception
     */
    public function payback($openid, $amount)
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );

        $unified = array(
            'mch_appid' => $config['appid'],
            'mchid' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'partner_trade_no' => time() . mt_rand(10000, 99999),
            'openid' => $openid,
            'check_name' => 'FORCE_CHECK', //强制验证真实姓名
            're_user_name' => '张唯唯', //真实姓名
            'amount' => $amount * 100,     //单位 转为分
            'desc' => '用户零钱提现',
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
        );

        $unified['sign'] = self::getSign($unified, $config['key']);
        $options = [
            CURLOPT_SSLCERT => \Yii::$app->getBasePath() . '/ca/apiclient_cert.pem',
            CURLOPT_SSLKEY  => \Yii::$app->getBasePath() . '/ca/apiclient_key.pem'
        ];
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', self::arrayToXml($unified), $options);
//        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $str);
        echo $responseXml;die;
        /*
        <xml>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <return_msg><![CDATA[OK]]></return_msg>
        <appid><![CDATA[wx00e5904efec77699]]></appid>
        <mch_id><![CDATA[1220647301]]></mch_id>
        <nonce_str><![CDATA[1LHBROsdmqfXoWQR]]></nonce_str>
        <sign><![CDATA[ACA7BC8A9164D1FBED06C7DFC13EC839]]></sign>
        <result_code><![CDATA[SUCCESS]]></result_code>
        <prepay_id><![CDATA[wx2015032016590503f1bcd9c30421762652]]></prepay_id>
        <trade_type><![CDATA[JSAPI]]></trade_type>
        </xml>
        */

        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($unifiedOrder === false) {
            throw new \Exception('parse xml error', 0);
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            throw new \Exception($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            throw new \Exception($unifiedOrder->err_code);
            /*
            NOAUTH  商户无此接口权限
            NOTENOUGH  余额不足
            ORDERPAID  商户订单已支付
            ORDERCLOSED  订单已关闭
            SYSTEMERROR  系统错误
            APPID_NOT_EXIST     APPID不存在
            MCHID_NOT_EXIST  MCHID不存在
            APPID_MCHID_NOT_MATCH appid和mch_id不匹配
            LACK_PARAMS 缺少参数
            OUT_TRADE_NO_USED 商户订单号重复
            SIGNERROR 签名错误
            XML_FORMAT_ERROR XML格式错误
            REQUIRE_POST_METHOD 请使用post方法
            POST_DATA_EMPTY post数据为空
            NOT_UTF8 编码格式错误
           */
        }
        return (array)$unifiedOrder;
    }

    /**
     * curl get
     *
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function curlPost($url = '', $postData = '', $options = array())
    {
        var_dump($options);die;
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $data = curl_exec($ch);
        echo curl_errno($ch);;die;
        curl_close($ch);
        return $data;
    }

    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 例如：
     * appid：    wxd930ea5d5a258f4f
     * mch_id：    10000100
     * device_info：  1000
     * Body：    test
     * nonce_str：  ibuaiVcKdpRxkhJA
     * 第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
     * stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
     * d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
     * 第二步：拼接支付密钥：
     * stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
     * sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A9CF3B7"
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

}