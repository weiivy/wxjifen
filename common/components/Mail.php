<?php
/**
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */

namespace common\components;

use yii\swiftmailer\Mailer;

class Mail extends Mailer
{
    public $contentBody;
    public $contentType;
    public $backup;
    public $recordLog;

    public function compose($view = null, array $params = [])
    {
        $message = $this->createMessage();
        if ($view === null) {
            return $message;
        }

        $this->contentType = 'html';
        if (is_array($view)) {
            if (isset($view['html'])) {
                $this->contentBody = $this->render($view['html'], $params, $this->htmlLayout);
                $this->contentType = 'html';
            }
            if (isset($view['text'])) {
                $this->contentBody = $this->render($view['text'], $params, $this->textLayout);
                $this->contentType = 'text';
            }
        } else {
            $this->contentBody = $this->render($view, $params, $this->htmlLayout);
        }

        return parent::compose($view, $params);
    }
}