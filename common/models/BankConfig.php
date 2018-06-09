<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_bank_config".
 *
 * @property integer $id
 * @property string $bank_id
 * @property integer $type
 * @property string $money
 * @property integer $score
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class BankConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_bank_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'score', 'status', 'created_at', 'updated_at', 'bank_id'], 'integer'],
            [['money'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_id' => 'Bank Id',
            'type' => 'Type',
            'money' => 'Money',
            'score' => 'Score',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
