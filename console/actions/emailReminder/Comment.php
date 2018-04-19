<?php
/**
 * 行后评价提醒
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */

namespace console\actions\emailReminder;


use Curl\Curl;
use yii\base\Action;
use yii;

class Comment extends Action
{
    public function run()
    {
        $this->send();
    }

    private function send()
    {
        $sql = "select m.name, m.screenname, m.email, b.tourcode, b.departure_date, b.return_date, b.product_title, b.lang, b.ownedby, o.orderconf,b.commented,b.bookingconf,ads.name as adviser_name, ads.name_en, ads.email as adviser_email, ads.avatar_2 FROM booking b RIGHT JOIN ordersum o USING (orderid)  left join members m  on(m.memberid=o.memberid) LEFT JOIN adviser_saler_orders aso ON(o.orderid = aso.orderid) LEFT JOIN adviser_salers ads ON (aso.saler_id = ads.admin_id) where b.status=5 and  curdate()=adddate(b.return_date,interval 3 day) and o.sourcetype = 'pc' and o.sourcefrom = 'tour-new'";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $order){
            Yii::$app->controller->layout = 'email.html';
            Yii::$app->controller->emailTitle = "行后评价提醒";
            $subject = "【路路行】刚结束的旅行是否很难忘呢？快来给小路打个分吧，点评即享25美金优惠";
            $order['rateUrl'] = Yii::$app->params['service']['www']. '/my/rate_tour/tourcode-' . $order['tourcode'] . '?bookingconf=' . $order['bookingconf'];
            $body = Yii::$app->controller->render('@console/views/emailReminder/comment.html', $order);

            $curl = new Curl();
            $curl->get(Yii::$app->config->api . "/order/{$order['orderconf']}/contact-email");
            $to = json_decode($curl->response, true);

            Yii::$app->mailer->compose('@common/mail/layout.html', ['content' => $body])
                ->setTo($to)->setSubject($subject)->send();
        }
    }
} 