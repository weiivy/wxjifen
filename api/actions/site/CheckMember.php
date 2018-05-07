<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\models\Member;
use Yii;

/**
 * 检查用户信息
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class CheckMember extends BaseAction
{
    public function run()
    {
        $openid = Yii::$app->request->post('openid');
        $member = Member::find()->where(['openid' => $openid])->asArray()->one();
        if(empty($member)) {
            return ['status' => 0, 'data' => null];
        }
        $member['gradeAlisa'] = Member::gradeAlisa($member['grade']);
        return ['status' => 200, 'data' => $member];
    }
}