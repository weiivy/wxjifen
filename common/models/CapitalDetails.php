<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_capital_details".
 *
 * @property integer $id
 * @property integer $member_id
 * @property string $type
 * @property integer $kind
 * @property string $money
 * @property integer $created_at
 * @property integer $updated_at
 */
class CapitalDetails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_capital_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'kind', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'number'],
            [['type'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'type' => 'Type',
            'kind' => 'Kind',
            'money' => 'Money',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
