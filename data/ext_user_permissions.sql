-- 用户权限关系表创建脚本
-- 设置默认起始索引为 10000
-- 从 ext_user_blocks 表重构而来，增加了 note_access 字段用于控制笔记访问权限

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_permissions_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户权限关系表
DROP TABLE IF EXISTS ext_user_permissions;
CREATE TABLE IF NOT EXISTS ext_user_permissions (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_permissions_id_seq'),
    user_id VARCHAR(255) NOT NULL DEFAULT '',                        -- 用户ID（控制权限的用户）
    target_id VARCHAR(255) NOT NULL DEFAULT '',                       -- 目标用户ID（被控制权限的用户）
    remark VARCHAR(255) NOT NULL DEFAULT '',                          -- 备注信息
    note_access BOOLEAN NOT NULL DEFAULT true,                       -- 笔记访问权限：true-允许访问，false-禁止访问
    status SMALLINT NOT NULL DEFAULT 1,                              -- 状态：1-拉黑中，2-已解除拉黑
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,   -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP    -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_permissions_user_pair ON ext_user_permissions(user_id, target_id) WHERE status = 1;
CREATE INDEX IF NOT EXISTS idx_ext_user_permissions_user_id ON ext_user_permissions(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_permissions_target_id ON ext_user_permissions(target_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_permissions_status ON ext_user_permissions(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_permissions_created_at ON ext_user_permissions(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_permissions_note_access ON ext_user_permissions(note_access);

-- 创建注释
COMMENT ON TABLE ext_user_permissions IS '用户权限关系表，存储用户之间的权限控制关系，包括拉黑关系和笔记访问权限';
COMMENT ON COLUMN ext_user_permissions.id IS '权限关系唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_permissions.user_id IS '用户ID，关联ext_users表的user_id字段（控制权限的用户）';
COMMENT ON COLUMN ext_user_permissions.target_id IS '目标用户ID，关联ext_users表的user_id字段（被控制权限的用户）';
COMMENT ON COLUMN ext_user_permissions.remark IS '备注信息';
COMMENT ON COLUMN ext_user_permissions.note_access IS '笔记访问权限：true-允许访问，false-禁止访问';
COMMENT ON COLUMN ext_user_permissions.status IS '状态：1-拉黑中，2-已解除拉黑';
COMMENT ON COLUMN ext_user_permissions.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_permissions.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_permissions_updated_at
    BEFORE UPDATE ON ext_user_permissions
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

