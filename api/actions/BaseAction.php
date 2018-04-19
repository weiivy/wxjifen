<?php

/**
 * api Action 继承类
 * @copyright (c) 2017, lulutrip.com
 * @author  Martin Ren<martin@lulutrip.com>
 */

namespace api\actions;

use yii\rest\Action;

class BaseAction extends Action
{
    public $modelClass = '';
}