-- 用户笔记表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_notes_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户笔记表
DROP TABLE IF EXISTS ext_user_notes;
CREATE TABLE IF NOT EXISTS ext_user_notes (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_notes_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL DEFAULT '',
    content TEXT NOT NULL DEFAULT '',

    -- 笔记分类和标签
    category_id BIGINT NOT NULL DEFAULT 0,           -- 笔记分类ID，关联分类表
    tags VARCHAR(500) NOT NULL DEFAULT '',          -- 笔记标签，多个标签用逗号分隔
    note_type SMALLINT NOT NULL DEFAULT 1,          -- 笔记类型：1-普通笔记，2-超级坐席笔记，3-系统笔记

    -- 访问统计
    view_count INTEGER NOT NULL DEFAULT 0,           -- 总访问次数
    today_view_count INTEGER NOT NULL DEFAULT 0,     -- 今日访问次数
    last_viewed_at TIMESTAMP WITH TIME ZONE,        -- 最后访问时间

    -- 互动统计
    like_count INTEGER NOT NULL DEFAULT 0,          -- 点赞数
    collect_count INTEGER NOT NULL DEFAULT 0,        -- 收藏数
    share_count INTEGER NOT NULL DEFAULT 0,          -- 分享数
    comment_count INTEGER NOT NULL DEFAULT 0,        -- 评论数

    -- 权限和可见性
    visibility SMALLINT NOT NULL DEFAULT 1,          -- 可见性：1-公开，2-仅好友，3-仅自己，4-指定用户
    is_pinned BOOLEAN NOT NULL DEFAULT false,        -- 是否置顶
    is_featured BOOLEAN NOT NULL DEFAULT false,      -- 是否精选
    allow_comment BOOLEAN NOT NULL DEFAULT true,     -- 是否允许评论

    -- 限流和审核
    rate_limit_enabled BOOLEAN NOT NULL DEFAULT false, -- 是否启用限流
    rate_limit_count INTEGER NOT NULL DEFAULT 0,      -- 限流次数
    rate_limit_period INTEGER NOT NULL DEFAULT 0,     -- 限流周期（分钟）
    audit_status SMALLINT NOT NULL DEFAULT 1,         -- 审核状态：1-待审核，2-审核通过，3-审核拒绝
    auditor_id BIGINT NOT NULL DEFAULT 0,            -- 审核人ID
    audited_at TIMESTAMP WITH TIME ZONE,             -- 审核时间

    -- 内容相关
    summary TEXT NOT NULL DEFAULT '',               -- 笔记摘要
    cover_image VARCHAR(500) NOT NULL DEFAULT '',   -- 封面图片
    content_type SMALLINT NOT NULL DEFAULT 1,       -- 内容类型：1-纯文本，2-富文本，3-图片，4-视频
    word_count INTEGER NOT NULL DEFAULT 0,          -- 字数统计

    -- 排序和推荐
    sort_order INTEGER NOT NULL DEFAULT 0,          -- 排序权重
    recommend_score DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- 推荐分数
    is_hot BOOLEAN NOT NULL DEFAULT false,          -- 是否热门

    -- 扩展字段
    source VARCHAR(100) NOT NULL DEFAULT '',        -- 来源：web、app、api等
    device_info VARCHAR(200) NOT NULL DEFAULT '',   -- 设备信息
    ip_address VARCHAR(50) NOT NULL DEFAULT '',      -- IP地址
    location VARCHAR(100) NOT NULL DEFAULT '',       -- 地理位置

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_user_id ON ext_user_notes(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_category_id ON ext_user_notes(category_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_note_type ON ext_user_notes(note_type);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_status ON ext_user_notes(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_visibility ON ext_user_notes(visibility);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_audit_status ON ext_user_notes(audit_status);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_created_at ON ext_user_notes(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_view_count ON ext_user_notes(view_count);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_recommend_score ON ext_user_notes(recommend_score);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_is_hot ON ext_user_notes(is_hot);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_is_featured ON ext_user_notes(is_featured);
CREATE INDEX IF NOT EXISTS idx_ext_user_notes_is_pinned ON ext_user_notes(is_pinned);

-- 创建注释
COMMENT ON TABLE ext_user_notes IS '用户笔记表，存储系统中用户笔记信息';
COMMENT ON COLUMN ext_user_notes.id IS '用户笔记唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_notes.user_id IS '用户ID';
COMMENT ON COLUMN ext_user_notes.title IS '用户笔记标题';
COMMENT ON COLUMN ext_user_notes.content IS '用户笔记内容';
COMMENT ON COLUMN ext_user_notes.category_id IS '笔记分类ID，关联分类表';
COMMENT ON COLUMN ext_user_notes.tags IS '笔记标签，多个标签用逗号分隔';
COMMENT ON COLUMN ext_user_notes.note_type IS '笔记类型：1-普通笔记，2-超级坐席笔记，3-系统笔记';
COMMENT ON COLUMN ext_user_notes.view_count IS '总访问次数';
COMMENT ON COLUMN ext_user_notes.today_view_count IS '今日访问次数';
COMMENT ON COLUMN ext_user_notes.last_viewed_at IS '最后访问时间';
COMMENT ON COLUMN ext_user_notes.like_count IS '点赞数';
COMMENT ON COLUMN ext_user_notes.collect_count IS '收藏数';
COMMENT ON COLUMN ext_user_notes.share_count IS '分享数';
COMMENT ON COLUMN ext_user_notes.comment_count IS '评论数';
COMMENT ON COLUMN ext_user_notes.visibility IS '可见性：1-公开，2-仅好友，3-仅自己，4-指定用户';
COMMENT ON COLUMN ext_user_notes.is_pinned IS '是否置顶';
COMMENT ON COLUMN ext_user_notes.is_featured IS '是否精选';
COMMENT ON COLUMN ext_user_notes.allow_comment IS '是否允许评论';
COMMENT ON COLUMN ext_user_notes.rate_limit_enabled IS '是否启用限流';
COMMENT ON COLUMN ext_user_notes.rate_limit_count IS '限流次数';
COMMENT ON COLUMN ext_user_notes.rate_limit_period IS '限流周期（分钟）';
COMMENT ON COLUMN ext_user_notes.audit_status IS '审核状态：1-待审核，2-审核通过，3-审核拒绝';
COMMENT ON COLUMN ext_user_notes.auditor_id IS '审核人ID';
COMMENT ON COLUMN ext_user_notes.audited_at IS '审核时间';
COMMENT ON COLUMN ext_user_notes.summary IS '笔记摘要';
COMMENT ON COLUMN ext_user_notes.cover_image IS '封面图片';
COMMENT ON COLUMN ext_user_notes.content_type IS '内容类型：1-纯文本，2-富文本，3-图片，4-视频';
COMMENT ON COLUMN ext_user_notes.word_count IS '字数统计';
COMMENT ON COLUMN ext_user_notes.sort_order IS '排序权重';
COMMENT ON COLUMN ext_user_notes.recommend_score IS '推荐分数';
COMMENT ON COLUMN ext_user_notes.is_hot IS '是否热门';
COMMENT ON COLUMN ext_user_notes.source IS '来源：web、app、api等';
COMMENT ON COLUMN ext_user_notes.device_info IS '设备信息';
COMMENT ON COLUMN ext_user_notes.ip_address IS 'IP地址';
COMMENT ON COLUMN ext_user_notes.location IS '地理位置';
COMMENT ON COLUMN ext_user_notes.status IS '用户笔记状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_user_notes.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_notes.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_notes_updated_at
    BEFORE UPDATE ON ext_user_notes
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_notes (user_id, title, content, category_id, tags, note_type, view_count, today_view_count, like_count, collect_count, share_count, comment_count, visibility, is_pinned, is_featured, allow_comment, rate_limit_enabled, audit_status, summary, content_type, word_count, sort_order, recommend_score, is_hot, source, device_info, ip_address, location, status) VALUES
(10001, '测试笔记1', '这是一个测试笔记内容，用于验证系统功能', 10001, '测试,笔记,功能', 1, 25, 5, 3, 1, 2, 0, 1, false, false, true, false, 2, '这是一个测试笔记的摘要', 1, 15, 0, 0.00, false, 'web', 'Chrome 120.0', '192.168.1.100', '北京市', 1),
(10002, '超级坐席笔记', '这是一个超级坐席笔记，具有特殊权限', 10002, '超级坐席,重要', 2, 150, 25, 15, 8, 5, 3, 1, true, true, true, true, 2, '超级坐席笔记摘要', 2, 50, 100, 8.50, true, 'app', 'iPhone 15 Pro', '192.168.1.101', '上海市', 1),
(10003, '系统公告', '系统维护通知', 10003, '系统,公告,维护', 3, 500, 100, 50, 20, 10, 5, 1, true, true, false, false, 2, '系统维护通知摘要', 1, 30, 200, 9.20, true, 'api', 'System', '127.0.0.1', '服务器', 1),
(10004, '普通用户笔记', '普通用户的个人笔记', 10001, '个人,生活', 1, 8, 2, 1, 0, 0, 0, 3, false, false, true, false, 1, '个人生活笔记', 1, 20, 0, 0.00, false, 'web', 'Firefox 121.0', '192.168.1.102', '广州市', 1),
(10005, '技术分享', '技术相关的分享内容', 10004, '技术,分享,编程', 1, 45, 12, 8, 3, 2, 1, 1, false, false, true, false, 2, '技术分享内容摘要', 2, 200, 10, 6.80, false, 'app', 'Android 14', '192.168.1.103', '深圳市', 1),
(10006, '限流测试笔记', '用于测试限流功能的笔记', 10001, '限流,测试', 1, 5, 5, 0, 0, 0, 0, 1, false, false, true, true, 1, '限流测试笔记', 1, 10, 0, 0.00, false, 'web', 'Safari 17.0', '192.168.1.104', '杭州市', 1);
