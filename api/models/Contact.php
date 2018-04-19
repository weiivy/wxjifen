<?php
/**
 * Created by PhpStorm.
 * User: zhangweiwei
 * Date: 18/4/9
 * Time: 上午7:19
 */

namespace api\models;

class Contact extends \common\models\Contact
{
    public function rules()
    {
        return [
            [['sex', 'created_at', 'updated_at'], 'integer'],
            [['openid'], 'string', 'max' => 50],
            [['nickname'], 'string', 'max' => 500],
            [['head_image'], 'string', 'max' => 255],
        ];
    }
} 