-- 导入现有的所有表
-- source /home/an/web/shipu/integrated-payment/scripts/admin_logs.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/admin_roles.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/admins.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/banks.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/cities.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/configs.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/districts.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/permission_ips.sql;
-- source /home/an/web/shipu/integrated-payment/scripts/provinces.sql;

-- 商户表
drop table if exists merchants;
create table if not exists merchants (
    id int unsigned not null auto_increment,
    name varchar(50) not null default '' comment '登录名称',
    password char(32) not null default '' comment '登录密码',
    secret char(32) not null default '' comment '密钥',
    merchant_code varchar(50) not null default '' comment '商户名称',
    merchant_name varchar(50) not null default '' comment '商户编码',
    payment_secret char(32) not null default '' comment '支付密码',
    phone varchar(50) not null default '' comment '手机号码',
    mail varchar(50) not null default '' comment '电子邮件',
    state tinyint unsigned not null default 0 comment '状态 0:停用;1:启用;',
    last_ip varchar(50) not null default '' comment '最后登录IP',
    last_login int unsigned not null default 0 comment '最后登录时间',
    login_count int unsigned not null default 0 comment '登录次数',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    merchant_type tinyint not null default 1 comment '商户类型 1:直属商户;2:非直属商户;',
    pay_in tinyint not null default 0 comment '入款权限 0:停用;1:启用;',
    pay_out tinyint not null default 0 comment '出款权限 0:停用;1:启用;',
    sort int not null default 0 comment '排序',
    remark varchar(200) not null default '' comment '备注',
    primary key(id),
    unique key(name),
    unique key(merchant_code),
    unique key(merchant_name)
);

-- 收款卡
drop table if exists cards;
create table if not exists cards (
    id int unsigned not null auto_increment,
    bank_id int unsigned not null default 0 comment '银行编号',
    bank_name varchar(50) not null default '' comment '银行名称',
    bank_code varchar(50) not null default '' comment '银行编码',
    branch_name varchar(50) not null default '' comment '支行信息',
    card_number varchar(50) not null default '' comment '银行卡号',
    real_name varchar(50) not null default '' comment '真实姓名',
    each_min decimal(13, 2) not null default 0 comment '每笔最低',
    each_max decimal(13, 2) not null default 0 comment '每笔最高',
    pay_max decimal(13, 2) not null default 0 comment '最高支付',
    call_count int unsigned not null default 0 comment '最多调用',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id),
    index(bank_id)
);

-- 商户银行卡
drop table if exists merchant_cards;
create table if not exists merchant_cards (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    card_id int unsigned not null default 0 comment '银行卡编号',
    state tinyint unsigned not null default 0 comment '状态 0:停用;1:启用;',
    polling tinyint not null default 0 comment '轮询状态 0:否;1:是;',
    primary key(id),
    index(merchant_id),
    index(card_id)
);

-- 后台登录日志
drop table if exists admin_login_logs;
create table if not exists admin_login_logs (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    domain varchar(50) not null default '' comment '域名',
    login_ip varchar(50) not null default '' comment '登录IP',
    login_area varchar(200) not null default '' comment '登录地区',
    created int unsigned not null default 0 comment '添加时间',
    primary key(id)
);

-- 操作日志
drop table if exists operation_logs;
create table if not exists operation_logs (
    id int unsigned not null auto_increment,
    admin_id int unsigned not null default 0 comment '后台用户编号',
    remark varchar(200) not null default '' comment '备注',
    method char(8) not null default '' comment '方法',
    url varchar(200) not null default '' comment '来源URL',
    operate_ip varchar(50) not null default '' comment '操作IP',
    operate_area varchar(200) not null default '' comment '操作地区',
    created int unsigned not null default 0 comment '添加时间',
    primary key(id)
);

-- 商户账户表
drop table if exists merchant_accounts;
create table if not exists merchant_accounts (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    remain decimal(13, 4) not null default 0 comment '可用余额', 
    frozen decimal(13, 4) not null default 0 comment '冻结金额',  
    total decimal(13, 4) not null default 0 comment '可用总额', 
    total_in decimal(13, 4) not null default 0 comment '入账总额',  
    total_out decimal(13, 4) not null default 0 comment '出账总额', 
    state tinyint unsigned not null default 0 comment '钱包状态 0:停用;1:启用;',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id)
);

