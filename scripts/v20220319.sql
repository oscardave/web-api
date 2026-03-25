-- 用户验证码
drop table if exists user_code;
create table if not exists user_code
(
    id          int unsigned not null auto_increment,
    user_id     int unsigned not null default 0 comment '用户编号',
    user_name   varchar(32)  not null default '' comment '用户名称',
    code        char(8)      not null default '' comment '编码',
    create_time int unsigned not null default 0 comment '创建时间',
    update_time int unsigned not null default 0 comment '修改时间',
    remark      varchar(256) not null default '' comment '备注',
    status      tinyint      not null default 0 comment '状态 0:未使用 1:已使用',
    index (user_id),
    index (user_name),
    unique index (code),
    index (status),
    primary key (id)
);

-- 支付分组
drop table if exists payment_group;
create table if not exists payment_group
(
    id          int unsigned not null auto_increment,
    name        varchar(32)  not null default '' comment '分组名称',
    create_time int unsigned not null default 0 comment '创建时间',
    update_time int unsigned not null default 0 comment '修改时间',
    status      tinyint      not null default 0 comment '状态 0:禁用 1:正常',
    remark      varchar(256) not null default '' comment '备注',
    index (status),
    primary key (id)
);

-- 收款银行卡片
drop table if exists receive_card;
create table if not exists receive_card
(
    id               int unsigned not null auto_increment,
    payment_group_id int unsigned not null default 0 comment '分组编号',
    bank_name        varchar(128) not null default '' comment '银行名称',
    card_number      varchar(32)  not null default '' comment '银行卡号',
    name             varchar(32)  not null default '' comment '持卡姓名',
    create_time      int unsigned not null default 0 comment '创建时间',
    update_time      int unsigned not null default 0 comment '修改时间',
    status           tinyint      not null default 0 comment '状态 0:禁用 1:正常',
    remark           varchar(256) not null default '' comment '备注',
    index (status),
    unique index (card_number),
    primary key (id)
);

-- 添加菜单: 支付分组
select id
into @parent_id
from menu
where name = '系统管理'
limit 1;
insert into menu (name, parent_id, url, state, level, `sort`)
values ('支付分组', @parent_id, '/payment_groups', 1, 2, 5000);

-- 添加菜单: 收款银行
select id
into @parent_id
from menu
where name = '系统管理'
limit 1;
insert into menu (name, parent_id, url, state, level, `sort`)
values ('收款银行', @parent_id, '/receive_cards', 1, 2, 4000);

-- 添加菜单: 会员验证
select id
into @parent_id
from menu
where name = '会员管理'
limit 1;
insert into menu (name, parent_id, url, state, level, `sort`)
values ('会员验证', @parent_id, '/user_codes', 1, 2, 1000);
--


-- 添加会员字段。
alter table user
    add withdraw_money decimal(10, 2) default 0.00 comment '提款金额';

-- 添加原始股权设置
-- 当前价格
INSERT INTO `system`(`key`, `value`) VALUES ('origin_stock_current_price', '1');

-- 发行价格
INSERT INTO `system`(`key`, `value`) VALUES ('origin_stock_price', '1');

-- 分红例比
INSERT INTO `system`(`key`, `value`) VALUES ('origin_stock_rate', '1');

-- 分红日期
INSERT INTO `system`(`key`, `value`) VALUES ('origin_stock_date', '1');

-- 累计销量
INSERT INTO `system`(`key`, `value`) VALUES ('company_profile', '1');

-- 商业
INSERT INTO `system`(`key`, `value`) VALUES ('business_scope', '1');

-- 成功案例
INSERT INTO `system`(`key`, `value`) VALUES ('success_case', '1');

-- 股权
INSERT INTO `system`(`key`, `value`) VALUES ('origin_stock_base', '1');


-- 添加支付分组
alter table user
    add column payment_group_id int unsigned not null default 0 comment '支付分组编号';

-- 添加唯一索引
create unique index system_key_un_idx on `system` (`key`);

-- 添加获奖图片
INSERT INTO `system`(`key`, `value`) VALUES ('reward_pic_1', ''),('reward_pic_2', ''),('reward_pic_3', ''),('reward_pic_4', ''),('reward_pic_5', ''),('reward_pic_6', '');

-- 签到赠送相关
INSERT INTO `system`(`key`, `value`)
VALUES ('sign_give_ex_gold_on_off', '1'),
       ('sign_give_ex_gold_num', '1'),
       ('sign_give_stock_on_off', '1'),
       ('sign_give_stock_num', '1');

-- 添加内容
INSERT INTO `system`(`key`, `value`)
VALUES ('text_service', ''),
       ('text_question', '');

-- 添加用户实名
alter table user_withdraw
    add column real_name varchar(64) not null default '' comment '用户名称';


--
alter table product add column commission_first decimal (10,3) default 0 comment '产品佣金第一级';
alter table product add column commission_second decimal (10,3) default 0 comment '产品佣金第二级';
alter table product add column commission_third decimal (10,3) default 0 comment '产品佣金第三级';