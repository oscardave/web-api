-- 用户扩展信息表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_extends_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户扩展信息表
DROP TABLE IF EXISTS ext_user_extends;
CREATE TABLE IF NOT EXISTS ext_user_extends (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_extends_id_seq'),
    user_id BIGINT NOT NULL,                                     -- 用户ID（关联基本信息表）

    -- 个人描述
    personal_description TEXT NOT NULL DEFAULT '',               -- 用户个人描述

    -- 会员和徽章相关
    member_level_type VARCHAR(50) NOT NULL DEFAULT '',          -- 会员等级类型
    member_expiration_time TIMESTAMP WITH TIME ZONE,            -- 会员等级类型截止时间
    identity_badge VARCHAR(100) NOT NULL DEFAULT '',            -- 身份徽章
    vanity_id_badge VARCHAR(100) NOT NULL DEFAULT '',           -- 靓号徽章
    platform_certification_badge VARCHAR(100) NOT NULL DEFAULT '', -- 平台认证徽章
    reputation_value INTEGER NOT NULL DEFAULT 0,                -- 声望值

    -- 用户状态和限制
    account_status VARCHAR(50) NOT NULL DEFAULT 'normal',       -- 当前账号状态：normal-正常，penalty-处罚
    penalty_type VARCHAR(100) NOT NULL DEFAULT '',              -- 处罚类型
    chat_prohibited BOOLEAN NOT NULL DEFAULT false,             -- 禁止聊天
    note_creation_prohibited BOOLEAN NOT NULL DEFAULT false,    -- 禁止创建笔记
    super_seat_note_prohibited BOOLEAN NOT NULL DEFAULT false,  -- 禁止超级坐席笔记

    -- 用户统计信息
    current_note_count INTEGER NOT NULL DEFAULT 0,              -- 当前笔记数量
    current_super_seat_note_count INTEGER NOT NULL DEFAULT 0,   -- 当前超级坐席笔记数量
    note_count_limit INTEGER NOT NULL DEFAULT 0,                -- 笔记数量上限
    super_seat_note_limit INTEGER NOT NULL DEFAULT 0,           -- 超级坐席笔记上限

    -- 圈子关系
    joined_circles_list TEXT NOT NULL DEFAULT '',               -- 用户加入的圈子列表（JSON格式）
    friends_count INTEGER NOT NULL DEFAULT 0,                   -- 用户好友人数

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                        -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                    -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_extends_user_id ON ext_user_extends(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_extends_member_level_type ON ext_user_extends(member_level_type);
CREATE INDEX IF NOT EXISTS idx_ext_user_extends_account_status ON ext_user_extends(account_status);
CREATE INDEX IF NOT EXISTS idx_ext_user_extends_status ON ext_user_extends(status);

-- 创建注释
COMMENT ON TABLE ext_user_extends IS '用户扩展信息表，存储用户的详细信息和扩展属性';
COMMENT ON COLUMN ext_user_extends.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_extends.user_id IS '用户ID，关联基本信息表';
COMMENT ON COLUMN ext_user_extends.personal_description IS '用户个人描述';
COMMENT ON COLUMN ext_user_extends.member_level_type IS '会员等级类型';
COMMENT ON COLUMN ext_user_extends.member_expiration_time IS '会员等级类型截止时间';
COMMENT ON COLUMN ext_user_extends.identity_badge IS '身份徽章';
COMMENT ON COLUMN ext_user_extends.vanity_id_badge IS '靓号徽章';
COMMENT ON COLUMN ext_user_extends.platform_certification_badge IS '平台认证徽章';
COMMENT ON COLUMN ext_user_extends.reputation_value IS '声望值';
COMMENT ON COLUMN ext_user_extends.account_status IS '当前账号状态：normal-正常，penalty-处罚';
COMMENT ON COLUMN ext_user_extends.penalty_type IS '处罚类型';
COMMENT ON COLUMN ext_user_extends.chat_prohibited IS '是否禁止聊天';
COMMENT ON COLUMN ext_user_extends.note_creation_prohibited IS '是否禁止创建笔记';
COMMENT ON COLUMN ext_user_extends.super_seat_note_prohibited IS '是否禁止超级坐席笔记';
COMMENT ON COLUMN ext_user_extends.current_note_count IS '当前笔记数量';
COMMENT ON COLUMN ext_user_extends.current_super_seat_note_count IS '当前超级坐席笔记数量';
COMMENT ON COLUMN ext_user_extends.note_count_limit IS '笔记数量上限';
COMMENT ON COLUMN ext_user_extends.super_seat_note_limit IS '超级坐席笔记上限';
COMMENT ON COLUMN ext_user_extends.joined_circles_list IS '用户加入的圈子列表（JSON格式）';
COMMENT ON COLUMN ext_user_extends.friends_count IS '用户好友人数';
COMMENT ON COLUMN ext_user_extends.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_user_extends.remark IS '备注信息';
COMMENT ON COLUMN ext_user_extends.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_extends.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_extends_updated_at
    BEFORE UPDATE ON ext_user_extends
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据（注意：需要先插入基本表数据，这里假设基本表已有数据）
-- 注意：实际使用时需要根据基本表的实际ID来插入
INSERT INTO ext_user_extends (user_id, personal_description, member_level_type, reputation_value, account_status, current_note_count, current_super_seat_note_count, note_count_limit, super_seat_note_limit, friends_count, joined_circles_list, status, remark) VALUES
(10000, '这是一个测试用户', '高级会员', 150, 'normal', 25, 10, 100, 50, 12, '["circle1", "circle2"]', 1, '测试用户数据'),
(10001, '另一个测试用户', '普通会员', 80, 'normal', 15, 5, 50, 20, 8, '["circle1"]', 1, '测试用户数据'),
(10002, '第三个测试用户', '免费用户', 30, 'penalty', 5, 0, 20, 5, 3, '[]', 1, '测试用户数据');