-- 商户账变表
drop table if exists merchant_changes;
create table if not exists merchant_changes (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default 0 comment '商户名称',
    change_type tinyint unsigned not null default 0 comment '账变类型 0:平账;1:入账;2:出账;',
    remain_before decimal(13, 4) not null default 0 comment '账变前余额',
    frozen_before decimal(13, 4) not null default 0 comment '账变前冻结',
    total_before decimal(13, 4) not null default 0 comment '账变前总额',
    remain_after decimal(13, 4) not null default 0 comment '账变后余额',
    frozen_after decimal(13, 4) not null default 0 comment '账变后冻结',
    total_after decimal(13, 4) not null default 0 comment '账变后总额',
    amount decimal(13, 4) not null default 0 comment '账变金额',
    remark varchar(200) not null default '' comment '备注',
    created int unsigned not null default 0 comment '账变时间',
    primary key(id)
);

-- 渠道信息
drop table if exists channels;
create table if not exists channels (
    id int unsigned not null auto_increment,
    external_id varchar(100) not null default '' comment '外部商户编号',
    name varchar(100) not null default '' comment '渠道名称',
    code varchar(200) not null default '' comment '渠道编码',
    app_id varchar(100) not null default '' comment 'APP-ID',
    app_key varchar(200)  not null default '' comment 'APP-KEY',
    app_secret varchar(200) not null default '' comment 'APP-SECRET',
    encrypt_type tinyint unsigned not null default 0 comment '加密方式 0:默认;1:MD5;2:AES;',
    url_order varchar(200) not null default '' comment '下单地址',
    url_callback varchar(200) not null default '' comment '回调地址',
    url_notify varchar(200) not null default '' comment '异步通知地址',
    remark varchar(200) not null default '' comment '备注',
    state tinyint unsigned not null default 0 comment '状态 0:停用;1:启用;',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id)
);

-- 渠道支付方式
drop table if exists channel_payments;
create table if not exists channel_payments (
    id int unsigned not null auto_increment,
    channel_id int unsigned not null default 0 comment '通道编号',
    name varchar(100) not null default '' comment '支付名称',
    code varchar(200) not null default '' comment '支付编码',
    amount_min decimal(9, 2) not null default 0 comment '最小金额',
    amount_max decimal(9, 2) not null default 0 comment '最大金额',
    amounts varchar(200) not null default '' comment '可用金额',
    state tinyint unsigned not null default 0 comment '状态 0:停用;1:启用;',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id)
);

-- 收款记录
drop table if exists card_records;
create table if not exists card_records (
    id int unsigned not null auto_increment,
    order_number char(20) not null default '' comment '订单编号',
    channel_name varchar(100) not null default '' comment '渠道名称',
    merchant_id int unsigned not null default 0 comment '商户编号',
    channel_code varchar(200) not null default '' comment '渠道编码',
    card_id int unsigned not null default 0 comment '卡片编号',
    amount decimal(13, 4) not null default 0 comment '付款金额',
    payer_name varchar(100)  not null default '' comment '付款姓名',
    payer_remark varchar(200) not null default '' comment '付款说明',
    bank_order_number varchar(100) not null default '' comment '银行订单号码',
    paid_amount decimal(13, 4) not null default 0 comment '实付金额',
    state tinyint not null default 0 comment '状态 0:待付;1:实付;2:取消;3:其他;',
    remark varchar(200) not null default 0 comment '备注',
    created int unsigned not null default 0 comment '添加时间',
    updated  int unsigned not null default 0 comment '修改时间',
    finished int unsigned not null default 0 comment '完成时间',
    primary key(id)
);

