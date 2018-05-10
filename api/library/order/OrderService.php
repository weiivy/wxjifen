<?php
namespace api\library\order;

use api\library\Help;
use api\models\Member;
use api\models\Order;
use api\models\OrderPhoto;
use yii\base\Component;
use yii\data\Pagination;
use yii\web\UploadedFile;

/**
 * 订单服务类
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class OrderService extends Component
{
    /**
     * 订单列表
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $page
     * @param $pageSize
     * @param $status
     * @param $memberId
     * @return array
     */
    public static function getList($page, $pageSize, $status, $memberId)
    {
        $order = Order::find();
        $order->where(['member_id' => $memberId]);
        if(in_array($status, [Order::STATUS_20, Order::STATUS_30, Order::STATUS_40])) {
            $order->andWhere(['status' => $status]);
        }
        $order->orderBy('id DESC');
        $pages = new Pagination(['totalCount' =>$order->count(), 'pageSize' => $pageSize]);
        $pages->setPage($page-1);
        $order = $order->offset($pages->offset)->limit($pages->pageSize)->asArray()->all();
        foreach ($order as &$value) {
            $value['statusAlisa'] = Order::statusAlisa($value['status']);
            $value['updated_at'] = date("Y-m-d H:i:s", $value['updated_at']);
            $value['bank'] = Order::bankAlisa($value['bank']);
        }
        return ['list' => $order, 'count' => $pages->totalCount];
    }

    /**
     * 生成订单
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-16
     * @param $post
     * @return string
     * @throws \Exception
     */
    public static function saveOrder($post)
    {
        $member = Member::findOne(['id' => $post['member_id']]);
        if(empty($member)) {
            \Yii::error("memberId:" . $post['member_id'] . "用户不存在");
            throw new \Exception("报单失败", 0);
        }

        //生成订单号
        $outTradeNo = static::generateOutTradeNo();
        $order = new Order();
        $order->out_trade_no = $outTradeNo;
        $order->member_id = $post['member_id'];
        $order->bank = $post['bank'];
        $order->integral = $post['score'];
        if(!empty($post['exchange_code'])) $order->exchange_code = $post['exchange_code'];
        if(isset($post['valid_time']) && $post['valid_time'] != '请选择有效期') $order->valid_time = $post['valid_time'];
        $order->status = Order::STATUS_10;
        if(!empty($post['remark'])) $order->remark = $post['remark'];
        $order->created_at = $order->updated_at = time();
        $order->save();
        if($order->errors) {
            \Yii::error(json_encode($order->errors));
            throw new \Exception("报单失败", 0);
        }
        return \Yii::$app->db->getLastInsertID();

    }

    /**
     * 生成订单号
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-16
     * @return string
     * @throws \Exception
     */
    private static function generateOutTradeNo()
    {
        //生成订单号
        $now = time();
        $sql = "SELECT COUNT(*) as total FROM ". Order::tableName() ." WHERE created_at = $now FOR UPDATE"; //当前时间(秒)订单数量
        $command = \Yii::$app->db->createCommand($sql);
        $count     = $command->queryAll($sql)['total'];
        if ($count == 999) {
            \Yii::error('创建订单失败, 生成订单号失败');
            throw new \Exception("报单失败", 0);
        }
        $count++;
        return strtoupper(dechex($now) . dechex(rand(1, 5) . str_pad($count, 3, '0', STR_PAD_LEFT) . rand(0, 9)));
    }

    /**
     * 保存图片
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-16
     * @param int $orderId
     * @throws \Exception
     */
    public static function uploadFile($orderId =1)
    {
        $image = UploadedFile::getInstanceByName('file');
        if($image == false){
            throw new \Exception('文件上传失败', 0);
        }

        $ext = $image->getExtension();

        $path_result = Help::createItemPath('/uploads/',$ext);
        $image->saveAs(\Yii::$app->getBasePath() . $path_result['save_path']);

        $model = new OrderPhoto();
        $model->order_id = $orderId;
        $model->image = $path_result['web_path'];
        $model->save();
        if($model->errors) {
           throw new \Exception("报单失败", 0);
        }
    }
}