-- 用户账户表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_accounts_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户账户表
DROP TABLE IF EXISTS ext_user_accounts;
CREATE TABLE IF NOT EXISTS ext_user_accounts (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_accounts_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    point_amount INTEGER NOT NULL DEFAULT 0,
    point_frozen_amount INTEGER NOT NULL DEFAULT 0,
    credit_amount INTEGER NOT NULL DEFAULT 0,
    credit_frozen_amount INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
DROP INDEX IF EXISTS idx_ext_user_accounts_user_id;
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_accounts_user_id ON ext_user_accounts(user_id);

-- 创建注释
COMMENT ON TABLE ext_user_accounts IS '用户账户表，存储用户账户信息';
COMMENT ON COLUMN ext_user_accounts.id IS '账户唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_accounts.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_user_accounts.point_amount IS '积分余额';
COMMENT ON COLUMN ext_user_accounts.point_frozen_amount IS '冻结积分金额';
COMMENT ON COLUMN ext_user_accounts.credit_amount IS '诚信保余额';
COMMENT ON COLUMN ext_user_accounts.credit_frozen_amount IS '冻结诚信保金额';
COMMENT ON COLUMN ext_user_accounts.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_accounts.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_accounts_updated_at
    BEFORE UPDATE ON ext_user_accounts
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据, 使用 select into 从 ext_users 中获取数据
INSERT INTO ext_user_accounts (user_id, point_amount, point_frozen_amount, credit_amount, credit_frozen_amount)
SELECT id, 0, 0, 0, 0 FROM ext_users;