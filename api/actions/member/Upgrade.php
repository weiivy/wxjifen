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
        $memberId = $this->memberId;

        //验证金额
        $type = Yii::$app->request->post('type');
        $fee = CapitalDetails::payFee($type);
        $fee = 0.01;
        if(empty($fee)) return ['status' => 0, 'message' => '操作失败'];

        $kind = CapitalDetails::KIND_31;

        //校验等级
        $member = Member::findOne(['id' => $memberId]);
        if(($fee == 199 && $member->grade >= Member::GRADE_20) || ($fee == 998 && $member->grade >= Member::GRADE_30)) {
            return ['status' => 0, 'message' => '您当前等级是：'. Member::gradeAlisa($member->grade)];
        }

        //生成充值记录
        try{
            $id = static::addRecord($member, $fee, $kind);
            $capitalDetails = CapitalDetails::findOne(['id' => $id]);
            $config = Yii::$app->params['wx']['wxPayConfig'];
            $payService = new WxpayService($config['mch_id'], $config['appid'], $config['key']);
            $notifyUrl = Url::toRoute('/pay/notify', 'http');
            $data = $payService->createJsBizPackage($member->openid, $capitalDetails->money, $capitalDetails->id, "充值", $notifyUrl, time());
            $data['timeStamp'] = (string)$data['timeStamp'];
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
        $capitalDetails->status = CapitalDetails::STATUS_NO;
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

