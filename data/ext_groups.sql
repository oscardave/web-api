-- 群聊扩展信息表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_groups_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建群聊扩展信息表
DROP TABLE IF EXISTS ext_groups;
CREATE TABLE IF NOT EXISTS ext_groups (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_groups_id_seq'),
    group_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 群聊ID（关联主群聊表）
    group_name VARCHAR(200) NOT NULL DEFAULT '',                 -- 群聊名称（后台可更改编辑）
    group_avatar_url TEXT NOT NULL DEFAULT '',                   -- 群聊头像URL（后台可更改编辑）
    group_description TEXT NOT NULL DEFAULT '',                  -- 群聊描述
    group_type VARCHAR(50) NOT NULL DEFAULT 'public',            -- 群聊类型：public-公开群，private-私密群

    -- 群主信息
    owner_id VARCHAR(255) NOT NULL DEFAULT '',                   -- 群主ID
    owner_nickname VARCHAR(100) NOT NULL DEFAULT '',             -- 群主昵称
    owner_vanity_id VARCHAR(100) NOT NULL DEFAULT '',            -- 群主靓号

    -- 群聊状态管理
    group_status VARCHAR(50) NOT NULL DEFAULT 'normal',          -- 群状态：normal-正常，banned-封禁
    member_limit INTEGER NOT NULL DEFAULT 200,                   -- 群人数上限：100/200/500/3000/5000/10000/12000
    current_member_count INTEGER NOT NULL DEFAULT 0,             -- 当前成员人数
    join_restriction_type VARCHAR(50) NOT NULL DEFAULT 'unlimited', -- 群限制类型：unlimited-不限制，specified-指定用户限制

    -- 群聊设置
    allow_member_invite BOOLEAN NOT NULL DEFAULT true,           -- 是否允许成员邀请
    allow_member_edit_info BOOLEAN NOT NULL DEFAULT false,       -- 是否允许成员编辑群信息
    auto_approve_join BOOLEAN NOT NULL DEFAULT true,             -- 是否自动批准加入

    -- 群聊统计信息
    total_message_count BIGINT NOT NULL DEFAULT 0,               -- 总消息数量
    daily_active_members INTEGER NOT NULL DEFAULT 0,             -- 日活跃成员数
    weekly_active_members INTEGER NOT NULL DEFAULT 0,            -- 周活跃成员数

    -- 时间信息
    created_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 群聊创建时间
    last_activity_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后活跃时间
    last_message_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 最后消息时间

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                         -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                     -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_groups_group_id ON ext_groups(group_id);
CREATE INDEX IF NOT EXISTS idx_ext_groups_group_name ON ext_groups(group_name);
CREATE INDEX IF NOT EXISTS idx_ext_groups_owner_id ON ext_groups(owner_id);
CREATE INDEX IF NOT EXISTS idx_ext_groups_owner_vanity_id ON ext_groups(owner_vanity_id);
CREATE INDEX IF NOT EXISTS idx_ext_groups_group_status ON ext_groups(group_status);
CREATE INDEX IF NOT EXISTS idx_ext_groups_join_restriction_type ON ext_groups(join_restriction_type);
CREATE INDEX IF NOT EXISTS idx_ext_groups_member_limit ON ext_groups(member_limit);
CREATE INDEX IF NOT EXISTS idx_ext_groups_current_member_count ON ext_groups(current_member_count);
CREATE INDEX IF NOT EXISTS idx_ext_groups_created_time ON ext_groups(created_time);
CREATE INDEX IF NOT EXISTS idx_ext_groups_last_activity_time ON ext_groups(last_activity_time);
CREATE INDEX IF NOT EXISTS idx_ext_groups_status ON ext_groups(status);

-- 创建注释
COMMENT ON TABLE ext_groups IS '群聊扩展信息表，存储系统中群聊的详细信息和扩展属性';
COMMENT ON COLUMN ext_groups.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_groups.group_id IS '群聊ID，关联主群聊表，唯一';
COMMENT ON COLUMN ext_groups.group_name IS '群聊名称，后台可更改编辑';
COMMENT ON COLUMN ext_groups.group_avatar_url IS '群聊头像URL，后台可更改编辑';
COMMENT ON COLUMN ext_groups.group_description IS '群聊描述';
COMMENT ON COLUMN ext_groups.group_type IS '群聊类型：public-公开群，private-私密群';
COMMENT ON COLUMN ext_groups.owner_id IS '群主ID';
COMMENT ON COLUMN ext_groups.owner_nickname IS '群主昵称';
COMMENT ON COLUMN ext_groups.owner_vanity_id IS '群主靓号';
COMMENT ON COLUMN ext_groups.group_status IS '群状态：normal-正常，banned-封禁';
COMMENT ON COLUMN ext_groups.member_limit IS '群人数上限：100/200/500/3000/5000/10000/12000';
COMMENT ON COLUMN ext_groups.current_member_count IS '当前成员人数';
COMMENT ON COLUMN ext_groups.join_restriction_type IS '群限制类型：unlimited-不限制，specified-指定用户限制';
COMMENT ON COLUMN ext_groups.allow_member_invite IS '是否允许成员邀请';
COMMENT ON COLUMN ext_groups.allow_member_edit_info IS '是否允许成员编辑群信息';
COMMENT ON COLUMN ext_groups.auto_approve_join IS '是否自动批准加入';
COMMENT ON COLUMN ext_groups.total_message_count IS '总消息数量';
COMMENT ON COLUMN ext_groups.daily_active_members IS '日活跃成员数';
COMMENT ON COLUMN ext_groups.weekly_active_members IS '周活跃成员数';
COMMENT ON COLUMN ext_groups.created_time IS '群聊创建时间';
COMMENT ON COLUMN ext_groups.last_activity_time IS '最后活跃时间';
COMMENT ON COLUMN ext_groups.last_message_time IS '最后消息时间';
COMMENT ON COLUMN ext_groups.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_groups.remark IS '备注信息';
COMMENT ON COLUMN ext_groups.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_groups.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_groups_updated_at
    BEFORE UPDATE ON ext_groups
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_groups (group_id, group_name, group_avatar_url, group_description, group_type, owner_id, owner_nickname, owner_vanity_id, member_limit, current_member_count, join_restriction_type, total_message_count, daily_active_members, weekly_active_members, status, remark) VALUES
('!group1:example.com', '测试群聊1', 'https://example.com/group1.jpg', '这是一个测试群聊', 'public', '@user1:example.com', '测试用户1', 'user001', 200, 45, 'unlimited', 1250, 25, 38, 1, '测试群聊数据'),
('!group2:example.com', '测试群聊2', 'https://example.com/group2.jpg', '另一个测试群聊', 'private', '@user2:example.com', '测试用户2', 'user002', 500, 128, 'specified', 3200, 89, 112, 1, '测试群聊数据'),
('!group3:example.com', '测试群聊3', 'https://example.com/group3.jpg', '第三个测试群聊', 'public', '@user3:example.com', '测试用户3', 'user003', 100, 67, 'unlimited', 890, 42, 58, 1, '测试群聊数据');
