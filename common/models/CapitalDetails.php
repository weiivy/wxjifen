<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_capital_details".
 *
 * @property integer $id
 * @property integer $member_id
 * @property integer $from_id
 * @property string $type
 * @property integer $kind
 * @property string $money
 * @property integer $status
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
            [['member_id', 'from_id', 'kind', 'status', 'created_at', 'updated_at'], 'integer'],
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
            'from_id' => 'From ID',
            'type' => 'Type',
            'kind' => 'Kind',
            'money' => 'Money',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
