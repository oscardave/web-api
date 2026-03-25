DROP TABLE IF EXISTS ext_circle_invite;
CREATE TABLE IF NOT EXISTS ext_circle_invite (
    id BIGSERIAL PRIMARY KEY,
    circle_id EXT NOT NULL,                    -- 圈子ID（关联圈子表）
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     --
    user_nickname VARCHAR(100) NOT NULL DEFAULT '',
    user_avatar TEXT NOT NULL,
    invite_id VARCHAR(100) NOT NULL DEFAULT '',               -- 用户在圈子中的昵称
    invite_avatar TEXT NOT NULL,               -- 用户在圈子中的昵称
    invite_nickname VARCHAR(100) NOT NULL DEFAULT '',               -- 用户在圈子中的昵称
    created  int not null default 0,
    status int not null default 0,
    type int not null default 0
);

-- 创建索引

CREATE  INDEX  ext_circle_invite_circle_id_user_id ON ext_circle_invite(circle_id, user_id);

-- 创建注释
COMMENT ON TABLE ext_circle_invite IS '圈子邀请记录表';
COMMENT ON COLUMN ext_circle_invite.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_circle_invite.invite_id IS '邀请人id';
COMMENT ON COLUMN ext_circle_invite.invite_avatar IS '邀请人头像';
COMMENT ON COLUMN ext_circle_invite.user_nickname IS '邀请人头像昵称';
COMMENT ON COLUMN ext_circle_invite.type IS '0是普通记录，1是申请加入圈子';


