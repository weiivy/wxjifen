<?php
/**
 * 行前提醒邮件
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */

namespace console\actions\emailReminder;

use Curl\Curl;
use yii\base\Action;
use yii;

class Itinerary extends Action
{
    /**
     * @var int 提前几天提醒
     */
    public $days = 3;

    public function run()
    {
        $this->send();
    }

    private function send()
    {
        $sql = "select m.name, m.screenname, m.email, b.tourcode, b.departure_date, b.return_date, b.product_title, b.lang, b.ownedby, o.orderconf,b.commented,b.bookingconf,ads.name as adviser_name, ads.name_en, ads.email as adviser_email, ads.avatar_2 FROM booking b RIGHT JOIN ordersum o USING (orderid)  left join members m  on(m.memberid=o.memberid) LEFT JOIN adviser_saler_orders aso ON(o.orderid = aso.orderid) LEFT JOIN adviser_salers ads ON (aso.saler_id = ads.admin_id) where b.status=5 and  b.departure_date=adddate(curdate(),interval ".$this->days." day) and o.sourcetype = 'pc' and o.sourcefrom = 'tour-new'";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $order){
            Yii::$app->controller->layout = 'email.html';
            Yii::$app->controller->emailTitle = "行前告知提醒";
            $subject = "Lulutrip.com 行前提醒：" .$order['orderconf'];

            $curl = new Curl();
            $curl->get(Yii::$app->config->api . "/order/{$order['orderconf']}/email-access-key");
            $emailAccessKey = $curl->response;
            $order['voucherUrl'] = Yii::$app->params['service']['www']. '/order/'.$order['orderconf'].'/voucher?emailAccessKey=' . $emailAccessKey;

            $body = Yii::$app->controller->render('@console/views/emailReminder/itinerary.html', $order);

            $curl = new Curl();
            $curl->get(Yii::$app->config->api . "/order/{$order['orderconf']}/contact-email");
            $to = json_decode($curl->response, true);

            Yii::$app->mailer->compose('@common/mail/layout.html', ['content' => $body])
                ->setTo($to)->setSubject($subject)->send();
        }
    }
} 