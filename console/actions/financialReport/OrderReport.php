<?php

namespace console\actions\financialReport;

use yii\base\Action;
use Yii;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * 导出订单报表
 * @package console\actions\financialReport
 * @author Victor Tang<victor.tang@ipptravel.com>
 * @copyright (c) 2017, lulutrip.com
 */
class OrderReport extends Action {
    /**
     * 导出订单报表
     * @author Victor Tang<victor.tang@ipptravel.com>
     * @copyright 2017-11-27
     * @param $startDate string 开始日期
     * @param $endDate string 结束日期
     */
    public function run($startDate = 'last week monday', $endDate = 'last week sunday') {
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $subject = "{$startDate} - {$endDate} 订单报表";
        $content = '请查看附件';
        $fileName = "{$startDate} - {$endDate} 订单报表.xlsx";

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Victor Tang")
            ->setLastModifiedBy("Victor Tang")
            ->setTitle($subject)
            ->setSubject($subject);

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)->setTitle("订单报表")
            ->setCellValue('A1', '订单种类（参团 玩乐 邮轮 租车）')
            ->setCellValue('B1', '订单号')
            ->setCellValue('C1', '订单金额（美金）')
            ->setCellValue('D1', '订购人姓名')
            ->setCellValue('E1', '订购人是否为第一次订购')
            ->setCellValue('F1', '订购平台（pc 英文站 手机端 平板）')
            ->setCellValue('G1', '订购产品编号')
            ->setCellValue('H1', '产品名（中文）')
            ->setCellValue('I1', '行程天数')
            ->setCellValue('J1', '供应商（地接名称）')
            ->setCellValue('K1', '订单参团人数')
            ->setCellValue('L1', '产品订购前置天数')
            ->setCellValue('M1', '供应商区域')
            ->setCellValue('N1', '行程开始城市')
            ->setCellValue('O1', '行程结束城市')
            ->setCellValue('P1', '参团类型（当地上车 or 接机）')
            ->setCellValue('Q1', '订购日期');

        foreach ($this->_getOrderReport($startDate, $endDate) as $key => $order) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, $key + 2, $order['orderType']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $key + 2, $order['orderConf']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, $key + 2, $order['totalAmount']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, $key + 2, $order['userName']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $key + 2, $order['firstTimePurchase']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, $key + 2, $order['sourcetype']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, $key + 2, $order['productCode']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7, $key + 2, $order['productTitle']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8, $key + 2, $order['duration']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9, $key + 2, $order['operatorname']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10, $key + 2, $order['passengerCount']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11, $key + 2, $order['leadTime']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12, $key + 2, $order['operatorstate']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13, $key + 2, $order['startLocation']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14, $key + 2, $order['endLocation']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15, $key + 2, $order['pickupType']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16, $key + 2, $order['order_date']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('/tmp/orderReport.xlsx');

