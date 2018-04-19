<?php

namespace api\library\capitalDetails;


use api\models\CapitalDetails;
use yii\base\Component;
use yii\data\Pagination;

/**
 * 资金明细服务类
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class CapitalDetailsService extends Component
{
    /**
     * 获取资金明细
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $page
     * @param $pageSize
     * @param $type
     * @param $memberId
     * @return array
     */
    public static function getList($page, $pageSize, $type, $memberId)
    {
        $list = CapitalDetails::find();
        $list->where(['member_id'=> $memberId]);
        if(!empty($type)) {
            $list->andWhere(['type' => $type]);
        }
        $list->orderBy('id DESC');
        $pages = new Pagination(['totalCount' =>$list->count(), 'pageSize' => $pageSize]);
        $pages->setPage($page-1);
        $list = $list->offset($pages->offset)->limit($pages->pageSize)->asArray()->all();

        foreach($list as $key => &$value) {
            $value['kind'] = CapitalDetails::kindAlisa($value['kind']);
            $value['created_at'] = date('Y-m-d H:i:s', $value['created_at']);
        }
        return ['list' => $list, 'count' => $pages->totalCount];
    }
}