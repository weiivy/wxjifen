<?php
/**
 * 一键包团景点类
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */
namespace common\library\base;

use common\models\Scene;
use yii\helpers\ArrayHelper;
use Yii;

class Scenes extends \yii\base\Component
{
    /**
     * 获取一键包团景点
     * @author martin ren<martin@lulutrip.com>
     * @copyright 2017-02-12
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getScenes()
    {
        $model = Yii::$app->cache->get('scenes');
        if(!$model) {
            $model = Scene::find()->select('sceneid, scenename_cn')->all();
            $model = ArrayHelper::map($model, 'sceneid', 'scenename_cn');
            Yii::$app->cache->set('scenes', $model);
        }

       return $model;
    }
}