<?php
/**
 * @copyright (c) 2017, lulutrip.com
 * @author  Serena Liu<serena.liu@ipptravel.com>
 */

namespace common\components;

use yii\log\EmailTarget;
use yii;

class EmailTargets extends EmailTarget
{
    public function export()
    {
        if (empty($this->message['subject'])) {
            $this->message['subject'] = 'Application Log';
        }
        $messages = array_map([$this, 'formatMessage'], $this->messages);
        $body = wordwrap(implode("\n", $messages), 70);

        $this->mailer->backup = false;
        $this->mailer->recordLog = false;

        $message = $this->mailer->compose();
        Yii::configure($message, $this->message);
        $message->setTextBody($body)->send($this->mailer);

        return $message;
    }
}