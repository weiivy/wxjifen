<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\models\Bank;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 银行信息配置
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetBankConfig extends BaseAction
{
    private $bankConfigs = [
        'JSYH' => [
            'listData' => [
                ["codenum" => "54000", "goods" => "建设积分兑换", "num" => "无规则", "money" => [
                    '1' => '75.60',
                    '2' => '75.60',
                    '3' => '75.60',
                ]],
            ],
        ],
        'GDYH' => [
            'listData' => [
                ["codenum" => "57800", "goods" => "50元电子E卡", "num" => "不限次数", "money" => [
                    '1' => '45.10',
                    '2' => '45.10',
                    '3' => '45.10',
                ]],
                ["codenum" => "115600", "goods" => "100元电子E卡", "num" => "不限次数", "money" => [
                    '1' => '6.40',
                    '2' => '6.80',
                    '3' => '7.20',
                ]],
            ],
        ],
        'ZHYH' => [
            'listData' => [
                ["codenum" => "5000", "goods" => "10元京东钢蹦", "num" => "2次/月", "money" => [
                    '1' => '8.30',
                    '2' => '8.30',
                    '3' => '8.30',
                ]],
                ["codenum" => "15000", "goods" => "30元京东钢蹦", "num" => "2次/月", "money" => [
                    '1' => '24.80',
                    '2' => '24.80',
                    '3' => '24.80',
                ]],
                ["codenum" => "25000", "goods" => "50元京东钢蹦", "num" => "2次/月", "money" => [
                    '1' => '41.30',
                    '2' => '41.30',
                    '3' => '41.30',
                ]],
                ["codenum" => "45000", "goods" => "100元京东钢蹦", "num" => "2次/月", "money" => [
                    '1' => '74.30',
                    '2' => '74.30',
                    '3' => '74.30',
                ]],
                ["codenum" => "90000", "goods" => "200元京东钢蹦", "num" => "2次/月", "money" => [
                    '1' => '148.50',
                    '2' => '148.50',
                    '3' => '148.50',
                ]],
            ],
        ],
        'BJYH' => [
            'listData' => [
                ["codenum" => "6000", "goods" => "10元京东钢蹦", "num" => "每人每月限兑6笔", "money" => [
                    '1' => '8.30',
                    '2' => '8.30',
                    '3' => '8.30',
                ]],
                ["codenum" => "18000", "goods" => "30元京东钢蹦", "num" => "每人每月限兑6笔", "money" => [
                    '1' => '24.80',
                    '2' => '24.80',
                    '3' => '24.80',
                ]],
                ["codenum" => "30000", "goods" => "50元京东钢蹦", "num" => "每人每月限兑6笔", "money" => [
                    '1' => '41.30',
                    '2' => '41.30',
                    '3' => '41.30',
                ]],
            ],
        ],
        'JTYH' => [
            'listData' => [
                ["codenum" => "53300", "goods" => "优惠券", "num" => "不限次数", "money" => [
                    '1' => '40.00',
                    '2' => '40.00',
                    '3' => '40.00',
                ]],
            ],
        ],
        'HFYH' => [
            'listData' => [
                ["codenum" => "27500", "goods" => "50元京东E卡", "num" => "不限次数", "money" => [
                    '1' => '39.60',
                    '2' => '39.60',
                    '3' => '39.60',
                ]],
                ["codenum" => "55500", "goods" => "100元京东E卡", "num" => "不限次数", "money" => [
                    '1' => '79.90',
                    '2' => '79.90',
                    '3' => '79.90',
                ]],
            ],
        ],
        'ZGYD' => [
            'listData' => [
                ["codenum" => "2850", "goods" => "3个月会员", "num" => "1次/天", "money" => [
                    '1' => '21.30',
                    '2' => '21.30',
                    '3' => '21.30',
                ]],
                ["codenum" => "5530", "goods" => "6个月会员", "num" => "1次/天", "money" => [
                    '1' => '41.50',
                    '2' => '41.50',
                    '3' => '41.50',
                ]],
                ["codenum" => "10400", "goods" => "年卡会员", "num" => "1次/天", "money" => [
                    '1' => '78.00',
                    '2' => '78.00',
                    '3' => '78.00',
                ]],
            ],
        ],
        'ZGLT' => [
            'listData' => [
                ["codenum" => "799", "goods" => "中杯星巴克", "num" => "1次/月", "money" => [
                    '1' => '6.40',
                    '2' => '6.80',
                    '3' => '7.20',
                ]],
            ],
        ],
    ];

    public function run()
    {
        $bankId = Yii::$app->request->post('bankId');
        $bank = Bank::findOne(['id' => $bankId]);
        $data = [];
        if(!empty($bank) && isset($this->bankConfigs[$bank->bank])) {
            $data = $this->bankConfigs[$bank->bank];
        }
        return [
            'status' => 200,
            'data'   => $data
        ];

    }
}