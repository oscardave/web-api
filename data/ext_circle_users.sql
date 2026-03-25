-- 圈子成员表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_circle_users_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建圈子成员表
DROP TABLE IF EXISTS ext_circle_users;
CREATE TABLE IF NOT EXISTS ext_circle_users (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_circle_users_id_seq'),
    circle_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 圈子ID（关联圈子表）
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    user_nickname VARCHAR(100) NOT NULL DEFAULT '',               -- 用户在圈子中的昵称
    user_avatar_url TEXT NOT NULL DEFAULT '',                     -- 用户头像URL
    user_vanity_id VARCHAR(100) NOT NULL DEFAULT '',              -- 用户靓号

    -- 成员角色和权限管理
    role_type VARCHAR(50) NOT NULL DEFAULT 'member',              -- 角色类型：owner-圈主，admin-管理员，member-普通成员
    permissions TEXT NOT NULL DEFAULT '',                         -- 用户权限（JSON格式存储）
    can_view_help BOOLEAN NOT NULL DEFAULT true,                  -- 是否允许查看帮办
    can_post_help BOOLEAN NOT NULL DEFAULT true,                  -- 是否允许发布帮办

    -- 成员限制状态
    help_view_prohibited BOOLEAN NOT NULL DEFAULT false,          -- 是否被禁止查看圈内帮办
    help_view_prohibit_duration INTEGER NOT NULL DEFAULT 0,       -- 帮办查看禁止时长（1/3/15天）
    help_view_prohibit_expire_time TIMESTAMP WITH TIME ZONE,      -- 帮办查看禁止到期时间
    help_post_prohibited BOOLEAN NOT NULL DEFAULT false,          -- 是否被禁止发布圈内帮办
    help_post_prohibit_duration INTEGER NOT NULL DEFAULT 0,       -- 帮办发布禁止时长（1/3/15天）
    help_post_prohibit_expire_time TIMESTAMP WITH TIME ZONE,      -- 帮办发布禁止到期时间

    -- 成员状态管理
    member_status VARCHAR(50) NOT NULL DEFAULT 'active',          -- 成员状态：active-正常，kicked-被踢出，left-主动离开，banned-被禁言
    join_method VARCHAR(50) NOT NULL DEFAULT 'invite',            -- 入圈方式：invite_code-邀请码，recommend-推荐，direct-直接加入

    -- 成员活跃状态
    last_login_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后登录时间
    last_activity_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后活跃时间
    is_over_month_inactive BOOLEAN NOT NULL DEFAULT false,        -- 是否超过1个月未登录
    is_weekly_active BOOLEAN NOT NULL DEFAULT false,              -- 是否属于周活跃用户
    is_monthly_active BOOLEAN NOT NULL DEFAULT false,             -- 是否属于月活跃用户

    -- 成员统计信息
    help_post_count INTEGER NOT NULL DEFAULT 0,                   -- 在圈子中发布的帮办数量
    last_help_post_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后发布帮办时间
    daily_active_count INTEGER NOT NULL DEFAULT 0,                -- 今日活跃次数
    weekly_active_count INTEGER NOT NULL DEFAULT 0,               -- 本周活跃次数
    monthly_active_count INTEGER NOT NULL DEFAULT 0,              -- 本月活跃次数

    -- 邀请关系管理
    inviter_id VARCHAR(255) NOT NULL DEFAULT '',                  -- 入圈邀请人ID
    inviter_nickname VARCHAR(100) NOT NULL DEFAULT '',            -- 邀请人昵称
    inviter_vanity_id VARCHAR(100) NOT NULL DEFAULT '',           -- 邀请人靓号
    circle_recommendation TEXT NOT NULL DEFAULT '',                -- 圈子推荐关系（JSON格式）
    circle_relationship TEXT NOT NULL DEFAULT '',                  -- 加圈关系（JSON格式）

    -- 时间信息
    join_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 加入圈子时间
    created_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 记录创建时间

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                          -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                      -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_circle_users_circle_user ON ext_circle_users(circle_id, user_id);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_user_id ON ext_circle_users(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_circle_id ON ext_circle_users(circle_id);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_role_type ON ext_circle_users(role_type);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_member_status ON ext_circle_users(member_status);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_join_time ON ext_circle_users(join_time);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_last_activity_time ON ext_circle_users(last_activity_time);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_last_login_time ON ext_circle_users(last_login_time);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_user_vanity_id ON ext_circle_users(user_vanity_id);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_inviter_id ON ext_circle_users(inviter_id);
CREATE INDEX IF NOT EXISTS idx_ext_circle_users_status ON ext_circle_users(status);

-- 创建注释
COMMENT ON TABLE ext_circle_users IS '圈子成员表，存储圈子中所有成员的信息和状态';
COMMENT ON COLUMN ext_circle_users.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_circle_users.circle_id IS '圈子ID，关联圈子表';
COMMENT ON COLUMN ext_circle_users.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_circle_users.user_nickname IS '用户在圈子中的昵称';
COMMENT ON COLUMN ext_circle_users.user_avatar_url IS '用户头像URL';
COMMENT ON COLUMN ext_circle_users.user_vanity_id IS '用户靓号';
COMMENT ON COLUMN ext_circle_users.role_type IS '角色类型：owner-圈主，admin-管理员，member-普通成员';
COMMENT ON COLUMN ext_circle_users.permissions IS '用户权限（JSON格式存储）';
COMMENT ON COLUMN ext_circle_users.can_view_help IS '是否允许查看帮办';
COMMENT ON COLUMN ext_circle_users.can_post_help IS '是否允许发布帮办';
COMMENT ON COLUMN ext_circle_users.help_view_prohibited IS '是否被禁止查看圈内帮办';
COMMENT ON COLUMN ext_circle_users.help_view_prohibit_duration IS '帮办查看禁止时长（1/3/15天）';
COMMENT ON COLUMN ext_circle_users.help_view_prohibit_expire_time IS '帮办查看禁止到期时间';
COMMENT ON COLUMN ext_circle_users.help_post_prohibited IS '是否被禁止发布圈内帮办';
COMMENT ON COLUMN ext_circle_users.help_post_prohibit_duration IS '帮办发布禁止时长（1/3/15天）';
COMMENT ON COLUMN ext_circle_users.help_post_prohibit_expire_time IS '帮办发布禁止到期时间';
COMMENT ON COLUMN ext_circle_users.member_status IS '成员状态：active-正常，kicked-被踢出，left-主动离开，banned-被禁言';
COMMENT ON COLUMN ext_circle_users.join_method IS '入圈方式：invite_code-邀请码，recommend-推荐，direct-直接加入';
COMMENT ON COLUMN ext_circle_users.last_login_time IS '最后登录时间';
COMMENT ON COLUMN ext_circle_users.last_activity_time IS '最后活跃时间';
COMMENT ON COLUMN ext_circle_users.is_over_month_inactive IS '是否超过1个月未登录';
COMMENT ON COLUMN ext_circle_users.is_weekly_active IS '是否属于周活跃用户';
COMMENT ON COLUMN ext_circle_users.is_monthly_active IS '是否属于月活跃用户';
COMMENT ON COLUMN ext_circle_users.help_post_count IS '在圈子中发布的帮办数量';
COMMENT ON COLUMN ext_circle_users.last_help_post_time IS '最后发布帮办时间';
COMMENT ON COLUMN ext_circle_users.daily_active_count IS '今日活跃次数';
COMMENT ON COLUMN ext_circle_users.weekly_active_count IS '本周活跃次数';
COMMENT ON COLUMN ext_circle_users.monthly_active_count IS '本月活跃次数';
COMMENT ON COLUMN ext_circle_users.inviter_id IS '入圈邀请人ID';
COMMENT ON COLUMN ext_circle_users.inviter_nickname IS '邀请人昵称';
COMMENT ON COLUMN ext_circle_users.inviter_vanity_id IS '邀请人靓号';
COMMENT ON COLUMN ext_circle_users.circle_recommendation IS '圈子推荐关系（JSON格式）';
COMMENT ON COLUMN ext_circle_users.circle_relationship IS '加圈关系（JSON格式）';
COMMENT ON COLUMN ext_circle_users.join_time IS '加入圈子时间';
COMMENT ON COLUMN ext_circle_users.created_time IS '记录创建时间';
COMMENT ON COLUMN ext_circle_users.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_circle_users.remark IS '备注信息';
COMMENT ON COLUMN ext_circle_users.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_circle_users.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_circle_users_updated_at
    BEFORE UPDATE ON ext_circle_users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_circle_users (circle_id, user_id, user_nickname, user_avatar_url, user_vanity_id, role_type, member_status, join_method, help_post_count, inviter_id, inviter_nickname, inviter_vanity_id, status, remark) VALUES
('!circle1:example.com', '@user1:example.com', '测试用户1', 'https://example.com/avatar1.jpg', 'user001', 'owner', 'active', 'direct', 45, '', '', '', 1, '圈主'),
('!circle1:example.com', '@user2:example.com', '测试用户2', 'https://example.com/avatar2.jpg', 'user002', 'admin', 'active', 'invite_code', 23, '@user1:example.com', '测试用户1', 'user001', 1, '管理员'),
('!circle1:example.com', '@user3:example.com', '测试用户3', 'https://example.com/avatar3.jpg', 'user003', 'member', 'active', 'recommend', 12, '@user1:example.com', '测试用户1', 'user001', 1, '普通成员'),
('!circle2:example.com', '@user2:example.com', '测试用户2', 'https://example.com/avatar2.jpg', 'user002', 'owner', 'active', 'direct', 67, '', '', '', 1, '圈主'),
('!circle2:example.com', '@user1:example.com', '测试用户1', 'https://example.com/avatar1.jpg', 'user001', 'member', 'active', 'invite_code', 34, '@user2:example.com', '测试用户2', 'user002', 1, '普通成员');
