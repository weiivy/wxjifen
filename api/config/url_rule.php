<?php
/**
 * 路由规则
 * @copyright 2017-07-03
 * @author Serena Liu<serena.liu@ipptravel.com>
 */

return [
    'POST  login'                                                => 'site/wxlogin',
    'POST sendSms'                                               => 'site/sendSms',
    'POST check-member'                                          => 'site/check-member',
    'POST get-mobile'                                            => 'site/get-mobile',
    'POST register'                                              => 'member/register',
    'POST get-capital-detail'                                    => 'member/get-capital-detail',
    'POST get-orders'                                            => 'member/get-orders',
    'POST bank-config'                                           => 'site/bank-config',
    'POST member'                                                => 'member/member',
    'POST friends'                                               => 'member/friends',
    'POST add-order'                                             => 'record/add-order',
    'GET index'                                                  => 'site/index'
];
