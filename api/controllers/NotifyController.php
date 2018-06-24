<?php

namespace api\controllers;

use api\library\capitalDetails\CapitalDetailsService;
use api\library\wxpay\WxpayService;
use api\models\CapitalDetails;
use api\models\Member;
use api\models\WxpayResult;
use yii\rest\Controller;

class NotifyController extends Controller
{
    //微信支付通知
    //Route::any('pay/notify/wxpay/{wxpayId}', ['as' => 'pay/notify/wxpay', 'm\controllers\WxpayController@notify'])->where(['wxpayId' => '\d+']);
    public function actionNotify()
    {
        $wxpay = (object)\Yii::$app->params['wx']['wxPayConfig'];

        $wxpayService = new WxpayService($wxpay->mch_id, $wxpay->appid, $wxpay->key);
        $info = $wxpayService->notify();


        // $mch_id = $info->mch_id;  //微信支付分配的商户号
        // $appid = $info->appid; //微信分配的公众账号ID
        // $openid = $info->openid; //用户在商户appid下的唯一标识
        // $transaction_id = $info->transaction_id;//微信支付订单号
        // $out_trade_no = $info->out_trade_no;//商户订单号
        // $total_fee = $info->total_fee; //订单总金额，单位为分
        // $is_subscribe = $info->is_subscribe; //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
        // $attach = $info->attach;//商家数据包，原样返回
        // $time_end = $info->time_end;//支付完成时间

        //金额单位转为元
        $totalFee = $info->total_fee / 100;

        $wxpayResult = new WxpayResult();
        $wxpayResult->mch_id = $info->mch_id;
        $wxpayResult->appid = $info->appid;
        $wxpayResult->out_trade_no = $info->out_trade_no;
        $wxpayResult->openid = $info->openid;
        $wxpayResult->transaction_id = $info->transaction_id;
        $wxpayResult->total_fee = $totalFee;
        $wxpayResult->time_end = $info->time_end;
        $wxpayResult->created_at = time();
        $wxpayResult->updated_at = time();
        $wxpayResult->save();
        if($wxpayResult->errors) {
            \Yii::error('save wxpay_result error. ' . \Yii::$app->db->createCommand()->getRawSql());
            return;
        }
        $this->processOrder($info->out_trade_no, $totalFee);
    }

    /**
     * 订单支后成功后回调 检查订单状态，并更新状态 ，请勿在此方法中，输出任何html内容
     * @param string $outTradeNo 订单号
     * @param float $totalFee 支付金额(元)
     */
    public function processOrder($outTradeNo, $totalFee)
    {
        $outTradeNo = trim($outTradeNo, 'W');
        \Yii::$app->db->beginTransaction();
        $capitalDetails = CapitalDetails::findOne(['id' => $outTradeNo, 'status' => CapitalDetails::STATUS_NO ]);
        if(empty($capitalDetails)) {
            \Yii::error("充值记录" . $outTradeNo . "不存在");
            \Yii::$app->db->transaction->rollBack();
            return;
        }

        //检查金额
        if($capitalDetails->money != $totalFee) {
            \Yii::error("充值记录" . $outTradeNo . "金额不一致");
            \Yii::$app->db->transaction->rollBack();
            return;
        }

        //修改订单状态
        $capitalDetails->status = CapitalDetails::STATUS_YES;
        $capitalDetails->updated_at = time();
        $capitalDetails->save();
        if($capitalDetails->errors) {
            \Yii::error("修改" . $outTradeNo . "状态失败");
            \Yii::$app->db->transaction->rollBack();
            return;
        }

        //修改用户grade
        $member = Member::findOne(['id' => $capitalDetails->member_id]);
        if($totalFee == 199) {
            $grade = Member::GRADE_20;
        } elseif ($totalFee == 998){
            $grade = Member::GRADE_20;
        }
        if($grade > $member->grade) {
            $member->grade = $grade;
        }
        $member->updated_at = time();
        $member->save();
        if($member->errors) {
            \Yii::error(json_encode($member->errors));
            \Yii::$app->db->transaction->rollBack();
            return false;
        }

        //升级返佣
        if($capitalDetails->kind == CapitalDetails::KIND_30) {
            if(!CapitalDetailsService::upgradeRebate($capitalDetails->member_id, $totalFee)) {
                \Yii::error("记录" . $outTradeNo . "升级返佣失败");
                \Yii::$app->db->transaction->rollBack();
                return;
            }

        }

        \Yii::$app->db->transaction->commit();
        return;
    }


}