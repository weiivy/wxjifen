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
        $info = Yii::$app->request->post('info');
        $files = Yii::$app->request->post('files');
        Yii::$app->db->beginTransaction();
        try{
            $orderId = OrderService::saveOrder($info);


            //验证图片信息
            if(in_array($info['bank_id'], [4]) && empty($files)) {
                throw new \Exception("请上传截图", 0);
            }
            OrderService::uploadFile($orderId,$files);
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