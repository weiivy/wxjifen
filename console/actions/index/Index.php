<?php

/**
 * Created by PhpStorm.
 * User: renlikang
 * Date: 16/8/16
 * Time: 下午2:17
 */
namespace console\actions\index;

use yii\base\Action;

class Index extends Action
{
    public $ssss;
    public function run()
    {
        var_dump($this->ssss, 'ee');exit;
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        while (true) {
            $redis->subscribe(['msg'], [$this,'callback']);
            sleep(2);
        }
    }

    public function callback ($instance, $channelName, $message)
    {
        echo $channelName, "==>", $message,PHP_EOL;
    }

}