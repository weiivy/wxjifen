<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\library\member\MemberService;
use api\models\Member;
use Yii;

class Login extends BaseAction
{
    public function run()
    {
        $mobile = Yii::$app->request->post('mobile');
        $password = Yii::$app->request->post('password');
        try{
            //获取用户信息
            $member = Member::find()
                ->select("id, mobile,openid,nickname,avatar,status,grade,pid,money")
                ->where('mobile=:mobile',[':mobile' => $mobile])->asArray()->one();
            if(empty($member)) {
                throw  new \Exception("电话为{$mobile}的用户不存在", 0);
            }

            //验证密码
            if(MemberService::generatePasswordHash($password) != $member['password_hash']) {
                throw  new \Exception('登录密码错误', 0);
            }

            $member['avatar'] = preg_match('/http/', $member['avatar']) ? $member['avatar'] : Yii::$app->params['uploadUrl'] . $member['avatar'];
            return [
                'status' => 200,
                'message' => "登录成功",
                'data'   => $member
            ];
        }catch (\Exception $e){
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }



    }
}