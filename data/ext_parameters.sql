-- 参数表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_parameters_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建参数表
DROP TABLE IF EXISTS ext_parameters;
CREATE TABLE IF NOT EXISTS ext_parameters (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_parameter_groups_id_seq'),
    group_id BIGINT NOT NULL DEFAULT 0,
    name VARCHAR(50) NOT NULL DEFAULT '',
    code VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_parameters_code ON ext_parameters(code);

-- 创建注释
COMMENT ON TABLE ext_parameters IS '参数表，存储系统中参数';
COMMENT ON COLUMN ext_parameters.id IS '参数唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_parameters.name IS '参数名称';
COMMENT ON COLUMN ext_parameters.code IS '参数编码';
COMMENT ON COLUMN ext_parameters.remark IS '参数备注';
COMMENT ON COLUMN ext_parameters.status IS '参数状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_parameters.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_parameters.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_parameters_updated_at
    BEFORE UPDATE ON ext_parameters
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_parameters (group_id, name, code, remark, status) VALUES
(10000, '会员设置', 'member', '会员相关参数', 1),
(10001,  '财务设置', 'finance', '财务相关参数', 1),
(10002, '网站设备', 'website', '网站设备相关参数', 1),
(10003, '系统设置', 'system', '系统设置相关参数', 1);

-- 创建视图
CREATE VIEW ext_parameters_v AS
SELECT
    p.id,
    p.group_id,
    pg.name as group_name,
    p.name,
    p.code,
    p.remark,
    p.status,
    p.created_at,
    p.updated_at
FROM ext_parameters p
LEFT JOIN ext_parameter_groups pg ON p.group_id = pg.id;
