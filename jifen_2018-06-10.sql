# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.7.19)
# Database: jifen
# Generation Time: 2018-06-10 02:33:16 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table pay_back_result
# ------------------------------------------------------------

CREATE TABLE `pay_back_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mch_id` varchar(100) DEFAULT '' COMMENT '微信支付商户号',
  `mch_appid` varchar(100) DEFAULT '' COMMENT '公众账号ID',
  `partner_trade_no` varchar(100) DEFAULT '' COMMENT '商户订单号',
  `payment_no` varchar(255) NOT NULL DEFAULT '' COMMENT '微信订单号',
  `payment_time` varchar(50) NOT NULL DEFAULT '0' COMMENT '企业付款成功时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '录入时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_wxpay
# ------------------------------------------------------------

CREATE TABLE `pay_wxpay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mch_id` varchar(50) NOT NULL DEFAULT '' COMMENT '微信支付商户号',
  `appid` varchar(100) NOT NULL DEFAULT '' COMMENT '公众号APPID',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT 'api key',
  `status` int(11) NOT NULL DEFAULT '2' COMMENT '状态',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '录入时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_wxpay_result
# ------------------------------------------------------------

CREATE TABLE `pay_wxpay_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mch_id` varchar(100) DEFAULT '' COMMENT '微信支付商户号',
  `appid` varchar(100) DEFAULT '' COMMENT '公众账号ID',
  `out_trade_no` varchar(100) DEFAULT '' COMMENT '订单号',
  `openid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户唯一标识',
  `transaction_id` varchar(255) NOT NULL DEFAULT '' COMMENT '微信支付交易号',
  `total_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付款金额',
  `time_end` varchar(50) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '录入时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table pre_bank
# ------------------------------------------------------------

CREATE TABLE `pre_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank` varchar(20) NOT NULL DEFAULT '' COMMENT '银行缩写',
  `bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称',
  `status` tinyint(2) NOT NULL DEFAULT '10' COMMENT '状态 10 正常  20 删除',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `ind_bank` (`bank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='银行';



# Dump of table pre_bank_config
# ------------------------------------------------------------

CREATE TABLE `pre_bank_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(10) NOT NULL COMMENT '银行id',
  `type` tinyint(2) NOT NULL DEFAULT '10' COMMENT '类型 10 合伙人  20 代理 30 股东',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `status` tinyint(2) NOT NULL DEFAULT '10' COMMENT '状态 10 正常  20 删除',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `index_bank_id` (`bank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='银行兑换比例';



# Dump of table pre_capital_details
# ------------------------------------------------------------

CREATE TABLE `pre_capital_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `type` varchar(2) NOT NULL DEFAULT '+' COMMENT '符号',
  `kind` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '交易类别：10 充值、20 提成、30 升级返现、40 提现',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `status` int(11) NOT NULL DEFAULT '2' COMMENT '状态',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `ind_member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资金明细表';




# Dump of table pre_contact
# ------------------------------------------------------------

CREATE TABLE `pre_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL DEFAULT '' COMMENT '用户标识',
  `nickname` varchar(500) NOT NULL DEFAULT '' COMMENT '昵称',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `province` varchar(64) DEFAULT NULL COMMENT '用户个人资料填写的省份',
  `country` varchar(32) DEFAULT NULL COMMENT '国家，如中国为CN',
  `city` varchar(64) DEFAULT NULL COMMENT '普通用户个人资料填写的城市',
  `head_image` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `ind_openid_id` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公众号粉丝';




# Dump of table pre_member
# ------------------------------------------------------------

CREATE TABLE `pre_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password_hash` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `openid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户唯一标识',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机',
  `password_salt` varchar(13) NOT NULL DEFAULT '' COMMENT 'Auth Key',
  `password_reset_token` varchar(255) NOT NULL DEFAULT '' COMMENT '重置密码Token',
  `mobile_check_token` varchar(255) NOT NULL DEFAULT '' COMMENT '手机验证Token',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '状态',
  `grade` int(1) NOT NULL DEFAULT '1' COMMENT '会员等级 1 会员 2 代理  3 股东',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`),
  KEY `ind_nickname` (`nickname`),
  KEY `ind_mobile` (`mobile`),
  KEY `ind_openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员表';



# Dump of table pre_order
# ------------------------------------------------------------

CREATE TABLE `pre_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `out_trade_no` varchar(32) DEFAULT '' COMMENT '订单号',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '下单人',
  `bank_id` int(10) NOT NULL COMMENT '银行id',
  `integral` int(11) NOT NULL DEFAULT '0' COMMENT '使用积分',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '兑换金额',
  `exchange_code` varchar(255) DEFAULT '' COMMENT '兑换码',
  `valid_time` date DEFAULT NULL COMMENT '有效日期',
  `remark` text COMMENT '备注',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `ind_out_trade_no` (`out_trade_no`),
  KEY `ind_member_id` (`created_at`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='报单订单表';



# Dump of table pre_order_photo
# ------------------------------------------------------------

CREATE TABLE `pre_order_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `ind_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='报单图片';




# Dump of table pre_user
# ------------------------------------------------------------

CREATE TABLE `pre_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL DEFAULT '',
  `password_reset_token` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `login_at` int(11) NOT NULL DEFAULT '0' COMMENT '登录时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `ind_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
