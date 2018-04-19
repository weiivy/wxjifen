<?php
namespace console\actions\comparePrice;

use yii\base\Action;
use Yii;
use console\models\admin\compare\WxProduct;
use console\models\admin\compare\WxComparison;

/**
 * 比价脚本
 * @copyright (c) 2017, lulutrip.com
 * @author Justin Jia<justin.jia@ipptravel.com>
 */
class Price extends Action
{
    /**
     * @var array 我行平台
     */
    public $platform = [
        '1' => [
            'platform'  => 'lulutrip',
            'name'      => 'Lulutrip',
            'lang'      => '中文',
            'cur'       => 'CNY'
        ],
        '2' => [
            'platform'  => 'globerouter',
            'name'      => 'Globerouter',
            'lang'      => '英文',
            'cur'       => 'USD',
        ],
        '3' => [
            'platform'  => 'woqu',
            'name'      => 'Woqu',
            'lang'      => '中文',
            'cur'       => 'CNY'
        ],
        '4' => [
            'platform'  => 'lulutrip4',
            'name'      => 'Lulutrip4',
            'lang'      => '中文',
            'cur'       => 'USD'
        ]
    ];

    /**
     * @var array 竞争对手网站
     */
    public $cp_platform = [
        'toursforfun'   => '途风中文站',
        'tours4fun'     => '途风英文站',
        'usitrip'       => '走四方',
        'taketours'     => 'TakeTours',
        'ctrip'         => '携程',
        'itrip'         => '爱去',
        'wannar'        => '玩哪儿',
    ];

    /**
     * @var array 产品类型
     */
    public $type = ['旅行团'];

    /**
     * curl Post 请求
     * @copyright 2017-10-24
     * @author Justin Jia<justin.jia@ipptravel.com>
     * @param $url
     * @param $post
     * @return mixed
     */
    private function curlPost($url, $post) {
        sleep(10);
        $result = Yii::$app->helper->curlJson($url, $post);
        return $result;
    }

    public function run($id = '')
    {
        try{
            $data = ['code' => 200, 'message' => $this->grabData($id)];
            Yii::info(json_encode($data), __METHOD__);
        }catch (\Exception $e){
            $data = ['code'=>$e->getCode(), 'message' => $e->getMessage()];
            Yii::error(json_encode($data), __METHOD__);
        }
    }
    /**
     * 抓取脚本
     * @author Serena Liu<serena.liu@ipptravel.com>
     * @copyright 2017-12-04
     */
    private function grabData($id){
        Yii::$app->db_wxadmin->close();
        Yii::$app->db_wxadmin->open();
        $sql = "UPDATE `wx_comparison` SET `cp_price` = `cp_price_new` WHERE cp_price_new != 0";
        Yii::$app->db_wxadmin->createCommand($sql)->execute();

        if($id){
            $sql = "SELECT * FROM `wx_product` WHERE id IN (" .  $id . ")";
        }else{
            $sql = "SELECT * FROM `wx_product` WHERE `status` = 1 and `updatetime` < '" . date('Y-m-d H:i:s', strtotime('-1 days')) . "'";
        }
        $products = Yii::$app->db_wxadmin->createCommand($sql)->queryAll();

        $apiUrl = Yii::$app->params['service']['api'] . '/admin/compare/compare-price';
        foreach ($products as $key => $var) {
            if (empty($var['platform']) || empty($var['url'])) continue;
            $post = ['platform' => $this->platform[$var['platform']]['platform'], 'url' => $var['url']];
            $result = $this->curlPost($apiUrl, $post);
            Yii::info('API-POST: ' . $apiUrl . '===' . json_encode($post) . '===' . json_encode($result), __METHOD__);
            if (!isset($result['code']) || $result['code'] !== 200) {
                Yii::error('compare-price: Error Msg ' . json_encode($result) . ' , ' . $var['url'], __METHOD__);
            } else {
                if (($result['price'] !== $var['price']) && $result['price'] > 0) {
                    Yii::$app->db_wxadmin->close();
                    Yii::$app->db_wxadmin->open();
                    $sql = "UPDATE `wx_product` SET `price`='" . $result['price'] . "', `tourtitle`='" . $result['title'] . "', updatetime = '" . date('Y-m-d H:i:s') . "' WHERE `id`='" . $var['id'] . "'";
                    Yii::$app->db_wxadmin->createCommand($sql)->execute();
                }
            }
            Yii::$app->db_wxadmin->close();
            Yii::$app->db_wxadmin->open();
            //关联产品价格
            $sql = "SELECT * FROM `wx_comparison` WHERE `status` = 1 and `product_id` = '" . $var['id'] . "'";
            $cp = Yii::$app->db_wxadmin->createCommand($sql)->queryAll();
            foreach ($cp as $val) {
                $post = ['platform' => $val['cp_platform'], 'url' => $val['cp_url']];
                $cpresult = $this->curlPost($apiUrl, $post);
                Yii::info('API-POST-CHILD: ' . $apiUrl . '===' . json_encode($post) . '===' . json_encode($cpresult), __METHOD__);
                if (!isset($cpresult['code']) || $cpresult['code'] !== 200) {
                    Yii::error('compare-price: Error Msg ' . json_encode($cpresult) . ' , ' . $val['cp_url'], __METHOD__);
                    Yii::$app->db_wxadmin->close();
                    Yii::$app->db_wxadmin->open();
                    $sql = "UPDATE `wx_comparison` SET `remark`='Fail', updatetime = '" . date('Y-m-d H:i:s') . "' WHERE `id`='" . $val['id'] . "'";
                    Yii::$app->db_wxadmin->createCommand($sql)->execute();
                    continue;
                } else{
                    Yii::$app->db_wxadmin->close();
                    Yii::$app->db_wxadmin->open();
                    $sql = "UPDATE `wx_comparison` SET `cp_price_new`='" . $cpresult['price'] . "', `remark`='" . $cpresult['price'] . "', updatetime = '" . date('Y-m-d H:i:s') . "' WHERE `id`='" . $val['id'] . "'";
                    Yii::$app->db_wxadmin->createCommand($sql)->execute();
                }
            }
            //关联产品价格 end
        }
        return 'ok';
    }
}