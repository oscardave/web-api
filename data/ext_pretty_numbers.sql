-- 号码表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_pretty_numbers_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建号码表
DROP TABLE IF EXISTS ext_pretty_numbers;
CREATE TABLE IF NOT EXISTS ext_pretty_numbers (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_pretty_numbers_id_seq'),
    pretty_number VARCHAR(20) NOT NULL DEFAULT '',
    user_id BIGINT NOT NULL DEFAULT 0,
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1, -- 1-未使用，2-已使用，3-禁用
    used_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_pretty_numbers_pretty_number ON ext_pretty_numbers(pretty_number);
CREATE INDEX IF NOT EXISTS idx_ext_pretty_numbers_user_id ON ext_pretty_numbers(user_id);

-- 创建注释
COMMENT ON TABLE ext_pretty_numbers IS '号码表，存储系统中号码信息';
COMMENT ON COLUMN ext_pretty_numbers.id IS '号码唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_pretty_numbers.pretty_number IS '号码';
COMMENT ON COLUMN ext_pretty_numbers.user_id IS '用户ID';
COMMENT ON COLUMN ext_pretty_numbers.remark IS '号码备注';
COMMENT ON COLUMN ext_pretty_numbers.status IS '号码状态：1-未使用，2-已使用，3-禁用';
COMMENT ON COLUMN ext_pretty_numbers.used_at IS '号码使用时间';
COMMENT ON COLUMN ext_pretty_numbers.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_pretty_numbers.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_pretty_numbers_updated_at
    BEFORE UPDATE ON ext_pretty_numbers
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_pretty_numbers (pretty_number, user_id, remark, status) VALUES
('138000', 10001, '测试号码', 1),
('139000', 10002, '测试号码', 1),
('137000', 10003, '测试号码', 1),
('136000', 10004, '测试号码', 1),
('135000', 10005, '测试号码', 1),
('134000', 10006, '测试号码', 1);
