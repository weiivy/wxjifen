<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\models\Contact;
use api\models\Member;
use Yii;

/**
 * 同步openid
 * @copyright (c) 2018
 * @author  Weiwei Zhang<zhangweiwei@2345.com>
 */
class SncyOId extends BaseAction
{
    public function run()
    {
        $openId = Yii::$app->request->post('openid');
        $avatar = Yii::$app->request->post('avatar');
        $nickname = Yii::$app->request->post('nickname');
        try{

            //验证openid
            $userInfo = Contact::findOne(['openid' => $openId]);
            if(!$userInfo) {
                throw new \Exception('openid错误', 0);
            }

            $member = Member::findOne(['id' => $this->memberId]);
            $member->openid = $userInfo->openid;
            if($avatar) $member->avatar = $avatar;
            if($nickname) $member->nickname = $nickname;
            $member->updated_at = time();
            $member->save();
            if($member->errors) {
                throw new \Exception('更新同步openid失败', 0);
            }
            return [
                'status' => 200,
                'message' => "更新同步openid成功"

            ];

        }catch (\Exception $e){
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage()

            ];
        }


    }
}