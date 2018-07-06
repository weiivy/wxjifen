<?php
namespace api\actions\site;


use api\actions\BaseAction;
use api\library\wxpay\WxpayService;
use api\models\CapitalDetails;
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
                throw new \Exception("openid不存在", 0);
            }

            //处理金额
            $money = $member->money < $money ? $member->money : $money;
            if($money < 0.30) {
                throw new \Exception("不满足提现条件", 0);
            }
            $wxpay = (object)Yii::$app->params['wx']['wxPayConfig'];

            $wxpayService = new WxpayService($wxpay->mch_id, $wxpay->appid, $wxpay->key);
            $data = $wxpayService->payback($member->openid, $money);

            $transaction = Yii::$app->db->beginTransaction();
            $payBack = new PayBackResult();
            $payBack->mch_appid = $data['mch_appid'];
            $payBack->mch_id = $data['mchid'];
            $payBack->partner_trade_no = $data['partner_trade_no'];
            $payBack->payment_no = $data['payment_no'];
            $payBack->payment_time = $data['payment_time'];
            $payBack->payment_money = $money;
            $payBack->created_at = $payBack->updated_at = time();
            $payBack->save();
            if($payBack->errors) {
                $transaction->rollBack();
                throw new \Exception("提现失败", 0);
            }

            //修改用户订单
            $member->money = (($member->money - $money) <= 0 ? 0 : ($member->money - $money));
            $member->updated_at = time();
            $member->save();
            if($member->errors) {
                $transaction->rollBack();
                throw new \Exception("提现失败", 0);
            }

            //提现明细
            $capitalDetails = new CapitalDetails();
            $capitalDetails->member_id = $member->id;
            $capitalDetails->type = "-";
            $capitalDetails->status = CapitalDetails::STATUS_YES;
            $capitalDetails->kind = CapitalDetails::KIND_40;
            $capitalDetails->money = $money;
            $capitalDetails->created_at = $capitalDetails->updated_at = time();
            $capitalDetails->save();
            if($capitalDetails->errors) {
                $transaction->rollBack();
                Yii::error(json_encode($capitalDetails->errors));
                throw new \Exception("操作失败", 0);
            }
            $transaction->commit();
            return ['status' => 200, 'message' => "提现成功"];
        } catch (\Exception $e){
            return ['status' => $e->getCode(), 'message' => $e->getMessage()];
        }

    }

}