<?php
/**
 * 竞争对手取价记录
 * @copyright (c) 2017, lulutrip.com
 * @author Justin Jia<justin.jia@ipptravel.com>
 */

namespace console\models\admin\compare;


class WxComparison extends \common\models\admin\compare\WxComparison
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWxProduct() {
        return $this->hasOne(WxProduct::className(), ['id' => 'product_id']);
    }
}