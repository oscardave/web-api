-- 用户兑换记录表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
DROP VIEW IF EXISTS ext_user_exchanges_v;
CREATE SEQUENCE IF NOT EXISTS ext_user_exchanges_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户兑换记录表
DROP TABLE IF EXISTS ext_user_exchanges;
CREATE TABLE IF NOT EXISTS ext_user_exchanges (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_exchanges_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    exchange_type INTEGER NOT NULL DEFAULT 0,
    membership_level BIGINT NOT NULL DEFAULT 0,
    original_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method SMALLINT NOT NULL DEFAULT 1,
    exchange_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    status SMALLINT NOT NULL DEFAULT 1,

    -- 系统字段
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_exchanges_user_id ON ext_user_exchanges(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_exchanges_membership_level ON ext_user_exchanges(membership_level);
CREATE INDEX IF NOT EXISTS idx_ext_user_exchanges_payment_method ON ext_user_exchanges(payment_method);
CREATE INDEX IF NOT EXISTS idx_ext_user_exchanges_exchange_time ON ext_user_exchanges(exchange_time);
CREATE INDEX IF NOT EXISTS idx_ext_user_exchanges_user_id_status ON ext_user_exchanges(user_id, status);

-- 创建注释
COMMENT ON TABLE ext_user_exchanges IS '用户兑换记录表，存储用户会员兑换记录';
COMMENT ON COLUMN ext_user_exchanges.id IS '兑换记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_exchanges.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_user_exchanges.exchange_type IS '兑换类型：1-会员等级兑换，2-诚信保兑换';
COMMENT ON COLUMN ext_user_exchanges.membership_level IS '会员等级：对应ext_user_levels.id';
COMMENT ON COLUMN ext_user_exchanges.original_price IS '原价';
COMMENT ON COLUMN ext_user_exchanges.discount IS '折扣金额';
COMMENT ON COLUMN ext_user_exchanges.paid_amount IS '实际付费金额';
COMMENT ON COLUMN ext_user_exchanges.payment_method IS '付费方式：1-积分兑换，2-现金支付，3-混合支付';
COMMENT ON COLUMN ext_user_exchanges.exchange_time IS '兑换时间';
COMMENT ON COLUMN ext_user_exchanges.status IS '兑换状态：0-待处理，1-成功，2-失败，3-处理中';
COMMENT ON COLUMN ext_user_exchanges.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_exchanges.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_exchanges_updated_at
    BEFORE UPDATE ON ext_user_exchanges
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 创建视图，关联用户表获取昵称和头像
DROP VIEW IF EXISTS ext_user_exchanges_v;
CREATE VIEW ext_user_exchanges_v AS
SELECT
    e.id,
    e.user_id,
    u.nickname,
    u.avatar_url as avatar,
    e.exchange_type,
    l.name as membership_level_name,
    e.membership_level,
    e.original_price,
    e.discount,
    e.paid_amount,
    e.payment_method,
    e.exchange_time,
    e.status,
    e.created_at,
    e.updated_at
FROM ext_user_exchanges e
LEFT JOIN ext_users u ON e.user_id = u.id
LEFT JOIN ext_user_levels l ON e.membership_level = l.id;

-- 插入测试数据
INSERT INTO ext_user_exchanges (user_id, exchange_type, membership_level, original_price, discount, paid_amount, payment_method, exchange_time, status) VALUES
(10001, 1, 10001, 98.00, 0.00, 98.00, 1, '2021-04-13 10:56:33', 1),
(10002, 1, 10001, 98.00, 0.00, 98.00, 1, '2021-04-13 10:56:33', 1),
(10003, 1, 10001, 98.00, 0.00, 98.00, 1, '2021-04-13 10:56:33', 1),
(10001, 1, 10002, 168.00, 0.00, 168.00, 1, '2021-04-13 10:56:33', 1),
(10002, 1, 10002, 168.00, 0.00, 168.00, 1, '2021-04-13 10:56:33', 1),
(10003, 1, 10002, 168.00, 0.00, 168.00, 1, '2021-04-13 10:56:33', 1),
(10001, 1, 10003, 298.00, 0.00, 298.00, 1, '2021-04-13 10:56:33', 1),
(10002, 1, 10003, 298.00, 0.00, 298.00, 1, '2021-04-13 10:56:33', 1),
(10003, 1, 10003, 298.00, 0.00, 298.00, 1, '2021-04-13 10:56:33', 1),
(10001, 1, 10003, 298.00, 0.00, 298.00, 1, '2021-04-13 10:56:33', 1);
