<?php
/**
 * 脚本 监控
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
namespace console\actions\index;

use yii\base\Action;
use Yii;

class Monitor extends Action
{
    public $rLogDir = '';

    public function run()
    {
        $this->rLogDir = Yii::$app->runtimePath . '/logs/base/';
        $warningArr = [];
        //脚本监控配置
        $logTime = include_once(Yii::$app->basePath . '/config/cronjob.php');
        foreach($logTime as $val)
        {
            $msg = $this->getWarning($val);
            if(!empty($msg)){
                $subject = 'URGENT！' . $val['logName'] . '.php脚本 被锁，无法执行！';
                Yii::$app->smsApi->sendSMS('15021720695', $subject);

                Yii::$app->mailer->backup = false;
                $mail= Yii::$app->mailer->compose('@common/mail/layout.html', ['content' => $subject]);
                $mail->setTo('serena.liu@ipptravel.com');
                $mail->setSubject($subject);
                $mail->send();
            }
        }
        die ("OK");
    }
    /**
     * 判断锁日志生成时间
     * @author Serena Liu<serena.liu@ipptravel.com>
     * @copyright 2017-10-11
     * @param array $logTime logName 日志(或锁日志)名称(即脚本名称), interTime 间隔时间(以分钟为单位)
     * @return null|string
     */
    private function getWarning($logTime = array('logName' => '', 'interTime' => ''))
    {
        $logFile = $this->rLogDir . $logTime['logName'] . '.log';
        if(!is_file($logFile)){
            return null;
        }
        $logStr = file_get_contents($logFile);
        //获取日志里面的第一行时间
        $time = strtotime(explode("\r\n", $logStr)[0]);

        //根据美国国会最新通过的能源法案，为加强日光节约，自2007年起延长夏令时间，从每年3月的第二个星期日开始，至每年11月的第一个星期日结束
        //冬令时是在冬天使用的标准时间。在使用日光节约时制的地区，夏天时钟拨快一小时，冬天再拨回来。这时采用的是标准时间，也就是冬令时。
        $interTime = self::canclChaZhi($logTime['interTime'], $time);
        $chazhi = floor((time() - $time) / 60) - $interTime;

        if($chazhi > 0)
        {
            return 'scripts no run：' . $chazhi . ' mins "' . $logTime['logName'] . '"';
        }

        if (preg_match('/Uncaught exception/', $logStr)) {
            return "\"{$logTime['logName']}\" script error: {$logStr}";
        }
    }

    /**
     * 计算夏令时差值
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2016-11-11
     * @param $interTime 执行时间
     * @param $time  文件生成时间
     *
     * @return float
     */
    public static function canclChaZhi($interTime, $time)
    {
        $chazhi = 0;
        //获取3月第二个礼拜日夏令时
        $march = date("Y-m", strtotime(date('Y') . "-03"));
        $marchSundays = self::getAllSundayOf($march, 0);
        $startTime =  $march . "-" . $marchSundays[1] . " 02:00";

        //11月的第一个星期日冬令时
        $november = date("Y-m", strtotime(date('Y') . "-11"));
        $marchSundays = self::getAllSundayOf($november, 0);
        $endTime =  $november . "-" . $marchSundays[0] . " 02:00";

        //从每年3月的第二个星期日开始，至每年11月的第一个星期日结束
        //文件生成时间在夏令时之前的，时间减1小时；和文件生成时间在冬令时之前的，，时间加1小时
        if(date('Y-m-d H:i:s') >= $startTime && date('Y-m-d H:i:s') < $endTime && date('Y-m-d H:i:s', $time) < $startTime && $interTime > 60) {
            $chazhi     = $interTime - 60;
        } elseif(date('Y-m-d H:i:s') >= $endTime && date('Y-m-d H:i:s', $time) < $endTime && $interTime > 60) {
            $chazhi     = $interTime + 60;
        } else {
            $chazhi     = $interTime;
        }
        return $chazhi;

    }

    /**
     * 根据日期获取当月对用礼拜日期
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2016-11-10
     * @param string $yearMonth  日期 2016-11
     * @param $weekDay  对应礼拜几值  0对应礼拜日
     *
     * @return array 返回date值
     */
    private static function getAllSundayOf($yearMonth = '', $weekDay){
        if(empty($year_month)){
            $yearMonth = date("Y-m");
        }
        $maxDay  = date('t', strtotime($yearMonth . "-01"));
        $mondays = array();
        for($i = 1; $i <= $maxDay; $i++){
            if(date('w', strtotime($yearMonth . "-" . $i)) == $weekDay){
                $mondays[] =($i > 9 ?'' : '0') .$i;
            }
        }
        return $mondays;
    }
}