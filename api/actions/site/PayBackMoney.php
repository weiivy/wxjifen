<?php
namespace api\actions\site;


use api\actions\BaseAction;
use api\library\wxpay\WxpayService;
use api\models\Member;
use Yii;

/**
 * 企业提现到零钱
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class PayBackMoney extends BaseAction
{
    public function run()
    {
        $money = Yii::$app->request->post('money');
        $openId = Yii::$app->request->post('openid');
        try{
            //验证用户是否存在
//            $member = Member::findOne(['openid' => $openId]);
//            if(empty($member)) {
//                throw new \Exception("用户不存在", 0);
//            }

            $wxpay = (object)Yii::$app->params['wx']['wxPayConfig'];

            $wxpayService = new WxpayService($wxpay->mch_id, $wxpay->appid, $wxpay->key);
            $data = $wxpayService->payback($openId, $money);
            var_dump($data);die;


            return ['status' => 200, 'message' => "提现成功"];
        } catch (\Exception $e){
            return ['status' => $e->getMessage(), 'message' => $e->getMessage()];
        }

    }

}