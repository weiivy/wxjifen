<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\models\Bank;
use api\models\BankConfig;
use Yii;

/**
 * 获取后台配置银行信息
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang
 */
class GetBankInfo extends BaseAction
{
    public function run()
    {
        return [
            'status' => 200,
            'data'   => self::getBank()
        ];
    }

    /**
     * 获取文章数据
     * @author Ivy Zhang
     * @copyright 2018-06-08
     *
     * @return array 返回数据
     */
    private static function getBank()
    {
        $banks = Bank::find()->alias('b')
            ->select("b.id, b.bank, b.bank_name")
            ->joinWith('bankConfig bc')
            ->where(['bc.status' => BankConfig::STATUS_YES,'b.status' => Bank::STATUS_YES])
            ->orderBy('b.id asc, bc.type asc')
            ->asArray()
            ->all();
        $data = [];
        //处理数组
        foreach ($banks as $bank){
            $data[$bank['id']] = [
                'id'   => $bank['id'],
                'bank' => $bank['bank'],
                'bankName' => $bank['bank_name'],
                'note' => ''
            ];
            foreach ($bank['bankConfig'] as $bankConfig){
                $data[$bank['id']]['bankConfig'][$bankConfig['type']] = [
                    'money' => $bankConfig['money'],
                    'score' => $bankConfig['score'],
                ];
            }

        }

        return $data;

    }
}