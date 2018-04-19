<?php

namespace console\actions\batchDataReport;

use yii\base\Action;
use Yii;
use PHPExcel;
use PHPExcel_IOFactory;
use common\library\base\Data;

/**
 * 导出订单操作时间报表
 * @package console\actions\financialReport
 * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
 * @copyright (c) 2017, lulutrip.com
 */
class orderOptimeReport extends Action {
    /**
     * 导出订单操作时间报表
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-12-22
     * @param $startDate string 开始日期
     * @param $endDate string 结束日期
     */
    public function run($sdate = '', $edate = '') {
        if(empty($sdate) || empty($edate)){
            echo '请输入开始日期或结束日期';
            exit;
        }
        $subject = "订单操作时间报表";

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
            ->setCellValue('C1', 'Operation')
            ->setCellValue('D1', 'Total order time（mintues）')
            ->setCellValue('E1', 'All orders')
            ->setCellValue('F1', 'IC orders')
            ->setCellValue('G1', '平均操作时间（time/all orders）')
            ->setCellValue('H1', 'OrderIds');

        foreach ($this->getOrderOptimeByRegionNew($sdate, $edate) as $key => $order) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, $key + 2, $order['date']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $key + 2, $order['region']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, $key + 2, $order['operation']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, $key + 2, $order['opTime']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $key + 2, $order['count']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, $key + 2, $order['ic']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, $key + 2, $order['avOpTime']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7, $key + 2, $order['orderIds']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $this->SaveViaTempFile($objWriter,$subject);
    }

