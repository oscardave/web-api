-- 菜单表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS syn_menus_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建菜单表
DROP TABLE IF EXISTS syn_menus;
CREATE TABLE IF NOT EXISTS syn_menus (
    id BIGINT PRIMARY KEY DEFAULT nextval('syn_menus_id_seq'),
    parent_id BIGINT NOT NULL DEFAULT 0,
    name VARCHAR(100) NOT NULL DEFAULT '',
    path VARCHAR(200) NOT NULL DEFAULT '',
    icon VARCHAR(100) NOT NULL DEFAULT '',
    sort INTEGER NOT NULL DEFAULT 0,
    type SMALLINT NOT NULL DEFAULT 0,
    status SMALLINT NOT NULL DEFAULT 1,
    remark VARCHAR(200) NOT NULL DEFAULT '',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_syn_menus_parent_id ON syn_menus(parent_id);
CREATE INDEX IF NOT EXISTS idx_syn_menus_sort ON syn_menus(sort);
CREATE INDEX IF NOT EXISTS idx_syn_menus_status ON syn_menus(status);
CREATE INDEX IF NOT EXISTS idx_syn_menus_type ON syn_menus(type);
CREATE INDEX IF NOT EXISTS idx_syn_menus_created_at ON syn_menus(created_at);

-- 创建注释
COMMENT ON TABLE syn_menus IS '菜单表，定义系统导航菜单结构和权限控制';
COMMENT ON COLUMN syn_menus.id IS '菜单唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN syn_menus.parent_id IS '父菜单ID，0表示顶级菜单，支持多级菜单结构';
COMMENT ON COLUMN syn_menus.name IS '菜单显示名称';
COMMENT ON COLUMN syn_menus.path IS '菜单路径/路由地址，用于前端路由跳转';
COMMENT ON COLUMN syn_menus.icon IS '菜单图标，支持图标类名或图标URL';
COMMENT ON COLUMN syn_menus.sort IS '菜单排序字段，数值越小排序越靠前';
COMMENT ON COLUMN syn_menus.type IS '菜单类型：0-菜单，1-按钮，2-页面';
COMMENT ON COLUMN syn_menus.status IS '菜单状态：0-禁用，1-启用';
COMMENT ON COLUMN syn_menus.remark IS '菜单备注，用于描述菜单功能或权限';
COMMENT ON COLUMN syn_menus.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN syn_menus.updated_at IS '记录更新时间，通过触发器自动更新';

-- 创建触发器
CREATE TRIGGER update_syn_menus_updated_at
    BEFORE UPDATE ON syn_menus
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
-- 顶级菜单
INSERT INTO syn_menus (id, parent_id, name, path, icon, sort, type, status, remark) VALUES
(10001, 0, '仪表盘', '/dashboard', 'dashboard', 1, 0, 1, '仪表盘'),
(10002, 0, '用户管理', '/users', 'users', 2, 0, 1, '用户管理'),
(10003, 0, '系统管理', '/system', 'setting', 3, 0, 1, '系统管理'),
(10004, 0, '内容管理', '/content', 'file-text', 4, 0, 1, '内容管理'),
(10005, 0, '日志管理', '/logs', 'log', 5, 0, 1, '日志管理');

-- 用户管理子菜单
INSERT INTO syn_menus (id, parent_id, name, path, icon, sort, type, status, remark) VALUES
(10006, 10002, '管理员列表', '/users/admins', 'user', 1, 0, 1, '管理员列表'),
(10007, 10002, '角色管理', '/users/roles', 'shield', 2, 0, 1, '角色管理'),
(10008, 10002, '权限配置', '/users/permissions', 'key', 3, 0, 1, '权限配置');

-- 系统管理子菜单
INSERT INTO syn_menus (id, parent_id, name, path, icon, sort, type, status, remark) VALUES
(10009, 10003, '菜单管理', '/system/menus', 'menu', 1, 0, 1, '菜单管理'),
(10010, 10003, '系统配置', '/system/config', 'tool', 2, 0, 1, '系统配置'),
(10011, 10003, '数据备份', '/system/backup', 'database', 3, 0, 1, '数据备份');

-- 内容管理子菜单
INSERT INTO syn_menus (id, parent_id, name, path, icon, sort, type, status, remark) VALUES
(10012, 10004, '文章管理', '/content/articles', 'file-text', 1, 0, 1, '文章管理'),
(10013, 10004, '分类管理', '/content/categories', 'folder', 2, 0, 1, '分类管理'),
(10014, 10004, '标签管理', '/content/tags', 'tag', 3, 0, 1, '标签管理');

-- 日志管理子菜单
INSERT INTO syn_menus (id, parent_id, name, path, icon, sort, type, status, remark) VALUES
(10015, 10005, '操作日志', '/logs/operations', 'activity', 1, 0, 1, '操作日志'),
(10016, 10005, '登录日志', '/logs/logins', 'log-in', 2, 0, 1, '登录日志'),
(10017, 10005, '错误日志', '/logs/errors', 'alert-circle', 3, 0, 1, '错误日志');