        Yii::$app->mailer->compose('layout.html', ['content' => $content])->setSubject($subject)->setTo([
            'vivi.song@ipptravel.com' => 'Vivi Song',
            'yan.sun@ipptravel.com' => 'Yan Sun',
            'sue@lulutrip.com' => 'Sue'
        ])->attach('/tmp/orderReport.xlsx', ['fileName' => $fileName])->send();
    }

    /**
     * 获取订单数据
     * @author Victor Tang<victor.tang@ipptravel.com>
     * @copyright 2017-11-27
     * @param $startDate
     * @param $endDate
     * @return array
     */
    private function _getOrderReport($startDate, $endDate) {
        $sql = "(select 'Tour New' as orderType, booking.bookingconf as orderConf, totalamount/usdrate as totalAmount, members.name as userName,
                (select IF(count(*) > 1, 'No', 'Yes') from ordersum where memberid = members.memberid and status in (3,4,5)) as firstTimePurchase,
                sourcetype, booking.tourcode as productCode, booking.product_title as productTitle, t_gt_product.duration, operatorname, operatorstate,
                (select count(*) from passenger_booking where bookingconf = booking.bookingconf) as passengerCount, t_gt_product.cutoffDates as leadTime,
                (select cn_name from basedata.t_wq_poi_base where code = startLocation) as startLocation,
                (select cn_name from basedata.t_wq_poi_base where code = endLocation) as endLocation,
                IF(pickupType=1, 'Airport Pickup', IF(pickupType=2, 'Local Pickup', 'Hotel')) as pickupType, ordersum.order_date
                from ordersum join members using (memberid)
                join booking using (orderid)
                join operators on (operators.operatorcode = booking.supplier_id)
                join grouptravel.t_gt_product on (t_gt_product.productCode = booking.tourcode and t_gt_product.deleted = 0)
                where ordersum.sourcefrom = 'tour-new' and order_date >= '{$startDate}' and order_date <= '{$endDate}' and ordersum.status in (3,4,5))
                
                union
                
                (select 'Tour' as orderType, booking.bookingconf as orderConf, totalamount/usdrate as totalAmount, members.name as userName,
                (select IF(count(*) > 1, 'No', 'Yes') from ordersum where memberid = members.memberid and status in (3,4,5)) as firstTimePurchase,
                sourcetype, tours.tourcode as productCode, tours.tournewtitle_cn as productTitle, tours.tourlen, operatorname, operatorstate,
                (select count(*) from passenger_booking where bookingconf = booking.bookingconf) as passengerCount, tours.tour_leadtime as leadTime,
                tours.tourstartcity, tours.tourendpoint,
                IF(pickupType=2, 'Airport Pickup', IF(pickupType=1, 'Local Pickup', 'Hotel')) as pickupType, ordersum.order_date
                from ordersum join members using (memberid)
                join booking using (orderid)
                join tours using (tourcode)
                join operators using (operatorcode)
                where ordersum.sourcefrom <> 'tour-new' and order_date >= '{$startDate}' and order_date <= '{$endDate}' and ordersum.status in (3,4,5))
                
                union
                
                (select 'Activity' as orderType, ordersum.orderconf as orderConf, totalamount/usdrate as totalAmount, members.name as userName,
                (select IF(count(*) > 1, 'No', 'Yes') from ordersum where memberid = members.memberid and status in (3,4,5)) as firstTimePurchase,
                sourcetype, c_acts.actid as productCode, c_acts.actname as productTitle, c_acts.actlength, operatorname, operatorstate,
                (select count(*) from c_act_passengers where aoid = c_act_orders.aoid) as passengerCount, c_acts.actlead as leadTime,
                null, null, null, ordersum.order_date
                from ordersum join members using (memberid)
                join c_act_orders using (orderid)
                join c_acts using (actid)
                join operators using (operatorcode)
                where order_date >= '{$startDate}' and order_date <= '{$endDate}' and ordersum.status in (3,4,5))
                
                union
                
                (select 'Cruise' as orderType, ordersum.orderconf as orderConf, totalamount/usdrate as totalAmount, members.name as userName,
                (select IF(count(*) > 1, 'No', 'Yes') from ordersum where memberid = members.memberid and status in (3,4,5)) as firstTimePurchase,
                sourcetype, tourico_cruise_line.tc_line_id as productCode, tourico_cruise_line.tc_line_name_cn as productTitle, null, null, null,
                (select count(*) from passenger_tourico_cruise where orderid = ordersum.orderid) as passengerCount, null,
                null, null, null, ordersum.order_date
                from ordersum join members using (memberid)
                join c_tourico_cruise_order using (orderid)
                join tourico_cruise_line using (tc_line_id)
                where order_date >= '{$startDate}' and order_date <= '{$endDate}' and ordersum.status in (3,4,5))
                
                union
                
                (select 'Rent Car' as orderType, ordersum.orderconf as orderConf, totalamount/usdrate as totalAmount, members.name as userName,
                (select IF(count(*) > 1, 'No', 'Yes') from ordersum where memberid = members.memberid and status in (3,4,5)) as firstTimePurchase,
                sourcetype, rent_car.id as productCode, rent_car.title as productTitle, null, null, null,
                (select count(*) from order_rentcar_passenger where or_id = order_rentcar.or_id) as passengerCount, null,
                null, null, null, ordersum.order_date
                from ordersum join members using (memberid)
                join order_rentcar using (orderid)
                join rent_car on (rent_car.id = order_rentcar.carid)
                where order_date >= '{$startDate}' and order_date <= '{$endDate}' and ordersum.status in (3,4,5))";

        return Yii::$app->db_slave->createCommand($sql)->queryAll();
    }
}