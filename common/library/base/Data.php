<?php
/**
 * 基础数据信息
 * @copyright (c) 2017, lulutrip.com
 * @author  Martin Ren<martin@lulutrip.com>
 */

namespace common\library\base;

use api\models\base\PhoneAreaCode;
use common\models\Cities;
use common\models\CStates;
use common\models\CTags;
use common\models\Scene;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Data extends \yii\base\Component
{
    /**
     * @Summary:生成州 父子关系
     * @Author: Serena Liu<serena@lulutrip.com>
     * @Param:  null
     * @Return: null
     * @Data:   2015.10.21
     */
    public static function getStates()
    {
        try{
            $data = Yii::$app->cache->get('gendata_states');
        } catch (Exception $e) {
            $data = [];
        }
        if(!$data) {
            $row = CStates::find()->select('state_code, state_name_cn, state_name_en, parent_state_code')->where("state_active = 'Y'")->orderBy("state_order")->asArray()->all();
            foreach($row as $val)
            {
                if(!empty($val['parent_state_code']))
                {
                    $data['statesOfPar'][$val['state_code']] = $val['parent_state_code'];
                    $data['statesChild'][$val['parent_state_code']][] = $val['state_code'];
                }
                $data['states'][$val['state_code']] = $val['state_name_cn'];
                $data['statesEN'][$val['state_code']] = strtolower($val['state_name_en']);
            }
            Yii::$app->cache->set('gendata_states', $data);
        }

        return $data;
    }
    /**
     * @Summary:生成景区id与名称的对应关系
     * @Author: Serena Liu<serena@lulutrip.com>
     * @Param:  null
     * @Return: null
     * @Data:   2017.2.21
     */
    public static function getScenes()
    {
        try{
            $data = Yii::$app->cache->get('gendata_scenes');
        } catch (Exception $e) {
            $data = [];
        }
        if(!$data)
        {
            $row = Scene::find()->select('sceneid, scenename_cn, scenestate, citycode')->where('sceneactive = "Y"')->asArray()->all();
            foreach($row as $val)
            {
                $data['scenes'][$val['sceneid']]       = $val['scenename_cn'];
                $data['sceneIdState'][$val['sceneid']] = $val['scenestate'];
                $data['scIdCityCd'][$val['sceneid']]   = $val['citycode'];
            }
            Yii::$app->cache->set('gendata_scenes', $data);
        }
        return $data;
    }
    /**
     * @Summary:生成景区id与名称的所有对应关系
     * @Author: Justin Jia<justin.jia@ipptravel.com>
     * @copyright 2017-07-24
     */
    public static function getScenesId()
    {
        try{
            $data = Yii::$app->cache->get('gendata_scenesId');
        } catch (Exception $e) {
            $data = [];
        }
        if(!$data) {
            $data = Scene::find()->select('sceneid, scenename_cn')->where('sceneactive = "Y"')->asArray()->all();
            $data = ArrayHelper::map($data, 'scenename_cn', 'sceneid');
            Yii::$app->cache->set('gendata_scenesId', $data);
        }
        return $data;
    }
    /**
     * @Summary:生成景区id与名称的所有对应关系
     * @Author: Serena Liu<serena@lulutrip.com>
     * @Param:  null
     * @Return: null
     * @Data:   2017.2.21
     */
    public static function getScenesAll()
    {
        try{
            $data = Yii::$app->cache->get('gendata_scenesAll');
        } catch (Exception $e) {
            $data = [];
        }
        if(!$data)
        {
            $rows = Scene::find()->select('sceneid, scenename_cn')->asArray()->all();
            $data = ArrayHelper::map($rows, 'sceneid', 'scenename_cn');
            Yii::$app->cache->set('gendata_scenesAll', $data);
        }
        return $data;
    }
    /**
     * @Summary:生成城市code与名称的对应关系
     * @Author: Serena Liu<serena@lulutrip.com>
     * @Param:  null
     * @Return: null
     * @Data:   2015.4.13
     */
    public static function getCities()
    {
        try{
            $data = Yii::$app->cache->get('gendata_cities');

        } catch (Exception $e) {
            $data = [];
        }
        if(!$data['cities'])
        {
            $rows = Cities::find()->select('citycode, cityname, state, displayorder')
                ->asArray()
                ->all();

            $data['cities'] = ArrayHelper::map($rows, 'citycode', 'cityname');
            $data['cityStates'] = ArrayHelper::map($rows, 'citycode', 'state');
            $data['cityOrders'] = ArrayHelper::map($rows, 'citycode', 'displayorder');
            Yii::$app->cache->set('gendata_cities', $data);

        }
        return $data;
    }

    /**
     * 获取搜索分词
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-07-06
     *
     * @return array 返回数据
     */
    public static function getParticiples()
    {

        $file = __DIR__ . "/../../data/search/sug.json";
        if(!is_file($file)) {
            return [];
        }
        $content = file_get_contents($file);

        return json_decode($content, true);
    }



    /**
     * 获取区域电话缓存
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-07-10
     *
     * @return array 返回数据
     */
    public static function getPhoneAreaCode()
    {
        try{
            $data = Yii::$app->cache->get('gendata_phoneAreaCodes');

        } catch (Exception $e) {
            $data = [];
        }

        if(empty($data)) {
            $data = PhoneAreaCode::find()
                ->select("country, area_code")
                ->asArray()
                ->all();
            Yii::$app->cache->set("gendata_phoneAreaCodes", $data, 600);
        }

        return $data;
    }
    public static function getTourTags(){
        try{
            $data = Yii::$app->cache->get('gendata_tourTags');

        } catch (Exception $e) {
            $data = [];
        }

        if(empty($data)) {
            $rows = CTags::find()
                ->select('tag_id, tag_code')
                ->where('tag_index=:tag_index', ['tag_index' => 'tour'])
                ->asArray()
                ->all();
            $data['tourTagsCdIds'] = ArrayHelper::map($rows, 'tag_code', 'tag_id');
            Yii::$app->cache->set("gendata_tourTags", $data, 600);
        }

        return $data;
    }
}