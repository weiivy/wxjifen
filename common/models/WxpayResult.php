<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pay_wxpay_result".
 *
 * @property integer $id
 * @property integer $wxpay_id
 * @property string $mch_id
 * @property string $appid
 * @property string $out_trade_no
 * @property string $openid
 * @property string $transaction_id
 * @property string $total_fee
 * @property string $time_end
 * @property integer $created_at
 * @property integer $updated_at
 */
class WxpayResult extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_wxpay_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wxpay_id', 'created_at', 'updated_at'], 'integer'],
            [['total_fee'], 'number'],
            [['mch_id', 'appid', 'out_trade_no'], 'string', 'max' => 100],
            [['openid', 'transaction_id'], 'string', 'max' => 255],
            [['time_end'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wxpay_id' => 'Wxpay ID',
            'mch_id' => 'Mch ID',
            'appid' => 'Appid',
            'out_trade_no' => 'Out Trade No',
            'openid' => 'Openid',
            'transaction_id' => 'Transaction ID',
            'total_fee' => 'Total Fee',
            'time_end' => 'Time End',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
