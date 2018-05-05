<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_contact".
 *
 * @property integer $id
 * @property string $openid
 * @property string $nickname
 * @property integer $sex
 * @property string $province
 * @property string $country
 * @property string $city
 * @property string $head_image
 * @property integer $created_at
 * @property integer $updated_at
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sex', 'created_at', 'updated_at'], 'integer'],
            [['openid'], 'string', 'max' => 50],
            [['nickname'],'safe'],
            [['province', 'city'], 'string', 'max' => 64],
            [['country'], 'string', 'max' => 32],
            [['head_image'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'openid' => 'Openid',
            'nickname' => 'Nickname',
            'sex' => 'Sex',
            'province' => 'Province',
            'country' => 'Country',
            'city' => 'City',
            'head_image' => 'Head Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
