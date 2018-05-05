<?php
return [
    'developer' => [
        'appId' => 'wx347d53defefdfa69',
        'appSecret' => '5a3ccea71a88bc2c4d14d0224877b8a2',
        'token' => 'jifen',
        'DebugAppId' => 'wx347d53defefdfa69',   // 【微信测试号】AppId
        'Debugappsecret' => '5a3ccea71a88bc2c4d14d0224877b8a2', // 【微信测试号】AppSecret
        'DebugOpenId' => 'oTUF7szKs33JSeT944HrNxCTQb98',    // 【微信测试号】open_id for Laurence
        'Access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token',
        'wx_create_menu_url' => 'https://api.weixin.qq.com/cgi-bin/menu/create',
        'wx_llt_authorize_url' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
//        'wx_url' => 'http://app.lulutrip.com',	//http://app.lulutrip.co
        'wx_id' => 'gh_d4164a338dae',	//gh_ab854f2e4d19
    ],
    'smsapi' => [
        'accountSid'    => '6b9b9217c9cf254c39d906d919c36167',
        'token'         => 'e9f1a56ceb56e6e0a3c3014037642356',
        'appid'         => '7e758527a6024d22b9ba53642c185f8e',
        'templateid'    => '304220'

    ],

// 微信支付配置
    'wxPayConfig' => [
        'appid' => 'wx347d53defefdfa69',
        'appkey' => 'cVurY00eEAQ9Xnt4SYmaNE1jJJdGhDUENL8hg8bTJMVZUPLxBhlLoFrh5PCvqvPPVEgdeXBPwxHHp0aRRIrVlttx5QQTmY572pKGSOR07e9LvWIPblN0oBevCqH3V6r0', // PaySign Key
        'signtype' => 'SHA1',
        'partnerid' => '1235468601', // deprecated: wxpay api upgrade
        'partnerkey' => '3541dfd760c82f709970b18686d7ccb5', // 通加密串 deprecated: wxpay api upgrade
        'mch_id' => '1501565661',
        'key' => 'mVuhSwD8Ocd96xYHfC7Bc3nerWXoVRzt'
    ],

];