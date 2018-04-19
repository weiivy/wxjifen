<?php
namespace common\library\touricoCruise;

/**
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
class TouricoSoap {
    public $url;
    public $username;
    public $password;
    public $culture;
    public $version;
    public $ns;

    public $client;
    public $header;
    public $showError = true;

    public $retry = 3;

    public function __construct() {
        if(YII_ENV == 'prod') {
            $this->url = 'http://cruisews.touricoholidays.com/CruiseServiceFlow.svc?wsdl';
            $this->username = 'ITr101';
            $this->password = '1Ty@cBBy';
            $this->culture = 'zh_CN';
            $this->version = '9';
            $this->ns = 'http://tourico.com/webservices/';
        } else {
            //DEV
            $this->url = 'http://demo-cruisews.touricoholidays.com/CruiseServiceFlow.svc?wsdl';
            $this->username = 'lul123';
            $this->password = '111111';
            $this->culture = 'zh_CN';
            $this->version = '9';
            $this->ns = 'http://tourico.com/webservices/';
        }
        try {
            $this->client = new \SoapClient($this->url);
            $this->header = new \SoapHeader($this->ns, "LoginHeader", array(
                "UserName" => $this->username,
                "Password" => $this->password,
                "Culture" => $this->culture,
                "Version" => $this->version
            ));
            $this->client->__setSoapHeaders($this->header);
        } catch (\SoapFault $e) {
            throw new \Exception(json_encode($e), 501);
        }
    }

    /**
     * 发送请求
     * @author LT<todd@lulutrip.com>
     * @copyright 2017-04-10
     * @param $function
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public function sendRequest($function, $param = array()) {
        try {
//            print_r([$function, $param]);die;
//            return ;
            $result = $this->client->__soapCall($function, $param);
//            $this->retry = 3;
            return $this->parseRes($result);
        } catch(\SoapFault $e) {
//            while ($this->retry--) {
//                sleep(rand(10, 30));
//                $this->sendRequest($function, $param);
//            }
//            $this->retry = 3;
            throw new \Exception(json_encode($e), 502);
        }
    }

    /**
     * 解析数据并转化位数组
     * author LT<todd@lulutrip.com>
     * copyright 2017-04-10
     * @param $obj
     * @return array
     */
    private function parseRes($obj) {
        $arr = [];
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach($_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? $this->parseRes($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
}