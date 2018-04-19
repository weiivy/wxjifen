<?php
/**
 * 航班提醒 每天更新
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
namespace console\actions\emailReminder;

use Curl\Curl;
use yii\base\Action;
use yii;

class Flight extends Action
{
    public $firstNoteDay = 10;//第一次提醒
    public $secNoteDay = 3;//第二次提醒

    public function run() {
        $this->send();
    }

    private function send() {
        $sql = "SELECT o.orderconf, o.orderid, b.product_title, b.bookingconf, b.departure_date, ads.name as adviser_name, ads.name_en, ads.email as adviser_email, ads.avatar_2, m.name as user_name, m.email as user_email  FROM booking b LEFT JOIN ordersum o ON(b.orderid = o.orderid) LEFT JOIN members m ON(o.memberid = m.memberid) LEFT JOIN adviser_saler_orders aso ON(o.orderid = aso.orderid) LEFT JOIN adviser_salers ads ON (aso.saler_id = ads.admin_id) WHERE b.pickup_type = 1 AND b.flight_filled != 8 AND b.flightinfo = '' AND o.sourcetype = 'pc' and o.sourcefrom = 'tour-new' AND (b.departure_date = adddate(curdate(), interval ".$this->firstNoteDay." day) || b.departure_date = adddate(curdate(), interval ".$this->secNoteDay." day)) and b.status >= 3";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $order){
            Yii::$app->controller->layout = 'email.html';
            Yii::$app->controller->emailTitle = "航班信息补填提醒";
            $subject = "Lulutrip.com 航班信息提醒：".$order['orderconf'];
            $order['hashtime'] = $hashtime = time();
            $hashdata = $order['bookingconf'] . $order['user_email'] . $hashtime . 'updateflighthash';
            $order['hash'] = md5($hashdata);
            $body = Yii::$app->controller->render('@console/views/emailReminder/flight.html', $order);

            $curl = new Curl();
            $curl->get(Yii::$app->config->api . "/order/{$order['orderconf']}/contact-email");
            $to = json_decode($curl->response, true);

            Yii::$app->mailer->compose('@common/mail/layout.html', ['content' => $body])
                ->setTo($to)->setSubject($subject)->send();
        }
    }
}