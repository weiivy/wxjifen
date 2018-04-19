<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_order".
 *
 * @property integer $id
 * @property string $out_trade_no
 * @property integer $member_id
 * @property string $bank
 * @property integer $integral
 * @property string $money
 * @property string $exchange_code
 * @property string $valid_time
 * @property string $remark
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'integral', 'status', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'number'],
            [['valid_time'], 'safe'],
            [['remark'], 'string'],
            [['out_trade_no'], 'string', 'max' => 32],
            [['bank'], 'string', 'max' => 50],
            [['exchange_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'out_trade_no' => 'Out Trade No',
            'member_id' => 'Member ID',
            'bank' => 'Bank',
            'integral' => 'Integral',
            'money' => 'Money',
            'exchange_code' => 'Exchange Code',
            'valid_time' => 'Valid Time',
            'remark' => 'Remark',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
