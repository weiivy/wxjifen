<?php
namespace console\actions\comparePrice;
use console\models\admin\compare\WxComparison;
use console\models\admin\compare\WxProduct;
use Yii;
use yii\base\Action;
use console\models\admin\compare\WxGroup;
use console\models\admin\compare\WxAdmin;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * @copyright (c) 2017, lulutrip.com
 * @author Justin Jia<justin.jia@ipptravel.com>
 */
class Email extends Action
{
    public function run($testEmail = '') {
        //生成excel
        $groupIds = $this->createExcel();
        Yii::$app->db_wxadmin->close();
        Yii::$app->db_wxadmin->open();
//            $cp = WxComparison::findOne(['id' => $key]);
//            $self = WxProduct::find()->where('id = :id', ['id' => $cp['product_id']])->asArray()->one();
        $sql = "SELECT * FROM `wx_group` wxg LEFT JOIN wx_admin wxa ON(wxg.group_id = wxa.group_id) WHERE wxg.group_id IN (" . implode(', ', $groupIds) . ")";
        $data = Yii::$app->db_wxadmin->createCommand($sql)->queryAll();
        $groups = [];
        foreach($data as $val){
            $groups[$val['group_id']]['email'] = $val['mail'];
            $groups[$val['group_id']]['child'][] = trim($val['username']);
        }
        foreach ($groups as $groupId => $group) {
            $date = date("Ymd", strtotime('-4 days'));
            $file = Yii::$app->runtimePath . '/files/compare/' . date("Ym") . '/' . $date . '-' . $groupId . '.xlsx';
            if($testEmail){
                $this->sendEmail([$testEmail], $file, $date . '-' . $groupId, 1);
            }else{
                $emails = array_filter(array_unique(array_values(array_merge([$group['email']], $group['child']))));
                $this->sendEmail($emails, $file, $date . '-' . $groupId);
            }
        }
    }

    /**
     * 发送邮件
     * @copyright 2017-10-24
     * @author Justin Jia<justin.jia@ipptravel.com>
     * @param $email
     * @param $fileName
     * @param $date
     * @return bool
     */
    public function sendEmail($email, $fileName, $date, $isTest = 0) {
        $admin_subject = 'admin_compareTool';
        $sign = md5(json_encode($email) . time());
        $data['logoPath'] = 'http://sdc.lulutrip.com/crm/logo?email=' . json_encode($email) . '&subject=' . $admin_subject . '&sign=' . $sign;
        Yii::$app->helper->crmSent($email, $admin_subject, $sign);
        $subject = '【比价工具Price Tool】' . $date . '产品比价变化记录';
        Yii::$app->controller->layout = false;
        Yii::$app->controller->emailTitle = $subject;
        $body = $this->controller->render('@console/views/compare/compare.html', $data);

        Yii::$app->mailer->backup = false;
        $mail= Yii::$app->mailer->compose('@common/mail/layout.html', ['content' => $body]);
        $mail->setTo($email);
        $isTest == 0 && $mail->setCc(['xiping.yi@ipptravel.com', 'serena.liu@ipptravel.com']);
        $mail->setSubject($subject);
        $mail->attach($fileName);
        $mail->send();
    }
    /**
     * 生成 EXCEL 报表
     * @author Serena Liu<serena.liu@ipptravel.com>
     * @copyright 2017-12-04
     */
    private function createExcel(){
        Yii::$app->db_wxadmin->close();
        Yii::$app->db_wxadmin->open();
//            $cp = WxComparison::findOne(['id' => $key]);
//            $self = WxProduct::find()->where('id = :id', ['id' => $cp['product_id']])->asArray()->one();
        $sql = "SELECT * FROM `wx_comparison` wc LEFT JOIN wx_product wp ON(wc.product_id = wp.id) WHERE wc.`status` = 1 and wc.cp_price != wc.cp_price_new AND wc.cp_price_new != 0 AND wc.cp_price != 0 AND wp.price != 0";
        $cp = Yii::$app->db_wxadmin->createCommand($sql)->queryAll();
        $groups = [];
        foreach ($cp as $products){
            $groups[$products['group_id']][] = $products;
        }
        foreach($groups as $groupId => $products){
            $this->createExcelByGroupId($products, $groupId);
        }
        return array_keys($groups);
    }
    /**
     * 生成 EXCEL 报表
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-11-24
     */
    private function createExcelByGroupId($products, $groupId) {
        $subject = '分组 ' .$groupId . ' 产品比价变化记录';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Xiaopei Dou")
            ->setLastModifiedBy("Xiaopei Dou")
            ->setTitle($subject)
            ->setSubject($subject);

        //设置当前活动的sheet
        $objPHPExcel->setActiveSheetIndex(0);

        //设置sheet名字
        $objPHPExcel->getActiveSheet()->setTitle($subject);

        // Add some data
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '产品编号Product code');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '产品名称Product name');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '产品语言Language');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '产品类型Category');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '竞争对手网站Competitors');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '我方当前价格Our Price');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '对方价格Competitors Price');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '对方上次价格Last time price');

        $num = 0;
        foreach ($products as $key => $val) {
            $num++;
            $excel = array(
                'tourcode'      => $val['tourcode'],
                'tourtitle'     => $val['tourtitle'],
                'lang'          => WxProduct::$platform[$val['platform']]['lang'],
                'type'          => WxProduct::$type[$val['type']],
                'cp_platform'   => $val['cp_platform'],
                'cur'           => WxProduct::$platform[$val['platform']]['cur'],
                'price'         => $val['price'],
                'cp_price_new'      => $val['cp_price_new'],
                'cp_price'     => $val['cp_price'],
            );

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $num + 1, $excel['tourcode']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $num + 1, $excel['tourtitle']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $num + 1, $excel['lang']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $num + 1, $excel['type']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $num + 1, WxComparison::$cp_platform[$excel['cp_platform']]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $num + 1, $excel['cur'].$excel['price']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $num + 1, $excel['cur'].$excel['cp_price_new']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $num + 1, $excel['cur'].$excel['cp_price']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $this->SaveViaTempFile($objWriter, $groupId);
        return true;
    }

    /**
     * 导出文件并写日志
     * @author Xiaopei Dou<xiaopei.dou@ipptravel.com>
     * @copyright 2017-11-24
     */
    private function SaveViaTempFile($objWriter, $groupId){
        //导出文件
        $filePath = Yii::$app->runtimePath . '/files/compare/' . date("Ym");
        @mkdir($filePath, 0777, true);
        $fileName = $filePath . '/' . date("Ymd", strtotime('-4 days')) . '-' . $groupId . '.xlsx';
        $objWriter->save($fileName);
    }
}