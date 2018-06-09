<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\library\capitalDetails\CapitalDetailsService;
use api\library\member\MemberService;
use Yii;

/**
 * 交易明细
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetCapitalDetail extends BaseAction
{
    public function run()
    {
        $type = [
            10 => '+',
            20 => '-'
        ];
        $page = Yii::$app->request->post('page');
        $page = $page > 0 ? $page : 1;
        $pageSize = Yii::$app->request->post('pageSize', 10);
        $kind = Yii::$app->request->post('type');
        if($kind && !in_array($kind, array_keys($type))) {
            return [
                'status' => 200,
                'data'   => []
            ];
        }
        $memberId = $this->memberId;
        return [
            'status' => 200,
            'data'   => CapitalDetailsService::getList($page, $pageSize, $type[$kind], $memberId)
        ];
    }
}