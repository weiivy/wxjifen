<?php
/**
 * 团购缓存数据
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
namespace common\library\generatecache;

use api\models\products\ProductsForActivity;
use yii\base\Component;
use Yii;
use yii\base\Exception;

class GroupBuysCache extends Component
{
    /**
     * 团购缓存
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-07-04
     * @param int $type 0 旅行团  1 自由行
     * @return array 返回数据
     */
    public static function getGroupBuys($type = 0)
    {
        $typeAlisa = $type == 1 ? 'act' : 'tour';
        try{
            $list = Yii::$app->cache->get('gendata_groupbuy_' . $typeAlisa);
        } catch (Exception $e) {
            $list = [];
        }

        if(empty($list)) {
            $date = date('Y-m-d H:i:s');
            $row = ProductsForActivity::find()->alias("pa")
                ->select("product_id, off_percent, off_price, pa.activity_id")
                ->joinWith("cGroupBuying as cg", false)
                ->where("pa.del_flag = 0 AND pa.type = :type AND cg.type = '团购' AND start_time < :stime AND end_time > :etime AND cg.del_flag = 0 AND online = 0 AND cg.platform in (0, 1)", [':type' => $type, ':stime' => $date, 'etime' => $date])
                ->asArray()
                ->all();
            foreach($row as $val) {
                $list[$val['product_id']] = (10 - $val['off_percent']) * 10;
            }

            Yii::$app->cache->set('gendata_groupbuy_'. $typeAlisa, $list, 600);
        }

        return $list;
    }


    /**
     * 获取团购区域信息
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-07-04
     *
     * @return array 返回数据
     */
    public static function getAreasOfGroupBuy()
    {
        try{
            $list = Yii::$app->cache->get('gendata_areasOfGroupBuy');
        } catch (Exception $e) {
            $list = [];
        }
        if(empty($list)) {
            $date = date('Y-m-d H:i:s');
            $row = ProductsForActivity::find()->alias("pa")
                ->select("product_id, pa.type, state_name, pa.product_order, pa.activity_id")
                ->joinWith("cGroupBuying as cg", false)
                ->where("pa.del_flag = 0 AND cg.type = '团购' AND start_time < :stime AND end_time > :etime AND cg.del_flag = 0 AND cg.online = 0 AND cg.platform in (0, 1) AND cg.isshow_onlistpage=1", [':stime' => $date, 'etime' => $date])
                ->orderBy("product_order ASC")
                ->asArray()
                ->all();
            foreach($row as $val) {
                $list[$val['state_name']][$val['type']][$val['product_id']] = $val['product_order'];
            }
            Yii::$app->cache->set('gendata_areasOfGroupBuy', $list, 600);
        }
        return $list;
    }
} 