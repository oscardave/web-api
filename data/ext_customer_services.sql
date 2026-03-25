-- 客服表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_customer_services_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建客服表
DROP TABLE IF EXISTS ext_customer_services;
CREATE TABLE IF NOT EXISTS ext_customer_services (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_customer_services_id_seq'),
    sort_order INT NOT NULL DEFAULT 1,
    nickname VARCHAR(100) NOT NULL DEFAULT '',
    username VARCHAR(50) NOT NULL DEFAULT '',
    beautiful_id VARCHAR(50) NOT NULL DEFAULT '',
    friends_count INT NOT NULL DEFAULT 0,
    max_friends INT NOT NULL DEFAULT 3000,
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_customer_services_username ON ext_customer_services(username);
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_customer_services_beautiful_id ON ext_customer_services(beautiful_id);
CREATE INDEX IF NOT EXISTS idx_ext_customer_services_sort_order ON ext_customer_services(sort_order);
CREATE INDEX IF NOT EXISTS idx_ext_customer_services_status ON ext_customer_services(status);

-- 创建注释
COMMENT ON TABLE ext_customer_services IS '客服表，存储客服账号信息';
COMMENT ON COLUMN ext_customer_services.id IS '客服唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_customer_services.sort_order IS '排序号，用于显示顺序';
COMMENT ON COLUMN ext_customer_services.nickname IS '客服昵称';
COMMENT ON COLUMN ext_customer_services.username IS '客服用户名/ID';
COMMENT ON COLUMN ext_customer_services.beautiful_id IS '靓靓号';
COMMENT ON COLUMN ext_customer_services.friends_count IS '已添加好友数';
COMMENT ON COLUMN ext_customer_services.max_friends IS '最大好友数限制';
COMMENT ON COLUMN ext_customer_services.status IS '客服状态：1-正常，2-隐藏';
COMMENT ON COLUMN ext_customer_services.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_customer_services.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_customer_services_updated_at
    BEFORE UPDATE ON ext_customer_services
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_customer_services (sort_order, nickname, username, beautiful_id, friends_count, max_friends, status) VALUES
(1, '昵称昵称昵称', 'jame12355', '1231232', 2520, 3000, 1),
(2, '昵称昵称昵称', 'test12345', '1231233', 2, 3000, 2);

-- 创建视图
CREATE VIEW ext_customer_services_v AS
SELECT
    cs.id,
    cs.sort_order,
    cs.nickname,
    cs.username,
    cs.beautiful_id,
    cs.friends_count,
    cs.max_friends,
    cs.status,
    CASE cs.status
        WHEN 1 THEN '正常'
        WHEN 2 THEN '隐藏'
        ELSE '未知'
    END as status_name,
    cs.created_at,
    cs.updated_at
FROM ext_customer_services cs;
