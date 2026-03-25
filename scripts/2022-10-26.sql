CREATE TABLE `account_change` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
    `before_account` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
    `change_account` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录IP',
    `after_account` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `before_withdraw` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `change_withdraw` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `after_withdraw` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `before_ex` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `change_ex` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `after_ex` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '登录地区',
    `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
    `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
    `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        PRIMARY KEY (`id`),
    INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


alter table `order` add column level_one_pid int not null default 0;
alter table `order` add column level_two_pid int not null default 0;
alter table `order` add column level_three_pid int not null default 0;

CREATE TABLE `donate_log` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
          `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
           `article_id` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `article_title` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          PRIMARY KEY (`id`),
          INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `deposit_fund` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
  `title` varchar(200)  NOT NULL DEFAULT '' COMMENT '添加时间',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
  `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`),
  INDEX `user_id`(`uid`)
) ENGINE=InnoDB ;

alter table `deposit_fund` add column type int not null default 0 COMMENT '1 红包。2存款送基金，3购买产品送基金,4捐赠';
alter table `product` add column subsidy_money int not null default 0 COMMENT '每日补贴金额';
alter table `user_ex` add column pid int not null default 0 COMMENT '上级';

CREATE TABLE `user_login_log` (
  `id` int  AUTO_INCREMENT,
  `username` varchar(100) NOT NULL DEFAULT '',
  `ip` varchar(100) NOT NULL DEFAULT '',
  `user_id` int NOT NULL DEFAULT '0',
  `created` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;

CREATE TABLE `user_login_log` (
       `id` int unsigned NOT NULL AUTO_INCREMENT,
       `username` varchar(100) NOT NULL DEFAULT '',
       `ip` varchar(100) NOT NULL DEFAULT '',
       `user_id` int NOT NULL DEFAULT '0',
       `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       PRIMARY KEY (`id`),
       INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;
CREATE TABLE `vip_log` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
        `low_id` int unsigned NOT NULL DEFAULT 0 COMMENT '域名',
        `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '域名',
        `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        PRIMARY KEY (`id`),
        INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;

CREATE TABLE `real_vip` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
      `vip` int unsigned NOT NULL DEFAULT 0 COMMENT '域名',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`),
      INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;

CREATE TABLE `real_vip_log` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
    `vip` int unsigned NOT NULL DEFAULT 0 COMMENT '域名',
    `before_vip` int unsigned NOT NULL DEFAULT 0 COMMENT '域名',
    `after_vip` int unsigned NOT NULL DEFAULT 0 COMMENT '域名',
    `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    PRIMARY KEY (`id`),
    INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `lottery_log` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
      `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`),
      INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `team` (
           `id` int unsigned NOT NULL AUTO_INCREMENT,
           `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
           `money` int NOT NULL DEFAULT 0 COMMENT '域名',
           `count` int  NOT NULL DEFAULT 0 COMMENT '积分',
           `active_num` int  NOT NULL DEFAULT 0 COMMENT '积分',
           `money_count` int  NOT NULL DEFAULT 0 COMMENT '积分',
           `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
           `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
           `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
           PRIMARY KEY (`id`),
           INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `new_activity` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
        `first` int NOT NULL DEFAULT 0 COMMENT '域名',
        `second` int  NOT NULL DEFAULT 0 COMMENT '积分',
        `three` int  NOT NULL DEFAULT 0 COMMENT '积分',
        `four` int  NOT NULL DEFAULT 0 COMMENT '积分',
        `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
        `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        PRIMARY KEY (`id`),
        INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


alter table `withdraw` add column type int not null default 1;
alter table `withdraw` add column usdt_account varchar(255) not null default '';


CREATE TABLE `new_activity_stock` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
    `first` int NOT NULL DEFAULT 0 COMMENT '域名',
    `second` int  NOT NULL DEFAULT 0 COMMENT '积分',
    `three` int  NOT NULL DEFAULT 0 COMMENT '积分',
    `four` int  NOT NULL DEFAULT 0 COMMENT '积分',
     `five` int  NOT NULL DEFAULT 0 COMMENT '积分',
    `six` int  NOT NULL DEFAULT 0 COMMENT '积分',
    `seven` int  NOT NULL DEFAULT 0 COMMENT '积分',
    `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
    `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    PRIMARY KEY (`id`),
    INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;



CREATE TABLE `red_envelope` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
        `max` int NOT NULL DEFAULT 0 COMMENT '域名',
        `has` int  NOT NULL DEFAULT 0 COMMENT '积分',
        `count` int  NOT NULL DEFAULT 0 COMMENT '积分',
        `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
        PRIMARY KEY (`id`),
        INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `activation` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
        `num` int NOT NULL DEFAULT 0 COMMENT '域名',
        PRIMARY KEY (`id`),
        INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `task_project` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
          `type` int NOT NULL DEFAULT 0 COMMENT '域名',
          `loading` int NOT NULL DEFAULT 0 COMMENT '域名',
          PRIMARY KEY (`id`),
          INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `admin_adjust_log` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int  NOT NULL DEFAULT '0' COMMENT '商户编号',
          `type` int  NOT NULL DEFAULT '0' COMMENT '商户编号',
          `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `money` decimal(10,2) not null default 0.0,
          PRIMARY KEY (`id`),
          INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `lottery` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `bill_no`  varchar(200) NOT NULL DEFAULT '' COMMENT '商户编号',
          `game_code` varchar(200) NOT NULL DEFAULT '' COMMENT '域名',
          `machine_code` varchar(200)  NOT NULL DEFAULT '' COMMENT '添加时间',
          `country_code` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `result` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `result_type` int  NOT NULL DEFAULT 0 COMMENT '输赢类型',
          `count_type` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

insert into lottery(`bill_no`,game_code,machine_code,country_code,`result`,result_type,count_type) values("20240319164","IHM","G28/1405","ES","7,7,4,7,2",1,50);
insert into lottery(`bill_no`,game_code,machine_code,country_code,`result`,result_type,count_type) values("20240319163","IHM","G28/1405","ES","7,7,4,7,2",1,50);
insert into lottery(`bill_no`,game_code,machine_code,country_code,`result`,result_type,count_type) values("20240319162","IHM","G28/1405","ES","7,7,4,7,2",1,50);
insert into lottery(`bill_no`,game_code,machine_code,country_code,`result`,result_type,count_type) values("20240319161","IHM","G28/1405","ES","7,7,4,7,2",1,50);
insert into lottery(`bill_no`,game_code,machine_code,country_code,`result`,result_type,count_type) values("20240319160","IHM","G28/1405","ES","7,7,4,7,2",1,50);

CREATE TABLE `user_lottery` (
       `id` int unsigned NOT NULL AUTO_INCREMENT,
       `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
       `top_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
       `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
       `bill_no`  varchar(200) NOT NULL DEFAULT '' COMMENT '商户编号',
       `game_code` varchar(200) NOT NULL DEFAULT '' COMMENT '域名',
       `machine_code` varchar(200)  NOT NULL DEFAULT '' COMMENT '添加时间',
       `country_code` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
       `result_type` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
       `result` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
       `miss_count` int  NOT NULL DEFAULT 0 COMMENT '商户编号',
       `count_type` int  NOT NULL DEFAULT 0 COMMENT '商户编号',
       `get_money` decimal(10,2) not null default 0,
       `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       PRIMARY KEY (`id`),
       INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE `invite_log` (
       `id` int unsigned NOT NULL AUTO_INCREMENT,
       `user_id`  int  NOT NULL DEFAULT 0 COMMENT '商户编号',
       `invite_username` varchar(200) NOT NULL DEFAULT '' COMMENT '域名',
       `username` varchar(200) NOT NULL DEFAULT '' COMMENT '域名',
       `invite_user_id`  int  NOT NULL DEFAULT 0 COMMENT '商户编号',
       `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `market_line` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `context` text ,
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `market_log` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
      `money` varchar(50) NOT NULL DEFAULT '' COMMENT '域名',
      `type` int  NOT NULL DEFAULT 0 COMMENT '0买，1卖',
      `buy_type` varchar(50)  NOT NULL DEFAULT '' COMMENT '类型',
      `profit`   decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `open_price`  varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `close_price`  varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `symbol`  varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `open_at` varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `close_at` varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `day` varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `bill_no` varchar(50) NOT NULL DEFAULT '' COMMENT '登录地区',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`),
      INDEX `user_id`(`user_id`),
      INDEX `day`(`day`)
) ENGINE=InnoDB ;



CREATE TABLE `platform_coin_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `day` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
  `price` decimal(10,4) NOT NULL DEFAULT 0 COMMENT '域名',
  `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `pledge_log` (
             `id` int unsigned NOT NULL AUTO_INCREMENT,
             `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
             `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
             `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
             `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
             `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
             `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
             PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `platform_coin_relax` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
  `money` decimal(10,3) NOT NULL DEFAULT 0 COMMENT '域名',
  `coin_price` decimal(10,3) NOT NULL DEFAULT 0 COMMENT '域名',
  `buy_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
  `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


insert into `system`(`key`,value) values("partner_status","1");
insert into `system`(`key`,value) values("partner_period","1");
insert into `system`(`key`,value) values("partner_period_first_num","39");
insert into `system`(`key`,value) values("partner_period_second_num","59");
insert into `system`(`key`,value) values("partner_period_third_num","79");


insert into `system`(`key`,value) values("partner_period_first_money","1000");
insert into `system`(`key`,value) values("partner_period_second_money","2000");
insert into `system`(`key`,value) values("partner_period_third_money","3000");

insert into `system`(`key`,value) values("partner_period_first_rate","1");
insert into `system`(`key`,value) values("partner_period_second_rate","1.2");
insert into `system`(`key`,value) values("partner_period_third_rate","1.5");


insert into `system`(`key`,value) values("partner_period_first_need","3000");
insert into `system`(`key`,value) values("partner_period_second_need","5000");
insert into `system`(`key`,value) values("partner_period_third_need","8000");

CREATE TABLE `partner_handle` (
       `id` int unsigned NOT NULL AUTO_INCREMENT,
       `admin` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
       `money` decimal(10,3) NOT NULL DEFAULT 0 COMMENT '域名',
       `period` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `type` int  NOT NULL DEFAULT 0 COMMENT '0买，1平台币分红，2余额分红',
       `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `partner_log` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `user_id` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `period` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `money` decimal(12,6) NOT NULL DEFAULT 0 COMMENT '域名',
          `end_time` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `type` int  NOT NULL DEFAULT 0 COMMENT '0买，1平台币分红，2余额分红',
          `status` int  NOT NULL DEFAULT 0 COMMENT '',
          `handle_type` int  NOT NULL DEFAULT 1 COMMENT '兑换手续费，交易手续费，生态分红',
          `rate` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `mining` (
       `id` int unsigned NOT NULL AUTO_INCREMENT,
       `title` varchar(100) NOT NULL DEFAULT '' COMMENT ' 添加时间',
       `type` int NOT NULL DEFAULT 0 COMMENT ' 添加时间',
       `price` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
       `management_fee`  decimal(12,2)   NOT NULL DEFAULT 0 COMMENT '添加时间',
       `num` int  NOT NULL DEFAULT 0 COMMENT '众筹人数',
       `buy_num` int  NOT NULL DEFAULT 0 COMMENT '众筹人数',
       `management_fee_type`  int   NOT NULL DEFAULT 0 COMMENT '1月，2半年，3年',
       `end_time` int  NOT NULL DEFAULT 0 COMMENT '0买，1平台币分红，2余额分红',
       `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `user_mining` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `username` varchar(100) NOT NULL DEFAULT '' COMMENT ' 添加时间',
      `user_id` int NOT NULL DEFAULT 0 COMMENT ' 添加时间',
      `money` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `fee`  decimal(12,2)   NOT NULL DEFAULT 0 COMMENT '添加时间',
      `copies` int  NOT NULL DEFAULT 0 COMMENT '众筹人数',
      `status` int  NOT NULL DEFAULT 0 COMMENT '众筹人数',
      `fee_time`  int   NOT NULL DEFAULT 0 COMMENT '1月，2半年，3年',
      `type`  int   NOT NULL DEFAULT 0 COMMENT '1月，2半年，3年',
      `end_time` int  NOT NULL DEFAULT 0 COMMENT '0买，1平台币分红，2余额分红',
      `mining_type` int  NOT NULL DEFAULT 0 COMMENT '0买，1平台币分红，2余额分红',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `pledge_money` (
       `id` int unsigned NOT NULL AUTO_INCREMENT,
       `username` varchar(100) NOT NULL DEFAULT '' COMMENT ' 添加时间',
       `user_id` int NOT NULL DEFAULT 0 COMMENT ' 添加时间',
       `money` int  NOT NULL DEFAULT 0 COMMENT '众筹人数',
       `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `advertise` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(100) NOT NULL DEFAULT '' COMMENT ' 添加时间',
        `url` varchar(255) NOT NULL DEFAULT '' COMMENT ' 添加时间',
        `link` varchar(255) NOT NULL DEFAULT '' COMMENT ' 添加时间',
        `language` varchar(255) NOT NULL DEFAULT '' COMMENT ' 添加时间',
        `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
        PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `video_log` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
          `type` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
          `day` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
          `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `pledge_setting` (
         `id` int unsigned NOT NULL AUTO_INCREMENT,
         `start_time` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
         `money` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
         `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
         `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
         PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `withdraw_pledge` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
      `withdraw_money` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `money` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `gat_pledge_log` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
      `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户编号',
      `money` decimal(12,4) NOT NULL DEFAULT 0 COMMENT '域名',
      `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `platform_withdraw` (
 `id` int NOT NULL AUTO_INCREMENT,
 `uid` int NOT NULL DEFAULT '0' COMMENT '用户id号',
 `money` double(15,4) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `username` varchar(100) NOT NULL DEFAULT '',
  `address` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未审核，1审核通过，2审核不通过',
  `bz` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1银行卡提现 2支付宝',
  `trade_no` varchar(255) DEFAULT '',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改',
  `name` varchar(255) NOT NULL DEFAULT '',
    `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `deny_reason` varchar(256) NOT NULL DEFAULT '' COMMENT '拒绝理由',
  `real_name` varchar(30) DEFAULT NULL COMMENT '比例类型',
  `usdt_account` varchar(255) NOT NULL DEFAULT '',
  `pid` int NOT NULL DEFAULT '0',
  `trade_hash` varchar(255) NOT NULL DEFAULT '',
  `auto_status` int NOT NULL DEFAULT '0',
  `cost` decimal(15,4) NOT NULL DEFAULT '0.00',
  `order_no` varchar(50) NOT NULL DEFAULT '',
  `bmc_cost` decimal(12,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `user_withdraw_username_idx` (`username`),
  KEY `user_withdraw_status_idx` (`status`),
  KEY `user_withdraw_type_idx` (`type`),
  KEY `user_withdraw_trade_no_idx` (`trade_no`),
  KEY `money` (`money`)
) ENGINE=InnoDB;



CREATE TABLE `order_deposit_setting` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `money` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
          `max` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
          `base` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '域名',
          `vip` int NOT NULL DEFAULT 0 COMMENT '域名',
          `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
          PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `c_to_c` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `type` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `username` varchar(50) DEFAULT NULL COMMENT '比例类型',
    `user_id` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `bill_no` varchar(100) DEFAULT NULL COMMENT '比例类型',
    `price` decimal(15,6) NOT NULL DEFAULT 0 COMMENT '域名',
    `number` decimal(15,6) NOT NULL DEFAULT 0 COMMENT '域名',
    `total_money` decimal(15,6) NOT NULL DEFAULT 0 COMMENT '域名',
    `cost` decimal(15,6) NOT NULL DEFAULT 0 COMMENT '域名',
    `cost_rate` varchar(50) DEFAULT NULL COMMENT '比例类型',
    `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
    PRIMARY KEY (`id`),
    INDEX `user_id`(`user_id`),
    INDEX `type`(`type`)
) ENGINE=InnoDB ;


CREATE TABLE `agent` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
      `account` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
      `password` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
      `username` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`),
      INDEX `user_id`(`user_id`)
) ENGINE=InnoDB ;

CREATE TABLE `ip_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(200) NOT NULL DEFAULT '' COMMENT '登录地区',
  PRIMARY KEY (`id`),
  INDEX `user_id`(`ip`)
) ENGINE=InnoDB ;