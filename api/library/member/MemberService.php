<?php

namespace api\library\member;


use api\library\Help;
use api\models\Contact;
use api\models\Member;
use yii\base\Component;
use yii\data\Pagination;
use Yii;

/**
 * 会员
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class MemberService extends Component
{
    public static $passwordSalt;
    /**
     * 保存粉丝悉尼下
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-09
     * @param $post
     * @return bool
     */
    public static function saveContact($post)
    {
        $contact = new Contact();
        $contact->openid = $post['openId'];
        $contact->nickname = $post['nickName'];
        $contact->head_image = $post['headimgurl'];
        $contact->city = $post['city'];
        $contact->province = $post['province'];
        $contact->country = $post['country'];
        $contact->sex = $post['gender'];
        $contact->save();
        if($contact->errors) {
            Yii::error(json_encode($contact->errors));
            return false;
        }
        return true;
    }


    /**
     * 注册会员
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-09
     * @param $post
     * @return string
     * @throws \Exception
     */
    public static function saveMember($post)
    {
        $pid = 0;
        //获取推荐人ID
        if(isset($post['invite'])) {
            $invitePeopleInfo = Member::find()->where(['mobile' => $post['invite']])->one();
            if($invitePeopleInfo)  $pid = $invitePeopleInfo->id;
        }

        //根据openid获取信息
        if(!empty($post['openid'])) $userInfo = Contact::findOne(['openid' => $post['openid']]);


        //保存用户注册信息
        $member = new Member();
        $member->mobile = $post['mobile'];
        if(!empty($userInfo)) {
            $member->openid = $post['openid'];
            $member->nickname = $userInfo->nickname;
            $member->avatar = $userInfo->head_image;
        }

        static::generatePasswordSalt();
        $member->password_hash = static::generatePasswordHash($post['password']);
        $member->password_salt = static::$passwordSalt;
        $member->pid = $pid;
        $time = time();
        $member->created_at = $member->updated_at = $time;
        $member->save();
        if($member->errors) {
            Yii::error(json_encode($member->errors));
            throw new \Exception("保存失败");
        }
        $id = Yii::$app->db->getLastInsertID();
        return Member::find()->select("id, mobile,openid,nickname,avatar,status,grade,pid,money")->where(['id' => $id])->asArray()->one();
    }

    /**
     * 生成秘要
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-09
     *
     * @return string
     */
    private static function generatePasswordSalt()
    {
        static::$passwordSalt = uniqid();
    }

    /**
     * 生成秘密
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-09
     * @param $password
     * @return string
     */
    public static function generatePasswordHash($password)
    {
        return md5($password . static::$passwordSalt);
    }


    /**
     * 根据openid获取会员id
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $openid
     * @return int|mixed
     */
    public static function getMemberByOpenid($openid)
    {
        $member = Member::find()->where(['openid' => $openid])->one();
        return empty($member) ? 0 : $member->id;
    }


    /**
     * 好友列表
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-13
     * @param $memberId
     * @param $page
     * @param $pageSize
     * @return array 返回数据
     */
    public static function getFriends($memberId, $page, $pageSize)
    {
        $memberPid = Member::find()->select('id')->where(['pid' => $memberId, 'status' => Member::status_10])->asArray()->all();
        $memberPid = empty($memberPid) ? [] : array_column($memberPid, 'id');
        $model = Member::find()->select('id,nickname, mobile')->where(['pid' => $memberId]);
        if(!empty($memberPid)) {
            $model->orWhere(['pid' => $memberPid]);
        }
        $model->orderBy('id DESC');
        $pages = new Pagination(['totalCount' =>$model->count(), 'pageSize' => $pageSize]);
        $pages->setPage($page-1);
        $friends = $model->offset($pages->offset)->limit($pages->pageSize)->asArray()->all();
        foreach ($friends as &$value) {
            $value['mobile'] = Help::fmtMobile($value['mobile']);
        }
        return ['list' => $friends, 'count' => $pages->totalCount];
    }

    /**
     * 根据会员ID获取会员信息
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-05-04
     * @param $memberId
     * @return null|static
     */
    public static function getMemberInfo($memberId)
    {
        return Member::findOne(['id' => $memberId]);
    }

    /**
     * 获取会员等级
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-05-04
     * @param $memberId
     * @return mixed|null
     */
    public static function getMemberGrade($memberId)
    {
        $member = static::getMemberInfo($memberId);
        return empty($member) ? null : $member->grade;
    }

    /**
     * 获取会员父级ID
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-05-04
     * @param $memberId
     * @return mixed|null
     */
    public static function getPid($memberId)
    {
        $member = static::getMemberInfo($memberId);
        return empty($member) ? 0 : $member->pid;
    }


} 