-- 通道配置 - channel_configs
drop table if exists channel_configs;
create table if not exists channel_configs (
    id int unsigned not null auto_increment,
    channel_id int unsigned not null default 0 comment '渠道编号',
    deposit_start char(8) not null default '00:00' comment '入款开始时间',
    deposit_end char(8) not null default '24:00' comment '入款开始时间',
    deposit_min decimal(13, 2) not null default 0 comment '入款最低额度',
    deposit_max decimal(13, 2) not null default 0 comment '入款最高额度',
    withdraw_start char(8) not null default '00:00' comment '出款开始时间',
    withdraw_end char(8) not null default '24:00' comment '出款开始时间',
    withdraw_min decimal(13, 2) not null default 0 comment '出款最低额度',
    withdraw_max decimal(13, 2) not null default 0 comment '出款最高额度',
    primary key(id)
);

-- 支付订单 - pay_orders
drop table if exists orders;
create table if not exists orders (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    order_number varchar(50) not null default '' comment '订单编号',
    channel_id int unsigned not null default 0 comment '渠道编号',
    amount decimal(13, 2) not null default 0 comment '金额',
    amount_paid decimal(13, 2) not null default 0 comment '实付金额',
    state tinyint unsigned not null default 0 comment '状态 0:待付;1:成功;2:失败;3:取消;4:拒绝;9:其他;',
    cost_ms decimal(8, 2) default 0 comment '费时毫秒',
    terminal_type tinyint unsigned not null default 0 comment '终端类型',
    ip varchar(20) not null default '' comment 'IP',
    area varchar(50) not null default '' comment '地区',
    trade_number varchar(50) not null default '' comment '交易单号',
    remark varchar(200) not null default '' comment '备注',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    finished int unsigned not null default 0 comment '完成时间',
    primary key(id)
);

-- 结算记录 - merchant_settles
drop table if exists merchant_settles;
create table if not exists merchant_settles (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    amount decimal(13, 2) not null default 0 comment '打款金额',
    fee decimal(13, 2) not null default 0 comment '手续费',
    amount_settled decimal(13, 2) not null default 0 comment '',
    bank_id int unsigned not null default 0 comment '商户编号',
    card_number varchar(50) not null default '' comment '银行卡号',
    real_name varchar(50) not null default '' comment '收款姓名',
    branch_name varchar(50) not null default '' comment '支行名称',
    phone varchar(50) not null default '' comment '手机号码',
    province_id int unsigned not null default 0 comment '省份编号',
    city_id int unsigned not null default 0 comment '城市编号',
    district_id int unsigned not null default 0 comment '县区编号',
    state tinyint not null default 0 comment '状态 0:待付;1:完成;2:取消;3:拒绝;9:其他;',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    finished int unsigned not null default 0 comment '完成时间',
    admin_id int unsigned not null default 0 comment '后台用户编号',
    admin_name varchar(50) not null default '' comment '后台用户名称',
    primary key(id)
);

-- 实时统计 - report_real_times
drop table if exists report_real_times;
create table if not exists report_real_times (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    income decimal(13, 2) not null default 0 comment '收入',
    cost decimal(13, 2) not null default 0 comment '成本',
    profit decimal(13, 2) not null default 0 comment '利润',
    success_total decimal(13, 2) not null default 0 comment '成功总额',
    success_count int unsigned not null default 0 comment '成功笔数',
    failure_total decimal(13, 2) not null default 0 comment '失败总额',
    failure_count int unsigned not null default 0 comment '失败笔数',
    primary key(id)
);

-- 历史收益 - 按日 - report_days
drop table if exists report_days;
create table if not exists report_days (
    id int unsigned not null auto_increment,
    day char(10) not null default '2020-01-01' comment '统计日期',
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    income decimal(13, 2) not null default 0 comment '收入',
    cost decimal(13, 2) not null default 0 comment '成本',
    profit decimal(13, 2) not null default 0 comment '利润',
    success_total decimal(13, 2) not null default 0 comment '成功总额',
    success_count int unsigned not null default 0 comment '成功笔数',
    failure_total decimal(13, 2) not null default 0 comment '失败总额',
    failure_count int unsigned not null default 0 comment '失败笔数',
    primary key(id)
);

