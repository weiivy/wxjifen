<?php
/**
 * 推荐榜单缓存
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */

namespace common\library\generatecache;


use api\models\Actslist;
use yii\base\Component;
use Yii;
use yii\base\Exception;

class RecListCache extends Component
{
    /**
     * 榜单缓存 有效期10分钟
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-07-03
     *
     * @return array 返回数据
     */
    public function getRecActCache()
    {

        try{
            $list = Yii::$app->cache->get('gendata_actByRec');
        } catch (Exception $e) {
            $list = [];
        }

        if(empty($list)) {
            $rows = Actslist::find()
                ->select("actslist_key_val, actslist_contents")
                ->where('actslist_type = 1 AND actslist_index != 0 AND actslist_contents != ""')
                ->asArray()
                ->all();
            foreach($rows as $actVl) {
                $keyVl = unserialize($actVl['actslist_key_val']);
                $arr = array();
                foreach($keyVl as $key => $val)
                {
                    $tVl = explode('##', $val);
                    $arr[] = $key . '-' . $tVl[0];
                }
                $tourCodes  = array_reverse(explode(',', $actVl['actslist_contents']));
                $list[implode('_', $arr)] = implode(',', $tourCodes);
            }

            Yii::$app->cache->set('gendata_actByRec', $list, 600);
        }

        return $list;
    }
} 