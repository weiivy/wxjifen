<?php


namespace console\actions\product;


use console\models\mdc\TrackDataItems;
use console\models\ProductPv;
use yii\base\Action;

/**
 * 缓存tour  PV数据 每天更新
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class TourPv extends Action
{
    public function run()
    {
        $startTime = strtotime('-7 days');
        $endTime = time();

        //查询所有产品ID
        $pvData = TrackDataItems::find()->select("itemid, count(itemid) as total")
            ->where(['itemtype' => 'tour'])
            ->andWhere("tdtime BETWEEN '{$startTime}' AND '{$endTime}'")
            ->groupBy("itemid")
            ->asArray()
            ->all();
        foreach($pvData as $pv) {
            $productPv = ProductPv::findOne(['product_type' => 1, "product_id" => $pv['itemid']]);
            if(!empty($productPv)) {
                $productPv->pv_value = $pv['total'];
                $productPv->save();
            } else {
                $productPv = new ProductPv();
                $productPv->product_id = $pv['itemid'];
                $productPv->product_type = 1;
                $productPv->pv_value = $pv['total'];
                $productPv->save();

            }
        }
    }

} 