<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_member".
 *
 * @property integer $id
 * @property string $password_hash
 * @property string $openid
 * @property string $nickname
 * @property string $avatar
 * @property string $mobile
 * @property string $password_salt
 * @property string $password_reset_token
 * @property string $mobile_check_token
 * @property integer $status
 * @property integer $grade
 * @property string $money
 * @property integer $pid
 * @property integer $created_at
 * @property integer $updated_at
 */
class Member extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'grade', 'pid', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'number'],
            [['password_hash', 'openid', 'nickname', 'avatar', 'mobile', 'password_reset_token', 'mobile_check_token'], 'string', 'max' => 255],
            [['password_salt'], 'string', 'max' => 13],
            [['openid'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'password_hash' => 'Password Hash',
            'openid' => 'Openid',
            'nickname' => 'Nickname',
            'avatar' => 'Avatar',
            'mobile' => 'Mobile',
            'password_salt' => 'Password Salt',
            'password_reset_token' => 'Password Reset Token',
            'mobile_check_token' => 'Mobile Check Token',
            'status' => 'Status',
            'grade' => 'Grade',
            'money' => 'Money',
            'pid' => 'Pid',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
