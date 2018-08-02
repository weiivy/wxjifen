<?php
namespace api\actions\site;


use api\actions\BaseAction;
use api\library\sms\Ucpaas;
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
    private $payMoney;

    public function run()
    {
        $money = Yii::$app->request->post('money');
        $memberId= Yii::$app->request->post('memberId');
        //缓存时间
        $time = (strtotime(date('Y-m-d') . ' 23:59:59') - time());
        try{

            if(date('H') < 9 || date('H') > 16){
                throw new \Exception(" 每天提现时间段9:00-16:00", 0);
            }

            //验证用户是否存在
            $member = Member::findOne(['id' => $memberId]);
            if(empty($member)) {
                throw new \Exception("用户不存在", 0);
            }
            if(empty($member->openid)) {
                throw new \Exception("openid不存在", 0);
            }
            $redisKey = 'backMoney:'.$member->id;
            if(Yii::$app->cache->get($redisKey) > 0) {
                throw new \Exception("您今天已经提现过了，每天仅限提1笔哦！", 0);
            }
            if($member->money - $money < 0 ) {
                throw new \Exception("输入的金额超过了您当前余额", 0);
            }
            Yii::$app->cache->set($redisKey, 1, $time);

            //处理金额
            $money = $member->money < $money ? $member->money : $money;
            if($money < 0.30) {
                Yii::$app->cache->forget($redisKey);
                throw new \Exception("不满足提现条件", 0);
            }

            //先修改用户金额
            $this->payMoney = $money;
            $member->money = (($member->money - $money) <= 0 ? 0 : ($member->money - $money));
            $member->updated_at = time();
            $member->save();
            if($member->errors) {
                Yii::$app->cache->forget($redisKey);
                throw new \Exception("提现失败", 0);
            }

            $wxpay = (object)Yii::$app->params['wx']['wxPayConfig'];
            $wxpayService = new WxpayService($wxpay->mch_id, $wxpay->appid, $wxpay->key);
            $data = $wxpayService->payback($member->openid, $money);

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
                Yii::info("提取成功：". json_encode($data));
                throw new \Exception("提现失败", 0);
            }

            //提现明细
            $capitalDetails = new CapitalDetails();
            $capitalDetails->member_id = $member->id;
            $capitalDetails->from_id = 0;
            $capitalDetails->type = "-";
            $capitalDetails->status = CapitalDetails::STATUS_YES;
            $capitalDetails->kind = CapitalDetails::KIND_40;
            $capitalDetails->money = $money;
            $capitalDetails->created_at = $capitalDetails->updated_at = time();
            $capitalDetails->save();
            if($capitalDetails->errors) {
                Yii::error(json_encode($capitalDetails->errors));
                throw new \Exception("操作失败", 0);
            }

            return ['status' => 200, 'message' => "提现成功"];
        } catch (\Exception $e){
            if($e->getCode() == 403){
                // 返回金额
                Yii::$app->cache->forget($redisKey);
                $member->money = $member->money + $this->payMoney;
                $member->updated_at = time();
                $member->save();
                if($e->getMessage() == 'NOTENOUGH'){
                    $ucPass = new Ucpaas(['accountsid' => Yii::$app->params['wx']['smsapi']['accountSid'], 'token' => Yii::$app->params['wx']['smsapi']['token']]);
                    $ucPass->SendSms(Yii::$app->params['wx']['smsapi']['appid'], 305131, '', '13721176821', '');
                }
                return ['status' => 0, 'message' => $e->getMessage()];
            }
            return ['status' => $e->getCode(), 'message' => $e->getMessage()];
        }

    }

}