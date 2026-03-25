create table merchant_apps (
    id int unsigned not null auto_increment,
    merchant_id int unsigned not null default 0 comment '商户编号',
    name varchar(50) not null default '' comment '应用名称',
    app_key char(32) not null default '' comment 'APP编号',
    state tinyint not null default 0 comment '状态 0:禁用; 1:启用;',
    allow_ips text comment 'IP白名单',
    pay_in tinyint not null default 0 comment '入款权限',
    pay_out tinyint not null default 0 comment '出款权限',
    remark varchar(200) not null default '' comment '备注',
    created int unsigned not null default 0 comment '添加时间',
    updated int unsigned not null default 0 comment '修改时间',
    primary key(id),
    index(merchant_id)
) AUTO_INCREMENT=168000;