    /**
     * 按区域统计订单操作时间
     * @author xiaopei dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-10-16
     */
    public function getOrderOptimeByRegion($sdate, $edate){
        $statesAll = Data::getStates();
        $statesOfPar = $statesAll['statesOfPar'];
        $regions = array(
            'Europe' => array('EU','EUSouth','EUNorth','EUCenEast','EUUK','EUWest'),
            'East' => array('USEast', 'Florida', 'Alaska'),
            'West' => array('USWest','Yellowstone','SA'),
            'Hawaii' => array('Hawaii'),
            'Canada' => array('CA'),
            'ANZ' => array('AUS', 'aueast', 'auwest', 'ausouth', 'aunorth', 'autas', 'FJI', 'NZ', 'AU'),
        );
        $areaNew = array(
            'POI_30097' => 'Europe',
            '1' => 'East',
            '2' => 'West',
            '4' => 'Hawaii',
            'POI_13392' => 'Canada',
            'POI_30098' => 'ANZ',
        );
        $specialState = array('USWest','Yellowstone','SA','USEast', 'Florida', 'Alaska','CA','Hawaii');
        $states = [];
        foreach ($regions as $key => $region){
            foreach ($region as $r){
                $states[$r] = $key;
            }
        }

        $sdateStr = strtotime($sdate);
        $datas = [];
        do {
            $edateStr = $sdateStr + 6*24*3600;
            $method = "'own', 'confirm_booking', 'do_send_invoice'";
            if($sdate>'2017-12-18'){
                $admin_table = 'llt2017logs.admin_logs';
                $log_table = 'llt2017logs.op_logs';
            }else{
                $admin_table = 'admin_logs';
                $log_table = 'op_logs';
            }
            //数据库迁移的临界时间
            if($sdateStr == strtotime('2017-12-15')){
                $sql = "SELECT * FROM "  .$admin_table. " WHERE controller='order' AND method IN ($method) AND datetime>=$sdateStr ORDER BY datetime ASC";
                $data = Yii::$app->db->createCommand($sql)->queryAll();
                $sql1 = "SELECT * FROM llt2017logs.admin_logs WHERE controller='order' AND method IN ($method) AND datetime<=$edateStr ORDER BY datetime ASC";
                $data1 = Yii::$app->db->createCommand($sql1)->queryAll();
                $sql2 = "SELECT * FROM "  .$log_table. " WHERE controller='order' AND method IN ($method) AND datetime>=$sdateStr ORDER BY datetime ASC";
                $dataOp = Yii::$app->db->createCommand($sql2)->queryAll();
                $sql3 = "SELECT * FROM llt2017logs.op_logs WHERE controller='order' AND method IN ($method) AND datetime<=$sdateStr ORDER BY datetime ASC";
                $dataOp1 = Yii::$app->db->createCommand($sql3)->queryAll();
                $data = array_merge($data,$data1,$dataOp,$dataOp1);
            }else{
                $sql = "SELECT * FROM "  .$admin_table. " WHERE controller='order' AND method IN ($method) AND datetime>=$sdateStr AND datetime<=$edateStr ORDER BY datetime ASC";
                $data = Yii::$app->db->createCommand($sql)->queryAll();
                $sql = "SELECT * FROM "  .$log_table. " WHERE controller='order' AND method IN ($method) AND datetime>=$sdateStr AND datetime<=$edateStr ORDER BY datetime ASC";
                $dataOp = Yii::$app->db->createCommand($sql)->queryAll();
                $data = array_merge($data,$dataOp);
            }
            $ordersTmpData = [];
            foreach ($data as $val){
                $tmp = [];
                $subject = json_decode($val['subject'],true) ? json_decode($val['subject'],true) : array();
                if(empty($subject)) continue;
                $tmp['method'] = $val['method'];
                $tmp['datetime'] = $val['datetime'];
                if ( 'own' == $val['method']) {
                    $tmp['orderid'] = $subject['orderid'];
                    if ('activity' == $subject['type']) {
                        $tmp['orderconf'] = $subject['orderid'].'-A'.$subject['id'];
                    }elseif ('tour' == $subject['type']) {
                        $tmp['orderconf'] = $subject['id'];
                    }else{
                        $tmp['orderconf'] = $subject['id'];
                    }
                } elseif ('confirm_booking' == $val['method']) {
                    if ('activity' == $subject['type']) {
                        $sql = "SELECT orderid FROM c_act_orders WHERE aoid = " . $subject['id'];
                        $row = Yii::$app->db->createCommand($sql)->queryOne();
                        $tmp['orderid'] = $row['orderid'];
                        $tmp['orderconf'] = $row['orderid'].'-A'.$subject['id'];
                    }elseif ('tour' == $subject['type']) {
                        $tmp['orderconf'] = $subject['id'];
                        $orderIdTmp = explode('-', $subject['id']);
                        $tmp['orderid'] = $orderIdTmp[2];
                    }else{
                        $tmp['orderid'] = $subject['id'];
                        $tmp['orderconf'] = $subject['id'];
                    }
                } elseif ('do_send_invoice' == $val['method'] ) {
                    $orderIdTmp = is_numeric($subject['id']) ? $subject['id'] : explode('-', $subject['id']);
                    $tmp['orderid'] = is_array($orderIdTmp) && $orderIdTmp[2] ? $orderIdTmp[2] : $orderIdTmp;
                    $tmp['orderconf'] = $subject['id'];
                }
                $tmp['region'] = 'Other';
                $tmp['ic'] = 0;
                //判断订单所属区域
                if (!empty($tmp['orderconf']) && !empty($tmp['orderid'])) {
                    $bookingconfArr = explode('-', $tmp['orderconf']);
                    if ('A' == @substr($bookingconfArr[1], 0,1)) {
                        //自由行
                        $sql = "SELECT o.paytype, ac.support_ic, s.scenestate FROM ordersum as o JOIN c_act_orders as aco on o.orderid = aco.orderid JOIN c_acts AS ac ON aco.actid=ac.actid JOIN scene AS s ON ac.sceneid=s.sceneid WHERE o.orderid=".$tmp['orderid'];
                        $actOrder = Yii::$app->db->createCommand($sql)->queryOne();
                        $tmp['paytype'] = $actOrder['paytype'];
                        $tmp['ic'] = $actOrder['support_ic'];
                        $state = (in_array($actOrder['scenestate'], $specialState)) ? $actOrder['scenestate'] : (empty($statesOfPar[$actOrder['scenestate']])? $actOrder['scenestate'] : $statesOfPar[$actOrder['scenestate']]);
                        if(in_array($actOrder['scenestate'], $specialState)){
                            $state = $actOrder['scenestate'];
                        }elseif (!empty($statesOfPar[$actOrder['scenestate']])){
                            $state = $statesOfPar[$actOrder['scenestate']];
                        }else{
                            $state = $actOrder['scenestate'];
                        }
                        $tmp['region'] = empty($states[$state]) ? 'Other' : $states[$state];
                    } else {
                        //旅行团
                        $sql = "SELECT o.paytype, bo.tourcode, o.sourcefrom FROM ordersum as o join booking as bo on o.orderid = bo.orderid WHERE o.orderid=".$tmp['orderid'];
                        $tourOrder = Yii::$app->db->createCommand($sql)->queryOne();
                        if(!empty($tourOrder)){
                            $tmp['paytype'] = $tourOrder['paytype'];
                            if($tourOrder['sourcefrom'] == 'tour-new'){
                                $sql = "SELECT area, immediateConfirm FROM grouptravel.t_gt_product WHERE productCode = '" . $tourOrder['tourcode'] ."'";
                                $rows = Yii::$app->db->createCommand($sql)->queryAll();
                                foreach ($rows as $gval){
                                    if(!empty($temp['region'])){
                                        continue;
                                    }
                                    if(!empty($gval['area'])){
                                        $tmp['ic'] = $gval['immediateConfirm'];
                                        $areaArr = explode(",", $gval['area']);
                                        $areaKey = current(array_intersect(array_keys($areaNew), $areaArr));
                                        $temp['region'] = empty($areaNew[$areaKey]) ? 'Other' : $areaNew[$areaKey];
                                    }
                                }

                            }else{
                                $sql = "SELECT t.tour_ic, s.scenestate FROM tours t JOIN tour_scene ts ON t.tourcode = ts.tourcode JOIN scene s ON ts.sceneid = s.sceneid WHERE t.tourcode = " . $tourOrder['tourcode'];
                                $tours = Yii::$app->db->createCommand($sql)->queryAll();
                                $tmp['ic'] = $tours[0]['tour_ic'] == 2 ? 1 : 0;
                                $tours = array_unique(array_column($tours, 'scenestate'));
                                $res = array_intersect($tours, $specialState);
                                $state = '';
                                if(!empty($res)){
                                    $state = current($res);
                                }else{
                                    foreach ($tours as $t) {
                                        $state = empty($statesOfPar[$t]) ? '' : $statesOfPar[$t];
                                        if(!empty($state)){
                                            continue;
                                        }
                                    }
                                }
                                $tmp['region'] = empty($states[$state]) ? 'Other' : $states[$state];
                            }
                        }else{
                            $tmp['ic'] = 0;
                            $tmp['region'] = 'Other';
                            $sql = "SELECT paytype FROM ordersum WHERE orderid=".$tmp['orderid'];
                            $otherOrder = Yii::$app->db->createCommand($sql)->queryOne();
                            if(!empty($otherOrder)){
                                $tmp['paytype'] = $otherOrder['paytype'];
                            }else{
                                $tmp['paytype'] = 'Check';
                            }
                        }
                    }

                    $ordersTmpData[$tmp['orderid']][] = $tmp;
                }
            }
            //var_dump($ordersTmpData);die;

            $out = [];
            foreach ($ordersTmpData as $order => $value){
                //if(empty($value[0]['region'])) continue;
                $temp = [];
                foreach($value as $val){
                    $temp['paytype'] = empty($val['paytype'])? 'Check' : $val['paytype'];
                    if($temp['paytype'] == 'Check'){
                        if ($val['method'] == 'confirm_booking') $temp['startTime'] = $val['datetime'];
                    }else{
                        if($val['method'] == 'own') $temp['startTime'] = $val['datetime'];
                    }
                    if ($val['method'] == 'do_send_invoice') $temp['endTime'] = $val['datetime'];
                }

                if(!isset($temp['startTime'])){
                    if($temp['paytype'] == 'Check'){
                        //$str = '{"url":"order/payment_order/id-'.$order.'","c":"order","m":"payment_order","id":"'.$order.'"}';
                        //$time = $this->db->getSingle("SELECT `datetime` FROM admin_logs WHERE `subject` = " .$str);
                        $temp['startTime'] = $sdate;
                    }else{
                        $sql = "SELECT `datetime` FROM c_admin_logs WHERE subject_id = " .$order. " AND operation = 'own'";
                        $time = Yii::$app->db->createCommand($sql)->queryOne();
                        $temp['startTime'] = $time['datetime'];
                    }
                }
                //如果当前时间区间内没有do_send_invoice 不计算该订单的操作时间
                if(!isset($temp['endTime'])){
                    $operateTime = 0;
                }else{
                    $operateTime = $temp['endTime'] - $temp['startTime'];
                }
                $out[$value[0]['region']][$order]['ic'] = $value[0]['ic'];
                if(!empty($operateTime)) $out[$value[0]['region']][$order]['operateTime'] = $operateTime;
            }
            $result = [];
            foreach ($out as $key => $val){
                $result[$key]['date'] = date('Y-m-d', $sdateStr) . '~' . date('Y-m-d', $edateStr);
                $numIc = $opTime = 0;
                $result[$key]['region'] = $key;
                $orderIdStr = '';
                foreach ($val as $order => $v){
                    if($key == 'Other'){
                        $orderIdStr .= $order . '||';
                    }else{
                        if($v['ic'] == 1) $numIc ++;
                    }
                    if($v['ic'] == 1) $numIc ++;
                    if(!empty($v['operateTime'])) $opTime += $v['operateTime'];
                }

                $result[$key]['opTime'] = empty($opTime) ? 0 : $opTime/60;
                $result[$key]['count'] = count($out[$key]);
                $result[$key]['ic'] = ($key == 'Other')? $orderIdStr : $numIc;
            }

            $excel = [];
            foreach ($result as $key => $val){
                if($key == 'Europe') $excel[0] = $val;
                if($key == 'East') $excel[1] = $val;
                if($key == 'West') $excel[2] = $val;
                if($key == 'Hawaii') $excel[3] = $val;
                if($key == 'Canada') $excel[4] = $val;
                if($key == 'ANZ') $excel[5] = $val;
                if($key == 'Other') $excel[6] = $val;
            }
            ksort($excel);

            $datas = array_merge($datas, $excel);
            $sdateStr = $edateStr + 24*3600;
        }while ($sdateStr < strtotime($edate));

        return $datas;
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
        $fileName = $filePath . '/订单操作时间统计数据.xlsx';
        $objWriter->save($fileName);
        Yii::$app->mailer->backup = false;
        Yii::$app->mailer->compose('layout.html', ['content' => '请查看附件'])->setSubject($subject)->setTo([
            'xiaopei.dou@ipptravel.com' => 'Xiaopei'
        ])->attach($fileName)->send();
    }

