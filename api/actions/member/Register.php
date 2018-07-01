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
//            $code = Yii::$app->cache->get($post['mobile'].'verifyCode');
//            if($code != $post['verifyCode']) {
//                throw new \Exception("验证码已失效", 0);
//            }

            $member = MemberService::memberInfo(['mobile' => $post['mobile']]);
            if(!$member) {
                //保存数据
                $member = MemberService::saveMember($post);
            }
            $member = static::formatMember($member);
            return [
                'status'  => 200,
                'message' => "登录成功",
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

    /**
     * 格式化用户信息
     * @param $member
     * @return mixed
     */
    private static function formatMember($member)
    {
        if(empty($member)) return $member;
        if(!empty($member['avatar'])) {
            $member['avatar'] = preg_match('/http/', $member['avatar']) ? $member['avatar'] : Yii::$app->params['uploadUrl'] . $member['avatar'];

        }else {
            $member['avatar'] = Yii::$app->params['staticUrl'] . '/logo.png';
        }
        $member['gradeAlias'] = Member::gradeAlisa($member['grade']);
        return $member;
    }
}