<?php

namespace console\controllers;

/**
 * 产品基础数据
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class ProductController extends BaseController
{
    public function actions()
    {
        return [
            'tour_pv'  => [
                'class' => 'console\actions\product\TourPv',
            ],
        ];
    }
} 