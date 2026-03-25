-- 管理员表创建脚本
-- 设置默认起始索引为 1000

-- 创建序列，起始值为 1000
CREATE SEQUENCE IF NOT EXISTS syn_admins_id_seq
    START WITH 1000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建管理员表
DROP TABLE IF EXISTS syn_admins;
CREATE TABLE IF NOT EXISTS syn_admins (
    id BIGINT PRIMARY KEY DEFAULT nextval('syn_admins_id_seq'),
    name VARCHAR(50) NOT NULL DEFAULT 'admin',
    mail VARCHAR(100) NOT NULL DEFAULT 'admin@example.com',
    password VARCHAR(255) NOT NULL DEFAULT '',
    full_name VARCHAR(100) NOT NULL DEFAULT 'admin',
    phone VARCHAR(20) NOT NULL DEFAULT '',
    avatar_url VARCHAR(255) NOT NULL DEFAULT '',
    role_id INT NOT NULL DEFAULT 0,
    status SMALLINT NOT NULL DEFAULT 0,
    last_login_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_ip VARCHAR(50) NOT NULL DEFAULT '',
    login_count INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_syn_admins_name ON syn_admins(name);
CREATE UNIQUE INDEX IF NOT EXISTS idx_syn_admins_mail ON syn_admins(mail);
CREATE INDEX IF NOT EXISTS idx_syn_admins_status ON syn_admins(status);
CREATE INDEX IF NOT EXISTS idx_syn_admins_role_id ON syn_admins(role_id);
CREATE INDEX IF NOT EXISTS idx_syn_admins_created_at ON syn_admins(created_at);

-- 创建注释
COMMENT ON TABLE syn_admins IS '管理员信息表，存储系统管理员的基本信息、认证信息和状态';
COMMENT ON COLUMN syn_admins.id IS '管理员唯一标识符，自增主键，起始值为1000';
COMMENT ON COLUMN syn_admins.name IS '管理员用户名，唯一，用于登录认证';
COMMENT ON COLUMN syn_admins.mail IS '管理员邮箱地址，唯一，用于登录和通知';
COMMENT ON COLUMN syn_admins.password IS '密码哈希值，使用bcrypt等安全算法加密';
COMMENT ON COLUMN syn_admins.full_name IS '管理员真实姓名';
COMMENT ON COLUMN syn_admins.phone IS '管理员联系电话';
COMMENT ON COLUMN syn_admins.avatar_url IS '管理员头像图片URL地址';
COMMENT ON COLUMN syn_admins.role_id IS '角色ID，关联角色表，定义管理员权限级别';
COMMENT ON COLUMN syn_admins.status IS '管理员状态：0-禁用，1-启用，2-暂停';
COMMENT ON COLUMN syn_admins.last_login_at IS '最后登录时间，带时区信息';
COMMENT ON COLUMN syn_admins.last_login_ip IS '最后登录IP地址';
COMMENT ON COLUMN syn_admins.login_count IS '登录次数统计';
COMMENT ON COLUMN syn_admins.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN syn_admins.updated_at IS '记录更新时间，通过触发器自动更新';

-- 创建触发器
CREATE TRIGGER update_syn_admins_updated_at
    BEFORE UPDATE ON syn_admins
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
-- 注意：密码哈希在实际应用中应该使用 bcrypt 等安全算法
INSERT INTO syn_admins (name, mail, password, full_name, phone, avatar_url, role_id, status, last_login_at, login_count,last_login_ip) VALUES
('admin001', 'admin001@example.com', '4448aea0cb4e769a2244916c3486a4e9', '张三', '+86-138-0010-0001', 'https://example.com/avatars/admin001.jpg', 10000, 1, '2024-01-15 10:30:00+08', 156,'127.0.0.1'),
('admin002', 'admin002@example.com', '$2b$10$example.hash.here', '李四', '+86-138-0010-0002', 'https://example.com/avatars/admin002.jpg', 10000, 1, '2024-01-14 14:20:00+08', 89,'127.0.0.1'),
('admin003', 'admin003@example.com', '$2b$10$example.hash.here', '王五', '+86-138-0010-0003', 'https://example.com/avatars/admin003.jpg', 10002, 1, '2024-01-13 09:15:00+08', 234,'127.0.0.1'),
('moderator001', 'moderator001@example.com', '$2b$10$example.hash.here', '赵六', '+86-138-0010-0004', 'https://example.com/avatars/moderator001.jpg', 10000, 1, '2024-01-12 16:45:00+08', 67,'127.0.0.1'),
('admin004', 'admin004@example.com', '$2b$10$example.hash.here', '钱七', '+86-138-0010-0005', 'https://example.com/avatars/admin004.jpg', 10001, 0, '2024-01-10 11:30:00+08', 45,'127.0.0.1'),
('admin005', 'admin005@example.com', '$2b$10$example.hash.here', '孙八', '+86-138-0010-0006', 'https://example.com/avatars/admin005.jpg', 10000, 2, '2024-01-08 13:20:00+08', 123,'127.0.0.1');
