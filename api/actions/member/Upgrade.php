<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\library\wxpay\WxpayService;
use api\models\CapitalDetails;
use api\models\Member;
use Yii;
use yii\helpers\Url;

class Upgrade extends BaseAction
{
    public function run()
    {
        $memberId = Yii::$app->request->post('memberId');
//        $fee = Yii::$app->request->post('fee');
        $fee = 0.01;
        $kind = Yii::$app->request->post('kind');
        if(empty($fee)) return ['status' => 0, 'message' => '操作失败'];
        $member = Member::findOne(['id' => $memberId]);


        //生成充值记录
        try{
//            $id = static::addRecord($member, $fee, $kind);
//            $capitalDetails = CapitalDetails::findOne(['id' => $id]);
//            $config = Yii::$app->params['wx']['wxPayConfig'];
//            $payService = new WxpayService($config['mch_id'], $config['appid'], $config['key']);
//            $notifyUrl = Url::toRoute('/pay/notify', 'http');
//            $data = $payService->createJsBizPackage($member->openid, $capitalDetails->money, $capitalDetails->id, "充值", $notifyUrl, time());
//
                        $data = '{"appId":"wx347d53defefdfa69","timeStamp":1525706829,"nonceStr":"5ckyu2gwSaSUsyeH","package":"prepay_id=wx07232715185613ca76f2355e1742238399","signType":"MD5","paySign":"DC53DC90D3B8298FB6A9019CE55F5680"}';
            $data = json_decode($data, true);

            return ['status' => 200, 'message' => "success", "data" => $data];

        }catch (\Exception $e){
            return ['status' => $e->getCode(), 'message' => $e->getMessage()];
        }



    }

    private static function addRecord(Member $member, $fee, $kind)
    {
        $capitalDetails = new CapitalDetails();
        $capitalDetails->member_id = $member->id;
        $capitalDetails->type = "+";
        $capitalDetails->status = CapitalDetails::STATUS_YES;
        $capitalDetails->kind = $kind;
        $capitalDetails->money = $fee;
        $capitalDetails->created_at = $capitalDetails->updated_at = time();
        $capitalDetails->save();
        if($capitalDetails->errors) {
            Yii::error(json_encode($capitalDetails->errors));
            throw new \Exception("操作失败", 0);
        }
        return Yii::$app->db->getLastInsertID();
    }
}

