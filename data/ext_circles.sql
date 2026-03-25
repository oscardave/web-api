-- 圈子扩展信息表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_circles_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建圈子扩展信息表
DROP TABLE IF EXISTS ext_circles;
CREATE TABLE IF NOT EXISTS ext_circles (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_circles_id_seq'),
    circle_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 圈子ID（关联主圈子表）
    circle_name VARCHAR(200) NOT NULL DEFAULT '',                 -- 圈子名称
    circle_avatar_url TEXT NOT NULL DEFAULT '',                   -- 圈子头像/配图URL
    circle_description TEXT NOT NULL DEFAULT '',                  -- 圈子描述
    circle_announcement TEXT NOT NULL DEFAULT '',                 -- 圈子公告
    circle_type VARCHAR(50) NOT NULL DEFAULT 'public',            -- 圈子类型：public-公开圈子，private-私密圈子

    -- 圈主信息
    owner_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 圈主ID
    owner_nickname VARCHAR(100) NOT NULL DEFAULT '',              -- 圈主昵称
    owner_vanity_id VARCHAR(100) NOT NULL DEFAULT '',             -- 圈主靓号
    allow_change_owner BOOLEAN NOT NULL DEFAULT true,             -- 是否允许更换圈主

    -- 圈子状态管理
    circle_status VARCHAR(50) NOT NULL DEFAULT 'normal',          -- 圈子状态：normal-正常，hidden-隐藏，disbanded-已解散
    member_limit INTEGER NOT NULL DEFAULT 5000,                   -- 圈子最高限制人数：5000/10000/12000
    current_member_count INTEGER NOT NULL DEFAULT 0,              -- 当前成员总数
    current_member_count_vip INTEGER NOT NULL DEFAULT 0,          -- 当前会员总数

    -- 圈子统计信息
    today_new_members INTEGER NOT NULL DEFAULT 0,                 -- 今日新增人数
    daily_active_members INTEGER NOT NULL DEFAULT 0,              -- 日活人数
    weekly_active_members INTEGER NOT NULL DEFAULT 0,             -- 周活人数
    monthly_active_members INTEGER NOT NULL DEFAULT 0,            -- 月活人数
    help_posts_24h INTEGER NOT NULL DEFAULT 0,                   -- 24小时内帮办发布数
    total_help_posts BIGINT NOT NULL DEFAULT 0,                  -- 总帮办发布数

    -- 入圈限制管理
    invitation_code_count INTEGER NOT NULL DEFAULT 5,             -- 邀请码数量设置（1-20个）
    join_user_level_restriction TEXT NOT NULL DEFAULT '',         -- 入圈用户等级限制（JSON格式：普通/初级/高级/超级/至尊/纯发，可多选）
    help_message_view_restriction TEXT NOT NULL DEFAULT '',       -- 圈内帮办大厅查看帮办消息限制（JSON格式：普通/初级/高级/超级/至尊/纯发，可多选）
    help_message_post_restriction TEXT NOT NULL DEFAULT '',       -- 圈内帮办大厅发布帮办消息限制（JSON格式：普通/初级/高级/超级/至尊/纯发，可多选）

    -- 管理员信息
    admin_ids TEXT NOT NULL DEFAULT '',                           -- 管理员ID列表（JSON格式存储）
    admin_nicknames TEXT NOT NULL DEFAULT '',                     -- 管理员昵称列表（JSON格式存储）
    allow_remove_admin BOOLEAN NOT NULL DEFAULT true,             -- 是否允许取消管理员

    -- 圈子设置
    allow_member_invite BOOLEAN NOT NULL DEFAULT true,            -- 是否允许成员邀请
    allow_member_edit_info BOOLEAN NOT NULL DEFAULT false,        -- 是否允许成员编辑圈子信息
    auto_approve_join BOOLEAN NOT NULL DEFAULT false,             -- 是否自动批准加入（需要审核）

    -- 时间信息
    created_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 圈子创建时间
    last_activity_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后活跃时间
    last_help_post_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 最后帮办发布时间
    apply_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,   -- 申请时间（待审列表展示）
    reviewed_at TIMESTAMP WITH TIME ZONE,                           -- 审核时间（通过/拒绝时写入）

    -- 系统字段（status：0-待审核，1-已通过，2-无效，3-已拒绝，4-已取消）
    status SMALLINT NOT NULL DEFAULT 0,                          -- 记录状态：0-待审核，1-已通过，2-无效，3-已拒绝，4-已取消
    reject_reason TEXT NOT NULL DEFAULT '',                      -- 拒绝原因（status=3 时填写）
    remark VARCHAR(255) NOT NULL DEFAULT '',                      -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_circles_circle_id ON ext_circles(circle_id);
CREATE INDEX IF NOT EXISTS idx_ext_circles_circle_name ON ext_circles(circle_name);
CREATE INDEX IF NOT EXISTS idx_ext_circles_owner_id ON ext_circles(owner_id);
CREATE INDEX IF NOT EXISTS idx_ext_circles_owner_vanity_id ON ext_circles(owner_vanity_id);
CREATE INDEX IF NOT EXISTS idx_ext_circles_circle_status ON ext_circles(circle_status);
CREATE INDEX IF NOT EXISTS idx_ext_circles_member_limit ON ext_circles(member_limit);
CREATE INDEX IF NOT EXISTS idx_ext_circles_current_member_count ON ext_circles(current_member_count);
CREATE INDEX IF NOT EXISTS idx_ext_circles_created_time ON ext_circles(created_time);
CREATE INDEX IF NOT EXISTS idx_ext_circles_last_activity_time ON ext_circles(last_activity_time);
CREATE INDEX IF NOT EXISTS idx_ext_circles_status ON ext_circles(status);

-- 创建注释
COMMENT ON TABLE ext_circles IS '圈子扩展信息表，存储系统中圈子的详细信息和扩展属性';
COMMENT ON COLUMN ext_circles.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_circles.circle_id IS '圈子ID，关联主圈子表，唯一';
COMMENT ON COLUMN ext_circles.circle_name IS '圈子名称';
COMMENT ON COLUMN ext_circles.circle_avatar_url IS '圈子头像/配图URL';
COMMENT ON COLUMN ext_circles.circle_description IS '圈子描述';
COMMENT ON COLUMN ext_circles.circle_announcement IS '圈子公告';
COMMENT ON COLUMN ext_circles.circle_type IS '圈子类型：public-公开圈子，private-私密圈子';
COMMENT ON COLUMN ext_circles.owner_id IS '圈主ID';
COMMENT ON COLUMN ext_circles.owner_nickname IS '圈主昵称';
COMMENT ON COLUMN ext_circles.owner_vanity_id IS '圈主靓号';
COMMENT ON COLUMN ext_circles.allow_change_owner IS '是否允许更换圈主';
COMMENT ON COLUMN ext_circles.circle_status IS '圈子状态：normal-正常，hidden-隐藏，disbanded-已解散';
COMMENT ON COLUMN ext_circles.member_limit IS '圈子最高限制人数：5000/10000/12000';
COMMENT ON COLUMN ext_circles.current_member_count IS '当前成员总数';
COMMENT ON COLUMN ext_circles.current_member_count_vip IS '当前会员总数';
COMMENT ON COLUMN ext_circles.today_new_members IS '今日新增人数';
COMMENT ON COLUMN ext_circles.daily_active_members IS '日活人数';
COMMENT ON COLUMN ext_circles.weekly_active_members IS '周活人数';
COMMENT ON COLUMN ext_circles.monthly_active_members IS '月活人数';
COMMENT ON COLUMN ext_circles.help_posts_24h IS '24小时内帮办发布数';
COMMENT ON COLUMN ext_circles.total_help_posts IS '总帮办发布数';
COMMENT ON COLUMN ext_circles.invitation_code_count IS '邀请码数量设置（1-20个）';
COMMENT ON COLUMN ext_circles.join_user_level_restriction IS '入圈用户等级限制（JSON格式：普通/初级/高级/超级/至尊/纯发，可多选）';
COMMENT ON COLUMN ext_circles.help_message_view_restriction IS '圈内帮办大厅查看帮办消息限制（JSON格式：普通/初级/高级/超级/至尊/纯发，可多选）';
COMMENT ON COLUMN ext_circles.help_message_post_restriction IS '圈内帮办大厅发布帮办消息限制（JSON格式：普通/初级/高级/超级/至尊/纯发，可多选）';
COMMENT ON COLUMN ext_circles.admin_ids IS '管理员ID列表（JSON格式存储）';
COMMENT ON COLUMN ext_circles.admin_nicknames IS '管理员昵称列表（JSON格式存储）';
COMMENT ON COLUMN ext_circles.allow_remove_admin IS '是否允许取消管理员';
COMMENT ON COLUMN ext_circles.allow_member_invite IS '是否允许成员邀请';
COMMENT ON COLUMN ext_circles.allow_member_edit_info IS '是否允许成员编辑圈子信息';
COMMENT ON COLUMN ext_circles.auto_approve_join IS '是否自动批准加入（需要审核）';
COMMENT ON COLUMN ext_circles.created_time IS '圈子创建时间';
COMMENT ON COLUMN ext_circles.last_activity_time IS '最后活跃时间';
COMMENT ON COLUMN ext_circles.last_help_post_time IS '最后帮办发布时间';
COMMENT ON COLUMN ext_circles.apply_time IS '申请时间（待审列表展示）';
COMMENT ON COLUMN ext_circles.reviewed_at IS '审核时间（通过或拒绝时的操作时间）';
COMMENT ON COLUMN ext_circles.status IS '记录状态：0-待审核，1-已通过，2-无效，3-已拒绝，4-已取消';
COMMENT ON COLUMN ext_circles.reject_reason IS '拒绝原因（审核拒绝时填写）';
COMMENT ON COLUMN ext_circles.remark IS '备注信息';
COMMENT ON COLUMN ext_circles.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_circles.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_circles_updated_at
    BEFORE UPDATE ON ext_circles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_circles (circle_id, circle_name, circle_avatar_url, circle_description, circle_announcement, circle_type, owner_id, owner_nickname, owner_vanity_id, member_limit, current_member_count, current_member_count_vip, today_new_members, daily_active_members, weekly_active_members, monthly_active_members, help_posts_24h, total_help_posts, invitation_code_count, join_user_level_restriction, help_message_view_restriction, help_message_post_restriction, admin_ids, admin_nicknames, status, remark) VALUES
('!circle1:example.com', '测试圈子1', 'https://example.com/circle1.jpg', '这是一个测试圈子', '欢迎加入测试圈子1！', 'public', '@user1:example.com', '测试用户1', 'user001', 5000, 1250, 89, 15, 456, 789, 1200, 25, 1250, 10, '["普通", "初级", "高级"]', '["普通", "初级", "高级", "超级"]', '["普通", "初级", "高级"]', '["@user2:example.com", "@user3:example.com"]', '["测试用户2", "测试用户3"]', 1, '测试圈子数据'),
('!circle2:example.com', '测试圈子2', 'https://example.com/circle2.jpg', '另一个测试圈子', '欢迎加入测试圈子2！', 'private', '@user2:example.com', '测试用户2', 'user002', 10000, 3200, 156, 28, 890, 1456, 2100, 45, 3200, 15, '["普通", "初级", "高级", "超级"]', '["普通", "初级", "高级", "超级", "至尊"]', '["普通", "初级", "高级", "超级"]', '["@user1:example.com", "@user4:example.com"]', '["测试用户1", "测试用户4"]', 1, '测试圈子数据'),
('!circle3:example.com', '测试圈子3', 'https://example.com/circle3.jpg', '第三个测试圈子', '欢迎加入测试圈子3！', 'public', '@user3:example.com', '测试用户3', 'user003', 12000, 8900, 234, 42, 1567, 2345, 3200, 67, 8900, 20, '["普通", "初级", "高级", "超级", "至尊", "纯发"]', '["普通", "初级", "高级", "超级", "至尊", "纯发"]', '["普通", "初级", "高级", "超级", "至尊"]', '["@user1:example.com", "@user2:example.com"]', '["测试用户1", "测试用户2"]', 1, '测试圈子数据');

-- =============================================================================
-- 以下为增量变更：对已有表添加审核相关字段，并统一 status 默认值与说明
-- 新建库已在上方 CREATE TABLE 中包含 status 默认 0；已有库执行下列 ALTER 即可
-- =============================================================================

-- 将 status 默认值改为 0（新插入记录为待审核）；已有数据不强制修改
ALTER TABLE ext_circles ALTER COLUMN status SET DEFAULT 0;

-- 申请时间：圈子申请/创建时间，待审列表展示用
ALTER TABLE ext_circles ADD COLUMN IF NOT EXISTS apply_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;
COMMENT ON COLUMN ext_circles.apply_time IS '申请时间（待审列表展示）；新建时与 created_time 一致';

-- 审核时间：管理员通过或拒绝的时间
ALTER TABLE ext_circles ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP WITH TIME ZONE;
COMMENT ON COLUMN ext_circles.reviewed_at IS '审核时间（通过或拒绝时的操作时间）';

-- 拒绝原因：审核拒绝时填写的说明
ALTER TABLE ext_circles ADD COLUMN IF NOT EXISTS reject_reason TEXT NOT NULL DEFAULT '';
COMMENT ON COLUMN ext_circles.reject_reason IS '拒绝原因（审核拒绝时填写，可为原因码或文案）';