DROP TABLE IF EXISTS ext_circle_read;
CREATE TABLE IF NOT EXISTS ext_circle_read (
    id BIGSERIAL PRIMARY KEY,
    circle_id TEXT NOT NULL,                    -- 圈子ID（关联圈子表）
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    read_type  int not null default 0,
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引

CREATE  INDEX  ext_circle_read_circle_id_user_id ON ext_circle_read(circle_id, user_id);

-- 创建注释
COMMENT ON TABLE ext_circle_read IS '圈子读记录表';
COMMENT ON COLUMN ext_circle_read.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_circle_read.updated IS '最后一次读取时间';
COMMENT ON COLUMN ext_circle_read.read_type IS '1大厅,2:帮办,3圈内公告';



ALTER TABLE users ADD COLUMN nickname varchar(100) DEFAULT '' NOT NULL;
ALTER TABLE users ADD COLUMN avatar_url TEXT NOT NULL;
ALTER TABLE users ADD COLUMN level int DEFAULT 0 NOT NULL;
ALTER TABLE users ADD COLUMN score int DEFAULT 0 NOT NULL;
ALTER TABLE users ADD COLUMN phone varchar(50) DEFAULT '' NOT NULL;
ALTER TABLE users ADD COLUMN email varchar(50) DEFAULT '' NOT NULL;



DROP TABLE IF EXISTS ext_circle_users_setting;
CREATE TABLE IF NOT EXISTS ext_circle_users_setting (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    city  VARCHAR(255) NOT NULL DEFAULT '',
    circle_id TEXT NOT NULL,
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引

CREATE  INDEX IF NOT EXISTS ext_circle_users_setting_user_id ON ext_circle_users_setting(user_id);

-- 创建注释
COMMENT ON TABLE ext_circle_users_setting IS '圈子用户浏览配置表';
COMMENT ON COLUMN ext_circle_users_setting.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_circle_users_setting.city IS '城市 json';
COMMENT ON COLUMN ext_circle_users_setting.circle_id IS '圈子id json';



DROP TABLE IF EXISTS ext_circle_content;
CREATE TABLE IF NOT EXISTS ext_circle_content (
    id BIGSERIAL PRIMARY KEY,
    circle_id TEXT NOT NULL,                    -- 圈子ID（关联圈子表）
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    user_nickname VARCHAR(255) NOT NULL DEFAULT '',
    user_avatar TEXT NOT NULL,
    score VARCHAR(255) NOT NULL DEFAULT '',
    url TEXT NOT NULL,
    city VARCHAR(255) NOT NULL DEFAULT '',
    content text NOT NULL DEFAULT '',
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX IF NOT EXISTS ext_circle_content_circle_id_user_id ON ext_circle_content(circle_id, user_id);

-- 创建注释
COMMENT ON TABLE ext_circle_content IS '圈子帮办记录表';
COMMENT ON COLUMN ext_circle_content.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_circle_content.circle_id IS '发布的圈子id json';
COMMENT ON COLUMN ext_circle_content.score IS '要用帮办人的诚信分';
COMMENT ON COLUMN ext_circle_content.url IS '用户发布的视频或者视频。json格式，';
COMMENT ON COLUMN ext_circle_content.city IS '发布的城市json';
COMMENT ON COLUMN ext_circle_content.content IS '发布的内容';



DROP TABLE IF EXISTS ext_circle_notice;
CREATE TABLE IF NOT EXISTS ext_circle_notice (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    user_avatar  TEXT NOT NULL,
    user_nickname  VARCHAR(255) NOT NULL DEFAULT '',
    circle_id TEXT NOT NULL,
    content  VARCHAR(255) NOT NULL DEFAULT '',
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX IF NOT EXISTS ext_circle_notice_user_id ON ext_circle_notice(user_id,circle_id);

-- 创建注释
COMMENT ON TABLE ext_circle_notice IS '圈子公告表';
COMMENT ON COLUMN ext_circle_notice.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_circle_notice.content IS '内容';



DROP TABLE IF EXISTS circle_content_report;
CREATE TABLE IF NOT EXISTS circle_content_report (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    user_avatar  TEXT NOT NULL,
    user_nickname  VARCHAR(255) NOT NULL DEFAULT '',
    circle_id TEXT NOT NULL,
    content  VARCHAR(255) NOT NULL DEFAULT '',
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX IF NOT EXISTS circle_content_report_circle_id ON circle_content_report(circle_id);

-- 创建注释
COMMENT ON TABLE circle_content_report IS '圈子投诉表';
COMMENT ON COLUMN circle_content_report.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN circle_content_report.content IS '内容';



ALTER TABLE ext_circles ADD COLUMN ban_status  int DEFAULT 0 NOT NULL ;
ALTER TABLE ext_circles ADD COLUMN limit_level_status int DEFAULT 0 NOT NULL ;
ALTER TABLE ext_circles ADD COLUMN limit_views_level_status int DEFAULT 0 NOT NULL ;
ALTER TABLE ext_circles ADD COLUMN limit_level varchar(100) DEFAULT '' NOT NULL ;
ALTER TABLE ext_circles ADD COLUMN limit_views_level varchar(100) DEFAULT '' NOT NULL ;


COMMENT ON COLUMN ext_circles.ban_status IS '圈子是否禁言';
COMMENT ON COLUMN ext_circles.limit_level_status IS '圈子是否等级限制';
COMMENT ON COLUMN ext_circles.limit_views_level_status IS '圈子是否浏览等级限制';
COMMENT ON COLUMN ext_circles.limit_level IS '圈子等级限制';
COMMENT ON COLUMN ext_circles.limit_views_level IS '圈子浏览等级限制';



ALTER TABLE ext_circle_users ADD COLUMN circle_avatar TEXT NOT NULL ;
ALTER TABLE ext_circle_users ADD COLUMN limit_reason varchar(100) DEFAULT '' NOT NULL;
ALTER TABLE ext_circle_users ADD COLUMN circle_type varchar(100) DEFAULT '' NOT NULL ;
ALTER TABLE ext_circle_users ADD COLUMN circle_name varchar(100) DEFAULT '' NOT NULL ;
ALTER TABLE ext_circle_users ADD COLUMN user_score int DEFAULT 0 NOT NULL ;
ALTER TABLE ext_circle_users ADD COLUMN hand_name varchar(100) DEFAULT '' NOT NULL;
ALTER TABLE ext_circle_users ADD COLUMN han_name varchar(100) DEFAULT '' NOT NULL ;


COMMENT ON COLUMN ext_circle_users.circle_avatar IS '圈子头像';
COMMENT ON COLUMN ext_circle_users.limit_reason IS '限制原因';
COMMENT ON COLUMN ext_circle_users.circle_type IS '圈子类型';
COMMENT ON COLUMN ext_circle_users.circle_name IS '圈子名称';
COMMENT ON COLUMN ext_circle_users.user_score IS '圈子用户信誉分';
COMMENT ON COLUMN ext_circle_users.hand_name IS '圈子通过管理员';
COMMENT ON COLUMN ext_circle_users.han_name IS '圈子处罚操作管理员';




DROP TABLE IF EXISTS ext_user_notes;
CREATE TABLE IF NOT EXISTS ext_user_notes (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    user_avatar  TEXT NOT NULL,
    user_nickname  VARCHAR(255) NOT NULL DEFAULT '',
    label VARCHAR(255) NOT NULL DEFAULT '',
    content  VARCHAR(255) NOT NULL DEFAULT '',
    title VARCHAR(255) NOT NULL DEFAULT '',
    note_avatar TEXT NOT NULL,
    images TEXT NOT NULL,
    videos TEXT NOT NULL,
    remark VARCHAR(255) NOT NULL DEFAULT '',
    chat_password VARCHAR(255) NOT NULL DEFAULT '',
    groups VARCHAR(255) NOT NULL DEFAULT '',
    remark_friends VARCHAR(255) NOT NULL DEFAULT '',
    type  int not null default 0,
    status  int not null default 1,
    pin  int not null default 0,
    pin_time  int not null default 0,
    sort  int not null default 0,
    verify_video TEXT NOT NULL,
    city VARCHAR(255) NOT NULL DEFAULT '',
    view_limit  int not null default 0,
    view_count  int not null default 0,
    view_max  int not null default 0,
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX IF NOT EXISTS ext_user_notes_user_id ON ext_user_notes(user_id);
CREATE  INDEX IF NOT EXISTS ext_user_notes_city ON ext_user_notes(city);
CREATE  INDEX IF NOT EXISTS ext_user_notes_label ON ext_user_notes(label);
CREATE  INDEX IF NOT EXISTS ext_user_notes_groups ON ext_user_notes(groups);
-- 创建注释
COMMENT ON TABLE ext_user_notes IS '用户笔记表';
COMMENT ON COLUMN ext_user_notes.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_user_notes.content IS '内容';
COMMENT ON COLUMN ext_user_notes.title IS '标题';
COMMENT ON COLUMN ext_user_notes.note_avatar IS '笔记封面';
COMMENT ON COLUMN ext_user_notes.label IS '标签';
COMMENT ON COLUMN ext_user_notes.images IS '图片';
COMMENT ON COLUMN ext_user_notes.videos IS '视频';
COMMENT ON COLUMN ext_user_notes.remark IS '备注';
COMMENT ON COLUMN ext_user_notes.chat_password IS '聊天密码';
COMMENT ON COLUMN ext_user_notes.groups IS '组';
COMMENT ON COLUMN ext_user_notes.remark_friends IS '备忘联系人';
COMMENT ON COLUMN ext_user_notes.type IS '0普通用户笔记。1超级笔记';
COMMENT ON COLUMN ext_user_notes.pin IS '置顶';
COMMENT ON COLUMN ext_user_notes.pin_time IS '置顶时间';
COMMENT ON COLUMN ext_user_notes.sort IS '排序';
COMMENT ON COLUMN ext_user_notes.city IS '城市位置';
COMMENT ON COLUMN ext_user_notes.status IS '状态。0预览,草稿，1正常。2下架';
COMMENT ON COLUMN ext_user_notes.view_limit IS '访问限制';
COMMENT ON COLUMN ext_user_notes.view_count IS '访问人数';
COMMENT ON COLUMN ext_user_notes.view_max IS '最大访问人数';
COMMENT ON COLUMN ext_user_notes.verify_video IS '验证视频';




DROP TABLE IF EXISTS ext_user_notes_log;
CREATE TABLE IF NOT EXISTS ext_user_notes_log (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',
    notes_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    day  VARCHAR(255) NOT NULL DEFAULT '',
    count  VARCHAR(255) NOT NULL DEFAULT '',
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX IF NOT EXISTS ext_user_notes_log_notes_id ON ext_user_notes_log(notes_id);
CREATE  INDEX IF NOT EXISTS ext_user_notes_log_user_id ON ext_user_notes_log(user_id);

-- 创建注释
COMMENT ON TABLE circle_content_report IS '圈子投诉表';
COMMENT ON COLUMN circle_content_report.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN circle_content_report.content IS '内容';



DROP TABLE IF EXISTS ext_user_notes_group;
CREATE TABLE IF NOT EXISTS ext_user_notes_group (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    groups  VARCHAR(255) NOT NULL DEFAULT '',
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX IF NOT EXISTS ext_user_notes_group_user_id ON ext_user_notes_group(user_id);

-- 创建注释
COMMENT ON TABLE ext_user_notes_group IS '笔记用户分组表';
COMMENT ON COLUMN ext_user_notes_group.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_user_notes_group.groups IS '组名';


DROP TABLE IF EXISTS ext_user_notes_label;
CREATE TABLE IF NOT EXISTS ext_user_notes_label (
    id BIGSERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    label  VARCHAR(255) NOT NULL DEFAULT '',
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引
CREATE  INDEX  ext_user_notes_label_user_id ON ext_user_notes_label(user_id);

-- 创建注释
COMMENT ON TABLE ext_user_notes_label IS '圈子投诉表';
COMMENT ON COLUMN ext_user_notes_label.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_user_notes_label.label IS '标签';


ALTER TABLE circle_content_report ADD COLUMN type INT;
ALTER TABLE circle_content_report ADD COLUMN images text;

COMMENT ON COLUMN circle_content_report.type IS '1,发布不适当内容对我造成骚扰,2发布色情内容对我造成骚扰,3发布违法违禁内容对我造成骚扰
    4发布赌博内容对我造成骚扰，5发布政治造谣内容对我造成骚扰，6，发布暴恐血腥内容对我造成骚扰，7发布其他违规内容对我造成骚扰
    8，存在欺诈骗钱行为，9，此账号可能被盗，10存在侵权行为，11，发布仿冒品信息';


DROP TABLE IF EXISTS ext_notes_read;
CREATE TABLE IF NOT EXISTS ext_notes_read (
   id BIGSERIAL PRIMARY KEY,
    notes_id VARCHAR(255) NOT NULL DEFAULT '',                    -- 圈子ID（关联圈子表）
    user_id VARCHAR(255) NOT NULL DEFAULT '',                     -- 用户ID（关联用户表）
    read_type  int not null default 1,
    created  int not null default 0,
    updated  int not null default 0
    );

-- 创建索引

CREATE  INDEX  ext_notes_read_notes_id_user_id ON ext_notes_read(notes_id, user_id);

-- 创建注释
COMMENT ON TABLE ext_notes_read IS '笔记读记录表';
COMMENT ON COLUMN ext_notes_read.id IS '记录唯一标识符，自增主键';
COMMENT ON COLUMN ext_notes_read.updated IS '最后一次读取时间';
COMMENT ON COLUMN ext_notes_read.read_type IS '1笔记';