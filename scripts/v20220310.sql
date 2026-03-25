-- 充值审核
alter table recharge
    add column deny_reason varchar(256) not null default '' comment '拒绝理由';

-- 商品
alter table goods
    add column deny_reason varchar(256) not null default '' comment '拒绝理由';

-- 订单
alter table `order`
    add column deny_reason varchar(256) not null default '' comment '拒绝理由';

