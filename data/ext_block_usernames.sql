-- 封禁用户名称表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_block_usernames_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建封禁用户名称表
DROP TABLE IF EXISTS ext_block_usernames;
CREATE TABLE IF NOT EXISTS ext_block_usernames (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_block_usernames_id_seq'),
    username VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_block_usernames_username ON ext_block_usernames(username);

-- 创建注释
COMMENT ON TABLE ext_block_usernames IS '封禁用户名称表，存储系统中封禁的用户名';
COMMENT ON COLUMN ext_block_usernames.id IS '封禁用户名称唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_block_usernames.username IS '封禁用户名';
COMMENT ON COLUMN ext_block_usernames.remark IS '封禁用户名备注';
COMMENT ON COLUMN ext_block_usernames.status IS '封禁用户名称状态：2-已封禁，1-已解封';
COMMENT ON COLUMN ext_block_usernames.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_block_usernames.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_block_usernames_updated_at
    BEFORE UPDATE ON ext_block_usernames
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_block_usernames (username, remark, status) VALUES
('admin', '管理员用户名', 2),
('root', '系统根用户', 2),
('test', '测试用户名', 2),
('guest', '访客用户名', 2),
('user', '普通用户名', 2),
('system', '系统用户名', 2);
