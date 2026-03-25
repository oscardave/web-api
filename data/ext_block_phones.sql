-- 封禁电话表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_block_phones_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建封禁电话表
DROP TABLE IF EXISTS ext_block_phones;
CREATE TABLE IF NOT EXISTS ext_block_phones (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_block_phones_id_seq'),
    phone VARCHAR(20) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_block_phones_phone ON ext_block_phones(phone);

-- 创建注释
COMMENT ON TABLE ext_block_phones IS '封禁电话表，存储系统中封禁的电话号码';
COMMENT ON COLUMN ext_block_phones.id IS '封禁电话唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_block_phones.phone IS '封禁电话号码';
COMMENT ON COLUMN ext_block_phones.remark IS '封禁电话备注';
COMMENT ON COLUMN ext_block_phones.status IS '封禁电话状态：2-已封禁，1-已解封';
COMMENT ON COLUMN ext_block_phones.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_block_phones.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_block_phones_updated_at
    BEFORE UPDATE ON ext_block_phones
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_block_phones (phone, remark, status) VALUES
('13800138000', '骚扰电话', 2),
('13900139000', '诈骗电话', 2),
('13700137000', '推销电话', 2),
('13600136000', '测试电话', 2),
('13500135000', '管理员电话', 2),
('13400134000', '客服电话', 2);
