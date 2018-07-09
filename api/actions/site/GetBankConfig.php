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
                ["codenum" => "25000", "goods" => "车主卡积分兑换", "num" => "不限次数", "money" => [
                    '1' => '75.00',
                    '2' => '80.00',
                    '3' => '85.00',
                ]],
                ["codenum" => "54000", "goods" => "建行积分兑换", "num" => "无限制", "money" => [
                    '1' => '70.20',
                    '2' => '75.60',
                    '3' => '86.40',
                ]],
            ],
        ],
        'GDYH' => [
            'listData' => [
                ["codenum" => "57000", "goods" => "50元电子E卡", "num" => "不限次数", "money" => [
                    '1' => '36.48',
                    '2' => '38.76',
                    '3' => '41.04',
                ]],
                ["codenum" => "115000", "goods" => "100元电子E卡", "num" => "不限次数", "money" => [
                    '1' => '73.60',
                    '2' => '78.20',
                    '3' => '82.80',
                ]],
            ],
        ],
        'ZGYH' => [
            'listData' => [
                ["codenum" => "5000", "goods" => "10元京东钢镚", "num" => "2次/月", "money" => [
                    '1' => '8.00',
                    '2' => '8.25',
                    '3' => '8.50',
                ]],
                ["codenum" => "15000", "goods" => "30元京东钢镚", "num" => "2次/月", "money" => [
                    '1' => '24.00',
                    '2' => '24.75',
                    '3' => '25.50',
                ]],
                ["codenum" => "25000", "goods" => "50元京东钢镚", "num" => "2次/月", "money" => [
                    '1' => '40.00',
                    '2' => '41.25',
                    '3' => '42.15',
                ]],
                ["codenum" => "45000", "goods" => "100元京东钢镚", "num" => "2次/月", "money" => [
                    '1' => '72.00',
                    '2' => '74.25',
                    '3' => '76.50',
                ]],
                ["codenum" => "90000", "goods" => "200元京东钢镚", "num" => "2次/月", "money" => [
                    '1' => '144.00',
                    '2' => '148.50',
                    '3' => '153.00',
                ]],

            ],
        ],
        'BJYH' => [
            'listData' => [
                ["codenum" => "6000", "goods" => "10元京东钢蹦", "num" => "每人每月限兑6笔", "money" => [
                    '1' => '7.98',
                    '2' => '8.28',
                    '3' => '8.52',
                ]],
                ["codenum" => "18000", "goods" => "30元京东钢蹦", "num" => "每人每月限兑6笔", "money" => [
                    '1' => '23.94',
                    '2' => '24.84',
                    '3' => '25.56',
                ]],
                ["codenum" => "30000", "goods" => "50元京东钢蹦", "num" => "每人每月限兑6笔", "money" => [
                    '1' => '36.90',
                    '2' => '41.40',
                    '3' => '42.60',
                ]],
            ],
        ],
        'GSYH' => [
            'listData' => [
                ["codenum" => "66667", "goods" => "电子油卡兑换", "num" => "不限次数", "money" => [
                    '1' => '70.00',
                    '2' => '75.00',
                    '3' => '80.00',
                ]],
            ],
        ],
        'HFYH' => [
            'listData' => [
                ["codenum" => "27500", "goods" => "50元京东E卡", "num" => "不限次数", "money" => [
                    '1' => '36.00',
                    '2' => '39.60',
                    '3' => '45.10',
                ]],
                ["codenum" => "55500", "goods" => "100元京东E卡", "num" => "不限次数", "money" => [
                    '1' => '72.70',
                    '2' => '79.90',
                    '3' => '91.00',
                ]],
            ],
        ],
        'ZGYD' => [
            'listData' => [
                ["codenum" => "820", "goods" => "10元天宏一卡通", "num" => "不限次数", "money" => [
                    '1' => '6.00',
                    '2' => '6.30',
                    '3' => '6.70',
                ]],
                ["codenum" => "2450", "goods" => "30元天宏一卡通", "num" => "不限次数", "money" => [
                    '1' => '18.10',
                    '2' => '18.80',
                    '3' => '20.0',
                ]],
                ["codenum" => "4090", "goods" => "50元天宏一卡通", "num" => "不限次数", "money" => [
                    '1' => '30.00',
                    '2' => '31.50',
                    '3' => '33.50',
                ]],
                ["codenum" => "8170", "goods" => "100元天宏一卡通", "num" => "不限次数", "money" => [
                    '1' => '60.50',
                    '2' => '63.00',
                    '3' => '67.00',
                ]],
            ],
        ],
        'PAYH' => [
            'listData' => [
                ["codenum" => "25000", "goods" => "50元沃尔玛", "num" => "不限次数", "money" => [
                    '1' => '41.00',
                    '2' => '42.00',
                    '3' => '44.00',
                ]],
                ["codenum" => "50000", "goods" => "100元沃尔玛", "num" => "不限次数", "money" => [
                    '1' => '82.00',
                    '2' => '84.00',
                    '3' => '88.00',
                ]],
                ["codenum" => "100000", "goods" => "200元沃尔玛", "num" => "不限次数", "money" => [
                    '1' => '164.00',
                    '2' => '168.00',
                    '3' => '176.00',
                ]],
            ],
        ],
        'JTYH' => [
            'listData' => [
                ["codenum" => "21000", "goods" => "交通中杯星巴克", "num" => "不限次数", "money" => [
                    '1' => '14.70',
                    '2' => '16.80',
                    '3' => '18.90',
                ]]
            ],
        ],
        'ZGLT' => [
            'listData' => [
                ["codenum" => "1020", "goods" => "10元Q币", "num" => "不限次数", "money" => [
                    '1' => '6.75',
                    '2' => '7.28',
                    '3' => '7.8',
                ]],
                ["codenum" => "2060", "goods" => "20元Q币", "num" => "不限次数", "money" => [
                    '1' => '13.50',
                    '2' => '14.56',
                    '3' => '15.60',
                ]],
                ["codenum" => "3090", "goods" => "30元Q币", "num" => "不限次数", "money" => [
                    '1' => '20.25',
                    '2' => '21.84',
                    '3' => '23.40',
                ]],
                ["codenum" => "5200", "goods" => "50元Q币", "num" => "不限次数", "money" => [
                    '1' => '33.75',
                    '2' => '36.40',
                    '3' => '39.00',
                ]],
                ["codenum" => "10400", "goods" => "100元Q币", "num" => "不限次数", "money" => [
                    '1' => '67.50',
                    '2' => '72.80',
                    '3' => '78.00',
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