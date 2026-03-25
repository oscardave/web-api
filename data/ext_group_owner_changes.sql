-- 群主转让记录表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_group_owner_changes_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建群主转让记录表
DROP TABLE IF EXISTS ext_group_owner_changes;
CREATE TABLE IF NOT EXISTS ext_group_owner_changes (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_group_owner_changes_id_seq'),
    group_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 群聊ID（关联群聊表）
    group_name VARCHAR(200) NOT NULL DEFAULT '',                 -- 群聊名称

    -- 转让前后群主信息
    old_owner_id VARCHAR(255) NOT NULL DEFAULT '',               -- 原群主ID
    old_owner_nickname VARCHAR(100) NOT NULL DEFAULT '',         -- 原群主昵称
    old_owner_vanity_id VARCHAR(100) NOT NULL DEFAULT '',        -- 原群主靓号
    new_owner_id VARCHAR(255) NOT NULL DEFAULT '',               -- 新群主ID
    new_owner_nickname VARCHAR(100) NOT NULL DEFAULT '',         -- 新群主昵称
    new_owner_vanity_id VARCHAR(100) NOT NULL DEFAULT '',        -- 新群主靓号

    -- 转让操作信息
    transfer_type VARCHAR(50) NOT NULL DEFAULT 'voluntary',       -- 转让类型：voluntary-主动转让，forced-强制转让，system-系统转让
    transfer_reason TEXT NOT NULL DEFAULT '',                     -- 转让原因
    transfer_method VARCHAR(50) NOT NULL DEFAULT 'admin',        -- 转让方式：admin-后台操作，user-用户操作，system-系统操作

    -- 操作人信息
    operator_id VARCHAR(255) NOT NULL DEFAULT '',                -- 操作人ID（后台管理员或系统）
    operator_nickname VARCHAR(100) NOT NULL DEFAULT '',          -- 操作人昵称
    operator_role VARCHAR(50) NOT NULL DEFAULT 'admin',          -- 操作人角色：admin-管理员，system-系统，user-用户

    -- 转让状态
    transfer_status VARCHAR(50) NOT NULL DEFAULT 'completed',    -- 转让状态：pending-待确认，completed-已完成，failed-失败，cancelled-已取消
    confirmation_time TIMESTAMP WITH TIME ZONE,                  -- 确认时间
    completion_time TIMESTAMP WITH TIME ZONE,                    -- 完成时间

    -- 时间信息
    transfer_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- 转让操作时间
    created_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                         -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                     -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_group_id ON ext_group_owner_changes(group_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_old_owner_id ON ext_group_owner_changes(old_owner_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_new_owner_id ON ext_group_owner_changes(new_owner_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_operator_id ON ext_group_owner_changes(operator_id);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_transfer_type ON ext_group_owner_changes(transfer_type);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_transfer_status ON ext_group_owner_changes(transfer_status);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_transfer_time ON ext_group_owner_changes(transfer_time);
CREATE INDEX IF NOT EXISTS idx_ext_group_owner_changes_status ON ext_group_owner_changes(status);

-- 创建注释
COMMENT ON TABLE ext_group_owner_changes IS '群主转让记录表，记录群聊中群主转让的所有历史记录';
COMMENT ON COLUMN ext_group_owner_changes.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_group_owner_changes.group_id IS '群聊ID，关联群聊表';
COMMENT ON COLUMN ext_group_owner_changes.group_name IS '群聊名称';
COMMENT ON COLUMN ext_group_owner_changes.old_owner_id IS '原群主ID';
COMMENT ON COLUMN ext_group_owner_changes.old_owner_nickname IS '原群主昵称';
COMMENT ON COLUMN ext_group_owner_changes.old_owner_vanity_id IS '原群主靓号';
COMMENT ON COLUMN ext_group_owner_changes.new_owner_id IS '新群主ID';
COMMENT ON COLUMN ext_group_owner_changes.new_owner_nickname IS '新群主昵称';
COMMENT ON COLUMN ext_group_owner_changes.new_owner_vanity_id IS '新群主靓号';
COMMENT ON COLUMN ext_group_owner_changes.transfer_type IS '转让类型：voluntary-主动转让，forced-强制转让，system-系统转让';
COMMENT ON COLUMN ext_group_owner_changes.transfer_reason IS '转让原因';
COMMENT ON COLUMN ext_group_owner_changes.transfer_method IS '转让方式：admin-后台操作，user-用户操作，system-系统操作';
COMMENT ON COLUMN ext_group_owner_changes.operator_id IS '操作人ID（后台管理员或系统）';
COMMENT ON COLUMN ext_group_owner_changes.operator_nickname IS '操作人昵称';
COMMENT ON COLUMN ext_group_owner_changes.operator_role IS '操作人角色：admin-管理员，system-系统，user-用户';
COMMENT ON COLUMN ext_group_owner_changes.transfer_status IS '转让状态：pending-待确认，completed-已完成，failed-失败，cancelled-已取消';
COMMENT ON COLUMN ext_group_owner_changes.confirmation_time IS '确认时间';
COMMENT ON COLUMN ext_group_owner_changes.completion_time IS '完成时间';
COMMENT ON COLUMN ext_group_owner_changes.transfer_time IS '转让操作时间';
COMMENT ON COLUMN ext_group_owner_changes.created_time IS '记录创建时间';
COMMENT ON COLUMN ext_group_owner_changes.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_group_owner_changes.remark IS '备注信息';
COMMENT ON COLUMN ext_group_owner_changes.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_group_owner_changes.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_group_owner_changes_updated_at
    BEFORE UPDATE ON ext_group_owner_changes
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_group_owner_changes (group_id, group_name, old_owner_id, old_owner_nickname, old_owner_vanity_id, new_owner_id, new_owner_nickname, new_owner_vanity_id, transfer_type, transfer_reason, transfer_method, operator_id, operator_nickname, operator_role, transfer_status, status, remark) VALUES
('!group1:example.com', '测试群聊1', '@user1:example.com', '测试用户1', 'user001', '@user2:example.com', '测试用户2', 'user002', 'voluntary', '用户主动转让群主权限', 'user', '@user1:example.com', '测试用户1', 'user', 'completed', 1, '用户主动转让'),
('!group2:example.com', '测试群聊2', '@user3:example.com', '测试用户3', 'user003', '@user2:example.com', '测试用户2', 'user002', 'forced', '原群主违规，强制转让', 'admin', '@admin:example.com', '系统管理员', 'admin', 'completed', 1, '管理员强制转让');
