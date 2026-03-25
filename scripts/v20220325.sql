create table if not exists article_kind
(
    id          int unsigned not null auto_increment,
    name        varchar(64)  not null default '' comment '分类名称',
    remark      varchar(256) not null default '' comment '分类备注',
    create_time int unsigned not null default 0 comment '创建时间',
    update_time int unsigned not null default 0 comment '修改时间',
    primary key (id),
    unique key (name)
);

-- 增加分类编号
alter table article
    add column kind_id int not null default 0 comment '分类编号';

-- 后台管理日志
alter table admin_log
    add column data text comment '数据记录';


alter table `order`
    add column proportion decimal(10, 3) default 0 comment '分红利率';

ALTER TABLE `product`
    ADD COLUMN `cumulative_number` int(0) NOT NULL DEFAULT 0 COMMENT '产品显示销量';

-- 修改错误日志大小
alter table user_log
    modify remark varchar(512) not null default '' comment '备注',
    modify user_agent varchar(512) not null default '' comment '代理信息';

-- 修改用户登录日志相关信息
alter table user_login_log
    modify remark varchar(512) not null default '' comment '备注',
    modify user_agent varchar(512) not null default '' comment '代理信息';

-- 修改用户开始计数
alter table user
    auto_increment = 100000;

-- 修改管理员开始计数
alter table admin
    auto_increment = 10000;

-- 提现审核
alter table user_withdraw
    add column deny_reason varchar(256) not null default '' comment '拒绝理由';

-- 创建索引
create index sign_day_time_idx on sign (day_time);
