-- 角色表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS syn_roles_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建角色表
DROP TABLE IF EXISTS syn_roles;
CREATE TABLE IF NOT EXISTS syn_roles (
    id BIGINT PRIMARY KEY DEFAULT nextval('syn_roles_id_seq'),
    name VARCHAR(50) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    permissions JSONB NOT NULL DEFAULT '[]',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_syn_roles_name ON syn_roles(name);
CREATE INDEX IF NOT EXISTS idx_syn_roles_status ON syn_roles(status);
CREATE INDEX IF NOT EXISTS idx_syn_roles_created_at ON syn_roles(created_at);
CREATE INDEX IF NOT EXISTS idx_syn_roles_permissions ON syn_roles USING GIN (permissions);

-- 创建注释
COMMENT ON TABLE syn_roles IS '角色表，定义系统中不同角色的权限和功能';
COMMENT ON COLUMN syn_roles.id IS '角色唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN syn_roles.name IS '角色名称，唯一，如：超级管理员、普通管理员等';
COMMENT ON COLUMN syn_roles.description IS '角色描述，详细说明角色的职责和权限范围';
COMMENT ON COLUMN syn_roles.permissions IS '权限JSON数组，存储该角色可访问的菜单ID列表，如：["10001", "10002"]';
COMMENT ON COLUMN syn_roles.status IS '角色状态：0-禁用，1-启用';
COMMENT ON COLUMN syn_roles.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN syn_roles.updated_at IS '记录更新时间，通过触发器自动更新';

-- 创建触发器
CREATE TRIGGER update_syn_roles_updated_at
    BEFORE UPDATE ON syn_roles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO syn_roles (name, description, permissions, status) VALUES
('super_admin', '超级管理员', '["10001", "10002", "10003", "10004", "10005", "10006", "10007", "10008"]', 1),
('admin', '普通管理员', '["10001", "10002", "10003", "10004", "10005"]', 1),
('moderator', '审核员', '["10001", "10002", "10003"]', 1),
('viewer', '查看者', '["10001", "10002"]', 1),
('guest', '访客', '["10001"]', 1);
