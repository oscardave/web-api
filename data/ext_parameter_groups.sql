-- 参数分组表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_parameter_groups_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建参数分组表
DROP TABLE IF EXISTS ext_parameter_groups;
CREATE TABLE IF NOT EXISTS ext_parameter_groups (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_parameter_groups_id_seq'),
    name VARCHAR(50) NOT NULL DEFAULT '',
    code VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_parameter_groups_code ON ext_parameter_groups(code);

-- 创建注释
COMMENT ON TABLE ext_parameter_groups IS '参数分组表，存储系统中参数分组';
COMMENT ON COLUMN ext_parameter_groups.id IS '参数分组唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_parameter_groups.name IS '参数分组名称';
COMMENT ON COLUMN ext_parameter_groups.code IS '参数分组编码';
COMMENT ON COLUMN ext_parameter_groups.remark IS '参数分组备注';
COMMENT ON COLUMN ext_parameter_groups.status IS '参数分组状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_parameter_groups.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_parameter_groups.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_parameter_groups_updated_at
    BEFORE UPDATE ON ext_parameter_groups
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_parameter_groups (name, code, remark, status) VALUES
('会员设置', 'member', '会员相关参数分组', 1),
('财务设置', 'finance', '财务相关参数分组', 1),
('网站设备', 'website', '网站设备相关参数分组', 1),
('系统设置', 'system', '系统设置相关参数分组', 1);
