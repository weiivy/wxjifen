<?php


namespace api\library\weixin;

use api\library\weixin\SDK\WXBizMsgCrypt;
use Yii;

/**
 * 微信公众号开发基础API类
 * https://mp.weixin.qq.com/
 *
 * @author ZouYiliang <it9981@gmail.com>
 * @since   1.0
 */
class Api
{
    protected $appId;
    protected $appSecret;
    protected $token;
    protected $encodingAesKey;
    protected $encodingAesKeyLast;

    protected $accessToken;

    //加密方式
    private $encryptType; //raw aes

    //本次请求所使用的encodingAesKey ($encodingAesKey或$encodingAesKeyLast其中一个)
    private $currentEncodingAesKey;

    //微信平台post过来的XML数据(已解密)
    private $msgXmlStr;
    //将xml数据转为对象
    private $msgObj;

    //微信平台设置的加密类型
    const ENCRYPT_TYPE_RAW = 'raw';
    const ENCRYPT_TYPE_AES = 'aes';

    /**
     * 构造方法 可以使用一个关联数组作为参数
     * @param array|string $appId
     * @param string|null $appSecret
     * @param string|null $token
     * @param string|null $encodingAesKey
     * @param string|null $encodingAesKeyLast
     */
    public function __construct($appId, $appSecret = null, $token = null, $encodingAesKey = null, $encodingAesKeyLast = null)
    {
        if (is_array($appId)) {
            extract($appId, EXTR_OVERWRITE);
        }

        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->encodingAesKeyLast = $encodingAesKeyLast;
    }

    /**
     * 返回appId
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * 使用token验证请求是否来自微信公共平台
     * @return bool
     */
    public function checkSignature()
    {
        //验证token
        $signature = isset($_GET['signature']) ? $_GET['signature'] : '';
        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        return $tmpStr === $signature;
    }

    /**
     * 返回微信传递过来的原始xml字符串(解密后的)
     * @return string|null
     */
    public function getMsgXmlStr()
    {
        if ($this->msgXmlStr === null) {
            /*if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
                Log::error('"HTTP_RAW_POST_DATA" is not exists in $GLOBALS');
                return null;
            }
            $this->msgXmlStr = $this->decrypt($GLOBALS['HTTP_RAW_POST_DATA']);
            */

            $postData = file_get_contents('php://input');
            if (empty($postData)) {
                Log::error('"HTTP_RAW_POST_DATA" is not exists in $GLOBALS');
                return null;
            }
            $this->msgXmlStr = $this->decrypt($postData);
        }
        return $this->msgXmlStr;
    }

    /**
     * 返回微信服务器请求的原始xml对象，字段大小写与开放平台文档一致
     * @return object|null
     */
    public function getMsgObj()
    {
        if ($this->msgObj === null) {
            $obj = @simplexml_load_string($this->getMsgXmlStr(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($obj === false) {
                Yii::error("simplexml_load_string error\n" . $this->getMsgXmlStr());
                return null;
            }
            //将SimpleXMLElement对象转为stdClass对象
            $this->msgObj = (object)(array)$obj;
        }
        return $this->msgObj;
    }

    /**
     * 尝试解密数据，从$_GET['encrypt_type']中获取加密类型，如果未加密，原样返回
     * @param string $message
     * @return string|null
     */
    public function decrypt($message)
    {
        //加密类型
        if ($this->encryptType === null) {
            $this->encryptType = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] == 'aes')) ? static::ENCRYPT_TYPE_AES : static::ENCRYPT_TYPE_RAW;
        }

        //未加密时原样返回
        if ($this->encryptType == static::ENCRYPT_TYPE_RAW) {
            return $message;
        }

        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $msgSignature = isset($_GET['msg_signature']) ? $_GET['msg_signature'] : '';

