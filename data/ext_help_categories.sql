-- 帮助分类表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_help_categories_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建帮助分类表
DROP TABLE IF EXISTS ext_help_categories;
CREATE TABLE IF NOT EXISTS ext_help_categories (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_help_categories_id_seq'),
    name VARCHAR(50) NOT NULL DEFAULT '',
    code VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_help_categories_code ON ext_help_categories(code);
CREATE INDEX IF NOT EXISTS idx_ext_help_categories_status ON ext_help_categories(status);
CREATE INDEX IF NOT EXISTS idx_ext_help_categories_sort_order ON ext_help_categories(sort_order);

-- 创建注释
COMMENT ON TABLE ext_help_categories IS '帮助分类表，存储系统中帮助分类信息';
COMMENT ON COLUMN ext_help_categories.id IS '帮助分类唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_help_categories.name IS '分类名称';
COMMENT ON COLUMN ext_help_categories.code IS '分类编码';
COMMENT ON COLUMN ext_help_categories.remark IS '分类备注';
COMMENT ON COLUMN ext_help_categories.status IS '分类状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_help_categories.sort_order IS '排序字段，数值越小排序越靠前';
COMMENT ON COLUMN ext_help_categories.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_help_categories.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_help_categories_updated_at
    BEFORE UPDATE ON ext_help_categories
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_help_categories (name, code, remark, status, sort_order) VALUES
('账户管理', 'account', '用户账户相关帮助信息', 1, 1),
('功能使用', 'features', '系统功能使用说明', 1, 2),
('支付相关', 'payment', '支付和财务相关帮助', 1, 3),
('安全设置', 'security', '账户安全和隐私设置', 1, 4),
('常见问题', 'faq', '用户常见问题解答', 1, 5);
