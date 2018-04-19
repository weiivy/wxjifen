<?php
/**
 * 缓存组件
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */
namespace common\components;

use yii\base\Component;
use Yii;
use yii\helpers\Json;

class Cache extends Component
{
    public $config;

    public function get($key)
    {
        $config = $this->config;
        return Json::decode(Yii::$app->$config->get($key));
    }

    public function set($key, $value, $time = 300)
    {
        $config = $this->config;
        $value  = Json::encode($value);
        Yii::$app->$config->setex($key, $time, $value);
        return true;
    }

    public function rPush($key,$value)
    {
        $config = $this->config;
        Yii::$app->$config->rpush($key, $value);
        return true;
    }

    public function lRanges($key,$head,$tail)
    {
        $config = $this->config;
        return Yii::$app->$config->lrange($key,$head,$tail);
    }

    public function rPop($key)
    {
        $config = $this->config;
        return Yii::$app->$config->rpop($key);
    }

    public function exists($key)
    {
        $config = $this->config;
        return Yii::$app->$config->exists($key);
    }

    public function increase($key)
    {
        $config = $this->config;
        return Yii::$app->$config->incr($key);
    }

    public function expire($key, $time)
    {
        $config = $this->config;
        return Yii::$app->$config->expire($key, $time);
    }
}