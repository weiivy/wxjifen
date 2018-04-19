<?php

namespace console\actions\reportSystem;

use yii\base\Action;
use Yii;
use common\library\base\Data;

/**
 * 订单关联表
 * @package console\actions\financialReport
 * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
 * @copyright (c) 2018, lulutrip.com
 */
class OrderRelated extends Action {
    /**
     * 订单关联表
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-10
     */
    public $fields = ['orderid','suborderid','status','region','product_type'];
    public $pageSize = 500;
    public $maxOrderid;
    public $types = [
        'tour-new' => 'tour',
        'tour' => 'tour',
        'activity' => 'activity',
        'ddss' => 'ddss',
        'car' => 'car',
        'rentCar' => 'rent-car',
        'addfee' => 'addfee',
        'packagetour' => 'packagetour',
        'cruise' => 'cruise',
        'privateTour' => 'private-tour',
        'privateTourAddfee' => 'private-tour-addfee',
    ];
    public function run($type = '',$page = '') {
        set_time_limit(0);
        //先更新子订单状态
        $this->updateStatus();
        $typeArr = ['tour-new', 'tour', 'activity', 'packagetour', 'car', 'ddss', 'rentCar', 'addfee', 'cruise', 'privateTour', 'privateTourAddfee'];
        if(in_array($type, $typeArr)){
            $this->insertType($type,$page);
        }elseif(empty($type) || $type == 'all'){
            foreach ($typeArr as $val){
                $this->insertType($val,$page);
            }
        }
        else{
            echo '请输入正确的类型';
            return;
        }
    }

    private function insertType($type,$page){
        try{
            $sql = "SELECT MAX(orderid) as orderid FROM order_correlation WHERE product_type = '" . $this->types[$type] . "'";
            $row = Yii::$app->db->createCommand($sql)->queryOne();
            $maxOrderid = empty($row['orderid'])? 0 : $row['orderid'];
            $sql = "SELECT COUNT(orderid) AS total FROM ordersum WHERE order_date >='2015-01-01' AND orderid > {$maxOrderid}";
            $rows = Yii::$app->db->createCommand($sql)->queryAll();
            $total = $rows[0]['total'];
            $pages = ceil($total / $this->pageSize);
            for($i = 1; $i <= $pages; $i++) {
                if(!empty($page) && ($i > $page)) break;
                //新订单
                $condition = ' LIMIT ' . ($i - 1) * $this->pageSize . ', ' . $this->pageSize;
                $sql = "SELECT orderid FROM ordersum WHERE order_date >='2015-01-01' AND orderid > {$maxOrderid} ORDER BY orderid" . $condition;
                $orderids = Yii::$app->db->createCommand($sql)->queryAll();
                if(empty($orderids)) break;
                $orderids = implode(",", array_column($orderids, 'orderid'));
                if($type == 'tour-new') $this->tourNew($orderids);
                if($type == 'tour') $this->tour($orderids);
                if($type == 'activity') $this->activity($orderids);
                if($type == 'packagetour') $this->packagetour($orderids);
                if($type == 'car') $this->car($orderids);
                if($type == 'ddss') $this->ddss($orderids);
                if($type == 'rentCar') $this->rentCar($orderids);
                if($type == 'addfee') $this->addfee($orderids);
                if($type == 'cruise') $this->cruise($orderids);
                if($type == 'privateTour') $this->privateTour($orderids);
                if($type == 'privateTourAddfee') $this->privateTourAddfee($orderids);
                //if($type == 'add') $this->addProductType();
            }
        }catch (\Exception $e){
            $data = ['code'=>$e->getCode(), 'message' => $e->getMessage()];
            var_dump($data);
            Yii::error(json_encode($data), __METHOD__);
            return $data;
        }
    }

