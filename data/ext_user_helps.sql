-- 用户帮办请求表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_helps_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户帮办请求表
DROP TABLE IF EXISTS ext_user_helps;
CREATE TABLE IF NOT EXISTS ext_user_helps (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_helps_id_seq'),
    user_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 发布用户ID
    user_nickname VARCHAR(100) NOT NULL DEFAULT '',              -- 发布用户昵称
    user_vanity_id VARCHAR(100) NOT NULL DEFAULT '',              -- 发布用户靓号

    -- 帮办请求内容
    content TEXT NOT NULL DEFAULT '',                            -- 帮办请求内容（最多500字符）
    media_urls TEXT[] NOT NULL DEFAULT '{}',                    -- 媒体附件URL数组（照片或视频）

    -- 关联信息（不设外键约束）
    circle_id VARCHAR(255) NOT NULL DEFAULT '',                  -- 关联圈子ID
    circle_name VARCHAR(200) NOT NULL DEFAULT '',                -- 圈子名称（冗余存储）
    city_id BIGINT NOT NULL DEFAULT 0,                          -- 关联城市ID
    city_name VARCHAR(100) NOT NULL DEFAULT '',                  -- 城市名称（冗余存储）

    -- VIP广播功能
    broadcast_enabled BOOLEAN NOT NULL DEFAULT false,            -- 是否开启VIP圈子广播
    broadcast_cost INTEGER NOT NULL DEFAULT 5,                   -- 广播费用（积分）
    broadcast_used_count INTEGER NOT NULL DEFAULT 0,             -- 本月已使用广播次数

    -- 诚信保要求
    credit_requirement_enabled BOOLEAN NOT NULL DEFAULT false,   -- 是否开启接单信用要求
    credit_requirement_value INTEGER NOT NULL DEFAULT 0,         -- 信用要求值（100的倍数，最高3000）

    -- 请求状态管理
    request_status VARCHAR(50) NOT NULL DEFAULT 'pending',       -- 请求状态：pending-待处理，active-进行中，completed-已完成，cancelled-已取消
    view_count INTEGER NOT NULL DEFAULT 0,                      -- 查看次数
    application_count INTEGER NOT NULL DEFAULT 0,                -- 申请次数

    -- 时间信息
    published_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 发布时间
    expired_at TIMESTAMP WITH TIME ZONE,                         -- 过期时间
    completed_at TIMESTAMP WITH TIME ZONE,                        -- 完成时间

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                          -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                     -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建帮办申请表序列
CREATE SEQUENCE IF NOT EXISTS ext_user_help_applications_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建帮办申请表
DROP TABLE IF EXISTS ext_user_help_applications;
CREATE TABLE IF NOT EXISTS ext_user_help_applications (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_help_applications_id_seq'),
    help_request_id BIGINT NOT NULL DEFAULT 0,                   -- 帮办请求ID
    applicant_user_id VARCHAR(255) NOT NULL DEFAULT '',          -- 申请用户ID
    applicant_nickname VARCHAR(100) NOT NULL DEFAULT '',          -- 申请用户昵称
    applicant_vanity_id VARCHAR(100) NOT NULL DEFAULT '',         -- 申请用户靓号

    -- 申请信息
    application_message TEXT NOT NULL DEFAULT '',                 -- 申请留言
    application_status VARCHAR(50) NOT NULL DEFAULT 'pending',    -- 申请状态：pending-待审核，accepted-已接受，rejected-已拒绝，completed-已完成

    -- 时间信息
    applied_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 申请时间
    reviewed_at TIMESTAMP WITH TIME ZONE,                        -- 审核时间
    completed_at TIMESTAMP WITH TIME ZONE,                        -- 完成时间

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                          -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                     -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_user_id ON ext_user_helps(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_circle_id ON ext_user_helps(circle_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_city_id ON ext_user_helps(city_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_request_status ON ext_user_helps(request_status);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_broadcast_enabled ON ext_user_helps(broadcast_enabled);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_credit_requirement_enabled ON ext_user_helps(credit_requirement_enabled);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_published_at ON ext_user_helps(published_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_expired_at ON ext_user_helps(expired_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_helps_status ON ext_user_helps(status);

CREATE INDEX IF NOT EXISTS idx_ext_user_help_applications_help_request_id ON ext_user_help_applications(help_request_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_help_applications_applicant_user_id ON ext_user_help_applications(applicant_user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_help_applications_application_status ON ext_user_help_applications(application_status);
CREATE INDEX IF NOT EXISTS idx_ext_user_help_applications_applied_at ON ext_user_help_applications(applied_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_help_applications_status ON ext_user_help_applications(status);

-- 创建注释
COMMENT ON TABLE ext_user_helps IS '用户帮办请求表，存储用户发布的帮办请求信息';
COMMENT ON COLUMN ext_user_helps.id IS '帮办请求唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_helps.user_id IS '发布用户ID';
COMMENT ON COLUMN ext_user_helps.user_nickname IS '发布用户昵称';
COMMENT ON COLUMN ext_user_helps.user_vanity_id IS '发布用户靓号';
COMMENT ON COLUMN ext_user_helps.content IS '帮办请求内容，最多500字符';
COMMENT ON COLUMN ext_user_helps.media_urls IS '媒体附件URL数组，支持照片或视频';
COMMENT ON COLUMN ext_user_helps.circle_id IS '关联圈子ID';
COMMENT ON COLUMN ext_user_helps.circle_name IS '圈子名称，冗余存储';
COMMENT ON COLUMN ext_user_helps.city_id IS '关联城市ID';
COMMENT ON COLUMN ext_user_helps.city_name IS '城市名称，冗余存储';
COMMENT ON COLUMN ext_user_helps.broadcast_enabled IS '是否开启VIP圈子广播';
COMMENT ON COLUMN ext_user_helps.broadcast_cost IS '广播费用，单位积分';
COMMENT ON COLUMN ext_user_helps.broadcast_used_count IS '本月已使用广播次数';
COMMENT ON COLUMN ext_user_helps.credit_requirement_enabled IS '是否开启接单信用要求';
COMMENT ON COLUMN ext_user_helps.credit_requirement_value IS '信用要求值，100的倍数，最高3000';
COMMENT ON COLUMN ext_user_helps.request_status IS '请求状态：pending-待处理，active-进行中，completed-已完成，cancelled-已取消';
COMMENT ON COLUMN ext_user_helps.view_count IS '查看次数';
COMMENT ON COLUMN ext_user_helps.application_count IS '申请次数';
COMMENT ON COLUMN ext_user_helps.published_at IS '发布时间';
COMMENT ON COLUMN ext_user_helps.expired_at IS '过期时间';
COMMENT ON COLUMN ext_user_helps.completed_at IS '完成时间';
COMMENT ON COLUMN ext_user_helps.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_user_helps.remark IS '备注信息';
COMMENT ON COLUMN ext_user_helps.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_helps.updated_at IS '记录更新时间，自动设置为当前时间';

COMMENT ON TABLE ext_user_help_applications IS '帮办申请表，存储用户对帮办请求的申请信息';
COMMENT ON COLUMN ext_user_help_applications.id IS '申请记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_help_applications.help_request_id IS '帮办请求ID';
COMMENT ON COLUMN ext_user_help_applications.applicant_user_id IS '申请用户ID';
COMMENT ON COLUMN ext_user_help_applications.applicant_nickname IS '申请用户昵称';
COMMENT ON COLUMN ext_user_help_applications.applicant_vanity_id IS '申请用户靓号';
COMMENT ON COLUMN ext_user_help_applications.application_message IS '申请留言';
COMMENT ON COLUMN ext_user_help_applications.application_status IS '申请状态：pending-待审核，accepted-已接受，rejected-已拒绝，completed-已完成';
COMMENT ON COLUMN ext_user_help_applications.applied_at IS '申请时间';
COMMENT ON COLUMN ext_user_help_applications.reviewed_at IS '审核时间';
COMMENT ON COLUMN ext_user_help_applications.completed_at IS '完成时间';
COMMENT ON COLUMN ext_user_help_applications.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_user_help_applications.remark IS '备注信息';
COMMENT ON COLUMN ext_user_help_applications.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_help_applications.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_helps_updated_at
    BEFORE UPDATE ON ext_user_helps
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_ext_user_help_applications_updated_at
    BEFORE UPDATE ON ext_user_help_applications
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 添加数据验证约束
ALTER TABLE ext_user_helps ADD CONSTRAINT chk_content_length CHECK (char_length(content) <= 500);
ALTER TABLE ext_user_helps ADD CONSTRAINT chk_credit_requirement_value CHECK (credit_requirement_value = 0 OR (credit_requirement_value >= 100 AND credit_requirement_value <= 3000 AND credit_requirement_value % 100 = 0));
ALTER TABLE ext_user_helps ADD CONSTRAINT chk_broadcast_cost CHECK (broadcast_cost >= 0);
ALTER TABLE ext_user_helps ADD CONSTRAINT chk_broadcast_used_count CHECK (broadcast_used_count >= 0);

-- 插入测试数据
INSERT INTO ext_user_helps (user_id, user_nickname, user_vanity_id, content, media_urls, circle_id, circle_name, city_id, city_name, broadcast_enabled, broadcast_cost, credit_requirement_enabled, credit_requirement_value, request_status, view_count, application_count, published_at, status, remark) VALUES
('@user1:example.com', '测试用户1', 'user001', '需要帮忙搬家，有家具需要搬运，希望有经验的朋友帮忙', '{"https://example.com/photo1.jpg", "https://example.com/photo2.jpg"}', '!circle1:example.com', '测试圈子1', 1001, '北京市', false, 5, false, 0, 'pending', 15, 3, CURRENT_TIMESTAMP, 1, '测试帮办请求1'),
('@user2:example.com', '测试用户2', 'user002', '寻找会修电脑的朋友，电脑开不了机，需要专业维修', '{"https://example.com/video1.mp4"}', '!circle2:example.com', '测试圈子2', 1002, '上海市', true, 5, true, 200, 'active', 28, 5, CURRENT_TIMESTAMP, 1, '测试帮办请求2'),
('@user3:example.com', '测试用户3', 'user003', '需要代购一些商品，希望有经验的朋友帮忙', '{}', '!circle3:example.com', '测试圈子3', 1003, '广州市', false, 5, false, 0, 'completed', 42, 8, CURRENT_TIMESTAMP, 1, '测试帮办请求3');

INSERT INTO ext_user_help_applications (help_request_id, applicant_user_id, applicant_nickname, applicant_vanity_id, application_message, application_status, applied_at, status, remark) VALUES
(10000, '@user4:example.com', '申请用户1', 'user004', '我有搬家经验，可以帮忙', 'pending', CURRENT_TIMESTAMP, 1, '测试申请1'),
(10000, '@user5:example.com', '申请用户2', 'user005', '我专业搬家，价格合理', 'accepted', CURRENT_TIMESTAMP, 1, '测试申请2'),
(10001, '@user6:example.com', '申请用户3', 'user006', '我是电脑维修师，可以帮忙', 'pending', CURRENT_TIMESTAMP, 1, '测试申请3');
