<?php


namespace api\actions\member;


use api\actions\BaseAction;
use api\library\member\MemberService;
use api\models\Member;
use Yii;
/**
 * 注册
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class Register extends BaseAction
{
    public function run()
    {
        $post = Yii::$app->request->post();
        try{

            //验证验证码
            $code = Yii::$app->cache->get('verifyCode');
            if($code != $post['code']) {
                throw new \Exception("验证码已失效", 0);
            }
            //验证密码
            if(!preg_match('/^[a-zA-Z\d_]{6,30}$/i', $post['password'])) {
                throw new \Exception("密码由数字字母下划线组成", 0);
            }

            if($post['password'] != $post['repassword']) {
                throw new \Exception("两次密码不一致", 0);
            }

            //保存数据
            $member = MemberService::saveMember($post);
            if(!empty($member)){
                $member['avatar'] = preg_match('/http/', $member['avatar']) ? $member['avatar'] : Yii::$app->params['uploadUrl'] . $member['avatar'];
            }
            return [
                'status'  => 200,
                'message' => "注册成功",
                'data'    => $member
            ];
        }catch (\Exception $e){
            return [
                'status'  => $e->getCode(),
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }




    }
}