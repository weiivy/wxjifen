<?php

namespace api\models;


class Bank extends \common\models\Bank
{
    const STATUS_YES = 10;
    const STATUS_NO  = 20;

    public  function getBankConfig()
    {
        return $this->hasMany(BankConfig::className(), ['bank_id' => 'id']);
    }
}