    /**
     * 按区域统计订单操作时间
     * @author xiaopei dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-05
     * @param $sdate
     * @param $endDate
     * @return array
     */
    public function getOrderOptimeByRegionNew($sdate, $endDate){
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
                $sql1 = "SELECT o.orderid, b.is_ic AS ic, o.sourcefrom, o.paytype FROM ordersum o JOIN booking b ON o.orderid = b.`orderid` JOIN tours t ON t.tourcode = b.tourcode JOIN tour_scene AS ts ON t.tourcode = ts.tourcode JOIN scene AS s ON ts.sceneid = s.sceneid WHERE o.sourcefrom <> 'tour-new' AND s.scenestate IN " .$region. " AND o.`status` > 1 AND o.`status` < 7  AND b.`status` > 1 AND b.`status` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row1 = Yii::$app->db->createCommand($sql1)->queryAll();
                $rows = array_merge($rows,$row1);
                $sql2 = "SELECT o.orderid, b.is_ic AS ic, o.sourcefrom, o.paytype FROM ordersum o JOIN booking b ON o.orderid = b.`orderid` JOIN grouptravel.t_gt_product t ON t.productCode = b.tourcode WHERE (t.channels & 2) > 0 AND t.deleted = 0 AND o.sourcefrom = 'tour-new' AND find_in_set('{$areaNew[$key]}', t.area) AND o.`status` > 1 AND o.`status` < 7 AND b.`status` > 1 AND b.`status` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row2 = Yii::$app->db_slave->createCommand($sql2)->queryAll();
                $rows = array_merge($rows,$row2);

                //自由行
                $sql = "SELECT o.orderid, if(operatorcode=52 or operatorcode=217,1,0) AS ic, o.sourcefrom, o.paytype FROM ordersum o JOIN c_act_orders co ON o.orderid = co.`orderid` JOIN c_acts AS ca ON ca.actid = co.actid JOIN scene AS s ON ca.sceneid = s.sceneid WHERE s.scenestate IN " .$region. " AND o.`status` > 1 AND o.`status` < 7 AND co.`aostatus` > 1 AND co.`aostatus` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row3 = Yii::$app->db->createCommand($sql)->queryAll();
                $rows = array_merge($rows,$row3);
                //标准化包团
                $sql = "SELECT o.orderid, o.sourcefrom, o.paytype FROM ordersum o JOIN order_packagetour op ON o.orderid = op.orderid JOIN packagetour_region pr ON op.packid = pr.packid WHERE pr.pack_regioncode = '{$areaPack[$key]}' AND o.`status` > 1 AND o.`status` < 7 AND op.`postatus` > 1 AND op.`postatus` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row4 = Yii::$app->db->createCommand($sql)->queryAll();
                foreach ($row4 as &$val4){
                    $val4['ic'] = 0;
                }
                $rows = array_merge($rows,$row4);

                //包车
                $sql = "SELECT o.orderid, o.sourcefrom, o.paytype FROM ordersum o JOIN c_car_orders cao ON o.orderid = cao.orderid JOIN Pbus pb ON cao.Pbus_id = pb.Pbus_id JOIN scene s ON pb.sceneid = s.sceneid WHERE s.scenestate IN " .$region. " AND o.`status` > 1 AND o.`status` < 7 AND cao.`costatus` > 1 AND cao.`costatus` < 7 AND o.`order_date` >= '" .$sdate. "' AND o.`order_date` <= '" .$edate. "' GROUP BY o.`orderid`";
                $row5 = Yii::$app->db->createCommand($sql)->queryAll();
                foreach ($row5 as &$val5){
                    $val5['ic'] = 0;
                }
                $rows = array_merge($rows,$row5);

                foreach ($rows as $order){
                    $timeArr = [];
                    if($order['sourcefrom'] == 'tour-new'){
                        $condition = "('own','payment','do_update_invoice')";
                    }else{
                        $condition = "('own','payment','send_invoice','send_invoice_after_send_voucher')";
                    }
                    $sql = "SELECT `operation`,`datetime` FROM c_admin_logs WHERE subject_id = " . $order['orderid'] . " AND operation IN {$condition}";
                    $result = Yii::$app->db->createCommand($sql)->queryAll();
                    $time = 0;
                    if(count($result)>1){
                        foreach ($result as $val){
                            if($order['paytype'] == 'Check'){
                                if($val['operation'] == 'payment') $timeArr['start'] = $val['datetime'];
                            }else{
                                if($val['operation'] == 'own') $timeArr['start'] = $val['datetime'];
                            }
                            if($order['sourcefrom'] == 'tour-new'){
                                if($val['operation'] == 'do_update_invoice') $timeArr['end'] = $val['datetime'];
                            }else{
                                if(in_array($val['operation'], ['send_invoice','send_invoice_after_send_voucher'])) $timeArr['end'] = $val['datetime'];
                            }
                        }
                        if(!empty($timeArr['start']) && !empty($timeArr['end'])){
                            $time = $timeArr['end'] - $timeArr['start'];
                        }
                    }
                    $out[$order['orderid']]['time'] = $time;
                    $out[$order['orderid']]['ic'] = $order['ic'];

                }
                $temp['date'] = $sdate .'~'. $edate;
                $temp['region'] = $key;
                $temp['operation'] = 'Own to Send Invoice';
                $temp['count'] = count($out);
                $icNum = $opTime = 0;
                $orderIds = '';
                foreach ($out as $key => $val){
                    $opTime += $val['time'];
                    if($val['ic'] == 1){
                        $icNum ++;
                    }
                    $orderIds .= $key . '||';
                }
                $temp['opTime'] = $opTime/60;
                $temp['ic'] = $icNum;
                $temp['avOpTime'] = $temp['opTime']/$temp['count'];
                $temp['orderIds'] = $orderIds;
                $data[] = $temp;
            }
            $sdate = date('Y-m-d', strtotime($edate) + 24*3600);
        }while ($sdate<$endDate);

        return $data;
    }

}