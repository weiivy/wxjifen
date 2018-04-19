<?php
/**
 * @copyright (c) 2017, lulutrip.com
 * @author LT<todd@lulutrip.com>
 */
namespace common\components;

use yii\base\Component;
use Yii;
use yii\helpers\Json;

class RedisShared extends Component
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

}