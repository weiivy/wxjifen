<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pay_back_result".
 *
 * @property integer $id
 * @property string $mch_id
 * @property string $mch_appid
 * @property string $partner_trade_no
 * @property string $payment_no
 * @property string $payment_time
 * @property string $payment_money
 * @property integer $created_at
 * @property integer $updated_at
 */
class PayBackResult extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_back_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['mch_id', 'mch_appid', 'partner_trade_no'], 'string', 'max' => 100],
            [['payment_no'], 'string', 'max' => 255],
            [['payment_time'], 'string', 'max' => 50],
            [['payment_money'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mch_id' => 'Mch ID',
            'mch_appid' => 'Mch Appid',
            'partner_trade_no' => 'Partner Trade No',
            'payment_no' => 'Payment No',
            'payment_time' => 'Payment Time',
            'payment_money' => 'Payment Money',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
