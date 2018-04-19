<?php
/**
 * memcache
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */

namespace common\components;


use yii\base\Component;
use yii\helpers\Json;
use Yii;

class MemcacheShared extends Component
{
    public $config;

    public function get($key)
    {
        $config = $this->config;
        return Json::decode(Yii::$app->$config->get($key));
    }
} 