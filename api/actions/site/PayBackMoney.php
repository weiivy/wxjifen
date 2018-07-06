<?php
namespace api\actions\site;


use api\actions\BaseAction;
use api\library\wxpay\WxpayService;
use api\models\Member;
use api\models\PayBackResult;
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
        $memberId= Yii::$app->request->post('memberId');
        try{
            //验证用户是否存在
            $member = Member::findOne(['id' => $memberId]);
            if(empty($member)) {
                throw new \Exception("用户不存在", 0);
            }
            if(empty($member->openid)) {
                throw new \Exception("openid不存在", 403);
            }

            $wxpay = (object)Yii::$app->params['wx']['wxPayConfig'];

            $wxpayService = new WxpayService($wxpay->mch_id, $wxpay->appid, $wxpay->key);
            $data = $wxpayService->payback($member->openid, $money);

            $payBack = new PayBackResult();
            $payBack->mch_appid = $data['mch_appid'];
            $payBack->mch_id = $data['mch_id'];
            $payBack->partner_trade_no = $data['partner_trade_no'];
            $payBack->payment_no = $data['payment_no'];
            $payBack->payment_time = $data['payment_time'];
            $payBack->created_at = $payBack->updated_at = time();
            $payBack->save();
            if($payBack->errors) {
                throw new \Exception("提现失败", 0);
            }
            return ['status' => 200, 'message' => "提现成功"];
        } catch (\Exception $e){
            return ['status' => $e->getCode(), 'message' => $e->getMessage()];
        }

    }

}