-- 验证码表创建脚本（合并邮箱和电话验证码）
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_verify_codes_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建验证码表
DROP TABLE IF EXISTS ext_verify_codes;
CREATE TABLE IF NOT EXISTS ext_verify_codes (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_verify_codes_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,                                     -- 用户ID（关联用户表）

    -- 联系方式字段
    contact_type VARCHAR(20) NOT NULL DEFAULT '',                 -- 联系方式类型：mail-邮箱, phone-电话
    contact_value VARCHAR(100) NOT NULL DEFAULT '',               -- 联系方式值（邮箱地址或电话号码）

    -- 验证码字段
    verify_code VARCHAR(10) NOT NULL DEFAULT '',                 -- 验证码（6-8位数字或字母）
    verify_type VARCHAR(50) NOT NULL DEFAULT '',                 -- 验证类型：register-注册, reset_password-重置密码, bind_account-绑定账号等

    -- 状态字段
    is_used BOOLEAN NOT NULL DEFAULT false,                     -- 是否已使用
    is_expired BOOLEAN NOT NULL DEFAULT false,                   -- 是否已过期
    request_ip VARCHAR(32) NOT NULL DEFAULT '',                 -- 客户端IP

    -- 时间字段
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 创建时间
    expired_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '5 minutes'),  -- 过期时间（默认创建时间+5分钟）
    used_at TIMESTAMP WITH TIME ZONE,                              -- 使用时间

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                          -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                      -- 备注
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_user_id ON ext_verify_codes(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_contact_type ON ext_verify_codes(contact_type);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_contact_value ON ext_verify_codes(contact_value);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_verify_type ON ext_verify_codes(verify_type);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_verify_code ON ext_verify_codes(verify_code);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_created_at ON ext_verify_codes(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_expired_at ON ext_verify_codes(expired_at);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_status ON ext_verify_codes(status);
CREATE INDEX IF NOT EXISTS idx_ext_verify_codes_request_ip ON ext_verify_codes(request_ip);

-- 创建注释
COMMENT ON TABLE ext_verify_codes IS '验证码表，存储用户的邮箱和电话验证码信息';
COMMENT ON COLUMN ext_verify_codes.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_verify_codes.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_verify_codes.contact_type IS '联系方式类型：mail-邮箱, phone-电话';
COMMENT ON COLUMN ext_verify_codes.contact_value IS '联系方式值（邮箱地址或电话号码）';
COMMENT ON COLUMN ext_verify_codes.verify_code IS '验证码，6-8位数字或字母';
COMMENT ON COLUMN ext_verify_codes.verify_type IS '验证类型：register-注册, reset_password-重置密码, bind_account-绑定账号等';
COMMENT ON COLUMN ext_verify_codes.is_used IS '是否已使用';
COMMENT ON COLUMN ext_verify_codes.is_expired IS '是否已过期';
COMMENT ON COLUMN ext_verify_codes.request_ip IS '请求IP';
COMMENT ON COLUMN ext_verify_codes.created_at IS '创建时间';
COMMENT ON COLUMN ext_verify_codes.expired_at IS '过期时间（默认创建时间+5分钟）';
COMMENT ON COLUMN ext_verify_codes.used_at IS '使用时间';
COMMENT ON COLUMN ext_verify_codes.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_verify_codes.remark IS '备注信息';
COMMENT ON COLUMN ext_verify_codes.updated_at IS '记录更新时间';

-- 创建触发器，自动更新更新时间
CREATE OR REPLACE TRIGGER update_ext_verify_codes_updated_at
    BEFORE UPDATE ON ext_verify_codes
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 创建函数：将过期时间大于当前时间的记录status改为2
CREATE OR REPLACE FUNCTION fn_set_verify_codes_expired()
RETURNS INTEGER AS $$
DECLARE
    affected_rows INTEGER;
BEGIN
    -- 更新过期记录的status为2
    UPDATE ext_verify_codes
    SET status = 2,
        updated_at = CURRENT_TIMESTAMP,
        is_expired = true
    WHERE expired_at < CURRENT_TIMESTAMP
      AND status = 1;

    -- 获取影响的行数
    GET DIAGNOSTICS affected_rows = ROW_COUNT;

    -- 返回影响的行数
    RETURN affected_rows;
END;
$$ LANGUAGE plpgsql;

-- 创建函数注释
COMMENT ON FUNCTION fn_set_verify_codes_expired() IS '将过期时间大于当前时间的验证码记录状态改为无效(status=2)';

-- 插入测试数据
-- 邮箱验证码测试数据
INSERT INTO ext_verify_codes (user_id, contact_type, contact_value, verify_code, verify_type, status, remark, request_ip) VALUES
(10000, 'mail', 'user1@example.com', '123456', 'register', 1, '测试邮箱验证码', '127.0.0.1'),
(10001, 'mail', 'user2@example.com', '654321', 'reset_password', 1, '测试重置密码验证码', '127.0.0.1'),
(10002, 'mail', 'user3@example.com', '888888', 'bind_account', 1, '测试绑定账号验证码', '127.0.0.1'),
(10003, 'mail', 'user4@example.com', '123456', 'register', 1, '测试邮箱验证码', '127.0.0.1'),
(10004, 'mail', 'user5@example.com', '654321', 'reset_password', 1, '测试重置密码验证码', '127.0.0.1'),
(10005, 'mail', 'user6@example.com', '888888', 'bind_account', 1, '测试绑定账号验证码', '127.0.0.1');

-- 电话验证码测试数据
INSERT INTO ext_verify_codes (user_id, contact_type, contact_value, verify_code, verify_type, status, remark, request_ip) VALUES
(10006, 'phone', '13800138001', '123456', 'register', 1, '测试电话验证码', '127.0.0.1'),
(10007, 'phone', '13800138002', '654321', 'reset_password', 1, '测试重置密码验证码', '127.0.0.1'),
(10008, 'phone', '13800138003', '888888', 'bind_account', 1, '测试绑定账号验证码', '127.0.0.1'),
(10009, 'phone', '13800138004', '123456', 'register', 1, '测试电话验证码', '127.0.0.1'),
(10010, 'phone', '13800138005', '654321', 'reset_password', 1, '测试重置密码验证码', '127.0.0.1'),
(10011, 'phone', '13800138006', '888888', 'bind_account', 1, '测试绑定账号验证码', '127.0.0.1');
