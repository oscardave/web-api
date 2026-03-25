-- 群成员表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_group_users_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建群成员表
DROP TABLE IF EXISTS ext_group_users;
CREATE TABLE IF NOT EXISTS ext_group_users (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_group_users_id_seq'),
    group_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 群聊ID（关联群聊表）
    user_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 用户ID（关联用户表）
    user_nickname VARCHAR(100) NOT NULL DEFAULT '',              -- 用户在群中的昵称
    user_avatar_url TEXT NOT NULL DEFAULT '',                    -- 用户头像URL
    user_vanity_id VARCHAR(100) NOT NULL DEFAULT '',             -- 用户靓号

    -- 用户在群中的角色和权限
    role_type VARCHAR(50) NOT NULL DEFAULT 'member',             -- 角色类型：owner-群主，admin-管理员，member-普通成员
    permissions TEXT NOT NULL DEFAULT '',                        -- 用户权限（JSON格式存储）
    is_muted BOOLEAN NOT NULL DEFAULT false,                     -- 是否被禁言
    mute_expire_time TIMESTAMP WITH TIME ZONE,                   -- 禁言到期时间

    -- 成员状态管理
    member_status VARCHAR(50) NOT NULL DEFAULT 'active',         -- 成员状态：active-正常，kicked-被踢出，left-主动离开，banned-被禁言
    join_method VARCHAR(50) NOT NULL DEFAULT 'invite',           -- 加入方式：invite-邀请，apply-申请，direct-直接加入

    -- 成员统计信息
    message_count INTEGER NOT NULL DEFAULT 0,                    -- 在群中发送的消息数量
    last_message_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后发送消息时间
    daily_message_count INTEGER NOT NULL DEFAULT 0,              -- 今日发送消息数量

    -- 时间信息
    join_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 加入群聊时间
    last_activity_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后活跃时间
    last_read_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,     -- 最后阅读时间

    -- 邀请人信息
    inviter_id VARCHAR(255) NOT NULL DEFAULT '',                 -- 邀请人ID
    inviter_nickname VARCHAR(100) NOT NULL DEFAULT '',           -- 邀请人昵称

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                         -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                     -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_group_users_group_user ON ext_group_users(group_id, user_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_user_id ON ext_group_users(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_group_id ON ext_group_users(group_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_role_type ON ext_group_users(role_type);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_member_status ON ext_group_users(member_status);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_join_time ON ext_group_users(join_time);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_last_activity_time ON ext_group_users(last_activity_time);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_user_vanity_id ON ext_group_users(user_vanity_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_users_status ON ext_group_users(status);

-- 创建注释
COMMENT ON TABLE ext_group_users IS '群成员表，存储群聊中所有成员的信息和状态';
COMMENT ON COLUMN ext_group_users.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_group_users.group_id IS '群聊ID，关联群聊表';
COMMENT ON COLUMN ext_group_users.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_group_users.user_nickname IS '用户在群中的昵称';
COMMENT ON COLUMN ext_group_users.user_avatar_url IS '用户头像URL';
COMMENT ON COLUMN ext_group_users.user_vanity_id IS '用户靓号';
COMMENT ON COLUMN ext_group_users.role_type IS '角色类型：owner-群主，admin-管理员，member-普通成员';
COMMENT ON COLUMN ext_group_users.permissions IS '用户权限（JSON格式存储）';
COMMENT ON COLUMN ext_group_users.is_muted IS '是否被禁言';
COMMENT ON COLUMN ext_group_users.mute_expire_time IS '禁言到期时间';
COMMENT ON COLUMN ext_group_users.member_status IS '成员状态：active-正常，kicked-被踢出，left-主动离开，banned-被禁言';
COMMENT ON COLUMN ext_group_users.join_method IS '加入方式：invite-邀请，apply-申请，direct-直接加入';
COMMENT ON COLUMN ext_group_users.message_count IS '在群中发送的消息数量';
COMMENT ON COLUMN ext_group_users.last_message_time IS '最后发送消息时间';
COMMENT ON COLUMN ext_group_users.daily_message_count IS '今日发送消息数量';
COMMENT ON COLUMN ext_group_users.join_time IS '加入群聊时间';
COMMENT ON COLUMN ext_group_users.last_activity_time IS '最后活跃时间';
COMMENT ON COLUMN ext_group_users.last_read_time IS '最后阅读时间';
COMMENT ON COLUMN ext_group_users.inviter_id IS '邀请人ID';
COMMENT ON COLUMN ext_group_users.inviter_nickname IS '邀请人昵称';
COMMENT ON COLUMN ext_group_users.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_group_users.remark IS '备注信息';
COMMENT ON COLUMN ext_group_users.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_group_users.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_group_users_updated_at
    BEFORE UPDATE ON ext_group_users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_group_users (group_id, user_id, user_nickname, user_avatar_url, user_vanity_id, role_type, member_status, join_method, message_count, inviter_id, inviter_nickname, status, remark) VALUES
('!group1:example.com', '@user1:example.com', '测试用户1', 'https://example.com/avatar1.jpg', 'user001', 'owner', 'active', 'direct', 156, '', '', 1, '群主'),
('!group1:example.com', '@user2:example.com', '测试用户2', 'https://example.com/avatar2.jpg', 'user002', 'admin', 'active', 'invite', 89, '@user1:example.com', '测试用户1', 1, '管理员'),
('!group1:example.com', '@user3:example.com', '测试用户3', 'https://example.com/avatar3.jpg', 'user003', 'member', 'active', 'invite', 45, '@user1:example.com', '测试用户1', 1, '普通成员'),
('!group2:example.com', '@user2:example.com', '测试用户2', 'https://example.com/avatar2.jpg', 'user002', 'owner', 'active', 'direct', 234, '', '', 1, '群主'),
('!group2:example.com', '@user1:example.com', '测试用户1', 'https://example.com/avatar1.jpg', 'user001', 'member', 'active', 'invite', 67, '@user2:example.com', '测试用户2', 1, '普通成员');
