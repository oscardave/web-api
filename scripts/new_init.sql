CREATE TABLE `admin_log`
(
    `id`         int unsigned     not null AUTO_INCREMENT,
    `admin_id`   int unsigned     not null default '0' comment '管理员编号',
    `admin_name` varchar(20)      not null default '' comment '管理员名称',
    object_id    int unsigned     not null default 0 comment '修改对象',
    object_name  varchar(20)      not null default '' comment '修改对象名称',
    `level`      tinyint unsigned not null default '0' comment '日志级别 0:调试 1:普通 2:警告 3:危险 4:致命 5:错误',
    `type`       tinyint unsigned not null default '0' comment '日志类型 0:登录退出 1:财务调整 2:会员管理 3:内容管理 4:系统设置 5:其他',
    value_old    varchar(256)     not null default '' comment '旧值',
    value_new    varchar(256)     not null default '' comment '新值',
    module       varchar(64)      not null default '' comment '模块',
    `url`        varchar(200)     not null default '' comment '调用地址',
    `created`    int unsigned     not null default '0' comment '添加时间',
    `remark`     varchar(20)      not null default '' comment '备注',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 100000
  DEFAULT CHARSET = utf8;

alter table admin
    add column update_time int not null default 0 comment '最后修改时间',
    add column login_count int not null default 0 comment '登录次数';

-- 用户登录日志
drop table if exists user_login_log;
CREATE TABLE `user_login_log`
(
    `id`        int unsigned not null AUTO_INCREMENT,
    `user_id`   int unsigned not null default '0' comment '用户编号',
    `username`  varchar(20)  not null default '' comment '用户名称',
    `url`       varchar(200) not null default '' comment '调用地址',
    login_ip    varchar(64)  not null default '' comment '登录IP',
    error_count int          not null default 0 comment '出错次数',
    user_agent  varchar(256) not null default '' comment '浏览信息',
    `created`   int unsigned not null default '0' comment '登录时间',
    `remark`    varchar(20)  not null default '' comment '备注',
    PRIMARY KEY (`id`, created)
) engine = innodb
  default charset = utf8
  auto_increment = 1000000 partition by range (created) (
    partition p2201 values less than (1643644800),
    partition p2202 values less than (1646064000),
    partition p2203 values less than (1648742400),
    partition p2204 values less than (1651334400),
    partition p2205 values less than (1654012800),
    partition p2206 values less than (1656604800),
    partition p2207 values less than (1659283200),
    partition p2208 values less than (1661961600),
    partition p2209 values less than (1664553600),
    partition p2210 values less than (1667232000),
    partition p2211 values less than (1669824000),
    partition p2212 values less than (1672502400),
    partition p2301 values less than (1675180800),
    partition p2302 values less than (1677600000),
    partition p2303 values less than (1680278400),
    partition p2304 values less than (1682870400),
    partition p2305 values less than (1685548800),
    partition p2306 values less than maxvalue
    );

-- 用户日志
drop table if exists user_log;
CREATE TABLE `user_log`
(
    `id`          int unsigned not null AUTO_INCREMENT,
    `user_id`     int unsigned not null default '0' comment '用户编号',
    `username`    varchar(20)  not null default '' comment '用户名称',
    `object_id`   int unsigned not null default '0' comment '对象编号',
    `object_name` varchar(20)  not null default '' comment '对象名称',
    level         tinyint      not null default 0 comment '日志等级',
    type          tinyint      not null default 0 comment '日志类型',
    `url`         varchar(200) not null default '' comment '调用地址',
    ip            varchar(64)  not null default '' comment '登录IP',
    user_agent    varchar(256) not null default '' comment '浏览信息',
    `created`     int unsigned not null default '0' comment '登录时间',
    `remark`      varchar(20)  not null default '' comment '备注',
    PRIMARY KEY (`id`, created)
) engine = innodb
  default charset = utf8
  auto_increment = 1000000 partition by range (created) (
    partition p2201 values less than (1643644800),
    partition p2202 values less than (1646064000),
    partition p2203 values less than (1648742400),
    partition p2204 values less than (1651334400),
    partition p2205 values less than (1654012800),
    partition p2206 values less than (1656604800),
    partition p2207 values less than (1659283200),
    partition p2208 values less than (1661961600),
    partition p2209 values less than (1664553600),
    partition p2210 values less than (1667232000),
    partition p2211 values less than (1669824000),
    partition p2212 values less than (1672502400),
    partition p2301 values less than (1675180800),
    partition p2302 values less than (1677600000),
    partition p2303 values less than (1680278400),
    partition p2304 values less than (1682870400),
    partition p2305 values less than (1685548800),
    partition p2306 values less than maxvalue
    );

--  添加索引
create index recharge_type_idx on recharge (type);
create index recharge_status_idx on recharge (status);
create index recharge_is_show_idx on recharge (is_show);
create index recharge_username_idx on recharge (username);

-- 添加索引
create index user_withdraw_username_idx on user_withdraw (username);
create index user_withdraw_status_idx on user_withdraw (status);
create index user_withdraw_type_idx on user_withdraw (type);
create index user_withdraw_trade_no_idx on user_withdraw (trade_no);

-- 添加索引
create index user_uid_idx on user (uid);

-- 授权ip
drop table if exists permission_ip;
create table if not exists permission_ip
(
    id          int          not null auto_increment,
    ip          varchar(64)  not null default 0 comment '授权IP',
    status      tinyint      not null default 0 comment '状态',
    remark      varchar(256) not null default 0 comment '备注 ',
    create_time int          not null default 0 comment '添加时间',
    update_time int          not null default 0 comment '修改时间',
    primary key (id)
);
create index permission_ip_ip on permission_ip (ip);
create index permission_ip_status on permission_ip (status);

-- 增加授权ip字段
alter table admin
    add column allow_ip varchar(256) not null default '' comment '授权ip';

alter table message
    add product_id  int         not null default 0 comment '产品编号',
    add update_time int         not null default 0 comment '状态变更时间',
    add user_name   varchar(32) not null default '' comment '用户名称',
    modify content text comment '消息内容';
create index message_product_id_idx on message (product_id);
create index message_product_type_idx on message (type);
create index message_product_is_read_idx on message (is_read);

alter table attachment
    add file_size int         not null default 0 comment '文件大小',
    add file_name varchar(64) not null default '' comment '文件名称';
create index attachment_user_id_idx on attachment (user_id);


alter table user
    auto_increment = 168100;
update user
set id = 168000 + id;