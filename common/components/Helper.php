<?php
/**
 * Helper类
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
namespace common\components;

use common\library\base\Data;
use common\models\admin\compare\CrmSents;
use common\models\CSettings;
use yii\base\Component;
use Yii;
use Curl\Curl;

class Helper extends Component
{

    private $fileArray = [];

    /**组合url
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-02-09
     * @param $reParam
     * @param $separator
     * @return string
     */
    public function mergeUrl($reParam, $separator = '/')
    {
        $urlArr = [];
        // 为了按顺序整合url, 故用了数组形式
        foreach ($reParam as $key => $val)
        {
            if ($val)
            {
                $urlArr[] = $key . '-' . $val;
            }

        }
        return $separator. implode($separator, $urlArr);
    }

    /**
     * curlPost
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-02-10
     * @param $url
     * @param array $post
     * @param array $setHeaders
     * @return mixed
     */
    public static  function curlPost($url, $post = array(), $setHeaders = [])
    {
        $curl = new Curl();
        foreach($setHeaders as $key => $setHeader) {
            $curl->setHeader($key, $setHeader);
        }
        $curl->post($url, $post);
        return self::stdClassObjectToArray($curl->response);;
    }

    /**
     * curlGet
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-02-10
     * @param $url
     * @param array $setHeaders
     * @return mixed
     */
    public static function curlGet($url, $setHeaders = [])
    {
        $curl = new Curl();
        foreach($setHeaders as $key => $setHeader) {
            $curl->setHeader($key, $setHeader);
        }
        $curl->get($url);
        return self::stdClassObjectToArray($curl->response);
    }

    /**
     * curlJson application/json
     * @author Serena Liu<serena.liu@ipptravel.com>
     * @copyright 2017-07-29
     * @param $url
     * @param array $jsonData
     * @return array
     */
    public static function curlJson($url, $jsonData = array())
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post($url, $jsonData);
        $data = self::stdClassObjectToArray($curl->response);
        return $data;
    }
    /**
     * [std_class_object_to_array 将对象转成数组]
     * @author Serena Liu<serena.liu@ipptravel.com>
     * @copyright 2017-08-01
     * @param [stdclass] $stdclassobject [对象]
     * @return [array] $array [数组]
     */
    public static function stdClassObjectToArray($stdclassobject)
    {
        $array = [];
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
        if(is_array($_array))
        {
            foreach ($_array as $key => $value){
                $value = (is_array($value) || is_object($value)) ? self::stdClassObjectToArray($value) : $value;
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * 图片域名分配[上传的图片]
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-02-09
     * @return array
     */
    public function getImgDomain()
    {
        return '//img1.quimg.com';
//        static $current = 0;
//        $current ++;
//        if($current > 6)
//        {
//            $current = 1;
//        }
//
//        return '//img' . $current . '.quimg.com';
    }

    /**
     * 图片域名分配[上传的图片]
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-02-09
     * @return array
     */
    public function getQuImg()
    {
        if(YII_ENV != 'prod') {
            return WOQU_IMAGE_BASE;
        }

        return '//www.quimg.com';
    }

    /**
     * 图片域名分配[git上传的js, css, 图片，即静态文件]
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-02-09
     * @return array
     */
    public function getStaticDomain()
    {
        return '//' . USRER_NAME . 's01' . LLTIMG_BASE;
//        static $current = 0;
//        $current ++;
//        if($current > 6)
//        {
//            $current = 1;
//        }
//        return '//' . USRER_NAME . 's0' . $current . LLTIMG_BASE;
    }

    /**
     * @param $fileBasePath
     * @return string
     */
    public  function getFileUrl($fileBasePath)
    {
        //判断文件是否已加载
        if(isset( $this->fileArray[$fileBasePath])) {
            return  $this->fileArray[$fileBasePath];
        }

        //根据域名获取文件路径
        $url = '';
        if(Yii::$app->id == 'woqu') {
            $fileBasePath = '/woqu' . $fileBasePath;
            $url = WOQU_IMAGE_BASE . $fileBasePath;
        } elseif(Yii::$app->id == 'lulutrip') {
            $fileBasePath = '/lulutrip'. $fileBasePath;
            $url = $this->getStaticDomain() . $fileBasePath;
        }

        $this->fileArray[$fileBasePath] = $url;
        $filePath = \Yii::$app->basePath . '/../static' . $fileBasePath;
        if (!file_exists($filePath)) {
            return '';
        }
        return $url . '?version=' . \Yii::$app->params['VS'];
    }
    /**
     * 通过国家，子区域 获取根区域 //增加参数 是否返回所有父区域
     * @author Daniel<daniel@lulutrip.com> 增加参数
     * @param  $state 区域代码
     * @param string $statesOfPar 父级区域数组
     * @param bool $returnArr 是否返回所有父级
     * @copyright  2016-12-19
     * @return array
     */
    public function getRegRootByState($state, $statesOfPar = '',$returnArr=false)
    {
        if(empty($statesOfPar))
        {
            $states = Data::getStates();
            $statesOfPar = $states['statesOfPar'];
        }
        $stateArr[] = $state;
        while(!empty($statesOfPar[$state])) {
            $stateArr[] = $state = $statesOfPar[$state];
        }
        return $returnArr ? array('arr'=>$stateArr,'root'=>$state) : $state;
    }

    /**
     * 通过景点 获取根区域 //增加参数 是否返回所有父区域
     * @author Daniel<daniel@lulutrip.com> 增加参数
     * @copyright 2016-12-19
     * @param $sceneId
     * @return array 返回数据
     */
    public function getRegRootBySceneId($sceneId)
    {
        $scenes = Data::getScenes();
        $state = $scenes['sceneIdState'][$sceneId];
        $state = $this->getRegRootByState($state);
        return $state;
    }

    /**
     * 解析pcode至产品种类
     * @author LT<todd@lulutrip.com>
     * @copyright 2016-09-19
     * @param $pcode
     * @return string
     */
    public function pcodeParse($pcode)
    {
        $pcode = intval($pcode);
        //ddss
        if(in_array(floor($pcode / 10000000), array(6,7,9))) {
            return 'DIY';
        }
        $type = '';
        $code = floor($pcode / 100000);
        switch($code) {
            case 3:
                $type = 'PBUS';
                break;
            case 4:
                $type = 'ACT';
                break;
            case 7:
                $type = 'HHTOUR';
                break;
            case 8:
                $type = 'PACKAGETOUR';
                break;
            case 9:
                $type = 'PBUSPACKAGE';
                break;
            case 0:
                $type = 'TOUR';
                break;
            default:
                break;
        }
        return $type;
    }

    /**
     * 电话匹配
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-07-28
     * @param string
     * @return array
     */
    public function matchTel($content) {
        $return = self::curlPost(Yii::$app->params['service']['api'] . '/admin/base/phone/list', ['phone' => 1])['data'];
        if(!empty($return['data'])){
            $phone = $return['data'];
        }else{
            $phone['USA']['number'] = '888-512-2588';
            $phone['China']['number'] = '400-821-8973';
        }
        return str_replace(array("[:TEL_US:]","[:TEL_SH:]"), array($phone['USA']['number'], $phone['China']['number']), $content);
    }

    /**
     * 邮箱@前面部分以星号隐藏
     * @author xiaopei.dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-07-28
     * @param string
     * @return array
     */
    public function emailHide($email)
    {
        $n = strpos($email, '@');
        if ($n < 3) {
            $username = substr_replace($email, "****", $n, 0);
        } else {
            $username = substr_replace($email, "****", 1, $n - 2);
        }
        return $username;
    }

    /**
     * 截取UTF8编码字符串从首字节开始指定宽度(非长度), 适用于字符串长度有限的如新闻标题的等宽度截取
     * 中英文混排情况较理想. 全中文与全英文截取后对比显示宽度差异最大,且截取宽度远大越明显.
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-07-28
     * @param string $str	UTF-8 encoding
     * @param int[option] $width 截取宽度
     * @param string[option] $end 被截取后追加的尾字符
     * @param float[option] $x3<p>
     * 	3字节（中文）字符相当于希腊字母宽度的系数coefficient（小数）
     * 	中文通常固定用宋体,根据ascii字符字体宽度设定,不同浏览器可能会有不同显示效果</p>
     * @return string
     */
    public function u8_title_substr($str, $width = 0, $end = '...', $x3 = 0) {
        global $CFG; // 全局变量保存 x3 的值
        if ($width <= 0 || $width >= strlen($str)) {
            return $str;
        }
        $arr = str_split($str);
        $len = count($arr);
        $w = 0;
        $width *= 10;

        // 不同字节编码字符宽度系数
        $x1 = 11;	// ASCII
        $x2 = 16;
        $x3 = $x3===0 ? ( $CFG['cf3']  > 0 ? $CFG['cf3']*10 : $x3 = 21 ) : $x3*10;
        $x4 = $x3;

        for ($i = 0; $i < $len; $i++) {
            if ($w >= $width) {
                $e = $end;
                break;
            }
            $c = ord($arr[$i]);
            if ($c <= 127) {
                $w += $x1;
            }
            elseif ($c >= 192 && $c <= 223) {	// 2字节头
                $w += $x2;
                $i += 1;
            }
            elseif ($c >= 224 && $c <= 239) {	// 3字节头
                $w += $x3;
                $i += 2;
            }
            elseif ($c >= 240 && $c <= 247) {	// 4字节头
                $w += $x4;
                $i += 3;
            }
        }
        return implode('', array_slice($arr, 0, $i) ). $e;
    }

    /**
     * 获取字符串长度, 字符字母长度为1，中文长度为2
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-07-28
     * @param string
     * @return array
     */
    public function strLength($string)
    {
        preg_match_all("/[\x{4e00}-\x{9fa5}]{1}/u", $string, $matches);
        $len = mb_strlen($string,'utf-8') + count($matches[0]);
        return $len;
    }

    /**
     * 邮箱正则
     * @author xiaopei.dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-08-01
     * @param string
     * @return boolean
     */
    public function ce($email) {
        return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email);
    }

    /**
     * 邮件发送记录
     * @author Justin Jia<justin.jia@ipptravel.com>
     * @copyright 2017-08-28
     * @param $email 邮箱
     * @param $subject 邮件主题
     * @param @sign
     * @return boolean
     */
    public function crmSent($email, $subject, $sign) {
        $crmSent = new CrmSents();
        $crmSent['crm_subject'] = $subject;
        $crmSent['crm_sign'] = $sign;
        $crmSent['reader_email'] = $email;
        $crmSent['datetime'] = time();
        $crmSent->save();
        return true;
    }

    /**
     * 获取公司名称
     * @author Serena Liu<serena@lulutrip.com>
     * @param $currency
     * @return mixed
     */
    public function getCompanyEntity($currency){
        $settings = CSettings::findOne(['skey' => 'company_entity']);
        $sValue = unserialize($settings->svalue);
        foreach($sValue as $val){
            if($val['domain'] == 'lulutrip.com' && in_array($currency, $val['currency'])){
                return $val['entity'];
            }
        }
        return 'Lulutrip Inc.';
    }

    /**
     * 过滤数组
     * @author xiaopei.dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-09-14
     * @param array $array
     * @return array
     */
    public function trimArray($array) {
        $out = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $out[$key] = trimArray($value);
            } else {
                $out[$key] = trim($value);
            }
        }
        return $out;
    }

    /**
     * 计算当前毫秒
     * @author Serena Liu<serena@ipptravel.com>
     * @copyright 2017-10-11
     * @return float
     */
    public function getMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    /**
     * 处理价格小数点的方法
     * @author Victor Tang <victor.tang@ipptravel.com>
     * @copyright 2017-10-27
     * @param $price
     * @return float
     */
    public function ceil2($price) {
        return round($price + 0.5 / 100, 2,PHP_ROUND_HALF_DOWN);
    }

    /**
     * 处理价格小数点的方法2
     * @author Victor Tang <victor.tang@ipptravel.com>
     * @copyright 2017-10-27
     * @param $price
     * @return float
     */
    public function floor2($price) {
        return round($price - 0.5 / 100, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * 获取客服电话列表
     * @author Xiaopei Dou <xiaopei.dou@ipptravel.com>
     * @copyright 2017-12-29
     */
    public function getCustomerServicePhoneList() {
        $servicePhoneList = self::curlPost(Yii::$app->params['service']['api'] . '/admin/base/phone/list');
        return empty($servicePhoneList)? [] : $servicePhoneList;
    }

    /**
     * 获取客服电话
     * @author Victor Tang <victor.tang@ipptravel.com>
     * @copyright 2017-12-06
     */
    public function getCustomerServicePhone() {
        $phone = self::curlPost(Yii::$app->params['service']['api'] . '/admin/base/phone/list', ['phone' => 1]);
        return empty($phone['data']) ? '888-512-2588' : $phone['data'][Yii::$app->params['IPArea']]['number'];
    }

    /**
     * 将内容进行UNICODE编码，编码后的内容格式：\u56fe\u7247
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-12-13
     */
    public function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0)
            {    // 两个字节的文字 每个字节变成16进制后的长度是否为1，如果为1前面要补"0"
                $str_1 = base_convert(ord($c), 10, 16);if(strlen($str_1) == 1) $str_1 = "0" . $str_1;
                $str_2 = base_convert(ord($c2), 10, 16);if(strlen($str_2) == 1) $str_2 = "0" . $str_2;
                $str .= '\u'.$str_1.$str_2;
            }
            else
            {
                $str .= $c2;
            }
        }
        return $str;
    }

    /**
     * 将UNICODE编码后的内容进行解码
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-12-13
     */
    public function unicode_decode($name)
    {
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches))
        {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++)
            {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0)
                {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code).chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                }
                else
                {
                    $name .= $str;
                }
            }
        }
        return $name;
    }

    /**
     * 实现对多维数组按照某个键值排序并保留数字索引
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-12-20
     */
    public function array_sort($array,$keys,$type='asc'){
        //$array为要排序的数组,$keys为要用来排序的键名,$type默认为升序排序
        $keysValue = $newArray = array();
        foreach ($array as $k=>$v){
            $keysValue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysValue);
        }else{
            arsort($keysValue);
        }
        reset($keysValue);
        foreach ($keysValue as $k=>$v){
            $newArray[$k] = $array[$k];
        }
        return $newArray;
    }

    /**
     * 跳转404页面
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-25
     */
    public function set404() {
        header("HTTP/1.1 404 Not Found");
        $ctt = Yii::$app->view->renderFile("@lulutrip/web/404.html");
        die($ctt);
    }

    /**
     * 二维数组验证一个值是否存在，并返回对应key值
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-25
     */
    public function arrayMultiSearch($value, $array) {
        foreach($array as $key => $item) {
            if(!is_array($item)) {
                if ($item == $value) {
                    return $key;
                } else {
                    continue;
                }
            }

            if(in_array($value, $item)) {
                return $key;
            } else {
                $res = self::arrayMultiSearch($value, $item);
                if($res){
                    return $res;
                }
            }
        }
        return false;
    }


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

}