    /**
     * 插入新数据
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    private function insert($data,$type){
        $scenesArr = Data::getScenes();
        $sceneIds = $insertArr = [];
        if($type == 'tour-new'){
            $insertArr = $data;
        }
        if(in_array($type, ['tour','packagetour'])){
            foreach ($data as $row){
                $key = $row['orderid'] . '-' . $row['suborderid'];
                $sceneIds[$key][] = $row['sceneid'];
                $insertArr[$key] = $row;
            }
            foreach ($sceneIds as $subkey => $sceneids){
                $state = $regionRoot = [];
                $sceneids = array_unique($sceneids);
                foreach ($sceneids as $sceneid){
                    $state[] = $scenesArr['sceneIdState'][$sceneid];
                    $regionRoot[] = Yii::$app->helper->getRegRootBySceneId($sceneid);
                }
                unset($insertArr[$subkey]['sceneid']);
                $insertArr[$subkey]['region'] = implode(",", $sceneids) .','. implode(",", array_unique(array_merge($state, $regionRoot)));
                $insertArr[$subkey]['product_type'] = $type;
            }
        }
        if(in_array($type, ['activity','car'])){
            foreach ($data as &$value){
                $state = $scenesArr['sceneIdState'][$value['sceneid']];
                $regionRoot = Yii::$app->helper->getRegRootBySceneId($value['sceneid']);
                $value['region'] = $value['sceneid'] . ',' . $state . ',' . $regionRoot;
                $value['product_type'] = $type;
                unset($value['sceneid']);
            }
            $insertArr = $data;
        }
        if(in_array($type, ['ddss','rent-car','addfee','cruise','private-tour','private-tour-addfee'])){
            foreach ($data as &$val){
                $val['region'] = '';
                $val['product_type'] = $type;
            }
            $insertArr = $data;
        }

        if(!empty($insertArr)){
            Yii::$app->db->createCommand()->batchInsert('order_correlation', $this->fields, $insertArr)->execute();
        }

    }

    /**
     * 更新子订单状态
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-13
     */
    private function updateStatus(){
        $tables = [
            'booking' => ['bookingconf','status','tour'],
            'c_act_orders' => ['aoid','aostatus','activity'],
            'order_packagetour' => ['poid','postatus','packagetour'],
            'c_car_orders' => ['coid','costatus','car'],
            'c_ddss_orders' => ['doid','status','ddss'],
            'order_rentcar' => ['or_id','status','rent-car'],
            'orderitems' => ['itemconf','itemstatus','addfee'],
            'c_tourico_cruise_order' => ['ctco_pid','ctco_status','cruise'],
            'PTproposal' => ['PTproposal_index','PTstatus','private-tour'],
            'PTinvoice_addfee' => ['ptaf_conf','ptaf_status','private-tour-addfee'],
        ];

        foreach ($tables as $table => $fields){
            $sql = "SELECT COUNT(*) AS total FROM order_correlation WHERE product_type = '{$fields[2]}'";
            $rows = Yii::$app->db->createCommand($sql)->queryOne();
            if($rows['total'] == 0) continue;
            if($table == 'PTproposal'){
                $sql = "UPDATE order_correlation oc INNER JOIN {$table} b ON oc.suborderid = b.{$fields[0]} INNER JOIN PTinvoice pt ON b.PTproposal_index = pt.PTproposal_index SET oc.`status` = (CASE
                WHEN b.`PTstatus` = 'C' THEN 1
                WHEN b.`PTstatus` = 'D' THEN 2
                WHEN pt.`PTinvoice_status` = 'M' THEN 3
                WHEN pt.`PTinvoice_status` = 'C' THEN 4
                WHEN pt.`PTinvoice_status`= 'Y' AND b.PTpaxlist = '' THEN 6
                WHEN pt.`PTinvoice_status` = 'Y' AND b.PTpaxlist <> '' THEN 7
                WHEN pt.`PTinvoice_status` = 'I' THEN 8
                WHEN pt.`PTinvoice_status` = 'V' THEN 9
                ELSE 0
                END) WHERE oc.product_type = '{$fields[2]}'";
            }else{
                $sql = "UPDATE order_correlation oc INNER JOIN {$table} b ON oc.suborderid = b.{$fields[0]} SET oc.`status` = b.`{$fields[1]}` WHERE oc.product_type = '{$fields[2]}'";
            }
            Yii::$app->db->createCommand($sql)->execute();
        }

    }


    /**
     * 新版旅行团
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function tourNew($orderids){
        //新版旅行团
        if(!empty($orderids)){
            $insertTourNew = [];
            $sql = "SELECT o.`orderid`,b.`bookingconf` AS suborderid,b.`status`,gt.`area` FROM ordersum o JOIN booking b USING(orderid) JOIN grouptravel.t_gt_product gt ON b.`tourcode` = gt.`productCode` WHERE o.orderid in ({$orderids}) AND o.`sourcefrom` = 'tour-new' AND b.`orderid` IS NOT NULL AND (gt.channels & 2) > 0 AND gt.deleted = 0 GROUP BY suborderid";
            $tourNews = Yii::$app->db_slave->createCommand($sql)->queryAll();
            foreach ($tourNews as $tourNew){
                $temp = [];
                $temp['orderid'] = $tourNew['orderid'];
                $temp['suborderid'] = $tourNew['suborderid'];
                $temp['status'] = $tourNew['status'];
                $region = $tourNew['area'];
                $areaArr = explode(",", $tourNew['area']);
                //c_states 表没有 北美
                if(in_array('POI_30096', $areaArr)) $region = $region . ',NA';
                $areaStr = "'" . implode("','", $areaArr) . "'";
                $sqli = "SELECT cs.state_code FROM basedata.t_wq_poi_base bt JOIN c_states cs ON bt.cn_name = cs.state_name_cn WHERE bt.`meta_type` = 'POI' AND bt.code in ({$areaStr})";
                $areaRow = Yii::$app->db->createCommand($sqli)->queryAll();
                $region = $region . ',' .implode(",", array_column($areaRow, 'state_code'));
                $temp['region'] = $region;
                $temp['product_type'] = 'tour';
                $insertTourNew[] = $temp;
            }
            if(!empty($insertTourNew)) $this->insert($insertTourNew, 'tour-new');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT o.orderid,b.bookingconf AS suborderid,b.`status` FROM ordersum o JOIN booking b USING(orderid) WHERE o.`sourcefrom` = 'tour-new' AND orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 老版旅行团
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function tour($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`,b.`bookingconf` AS suborderid,b.`status`,ts.sceneid FROM ordersum o LEFT JOIN booking b ON o.`orderid` = b.`orderid` LEFT JOIN tour_scene ts ON b.tourcode = ts.`tourcode` WHERE o.orderid in ({$orderids}) AND o.`sourcefrom` <> 'tour-new' AND b.`orderid` IS NOT NULL";
            $rows = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($rows)) $this->insert($rows, 'tour');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT o.orderid,b.bookingconf AS suborderid,b.`status` FROM ordersum o JOIN booking b USING(orderid) WHERE o.`sourcefrom` <> 'tour-new' AND orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 当地玩乐
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function activity($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`, cao.aoid AS suborderid, cao.`aostatus` AS `status`,ca.`sceneid` FROM ordersum o LEFT JOIN c_act_orders cao ON o.`orderid` = cao.`orderid` LEFT JOIN c_acts ca ON cao.`actid` = ca.`actid` WHERE o.orderid in ({$orderids}) AND cao.`orderid` IS NOT NULL";
            $insertAct = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertAct)) $this->insert($insertAct, 'activity');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,aoid AS suborderid,`aostatus` AS `status` FROM c_act_orders WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 标准化包团
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function packagetour($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`, op.poid AS suborderid, op.`postatus` AS `status`,ps.`sceneid` FROM ordersum o LEFT JOIN order_packagetour op ON o.`orderid` = op.`orderid` LEFT JOIN packagetours pa ON op.`packid` = pa.`packid` LEFT JOIN packagetour_scene ps on pa.`packid` = ps.`packid` WHERE o.orderid IN ({$orderids}) AND op.`orderid` IS NOT NULL";
            $rows  = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($rows)) $this->insert($rows, 'packagetour');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,poid AS suborderid,`postatus` AS `status` FROM order_packagetour WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 包车
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function car($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`, co.coid AS suborderid, co.`costatus` AS `status`,pb.`sceneid` FROM ordersum o LEFT JOIN c_car_orders co ON o.`orderid` = co.`orderid` LEFT JOIN Pbus pb ON co.`Pbus_id` = pb.`Pbus_id` WHERE o.orderid IN ({$orderids}) AND co.`orderid` IS NOT NULL";
            $insertCar  = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertCar)) $this->insert($insertCar, 'car');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,coid AS suborderid,`costatus` AS `status` FROM c_car_orders WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * ddss
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function ddss($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`,co.`doid` AS suborderid,co.status FROM ordersum o LEFT JOIN c_ddss_orders co USING(orderid) WHERE o.orderid IN ({$orderids}) AND co.`orderid` IS NOT NULL";
            $insertDdss = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertDdss)) $this->insert($insertDdss,'ddss');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,doid AS suborderid,`status` FROM c_ddss_orders WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 租车
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function rentCar($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`,co.`or_id` AS suborderid,co.status FROM ordersum o LEFT JOIN order_rentcar co USING(orderid) WHERE o.orderid IN ({$orderids}) AND co.`orderid` IS NOT NULL";
            $insertrentCar = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertrentCar)) $this->insert($insertrentCar,'rent-car');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,or_id AS suborderid,`status` FROM order_rentcar WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 普通额外支付
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function addfee($orderids){
        if(!empty($orderids)){
            $sql = "SELECT co.`itemref` AS orderid,co.`itemconf` AS suborderid,co.`itemstatus` AS `status` FROM ordersum o LEFT JOIN orderitems co USING(orderid) WHERE co.itemref IN ({$orderids}) AND co.itemref <> co.orderid AND co.`orderid` IS NOT NULL";
            $insertAddfee = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertAddfee)) $this->insert($insertAddfee,'addfee');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,itemconf AS suborderid,`itemstatus` AS `status` FROM orderitems WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 邮轮
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function cruise($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`,co.`ctco_pid` AS suborderid,co.`ctco_status` AS `status` FROM ordersum o LEFT JOIN c_tourico_cruise_order co USING(orderid) WHERE o.orderid IN ({$orderids}) AND co.`orderid` IS NOT NULL";
            $insertCruise = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertCruise)) $this->insert($insertCruise,'cruise');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,ctco_pid AS suborderid,`ctco_status` AS `status` FROM c_tourico_cruise_order WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 个性化定制
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function privateTour($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`,co.`PTproposal_index` AS suborderid, CASE
                WHEN co.`PTstatus` = 'C' THEN 1
                WHEN co.`PTstatus` = 'D' THEN 2
                WHEN pt.`PTinvoice_status` = 'M' THEN 3
                WHEN pt.`PTinvoice_status` = 'C' THEN 4
                WHEN pt.`PTinvoice_status`= 'Y' AND co.PTpaxlist = '' THEN 6
                WHEN pt.`PTinvoice_status` = 'Y' AND co.PTpaxlist <> '' THEN 7
                WHEN pt.`PTinvoice_status` = 'I' THEN 8
                WHEN pt.`PTinvoice_status` = 'V' THEN 9
                ELSE 0 END AS `status` FROM ordersum o LEFT JOIN PTproposal co USING(orderid) LEFT JOIN PTinvoice pt ON co.`PTproposal_index` = pt.`PTproposal_index` WHERE o.orderid IN ({$orderids}) AND co.`orderid` IS NOT NULL AND co.`orderid` <> 0 GROUP BY co.`PTproposal_index`";
            $insertPrivateTour = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertPrivateTour)) $this->insert($insertPrivateTour,'private-tour');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,PTproposal_index AS suborderid,`PTstatus` AS `status` FROM PTproposal WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 个性化定制额外支付
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function privateTourAddfee($orderids){
        if(!empty($orderids)){
            $sql = "SELECT o.`orderid`,co.`ptaf_conf` AS suborderid,co.`ptaf_status` AS `status` FROM ordersum o LEFT JOIN PTinvoice_addfee co USING(orderid) WHERE o.orderid IN ({$orderids}) AND co.`orderid` IS NOT NULL AND co.`orderid` <> 0";
            $insertPrivateTourAddfee = Yii::$app->db->createCommand($sql)->queryAll();
            if(!empty($insertPrivateTourAddfee)) $this->insert($insertPrivateTourAddfee,'private-tour-addfee');
        }

//        //修改老订单状态
//        if(!empty($oldOrderids)){
//            $sql = "SELECT orderid,ptaf_conf AS suborderid,`ptaf_status` AS `status` FROM PTinvoice_addfee WHERE orderid IN ({$oldOrderids})";
//            $statusArr = Yii::$app->db->createCommand($sql)->queryAll();
//            if(!empty($statusArr)) $this->updateStatus($statusArr);
//        }
    }

    /**
     * 填充ordersum表product_type字段值
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2018-01-11
     */
    public function addProductType(){
        //product_type已有值的，不处理
        $sql = "SELECT orderid,product_type FROM order_correlation oc JOIN ordersum o ON oc.orderid = o.orderid WHERE o.product_type = ''";
        $row = Yii::$app->db->createCommand($sql)->queryAll();
        $total = $row[0]['total'];
        $pages = ceil($total / $this->_pageSize);
        for($i = 1; $i <= $pages; $i++){
            $condition = ' LIMIT ' . ($i - 1) * $this->pageSize . ', ' . $this->pageSize;
            $sql = "SELECT orderid,product_type FROM order_correlation oc JOIN ordersum o ON oc.orderid = o.orderid WHERE o.product_type = ''" .$condition;
            $rows = Yii::$app->db->createCommand($sql)->queryAll();
            $data = [];
            foreach ((array)$rows as $value){
                $data[$value['orderid']]['product_type'][] = $value['product_type'];
            }
            foreach ((array)$data as $order => $val){
                $productType = implode(",", array_unique($val['product_type']));
                if(!empty($productType)){
                    $sql = "update ordersum set `product_type` = '{$productType}' WHERE orderid = {$order}";
                    Yii::$app->db->createCommand($sql)->execute();
                }
            }
        }
    }
}