-- 历史收益 - 按月 - report_months
drop table if exists report_months;
create table if not exists report_months (
    id int unsigned not null auto_increment,
    month char(8) not null default '2020-01' comment '月份',
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    income decimal(13, 2) not null default 0 comment '收入',
    cost decimal(13, 2) not null default 0 comment '成本',
    profit decimal(13, 2) not null default 0 comment '利润',
    success_total decimal(13, 2) not null default 0 comment '成功总额',
    success_count int unsigned not null default 0 comment '成功笔数',
    failure_total decimal(13, 2) not null default 0 comment '失败总额',
    failure_count int unsigned not null default 0 comment '失败笔数',
    primary key(id)
);

-- 历史收益 - 按年 - report_years
drop table if exists report_years;
create table if not exists report_years (
    id int unsigned not null auto_increment,
    year char(4) not null default '2020' comment '年份',
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    income decimal(13, 2) not null default 0 comment '收入',
    cost decimal(13, 2) not null default 0 comment '成本',
    profit decimal(13, 2) not null default 0 comment '利润',
    success_total decimal(13, 2) not null default 0 comment '成功总额',
    success_count int unsigned not null default 0 comment '成功笔数',
    failure_total decimal(13, 2) not null default 0 comment '失败总额',
    failure_count int unsigned not null default 0 comment '失败笔数',
    primary key(id)
);

-- 商户通道 - merchant_channels
drop table if exists merchant_channels;
create table if not exists merchant_channels (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    channel_id int unsigned not null default 0 comment '通道编号',
    primary key(id)
);

-- 通道实时报告 - report_channels
drop table if exists report_channels;
create table if not exists report_channels (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    merchant_name varchar(50) not null default '' comment '商户名称',
    channel_id int unsigned not null default 0 comment '通道编号',
    primary key(id)
);

-- 通道上游 - channel_up_streams
drop table if exists channel_up_streams;
create table if not exists channel_up_streams (
    id int unsigned not null auto_increment,
    name varchar(50) not null default '' comment '名称',
    code varchar(50) not null default '' comment '编码',
    priority int not null default 0 comment '序号',
    callback_ip varchar(50) not null default '' comment '回调IP',
    state tinyint unsigned not null default 0 comment '状态 0:禁用;1:启用;',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id)
);

-- 通道下游 - channel_down_streams
drop table if exists channel_down_streams;
create table if not exists channel_down_streams (
    id int unsigned not null auto_increment,
    up_stream_id int unsigned not null default 0 comment '代付通道ID',
    up_stream_name varchar(50) not null default '' comment '代付通道名称',
    fee decimal(6, 2) not null default 0 comment '代付费率',
    fee_min decimal(6, 2) not null default 0 comment '代付最低费率',
    code varchar(50) not null default '' comment '编码',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id)
);

-- 商户产品 - channel_products
drop table if exists channel_products;
create table if not exists channel_products (
    id int unsigned not null auto_increment,
    name varchar(50) not null default '' comment '产品名称',
    code varchar(50) not null default '' comment '产品编码',
    state tinyint unsigned not null default 0 comment '状态 0:未生效;1:已生效;',
    remark varchar(200) not null default '' comment '备注',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id)
);

-- 代付
drop table if exists payouts;
create table if not exists payouts (
    id int unsigned not null auto_increment,
    channel_id int unsigned not null default 0 comment '渠道编号',
    merchant_id int unsigned not null default 0 comment '商户编号',
    order_number char(20) not null default '' comment '下游单号',
    trade_number char(30) not null default '' comment '交易单号',
    app_id int unsigned not null default 0 comment 'app编号',
    bank_code varchar(20) not null default '' comment '银行编码',
    bank_name varchar(30) not null default '' comment '银行名称',
    bank_branch varchar(200) not null default '' comment '支行名称',
    bank_card varchar(20) not null default '' comment '银行卡号',
    name varchar(50) not null default '' comment '名称',
    amount decimal(9, 2) not null default 0 comment '金额',
    paied decimal(9, 2) not null default 0 comment '实付金额',
    state tinyint unsigned not null default 0 comment '状态 0:待处理; 1:处理成功; 2: 处理失败; 3: 已取消; 4: 交易拒绝; 5: 其他;',
    finisned int unsigned not null default 0 comment '完成时间',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    remark varchar(200) not null default '' comment '备注',
    index(merchant_id),
    index(app_id),
    primary key(id)
) default charset=utf8 collate=utf8_general_ci;
