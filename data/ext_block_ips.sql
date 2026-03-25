-- 封禁IP表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_block_ips_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建封禁IP表
DROP TABLE IF EXISTS ext_block_ips;
CREATE TABLE IF NOT EXISTS ext_block_ips (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_block_ips_id_seq'),
    ip VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_block_ips_ip ON ext_block_ips(ip);

-- 创建注释
COMMENT ON TABLE ext_block_ips IS '封禁IP表，存储系统中封禁的IP地址';
COMMENT ON COLUMN ext_block_ips.id IS '封禁IP唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_block_ips.ip IS '封禁IP地址';
COMMENT ON COLUMN ext_block_ips.remark IS '封禁IP备注';
COMMENT ON COLUMN ext_block_ips.status IS '封禁IP状态：2-已封禁，1-已解封';
COMMENT ON COLUMN ext_block_ips.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_block_ips.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_block_ips_updated_at
    BEFORE UPDATE ON ext_block_ips
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_block_ips (ip, remark, status) VALUES
('127.0.0.1', '管理员使用IP', 2),
('127.0.0.2', '管理员使用IP', 2),
('127.0.0.3', '管理员使用IP', 2),
('127.0.0.4', '管理员使用IP', 2),
('127.0.0.5', '管理员使用IP', 2),
('127.0.0.6', '管理员使用IP', 2);
