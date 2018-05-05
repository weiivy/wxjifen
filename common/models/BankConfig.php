<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_bank_config".
 *
 * @property integer $id
 * @property string $bank
 * @property integer $type
 * @property string $money
 * @property integer $score
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
            [['type', 'score', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'number'],
            [['bank'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank' => 'Bank',
            'type' => 'Type',
            'money' => 'Money',
            'score' => 'Score',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
