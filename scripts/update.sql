alter table orders add column app_id int unsigned not null default 0 comment 'APP ID';

alter table merchant_apps add column notify_url varchar(200) not null default '' comment '异步回调地址';

alter table orders drop column merchant_name;

alter table orders add column upstream_confirmed int unsigned not null default 0 comment '上游确认时间';
alter table orders add column downstream_confirmed int unsigned not null default 0 comment '下游确认时间';


alter table orders add column downstream_notified int unsigned not null default 0 comment '下游最后通知时间';
alter table orders add column downstream_notify_count int unsigned not null default 0 comment '下游通知次数';

-- 下游通知
drop table if exists notify_downs;
create table if not exists notify_downs (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    app_id int unsigned not null default 0 comment '应用编号',
    order_number varchar(50) not null default '' comment '下游订单编号',
    notify_url varchar(200) not null default '' comment '通知地址',
    notify_reply text comment '回复信息',
    notify_status tinyint unsigned not null default 0 comment '回复状态',
    created int unsigned not null default 0 comment '通知时间',
    failure_count int unsigned not null default 0 comment '失败次数',
    remark varchar(200) not null default '' comment '备注',
    primary key(id)
);

-- 上游通知
drop table if exists notify_ups;
create table if not exists notify_ups (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    app_id int unsigned not null default 0 comment '应用编号',
    trade_number varchar(50) not null default '' comment '交易单号',
    channel_id int unsigned not null default 0 comment '渠道编号',
    request_url varchar(200) not null default '' comment '通知来源',
    request_ip varchar(50) not null default '' comment '请求IP',
    reply text comment '回复信息',
    created int unsigned not null default 0 comment '通知时间',
    remark varchar(200) not null default '' comment '备注',
    primary key(id)
);

alter table channel_payments add column payment_type tinyint unsigned not null default 0 comment '支付类型';

update menus set name = '支付管理' where id = '1115' and name = '交易管理';
insert into menus (name, parent_id, method, url, is_blank, is_view, state, level, sort)  values
    ('代付管理', 0, 1, '#', 0, 1, 1, 1, 16500);
select last_insert_id() into @last_id;
insert into menus (name, parent_id, method, url, is_blank, is_view, state, level, sort)  values
    ('代付订单', @last_id, 0, '/payouts', 0, 1, 1, 2, 100);
insert into menus (name, parent_id, method, url, is_blank, is_view, state, level, sort)  values
    ('代付记录', @last_id, 0, '/payout_records', 0, 1, 1, 2, 90);
insert into menus (name, parent_id, method, url, is_blank, is_view, state, level, sort)  values
    ('上游通知', @last_id, 0, '/payout_notify_ups', 0, 1, 1, 2, 80);
insert into menus (name, parent_id, method, url, is_blank, is_view, state, level, sort)  values
    ('下游通知', @last_id, 0, '/payout_notify_downs', 0, 1, 1, 2, 70);
update menus set icon = 'layui-icon-rmb' where name = '代付管理' limit 1;