        //解密
        if ($this->encryptType == static::ENCRYPT_TYPE_AES) {

            $this->currentEncodingAesKey = $this->encodingAesKey;
            if ($this->decryptMsg($msgSignature, $timestamp, $nonce, $message, $decryptMsg)) {
                return $decryptMsg;
            }

            if ($this->encodingAesKeyLast !== null) {
                $this->currentEncodingAesKey = $this->encodingAesKeyLast;
                if ($this->decryptMsg($msgSignature, $timestamp, $nonce, $message, $decryptMsg)) {
                    return $decryptMsg;
                }
            }
        }
        Yii::error('decrypt error');
        return null;
    }

    /**
     * 响应的消息进行加密
     * @param $message
     * @return string|null
     */
    public function encrypt($message)
    {
        //请求未加密时，回复明文内容
        if ($this->encryptType == static::ENCRYPT_TYPE_RAW) {
            return $message;
        }

        //加密
        if ($this->encryptType == static::ENCRYPT_TYPE_AES) {

            $timestamp = time();
            $nonce = uniqid();

            if ($this->encryptMsg($message, $timestamp, $nonce, $encryptMsg)) {
                return $encryptMsg;
            }
        }

        Yii::error('encrypt error');
        return null;
    }

    /**
     * 解密微信推送的消息
     * @param string $msgSignature
     * @param string $timestamp
     * @param string $nonce
     * @param string $encryptMsg
     * @param string $msg 解密成功后的内容或错误消息
     * @return bool
     */
    public function decryptMsg($msgSignature, $timestamp, $nonce, $encryptMsg, &$msg)
    {
        //传入公众号第三方平台的token（申请公众号第三方平台时填写的接收消息的校验token）, 公众号第三方平台的appid, 公众号第三方平台的 EncodingAESKey（申请公众号第三方平台时填写的接收消息的加密symmetric_key）
        $pc = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);

        // 第三方收到公众号平台发送的消息
        $errCode = $pc->decryptMsg($msgSignature, $timestamp, $nonce, $encryptMsg, $msg);
        return $errCode == 0;
    }

    /**
     * 加密
     * @param $replyMsg
     * @param $timestamp
     * @param $nonce
     * @param $msg
     * @return bool
     */
    public function encryptMsg($replyMsg, $timestamp, $nonce, &$msg)
    {
        //传入公众号第三方平台的token（申请公众号第三方平台时填写的接收消息的校验token）, 公众号第三方平台的appid, 公众号第三方平台的 EncodingAESKey（申请公众号第三方平台时填写的接收消息的加密symmetric_key）
        $pc = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);

        // 第三方收到公众号平台发送的消息
        $errCode = $pc->encryptMsg($replyMsg, $timestamp, $nonce, $msg);
        return $errCode == 0;
    }


    public function curlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function curlPost($url, $post_data)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //POST
        curl_setopt($ch, CURLOPT_POST, 1);
        //post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 设置AccessToken 主要用于别的平台共享AccessToken时
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 获取公众号的全局唯一票据accessToken，公众号主动调用各接口时都需使用accessToken
     * accessToken默认有效时间为7200秒，一天最多只能调用2000次(测试号200次)
     *
     * @param bool $useCache 是否使用缓存
     * @return string|null
     */
    public function getAccessToken($useCache = true)
    {
        if ($this->accessToken !== null) {

            return $this->accessToken;
        }

        //通过缓存的时间，判断是否过期，已过期，则从服务器获取
        $cacheKey = $this->appId . 'accessToken';
        if (!$useCache) {
            Yii::$app->cache->forget($cacheKey);
        } else {
            $accessToken = Yii::$app->cache->get($cacheKey);
//            if ($accessToken !== false) {
//                return $accessToken;
//            }
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
        $url = sprintf($url, $this->appId, $this->appSecret);

        $jsonStr = $this->curlGet($url);

        //解析返回结果
        //返回格式 {"access_token":"ACCESS_TOKEN","expires_in":7200}
        //{"access_token":"43BKbLBjBnwH600H5TMgGrNhkMesqA7hhi-2V__RWXd2SYohl2OI-nCZsT_SIztF6H43Gq8gd1Dt5kJgIytt-NNNxCg86UY3qIsSVlgIbVXSlx6AKT9TCZ3BRXjxvT0erRpzHTIaUuaJBDUoUykTqA","expires_in":7200}
        $arr = json_decode($jsonStr, true);

        if (is_array($arr) && array_key_exists('access_token', $arr)) {
            $accessToken = $arr['access_token'];

            $expires = (int)$arr['expires_in'];

            //默认时间是7200秒(120分钟)
            Yii::$app->cache->set($cacheKey, $accessToken, $expires);
            return $accessToken;
        }

        Yii::error("get access token error\n" . $jsonStr);
        return null;
    }



    /**
     * 获取用户信息
     * @param string $openid
     * @return null|array 成功返回数组
     *        subscribe        用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息
     *        openid            用户的标识，对当前公众号唯一
     *        headimgurl        用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     *        nickname        用户的昵称
     *        sex                用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *        city            用户所在城市
     *        province        用户所在省份
     *        country            用户所在国家
     *        subscribe_time    用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
     *        language        用户的语言，简体中文为zh_CN
     *
     */
    public function getUserInfo($openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
        $url = sprintf($url, $this->getAccessToken(), $openid);

        $output = $this->curlGet($url);
        /*
         {
            "subscribe": 1,
            "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
            "nickname": "Band",
            "sex": 1,
            "language": "zh_CN",
            "city": "广州",
            "province": "广东",
            "country": "中国",
            "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
            "subscribe_time": 1382694957
        }*/

        $arr = json_decode($output, true);

        if (is_array($arr) && array_key_exists('openid', $arr)) {
            return $arr;
        }

        //{"errcode":40013,"errmsg":"invalid appid"}
        Yii::error("getUserInfo error \n" . $output);
        return null;
    }



    /**
     * 获取微信用户信息 此方法会跳转到微信授权页面获取用户授权然后返回
     *
     * @param bool $openidOnly 仅返回openid 响应速度会更快，并且不需要用户授权
     * @param string|null $middleUrl
     * @return array 成功返回用户信息
     * [
     *    'openid'=>            与公众帐号对应的用户的唯一标识
     *    'nickname'=>        用户昵称
     *    'sex'=>                用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *    'province'=>        用户个人资料填写的省份
     *    'city'=>            普通用户个人资料填写的城市
     *    'country'=>            国家，如中国为CN
     *    'headimgurl'=>        用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     *    'privilege'=>[]        用户特权信息数组，如微信沃卡用户为（chinaunicom）
     * ]
     *
     * @throws \Exception
     */
    public function getOpenAuthUserInfo($openidOnly = false, $middleUrl = null)
    {
        $flashKey = 'oAuthAuthState';
        $session = Yii::$app->session;

        //从微信oAuth页面跳转回来
        if ($session->get($flashKey)) {

            $state = $session->get($flashKey);
            if (!(isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] === $state)) {
                Yii::error('网页授权获取用户基本信息错误，请检查appid等相关信息');
                return null;
            }

            $code = $_GET['code']; //用来换到accessToken的code

            //获取AccessToken
            $arr = $this->getOauthAccessToken($code);

            if ($openidOnly === true) {

                if (is_array($arr) && array_key_exists('openid', $arr)) {
                    return $arr;
                }
                return null;
            }

            //获取用户信息
            $userInfo = $this->getOauthUserInfo($arr['openid'], $arr['access_token']);
            return $userInfo;
        }

        //跳转到微信oAuth授权页面
        $state = uniqid();
        $session->set($flashKey, $state);

        //当前url
        $redirectUri = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        //通过一个中间url跳转
        if ($middleUrl !== null) {
            //http://wechat.chehutong.cn/api/wechat/authorizeRedirect

            //middleUrl指向authorize-redirect.php，代码代码如下
            //$code = urlencode(isset($_GET['code']) ? $_GET['code'] : '');
            //$state = urlencode(isset($_GET['state']) ? $_GET['state'] : '');

            $url = isset($_GET['url']) ? $_GET['url'] : '';

            if (strlen($url) > 0) {

                $url = urldecode($url);

                if (strpos($url, '?') === false) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }
                header('Location: ' . $url);
                exit;

            } else {
                echo 'url is empty!';
            }
            $redirectUri = $redirectUri . 'url=' . urlencode($middleUrl);
        }

        //跳转到微信授权url
        $url = $this->getOauthAuthorizeUrl($redirectUri, $state, $openidOnly === true ? 'snsapi_base' : 'snsapi_userinfo');
        header('Location: ' . $url);
        exit;
    }

    /**
     * 网页授权获取用户基本信息 流程第1步
     * 引导用户进入授权页面的Url (用户允许后，获取code)
     * 调用本方法前，需要配置 appid属性
     * http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
     * @param $redirect_uri 授权后重定向的回调链接地址
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo（弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public function getOauthAuthorizeUrl($redirect_uri, $state = '0', $scope = 'snsapi_userinfo')
    {

        $appId = $this->appId;

        //使用urlencode对链接进行处理
        $redirect_uri = urlencode($redirect_uri);

        //返回类型，请填写code
        $response_type = 'code';

        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appId}&redirect_uri={$redirect_uri}&response_type={$response_type}&scope={$scope}&state={$state}#wechat_redirect";
        return $url;
    }

    /**
     * 网页授权获取用户基本信息 流程第2步
     * 通过code换取网页授权access_token
     * 调用本方法前，需要配置 appid 和 secret属性
     * @param $code
     * @return array | null
     */
    public function getOauthAccessToken($code)
    {
        $appId = $this->appId;
        $secret = $this->appSecret;
        $grant_type = 'authorization_code';

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appId}&secret={$secret}&code={$code}&grant_type={$grant_type}";
        $output = $this->curlGet($url);
        $output = '{"access_token":"11_juhSmNBL2QJWjPq6zIVIhJBUqSE3aYMBLbY9_DQotYtQQDVJa2ZbVjepo1EF99lXdy7mtHdmPP5_GArMwzFE3w","expires_in":7200,"refresh_token":"11_XqDAwf7imzExHgsATro1FcK4xwKYbSlhf9R8PSlCv9WeOkcjjuoanNRSWAnuSUccvUHs3cErfz6l72gsuiXd6g","openid":"oCGeK1bpywpdiqyj1IWNGlPnqEE0","scope":"snsapi_userinfo"}';


        /*{
           "access_token":"ACCESS_TOKEN",
           "expires_in":7200,					 	access_token接口调用凭证超时时间，单位（秒）
           "refresh_token":"REFRESH_TOKEN",
           "openid":"OPENID",
           "scope":"SCOPE"
        }*/

        //{"errcode":40029,"errmsg":"invalid code"}

        $arr = json_decode($output, true);
        if (is_array($arr) && array_key_exists('access_token', $arr)) {
            return $arr;
        }
        return null;
    }

    /**
     * 网页授权获取用户基本信息 流程第3步
     * 刷新access_token（如果需要）
     */
    public function refreshOauthAccessToken()
    {
        //todo
    }

    /**
     * 网页授权获取用户基本信息 流程第4步
     * 拉取用户信息
     * http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
     * @param $openId
     * @param $accessToken
     * @return array
     * [
     *    'openid'=>            用户的唯一标识
     *    'nickname'=>        用户昵称
     *    'sex'=>                用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *    'province'=>        用户个人资料填写的省份
     *    'city'=>            普通用户个人资料填写的城市
     *    'country'=>            国家，如中国为CN
     *    'headimgurl'=>        用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     *    'privilege'=>[]        用户特权信息数组，如微信沃卡用户为（chinaunicom）
     *
     * ]
     */
    public function getOauthUserInfo($openId, $accessToken)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
        $output = $this->curlGet($url);

        /*{
           "openid":" omd6J5bHKLRYkL1MEcp6WE7BauM",
           " nickname": NICKNAME,
           "sex":"1",
           "province":"PROVINCE"
           "city":"CITY",
           "country":"COUNTRY",
            "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
            "privilege":[
                "PRIVILEGE1"
                "PRIVILEGE2"
            ]
        }*/

        //{"errcode":40003,"errmsg":" invalid openid "}

        $arr = json_decode($output, true);
        if (array_key_exists('openid', $arr)) {
            return $arr;
        }
        return null;
    }


    /**
     * 返回js sdk SignPackage
     *
     * [php] $signPackage = $api->getSignPackage(); [/php]
     *
     * <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
     * <script>
     * wx.config({
     *     appId: '<?php echo $signPackage["appId"];?>',
     *     timestamp: <?php echo $signPackage["timestamp"];?>,
     *     nonceStr: '<?php echo $signPackage["nonceStr"];?>',
     *     signature: '<?php echo $signPackage["signature"];?>',
     *     jsApiList: [
     *         // 所有要调用的 API 都要加到这个列表中
     *         'onMenuShareAppMessage',
     *         'onMenuShareTimeline',
     *         'onMenuShareQQ',
     *         'onMenuShareWeibo',
     *
     *         //拍照或相册
     *         'chooseImage',
     *         //上传图片
     *         'uploadImage'
     *     ]
     * });
     * wx.ready(function () {
     *     // 在这里调用 API
     * });
     * </script>
     *
     * @return array
     */
    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();

        $url = Yii::$app->params['h5Url'] . '/';
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    public function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 公众号用于调用微信JS接口的临时票据
     *
     * http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95
     * jsapi_ticket 的type为jsapi (腾讯demo中的JSSDK.php代码中type为1 不知为何)
     * 卡券 api_ticket 的type为 wx_card
     *
     * @param string $type
     * @return string
     */
    public function getJsApiTicket($type = 'jsapi')
    {

        $cacheKey = $this->appId . $type . 'jsapi_ticket';

        $ticket = Yii::$app->cache->get($cacheKey);

//        if ($ticket !== false) {
//            return $ticket;
//        }

        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=%s&access_token=%s";
        $data = json_decode($this->curlGet(sprintf($url, $type, $this->getAccessToken())), true);

        if (is_array($data) && array_key_exists('errcode', $data) && $data['errcode'] == '40001') {

            //access_token无效，尝试跳过缓存从新获取access_token
           Yii::warning('access_token cache error');
           $data = json_decode($this->curlGet(sprintf($url, $type, $this->getAccessToken(false))), true);
        }

        if (is_array($data) && array_key_exists('ticket', $data)) {

            $ticket = $data['ticket'];
            Yii::$app->cache->set($cacheKey, $ticket, $data['expires_in']);

            return $ticket;
        }

        Yii::error($data);
        return null;
    }







}