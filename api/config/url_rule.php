<?php
/**
 * 路由规则
 * @copyright 2017-07-03
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
return [
    'POST  login'                                                => 'member/login',
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
    'POST pay/notify'                                            => 'notify/notify',
    'POST upgrade'                                               => 'member/upgrade',
    'POST get-money'                                             => 'member/get-money',
    'POST pay-back'                                              => 'site/pay-back',
    'GET  bank-info'                                             => 'site/bank-info',
    'POST  upload-avatar'                                        => 'member/upload-avatar',
    'POST  upload-product'                                       => 'site/upload-product',
    'GET  sign'                                                  => 'wx/get-sign',
    'POST  user-info'                                             => 'wx/user-info',
    'POST  get-auth'                                              => 'wx/get-auth',
    'POST  update-oid'                                              => 'member/sncy-oid',
];
