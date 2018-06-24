<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pre_bank".
 *
 * @property integer $id
 * @property string $bank
 * @property string $bank_name
 * @property string $note
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Bank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pre_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['note'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['bank'], 'string', 'max' => 20],
            [['bank_name'], 'string', 'max' => 100],
            [['note'], 'string', 'max' => 255],
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
            'bank_name' => 'Bank Name',
            'note' => 'Note',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
