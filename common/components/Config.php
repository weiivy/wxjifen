<?php
/**
 * Config 常用配置
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
namespace common\components;

use yii\base\Component;
use Yii;

class Config extends Component
{
    public $www = '';
    public $ssl = '';
    public $app = '';
    public $api = '';
    public $apiHk = '';
    public $diy = '';
    public $tourapi = '';
    public $bookingUrl = '';
    public $testEmail = '';

    public function __construct(){
        parent::__construct();
        $this->www = Yii::$app->params['service']['www'];
        $this->ssl = Yii::$app->params['service']['ssl'];
        $this->app = Yii::$app->params['service']['app'];
        $this->api = Yii::$app->params['service']['api'];
        $this->apiHk = Yii::$app->params['service']['apiHk'];
        $this->diy = Yii::$app->params['service']['diy'];
        $this->bookingUrl = Yii::$app->params['service']['bookingUrl'];
        $this->tourapi = Yii::$app->params['service']['tourapi'];
        $this->testEmail = Yii::$app->params['service']['testEmail'];
    }
}