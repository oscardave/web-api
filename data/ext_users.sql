-- 用户基本信息表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_users_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户基本信息表
DROP TABLE IF EXISTS ext_users;
CREATE TABLE IF NOT EXISTS ext_users (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_users_id_seq'),
    user_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 用户ID（关联主用户表）
    nickname VARCHAR(100) NOT NULL DEFAULT '',                   -- 用户昵称
    avatar_url TEXT NOT NULL DEFAULT '',                         -- 用户头像URL
    personal_description TEXT NOT NULL DEFAULT '',               -- 个人描述/个性签名
    gender VARCHAR(20) NOT NULL DEFAULT '',                      -- 性别：male-男, female-女, other-其他

    -- 登录设备相关
    login_ip VARCHAR(50) NOT NULL DEFAULT '',                    -- 登录IP地址
    device_id VARCHAR(255) NOT NULL DEFAULT '',                  -- 设备ID
    device_model VARCHAR(100) NOT NULL DEFAULT '',               -- 设备型号
    current_version VARCHAR(50) NOT NULL DEFAULT '',             -- 当前版本

    -- 邀请关系
    referrer_id bigint NOT NULL DEFAULT 0,                      -- 注册邀请人ID
    referrer_invite_code VARCHAR(100) NOT NULL DEFAULT '',       -- 注册邀请码
    referrer_user_id VARCHAR(255) NOT NULL DEFAULT '',          -- 注册邀请人昵称
    referrer_vanity_id VARCHAR(100) NOT NULL DEFAULT '',         -- 注册邀请人靓号
    subordinate_referrals_count INTEGER NOT NULL DEFAULT 0,      -- 下级邀请人数

    -- 时间信息
    registration_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,   -- 注册时间
    last_login_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,     -- 最近登录时间

    -- 绑定信息
    bound_type varchar(20) NOT NULL DEFAULT '',                  -- 绑定类型：phone-手机, email-邮箱
    bound_value VARCHAR(100) NOT NULL DEFAULT '',               -- 绑定值（手机号或邮箱地址）
    -- 联系信息
    bound_phone VARCHAR(20) NOT NULL DEFAULT '',                 -- 绑定手机号
    bound_email VARCHAR(100) NOT NULL DEFAULT '',                -- 绑定邮箱号

    -- 会员信息
    member_level_type VARCHAR(50) NOT NULL DEFAULT '',           -- 会员等级类型
    member_expiration_time TIMESTAMP WITH TIME ZONE,             -- 会员到期时间

    -- 徽章信息
    identity_badge VARCHAR(100) NOT NULL DEFAULT '',             -- 身份徽章
    vanity_id_badge VARCHAR(100) NOT NULL DEFAULT '',            -- 靓号徽章
    platform_certification_badge VARCHAR(100) NOT NULL DEFAULT '', -- 平台认证徽章

    -- 信誉与状态
    reputation_value INTEGER NOT NULL DEFAULT 0,                 -- 信誉值
    account_status VARCHAR(50) NOT NULL DEFAULT 'normal',        -- 账号状态
    penalty_type VARCHAR(100) NOT NULL DEFAULT '',               -- 处罚类型

    -- 禁用设置
    chat_prohibited BOOLEAN NOT NULL DEFAULT false,              -- 是否禁止聊天
    note_creation_prohibited BOOLEAN NOT NULL DEFAULT false,     -- 是否禁止创建笔记
    super_seat_note_prohibited BOOLEAN NOT NULL DEFAULT false,   -- 是否禁止创建超级座位笔记

    -- 笔记统计
    current_note_count INTEGER NOT NULL DEFAULT 0,               -- 当前笔记数量
    current_super_seat_note_count INTEGER NOT NULL DEFAULT 0,    -- 当前超级座位笔记数量
    note_count_limit INTEGER NOT NULL DEFAULT 0,                 -- 笔记数量限制
    super_seat_note_limit INTEGER NOT NULL DEFAULT 0,            -- 超级座位笔记限制

    -- 社交关系
    joined_circles_list TEXT NOT NULL DEFAULT '',                -- 已加入的圈子列表
    friends_count INTEGER NOT NULL DEFAULT 0,                    -- 好友数量

    -- 等级积分
    level INTEGER NOT NULL DEFAULT 0,                            -- 用户等级
    score INTEGER NOT NULL DEFAULT 0,                            -- 用户积分

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                          -- 记录状态：1-有效，2-无效
    remark VARCHAR(255) NOT NULL DEFAULT '',                     -- 备注
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_users_user_id ON ext_users(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_users_bound_email ON ext_users(bound_email);
CREATE INDEX IF NOT EXISTS idx_ext_users_bound_phone ON ext_users(bound_phone);
CREATE INDEX IF NOT EXISTS idx_ext_users_referrer_id ON ext_users(referrer_id);
CREATE INDEX IF NOT EXISTS idx_ext_users_registration_time ON ext_users(registration_time);
CREATE INDEX IF NOT EXISTS idx_ext_users_last_login_time ON ext_users(last_login_time);
CREATE INDEX IF NOT EXISTS idx_ext_users_account_status ON ext_users(account_status);
CREATE INDEX IF NOT EXISTS idx_ext_users_level ON ext_users(level);
CREATE INDEX IF NOT EXISTS idx_ext_users_status ON ext_users(status);

-- 创建表注释
COMMENT ON TABLE ext_users IS '用户基本信息表，存储用户的基本信息、联系信息、登录信息、会员信息、徽章信息和邀请关系';

-- 创建字段注释
COMMENT ON COLUMN ext_users.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_users.user_id IS '用户ID，关联主用户表，唯一';
COMMENT ON COLUMN ext_users.nickname IS '用户昵称';
COMMENT ON COLUMN ext_users.avatar_url IS '用户头像URL';
COMMENT ON COLUMN ext_users.personal_description IS '个人描述/个性签名';
COMMENT ON COLUMN ext_users.gender IS '性别：male-男, female-女, other-其他';
COMMENT ON COLUMN ext_users.login_ip IS '登录IP地址';
COMMENT ON COLUMN ext_users.device_id IS '设备ID';
COMMENT ON COLUMN ext_users.device_model IS '设备型号';
COMMENT ON COLUMN ext_users.current_version IS '当前版本';
COMMENT ON COLUMN ext_users.referrer_id IS '注册邀请人ID，引用ext_users表的id字段';
COMMENT ON COLUMN ext_users.referrer_invite_code IS '注册邀请码';
COMMENT ON COLUMN ext_users.referrer_user_id IS '注册邀请人的user_id（Matrix ID）';
COMMENT ON COLUMN ext_users.referrer_vanity_id IS '注册邀请人靓号';
COMMENT ON COLUMN ext_users.subordinate_referrals_count IS '下级邀请人数';
COMMENT ON COLUMN ext_users.registration_time IS '注册时间';
COMMENT ON COLUMN ext_users.last_login_time IS '最近登录时间';
COMMENT ON COLUMN ext_users.bound_type IS '绑定类型：phone-手机, email-邮箱';
COMMENT ON COLUMN ext_users.bound_value IS '绑定值（手机号或邮箱地址）';
COMMENT ON COLUMN ext_users.bound_phone IS '绑定手机号';
COMMENT ON COLUMN ext_users.bound_email IS '绑定邮箱号';
COMMENT ON COLUMN ext_users.member_level_type IS '会员等级类型';
COMMENT ON COLUMN ext_users.member_expiration_time IS '会员到期时间';
COMMENT ON COLUMN ext_users.identity_badge IS '身份徽章';
COMMENT ON COLUMN ext_users.vanity_id_badge IS '靓号徽章';
COMMENT ON COLUMN ext_users.platform_certification_badge IS '平台认证徽章';
COMMENT ON COLUMN ext_users.reputation_value IS '信誉值';
COMMENT ON COLUMN ext_users.account_status IS '账号状态';
COMMENT ON COLUMN ext_users.penalty_type IS '处罚类型';
COMMENT ON COLUMN ext_users.chat_prohibited IS '是否禁止聊天';
COMMENT ON COLUMN ext_users.note_creation_prohibited IS '是否禁止创建笔记';
COMMENT ON COLUMN ext_users.super_seat_note_prohibited IS '是否禁止创建超级座位笔记';
COMMENT ON COLUMN ext_users.current_note_count IS '当前笔记数量';
COMMENT ON COLUMN ext_users.current_super_seat_note_count IS '当前超级座位笔记数量';
COMMENT ON COLUMN ext_users.note_count_limit IS '笔记数量限制';
COMMENT ON COLUMN ext_users.super_seat_note_limit IS '超级座位笔记限制';
COMMENT ON COLUMN ext_users.joined_circles_list IS '已加入的圈子列表';
COMMENT ON COLUMN ext_users.friends_count IS '好友数量';
COMMENT ON COLUMN ext_users.level IS '用户等级';
COMMENT ON COLUMN ext_users.score IS '用户积分';
COMMENT ON COLUMN ext_users.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_users.remark IS '备注信息';
COMMENT ON COLUMN ext_users.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_users.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_users_updated_at
    BEFORE UPDATE ON ext_users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 添加客服用户标志列
alter table ext_users drop column if exists is_customer_servcie;

alter table ext_users add column is_customer_service BOOLEAN NOT NULL DEFAULT false;
comment on column ext_users.is_customer_service is '是否是客服号';

-- 增加排序
alter table ext_users add column sort INT NOT NULL DEFAULT 0;
comment on column ext_users.sort is '排序';

-- 增加诚信徽章列
alter table ext_users add column credit_badge_id BIGINT NOT NULL DEFAULT 0;
COMMENT ON COLUMN ext_users.credit_badge_id IS '诚信徽章ID';

-- 修改字段 member_level_type -> member_level_id
-- 先删除再添加
ALTER TABLE ext_users DROP COLUMN IF EXISTS member_level_type;
ALTER TABLE ext_users DROP COLUMN IF EXISTS member_level_id;
ALTER TABLE ext_users ADD COLUMN member_level_id BIGINT NOT NULL DEFAULT 1;
COMMENT ON COLUMN ext_users.member_level_id IS '会员等级ID，默认10000表示普通用户';

-- 修改字段 identity_badge -> identity_badge_id
ALTER TABLE ext_users DROP COLUMN IF EXISTS identity_badge;
ALTER TABLE ext_users DROP COLUMN IF EXISTS identity_badge_id;
ALTER TABLE ext_users ADD COLUMN identity_badge_id BIGINT NOT NULL DEFAULT 0;
COMMENT ON COLUMN ext_users.identity_badge_id IS '身份徽章ID';

-- 修改字段 vanity_id_badge -> vanity_badge_id
ALTER TABLE ext_users DROP COLUMN IF EXISTS vanity_id_badge;
ALTER TABLE ext_users DROP COLUMN IF EXISTS vanity_badge_id;
ALTER TABLE ext_users ADD COLUMN vanity_badge_id BIGINT NOT NULL DEFAULT 0;
COMMENT ON COLUMN ext_users.vanity_badge_id IS '靓号徽章ID';

-- 删除字段
alter table ext_users drop column if exists platform_certification_badge;

-- 更新会员等级ID
update ext_users set member_level_id = 10000;

-- 增加纯发徽章是否列
ALTER TABLE ext_users ADD COLUMN IF NOT EXISTS pure_badge_enabled BOOLEAN NOT NULL DEFAULT false;
COMMENT ON COLUMN ext_users.pure_badge_enabled IS '是否纯发徽章';

-- 增加圈子徽章ID列
ALTER TABLE ext_users ADD COLUMN IF NOT EXISTS circle_badge_id BIGINT NOT NULL DEFAULT 0;
COMMENT ON COLUMN ext_users.circle_badge_id IS '圈子徽章ID，对应 ext_circle_badges.id';
