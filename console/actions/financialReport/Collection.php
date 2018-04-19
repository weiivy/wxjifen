<?php

namespace console\actions\financialReport;

use yii\base\Action;
use Yii;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * 我趣收款/退款记录
 * @package console\actions\financialReport
 * @copyright (c) 2017, lulutrip.com
 * @author Victor Tang<victor.tang@ipptravel.com>
 */
class Collection extends Action {
    /**
     * 导出我趣收款/退款记录 Excel 报表
     * @author Victor Tang<victor.tang@ipptravel.com>
     * @copyright 2017-11-13
     * @param $date string 日期
     */
    public function run($date = 'yesterday') {
        $date = date('Y-m-d', strtotime($date));
        $subject = $date . '我趣收款/退款记录';
        $fileName = $date . '我趣收款/退款记录.xlsx';

        $summary = $this->_getSummary();
        $content = "截止目前，人民币我趣收款一共 {$summary['count']} 单，其中补单成功 {$summary['count_success']} 单，补单失败 {$summary['count_failed']} 单。<br /><br /><br />";
        if ($summary['count_failed'] > 0) {
            $content .= "补单失败的订单号：{$summary['failed_orders']}。<br /><br /><br />";
        }
        $content .= "{$date} 我趣收款/退款记录请查看附件。<br /><br /><br />";

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Victor Tang")
            ->setLastModifiedBy("Victor Tang")
            ->setTitle($subject)
            ->setSubject($subject);

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)->setTitle("收款记录")
            ->setCellValueExplicit('A1', 'Lulutrip订单号')
            ->setCellValueExplicit('B1', '支付渠道（支付宝、微信、财付通、信用卡）')
            ->setCellValueExplicit('C1', '对应的付款流水号')
            ->setCellValueExplicit('D1', '客人支付的RMB金额')
            ->setCellValueExplicit('E1', 'Woqu订单号');

        foreach ($this->_getVirtualOrder($date) as $key => $virtualOrder) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow(0, $key + 2, $virtualOrder['orderconf']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow(1, $key + 2, $virtualOrder['paytype']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow(2, $key + 2, $virtualOrder['paypalid']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow(3, $key + 2, $virtualOrder['payment_amount'], 'n');
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow(4, $key + 2, $virtualOrder['woqu_order_id']);
        }

        $objPHPExcel->createSheet(1);

        $objPHPExcel->setActiveSheetIndex(1)->setTitle("退款记录")
            ->setCellValueExplicit('A1', 'Lulutrip退款号')
            ->setCellValueExplicit('B1', 'Woqu退款号')
            ->setCellValueExplicit('C1', '退款RMB金额')
            ->setCellValueExplicit('D1', 'Lulutrip订单号')
            ->setCellValueExplicit('E1', '退款状态');

        foreach ($this->_getVirtualRefund($date) as $key => $virtualRefund) {
            $objPHPExcel->setActiveSheetIndex(1)->setCellValueExplicitByColumnAndRow(0, $key + 2, $virtualRefund['refund_id']);
            $objPHPExcel->setActiveSheetIndex(1)->setCellValueExplicitByColumnAndRow(1, $key + 2, $virtualRefund['woqu_refund_id']);
            $objPHPExcel->setActiveSheetIndex(1)->setCellValueExplicitByColumnAndRow(2, $key + 2, $virtualRefund['refund_amount'], 'n');
            $objPHPExcel->setActiveSheetIndex(1)->setCellValueExplicitByColumnAndRow(3, $key + 2, $virtualRefund['orderconf']);
            $objPHPExcel->setActiveSheetIndex(1)->setCellValueExplicitByColumnAndRow(4, $key + 2, $virtualRefund['status'] == 'CONFIRMED' ? '已退款' : '处理中');
        }

        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('/tmp/collection.xlsx');

        Yii::$app->mailer->compose('layout.html', ['content' => $content])->setSubject($subject)->setTo([
            'accounting@lulutrip.com' => 'Lulutrip Accounting',
            'fan.ye@ipptravel.com' => 'fan.ye@ipptravel.com',
            'chaohui.wang@ipptravel.com' => 'chaohui.wang@ipptravel.com',
            'fiona@ipptravel.com' => 'fiona@ipptravel.com',
            'yanfang.xu@ipptravel.com' => 'yanfang.xu@ipptravel.com',
            'victor.tang@ipptravel.com' => 'Victor Tang'
        ])->attach('/tmp/collection.xlsx', ['fileName' => $fileName])->send();
    }

    /**
     * 统计我趣收款补单记录
     * @author Victor Tang<victor.tang@ipptravel.com>
     * @copyright 2018-01-03
     * @return array|false
     */
    private function _getSummary() {
        $sql = "select count(*) as count, sum(virtual_order.order_id is not null) as count_success, sum(virtual_order.order_id is null) as count_failed, group_concat(DISTINCT IF(virtual_order.order_id is null, orderconf, '') SEPARATOR ' / ') as failed_orders from ordersum left join virtual_order on (virtual_order.order_id = ordersum.orderid) where paysubject like 'woquRMB%';";

        $result = Yii::$app->db->createCommand($sql)->queryOne();

        return $result;
    }

    /**
     * 导出虚拟订单数据
     * @param $date
     * @return array
     * @author Victor Tang<victor.tang@ipptravel.com>
     * @copyright 2017-11-13
     */
    private function _getVirtualOrder($date) {
        $sql = "select * from virtual_order join ordersum on (ordersum.orderid = virtual_order.order_id) where date(virtual_order.create_time) = '{$date}'";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    /**
     * 导出虚拟退款数据
     * @param $date
     * @return array
     * @author Victor Tang<victor.tang@ipptravel.com>
     * @copyright 2017-11-13
     */
    private function _getVirtualRefund($date) {
        $sql = "select *, virtual_refund.status from virtual_refund join order_refund on (order_refund.refund_id = virtual_refund.refund_id) join ordersum on (ordersum.orderid = order_refund.orderid) where date(virtual_refund.create_time) = '{$date}'";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
}