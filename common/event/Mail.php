<?php
/**
 * email
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */

namespace common\event;


use yii\base\Component;
use yii\base\Event;
use common\models\EmailLltLog;

class Mail extends Component
{
    public static function handle(Event $event)
    {
        if($event->sender->backup === true) {
            $event->message->bcc = array_merge((array)$event->message->bcc, ['backup@lulutrip.com' => 'Lulutrip Backup']);
        }

        if(YII_ENV != 'prod') {
            //测试记录文本
            $str = '<br /><br />以下是发送邮箱信息数组，测试服务器数据专用:<br / >发送:' . var_export($event->message->to, true) . '<br />cc：' . var_export($event->message->cc, true) . '<br />bcc:' . var_export($event->message->bcc, true);
            if($event->sender->contentType == 'html') {
                $event->message->setHtmlBody($event->sender->contentBody . $str);
            } else if($event->sender->contentType == 'text') {
                $event->message->setTextBody($event->sender->contentBody . $str);
            }
            $event->message->setTo([\Yii::$app->config->testEmail => 'tech_test']);
            $event->message->setCc([]);
            $event->message->setBcc([]);
        }

        return true;
    }

    /**
     * 发送邮件之后 记录日志
     * @author Serena Liu<serena.liu@ipptravel.com>
     * @param Event $event
     * @return bool
     */
    public static function afterEmail(Event $event)
    {
        if($event->sender->recordLog === true){
            //记录日志
            //收件人
            $sendTo = $sendCc = [];
            foreach ($event->message->to as $key => $to) {
                $sendTo[] = $key;
            }
            $sendTo = implode(',', $sendTo);

            //抄送人
            if (!empty($event->message->cc)) {
                foreach ($event->message->cc as $ckey => $cc) {
                    $sendCc[] = $ckey;
                }
            }
            if (!empty($event->message->bcc)) {
                foreach ($event->message->bcc as $bkey => $bcc) {
                    $sendCc[] = $bkey;
                }
            }
            $sendCc = implode(',', $sendCc);

            $controller = \Yii::$app->controller->module->id . '/' . \Yii::$app->controller->id . '/' . \Yii::$app->controller->action->id;
            \Yii::$app->db->beginTransaction();
            $emaillog = new EmailLltLog();
            $emaillog->controller = $controller;
            $emaillog->createTime = date('Y-m-d H:i:s', time());
            $emaillog->sendTo = $sendTo;
            $emaillog->sendCc = $sendCc;
            $emaillog->isSuccessful = ($event->isSuccessful === true) ? 1 : 0;
            $emaillog->save();
            if ($emaillog->errors) {
                \Yii::error($emaillog->errors);
            } else {
                \Yii::$app->db->transaction->commit();
            }
        }
        return true;
    }
}