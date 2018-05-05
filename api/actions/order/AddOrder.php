<?php

namespace api\actions\order;


use api\actions\BaseAction;
use api\library\order\OrderService;
use Yii;

/**
 * 生成订单
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class AddOrder extends BaseAction
{
    public function run()
    {
        $body = \Yii::$app->getRequest()->getBodyParams();
        Yii::$app->db->beginTransaction();
        try{
            $orderId = OrderService::saveOrder($body);


            //验证图片信息
            OrderService::uploadFile($orderId);
            Yii::$app->db->transaction->commit();

            return [
                'status' => 200,
                'message' => "报单成功"
            ];
        }catch (\Exception $e){
            Yii::$app->db->transaction->rollBack();

            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

    }
}