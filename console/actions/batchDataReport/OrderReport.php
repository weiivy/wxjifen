<?php

namespace console\actions\batchDataReport;

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
    public function run($sdate = '', $edate = '') {
        $subject = "订单报表";

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Xiaopei")
            ->setLastModifiedBy("Xiaopei")
            ->setTitle($subject)
            ->setSubject($subject);

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)->setTitle("订单报表")
            ->setCellValue('A1', '日期')
            ->setCellValue('B1', '产品区域')
            ->setCellValue('C1', '订单数')
            ->setCellValue('D1', 'IC订单数');

        //var_dump($this->getOrderByRegion());die;
        foreach ($this->getOrderByRegion($sdate,$edate) as $key => $order) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, $key + 2, $order['date']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $key + 2, $order['region']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, $key + 2, $order['count']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, $key + 2, $order['ic']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $this->SaveViaTempFile($objWriter,$subject);
    }

    /**
     * 按区域统计订单数据
     * @author xiaopei dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-12-19
     * @param $sdate
     * @param $endDate
     * @return array
     */
    public function getOrderByRegion($sdate,$endDate){
        $regions = array(
            'Europe' => "(SELECT DISTINCT(state_code) FROM c_states WHERE parent_state_code IN ('EU', 'EUWest', 'EUSouth','EUCenEast', 'EUNorth', 'EUUK') OR state_code = 'EU')",
            'East' => "('USEast', 'Florida', 'Alaska')",
            'West' => "('USWest','Yellowstone','SA')",
            'Hawaii' => "('Hawaii')",
            'Canada' => "('CA')",
            'ANZ' => "('AUS', 'aueast', 'auwest', 'ausouth', 'aunorth', 'autas', 'FJI', 'NZ', 'AU')",
        );
        $areaNew = array(
            'Europe' => 'POI_30097',
            'East' => '1',
            'West' => '2',
            'Hawaii' => '4',
            'Canada' => 'POI_13392',
            'ANZ' => 'POI_30098',
        );
        $areaPack = array(
            'Europe' => 'EU',
            'East' => 'USEast',
            'West' => 'USWest',
            'Hawaii' => 'Hawaii',
            'Canada' => 'CA',
            'ANZ' => 'AU',
        );
        $data = [];
        do{
            $edate = date('Y-m-d', strtotime($sdate) + 6*24*3600);
            foreach ($regions as $key => $region){
                $temp = $rows = $out = [];
                //旅行团
                $sql1 = "SELECT o.orderid, b.tourcode, b.is_ic AS ic FROM ordersum o JOIN booking b ON o.orderid = b.`orderid` JOIN tours t ON t.tourcode = b.tourcode JOIN tour_scene AS ts ON t.tourcode = ts.tourcode JOIN scene AS s ON ts.sceneid = s.sceneid WHERE o.sourcefrom <> 'tour-new' AND s.scenestate IN " .$region. " AND o.`status` > 1 AND o.`status` < 7 AND b.`status` > 1 AND b.`status` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row1 = Yii::$app->db->createCommand($sql1)->queryAll();
                $rows = array_merge($rows,$row1);
                $sql2 = "SELECT o.orderid, b.tourcode, b.is_ic AS ic FROM ordersum o JOIN booking b ON o.orderid = b.`orderid` JOIN grouptravel.t_gt_product t ON t.productCode = b.tourcode WHERE (t.channels & 2) > 0 AND t.deleted = 0 AND o.sourcefrom = 'tour-new' AND find_in_set('{$areaNew[$key]}', t.area) AND o.`status` > 1 AND o.`status` < 7 AND b.`status` > 1 AND b.`status` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row2 = Yii::$app->db_slave->createCommand($sql2)->queryAll();
                $rows = array_merge($rows,$row2);

                //自由行
                $sql = "SELECT o.orderid, ca.actid, if(operatorcode=52 or operatorcode=217, 1, 0) AS ic FROM ordersum o JOIN c_act_orders co ON o.orderid = co.`orderid` JOIN c_acts AS ca ON ca.actid = co.actid JOIN scene AS s ON ca.sceneid = s.sceneid WHERE s.scenestate IN " .$region. " AND o.`status` > 1 AND o.`status` < 7 AND co.`aostatus` > 1 AND co.`aostatus` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row3 = Yii::$app->db->createCommand($sql)->queryAll();
                $rows = array_merge($rows,$row3);

                //标准化包团
                $sql = "SELECT o.orderid,op.packid FROM ordersum o JOIN order_packagetour op ON o.orderid = op.orderid JOIN packagetour_region pr ON op.packid = pr.packid WHERE pr.pack_regioncode = '{$areaPack[$key]}' AND o.`status` > 1 AND o.`status` < 7 AND op.`postatus` > 1 AND op.`postatus` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row4 = Yii::$app->db->createCommand($sql)->queryAll();
                foreach ($row4 as &$val4){
                    $val4['ic'] = 0;
                }
                $rows = array_merge($rows,$row4);

                //包车
                $sql = "SELECT o.orderid,cao.Pbus_id FROM ordersum o JOIN c_car_orders cao ON o.orderid = cao.orderid JOIN Pbus pb ON cao.Pbus_id = pb.Pbus_id JOIN scene s ON pb.sceneid = s.sceneid WHERE s.scenestate IN " .$region. " AND o.`status` > 1 AND o.`status` < 7 AND cao.`costatus` > 1 AND cao.`costatus` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row5 = Yii::$app->db->createCommand($sql)->queryAll();
                foreach ($row5 as &$val5){
                    $val5['ic'] = 0;
                }
                $rows = array_merge($rows,$row5);

                foreach ($rows as $vali){
                    $out[$vali['orderid']] = $vali;
                }
                $temp['date'] = $sdate .'~'. $edate;
                $temp['region'] = $key;
                $temp['count'] = count($out);
                $icNum = 0;
                foreach ($out as $val){
                    if(in_array($val['ic'], [1,2])){
                        $icNum ++;
                    }
                }
                $temp['ic'] = $icNum;
                $data[] = $temp;
            }
            $sdate = date('Y-m-d', strtotime($edate) + 24*3600);
        }while ($sdate<$endDate);

        return $data;
    }

    /**
     * 导出文件
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-11-24
     */
    private function SaveViaTempFile($objWriter, $subject){
        //导出文件
        $filePath = Yii::$app->runtimePath . '/files/batchData';
        @mkdir($filePath, 0777, true);
        $fileName = $filePath . '/订单统计数据.xlsx';
        $objWriter->save($fileName);
        Yii::$app->mailer->backup = false;
        Yii::$app->mailer->compose('layout.html', ['content' => '请查看附件'])->setSubject($subject)->setTo([
            'xiaopei.dou@ipptravel.com' => 'Xiaopei'
        ])->attach($fileName)->send();
    }
}