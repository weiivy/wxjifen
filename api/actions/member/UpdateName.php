<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\models\Member;
use Yii;

class UpdateName extends BaseAction
{
    public function run()
    {
        $name = Yii::$app->request->post('nickname');
        if(empty($name)) {
            return [
                'status' => 0,
                'message' => "请输入昵称",
            ];
        }

        //获取用户信息
        $member = Member::findOne(['id' => $this->memberId]);
        if(empty($member)) {
            return [
                'status' => 0,
                'message' => "用户不存在",
            ];
        }

        if($member->nickname == $name) {
            $member->nickname = $name;
            $member->updated_at = time();
            $member->save();
            if($member->errors) {
                return [
                    'status' => 0,
                    'message' => "昵称修改失败",
                ];
            }
        }


        return [
            'status' => 200,
            'message' => "昵称已修改",
        ];
    }
}