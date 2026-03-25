-- 管理员表创建脚本
-- 设置默认起始索引为 1000000

-- 创建序列，起始值为 1000000
CREATE SEQUENCE IF NOT EXISTS syn_admin_logs_id_seq
    START WITH 1000000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建管理员表
DROP TABLE IF EXISTS syn_admin_logs;
CREATE TABLE IF NOT EXISTS syn_admin_logs (
    id BIGINT PRIMARY KEY DEFAULT nextval('syn_admin_logs_id_seq'),
    admin_id INT NOT NULL DEFAULT 0,
    path VARCHAR(255) NOT NULL DEFAULT '',
    menu_id INT NOT NULL DEFAULT 0,
    action VARCHAR(255) NOT NULL DEFAULT '',
    method VARCHAR(255) NOT NULL DEFAULT '',
    ip VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_syn_admin_logs_admin_id ON syn_admin_logs(admin_id);
CREATE INDEX IF NOT EXISTS idx_syn_admin_logs_action ON syn_admin_logs(action);
CREATE INDEX IF NOT EXISTS idx_syn_admin_logs_ip ON syn_admin_logs(ip);
CREATE INDEX IF NOT EXISTS idx_syn_admin_logs_created_at ON syn_admin_logs(created_at);

-- 创建注释
COMMENT ON TABLE syn_admin_logs IS '管理员日志表，存储系统管理员的操作日志';
COMMENT ON COLUMN syn_admin_logs.id IS '日志唯一标识符，自增主键，起始值为1000';
COMMENT ON COLUMN syn_admin_logs.admin_id IS '管理员ID，关联管理员表';
COMMENT ON COLUMN syn_admin_logs.path IS '请求路径';
COMMENT ON COLUMN syn_admin_logs.menu_id IS '菜单ID，关联菜单表';
COMMENT ON COLUMN syn_admin_logs.action IS '操作类型，如登录、退出、增删改查等';
COMMENT ON COLUMN syn_admin_logs.method IS '请求方法，如GET、POST、PUT、DELETE等';
COMMENT ON COLUMN syn_admin_logs.ip IS '操作IP地址';
COMMENT ON COLUMN syn_admin_logs.remark IS '备注';
COMMENT ON COLUMN syn_admin_logs.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN syn_admin_logs.updated_at IS '记录更新时间，通过触发器自动更新';

-- 创建触发器
CREATE TRIGGER update_syn_admin_logs_updated_at
    BEFORE UPDATE ON syn_admin_logs
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
-- 注意：密码哈希在实际应用中应该使用 bcrypt 等安全算法
INSERT INTO syn_admin_logs (admin_id, action, ip, remark, path, menu_id, method) VALUES
(1000, '登录', '127.0.0.1', '测试备注', '/test', 1, 'GET'),
(1001, '退出', '127.0.0.1', '测试备注', '/test', 1, 'GET'),
(1002, '增', '127.0.0.1', '测试备注', '/test', 1, 'GET'),
(1003, '删', '127.0.0.1', '测试备注', '/test', 1, 'GET'),
(1004, '改', '127.0.0.1', '测试备注', '/test', 1, 'GET'),
(1005, '查', '127.0.0.1', '测试备注', '/test', 1, 'GET');
