-- ip黑名单
create table if not exists blocked_ip
(
    id          int unsigned not null auto_increment,
    ip          varchar(64)  not null default '' comment 'IP',
    remark      varchar(256) not null default '' comment '备注',
    create_time int unsigned not null default 0,
    update_time int unsigned not null default 0,
    unique key (ip),
    primary key (id)
);

-- 设备黑名单
create table if not exists blocked_device
(
    id          int unsigned not null auto_increment,
    device      varchar(64)  not null default '' comment '设备信息',
    remark      varchar(256) not null default '' comment '备注',
    create_time int unsigned not null default 0,
    update_time int unsigned not null default 0,
    unique key (device),
    primary key (id)
);

-- 用户黑名单
create table if not exists blocked_user
(
    id          int unsigned not null auto_increment,
    username    varchar(64)  not null default '' comment '用户名称',
    remark      varchar(256) not null default '' comment '备注',
    create_time int unsigned not null default 0,
    update_time int unsigned not null default 0,
    unique key (username),
    primary key (id)
);

--  工资表
CREATE TABLE `salary`  (
  `id` int(0) NOT NULL  AUTO_INCREMENT,
  `user_id` int(0) NOT NULL DEFAULT 0,
  `username` varchar(30) NOT NULL DEFAULT '',
  `num` int(0) NOT NULL DEFAULT 0 COMMENT '有效人数',
  `salary_time` int(0) NOT NULL DEFAULT 0,
  `create_time` int(0) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `salary_time`(`salary_time`) USING BTREE
) ENGINE = InnoDB;

-- 工资记录
CREATE TABLE `salary_log`  (
      `id` int(0) NOT NULL AUTO_INCREMENT,
      `user_id` int(0) NOT NULL DEFAULT 0,
      `username` varchar(30) NOT NULL DEFAULT '',
      `money` decimal(10, 3) NOT NULL DEFAULT 0,
      `remark` varchar(50) NOT NULL DEFAULT '',
      `created` int(0) NOT NULL DEFAULT 0,
      `updated` int(0) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      INDEX `user_id`(`user_id`) USING BTREE,
      INDEX `created`(`created`) USING BTREE
) ENGINE = InnoDB;
-- 产品加个结束天数
alter table product add column end_day tinyint not null default 0 comment '产品几天结束';
alter table product add column  product_limit int null default 0 comment '个人限购';
 -- 订单增加上级ID
ALTER TABLE `order`
ADD COLUMN `pid` int(0) NOT NULL DEFAULT 0 AFTER `get_method`,
ADD INDEX `pid`(`pid`) USING BTREE;

-- 工资配置
INSERT INTO `system`(`key`, `value`)
VALUES ('salary_valid_min', '1'),
       ('salary_valid_money', '1'),
       ('salary_valid_month_min', '1'),
       ('salary_valid_month_money', '1');
-- 基金不同分佣
INSERT INTO `system`(`key`, `value`)
VALUES ('fund_one', '1'),
       ('fund_two', '1'),
       ('fund_three', '1');

-- 添加注释
 ALTER TABLE `product`  MODIFY COLUMN `p_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '产品类型1：货币，2基金，3股权，4纪念币,5保险';


-- 图片修改
alter table `order` modify img varchar(255)  default "" comment '图片';

-- VIP修改
ALTER TABLE `vip`ADD COLUMN `deposit_min` int(0) NOT NULL DEFAULT 0 COMMENT '最低存款要求' ,ADD COLUMN `valid_min` int(0) NOT NULL DEFAULT 0 COMMENT '最低有效人數' ,ADD COLUMN `deposit_num` int(0) NOT NULL DEFAULT 0 COMMENT '最低存款次數' ,ADD COLUMN `payment_group_id` tinyint NOT NULL DEFAULT 0 COMMENT '支付通道';

INSERT INTO `system`(`key`, `value`)VALUES ('invite_award', '1'),VALUES ('deposit_rate', '1');



CREATE TABLE `deposit_usdt`  (
       `id` int(0) NOT NULL AUTO_INCREMENT,
       `user_id` int(0) NOT NULL DEFAULT 0,
       `username` varchar(30) NOT NULL DEFAULT '',
       `money` decimal(10, 3) NOT NULL DEFAULT 0,
       `image` varchar(255) NOT NULL DEFAULT '',
       `user_usdt` varchar(255) NOT NULL DEFAULT '',
       `company_usdt` varchar(255) NOT NULL DEFAULT '',
       `trade` varchar(255) NOT NULL DEFAULT '',
       `status` int(0) NOT NULL DEFAULT 0,
       `created` int(0) NOT NULL DEFAULT 0,
       `updated` int(0) NOT NULL DEFAULT 0,
       PRIMARY KEY (`id`)
) ENGINE = InnoDB;