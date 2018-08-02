<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_bank_goods".
 *
 * @property integer $id
 * @property integer $bank_id
 * @property integer $codenum
 * @property string $goods
 * @property string $num
 * @property string $money
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class BankGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_bank_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id'], 'required'],
            [['bank_id', 'codenum', 'status', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'string'],
            [['goods', 'num'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_id' => 'Bank ID',
            'codenum' => 'Codenum',
            'goods' => 'Goods',
            'num' => 'Num',
            'money' => 'Money',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
