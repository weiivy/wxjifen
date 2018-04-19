<?php
/**
 * ip组件
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
namespace common\components;
use yii\base\Component;
use common\models\DbIp;
use Yii;

class Ip extends Component
{
    /**
     * 获取当前IP
     * @author Serena Liu<serena@lulutrip.com>
     * @copyright 2017-03-01
     *
     * @return string 返回数据
     */
    public function realIP() {
        static $realip = NULL;

        if ($realip !== NULL)
        {
            return $realip;
        }

        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_TRUECLIENTIP']) && trim($_SERVER['HTTP_TRUECLIENTIP']) != 'unknown')
            {
                $realip = $_SERVER['HTTP_TRUECLIENTIP'];
            }
            elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr AS $ip)
                {
                    $ip = trim($ip);

                    if ($ip != 'unknown')
                    {
                        $realip = $ip;

                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = 'unknown';
                }
            }
        }
        else
        {
            if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP'))
            {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else
            {
                $realip = getenv('REMOTE_ADDR');
            }
        }

        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : 'unknown';

        return $realip;
    }

    /**
     * 根据IP匹配当前的货币语言以及区域
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-02-15
     * @param $ip
     *
     * @return array 返回数据
     */
    public function matchArea($ip)
    {
        $location = $this->getLocation($ip);
        $currencyCountries = Yii::$app->params['ipCountries']['currencyCountries'];

        foreach($currencyCountries as $currency => $countries)
        {
            foreach($countries as $area => $value)
            {
                if(is_array($value['children']) && in_array($location['country'], $value['children']))
                {
                    return array_merge($value['fields'], ['currency' => $currency, 'area' => $area]);
                }
            }
        }

        return array("lang" => "CN", "currency" => "USD", "area" => "USA", "countrycode" => "US");
    }


    /**
     * 获取当前用户IP对应的地址信息
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-02-15
     * @param $ip
     *
     * @return array 返回数据
     */
    public function getLocation($ip) {
        $countries = Yii::$app->params['ipCountries']['countries'];
        $location = [];
        if(empty($location) || $location['country_en'] == 'Unknown') {
            $location = self::getIpFromApi($ip);
            $location['country_en'] = str_replace(array("CZ88.NET"), array(""), $location['country_en']);
            $location['area'] = str_replace(array("CZ88.NET"), array(""), $location['area']);
            $location['city'] = str_replace(array("CZ88.NET"), array(""), $location['city']);

            $location['country'] = ($location['country_en'] == 'Unknown') ? "未知" : $countries[$location['country_en']];
        }
        return $location;
    }

    /**
     * 根据IP查询地址信息
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-02-15
     * @param $ip
     *
     * @return array 返回数据
     */
    private function getIpFromApi($ip)
    {
        $result = array(
            'ip' => $ip,
            'beginip' => '',
            'endip' => '',
            'country_en' => 'Unknown',
            'country' => '',
            'area' => '',
            'city' => ''
        );
        if(empty($ip) || !preg_match('/^(1\\d{2}|2[0-4]\\d|25[0-5]|[1-9]\\d|[1-9])\\.(1\\d{2}|2[0-4]\\d|25[0-5]|[1-9]\\d|\\d)\\.(1\\d{2}|2[0-4]\\d|25[0-5]|[1-9]\\d|\\d)\\.(1\\d{2}|2[0-4]\\d|25[0-5]|[1-9]\\d|\\d)$/', $ip)) {
            return $result;
        }

        $data = $this->getAddressFromIp($ip);
        if(!empty($data)) {
            $result = array(
                'ip' => $ip,
                'beginip' => $data['ip_start'],
                'endip' => $data['ip_end'],
                'country_en' => $data['country'],
                'country' => '',
                'area' => $data['stateprov'],
                'city' => $data['city']
            );
        }
        return $result;
    }
    /**
     * 根据Ip获取地址信息
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-02-14
     * @param $ip IP地址
     *
     * @return array 返回数据
     */
    private function getAddressFromIp($ip)
    {
        $addrType = static::addrType($ip);
        $model = DbIp::find()
            ->select('*')
            ->where('addr_type =:addr_type and ip_start <=:ip_start', array(':addr_type' => $addrType, ':ip_start' => inet_pton($ip)))
            ->orderBy('ip_start desc')
            ->limit(1);
        $data= $model->one();
        if(empty($data)) return array();
        $data['ip_start'] = inet_ntop($data['ip_start']);
        $data['ip_end'] = inet_ntop($data['ip_end']);
        return $data;

    }
    /**
     * 获取IP类型
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @param $addr
     * @return string
     * @throws \yii\base\Exception
     */
    private static function addrType($addr) {
        if (ip2long($addr) !== false) {
            return "ipv4";
        } else if (preg_match('/^[0-9a-fA-F:]+$/', $addr) && @inet_pton($addr)) {
            return "ipv6";
        }
        throw new Exception("unknown address type for {$addr}");
    }
}