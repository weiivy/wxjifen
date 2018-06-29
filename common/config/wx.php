<?php
return [
    'developer' => [
        'appId' => 'wxd487a0dc8db164d3',
        'appSecret' => '7ee8df8b07ee2fe28d6098f7c4b3b311',
        'token' => 'jifensuibiandui118',
        'DebugAppId' => 'wx347d53defefdfa69',   // 【微信测试号】AppId
        'Debugappsecret' => '5a3ccea71a88bc2c4d14d0224877b8a2', // 【微信测试号】AppSecret
        'DebugOpenId' => 'oTUF7szKs33JSeT944HrNxCTQb98',    // 【微信测试号】open_id for Laurence
        'Access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token',
        'wx_create_menu_url' => 'https://api.weixin.qq.com/cgi-bin/menu/create',
        'wx_llt_authorize_url' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
//        'wx_url' => 'http://app.lulutrip.com',	//http://app.lulutrip.co
        'wx_id' => 'gh_fda7d13ea2a6',	//gh_ab854f2e4d19
        'encodingAESKey' => 'N5tqIbb8oErw3giNowBgd9XKlvAv15OqVcQG9jag5mX'
    ],
    'smsapi' => [
        'accountSid'    => 'd8d3df5897a3de5df9af68b82c4bedfd',
        'token'         => '98556c519e9ff3591c1f06990f8f123b',
        'appid'         => '549cf781a3034e57986c7463d046fff5',
        'templateid'    => '305130'

    ],

// 微信支付配置
    'wxPayConfig' => [
        'appid' => 'wxd487a0dc8db164d3',
        'appkey' => 'cVurY00eEAQ9Xnt4SYmaNE1jJJdGhDUENL8hg8bTJMVZUPLxBhlLoFrh5PCvqvPPVEgdeXBPwxHHp0aRRIrVlttx5QQTmY572pKGSOR07e9LvWIPblN0oBevCqH3V6r0', // PaySign Key
        'signtype' => 'SHA1',
        'partnerid' => '1235468601', // deprecated: wxpay api upgrade
        'partnerkey' => '3541dfd760c82f709970b18686d7ccb5', // 通加密串 deprecated: wxpay api upgrade
        'mch_id' => '1507282181',
        'key' => 'ttuhSwD8Ocd96x77fC7Bc3nerWXoVRzt'
    ],

];