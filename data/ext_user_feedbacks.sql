-- 用户反馈表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_feedbacks_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户反馈表
DROP TABLE IF EXISTS ext_user_feedbacks;
CREATE TABLE IF NOT EXISTS ext_user_feedbacks (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_feedbacks_id_seq'),
    type SMALLINT NOT NULL DEFAULT 1,
    content TEXT NOT NULL DEFAULT '',
    user_id BIGINT NOT NULL DEFAULT 0,
    target_type VARCHAR(20) NOT NULL DEFAULT 'other',
    target_id BIGINT NOT NULL DEFAULT 0,
    source_tag VARCHAR(50) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    reviewer_id BIGINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_feedbacks_type ON ext_user_feedbacks(type);
CREATE INDEX IF NOT EXISTS idx_ext_user_feedbacks_user_id ON ext_user_feedbacks(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_feedbacks_status ON ext_user_feedbacks(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_feedbacks_created_at ON ext_user_feedbacks(created_at);

-- 创建注释
COMMENT ON TABLE ext_user_feedbacks IS '用户反馈表，存储用户投诉和建议';
COMMENT ON COLUMN ext_user_feedbacks.id IS '反馈唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_feedbacks.type IS '反馈类型：1-投诉，2-建议';
COMMENT ON COLUMN ext_user_feedbacks.content IS '反馈内容';
COMMENT ON COLUMN ext_user_feedbacks.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_user_feedbacks.target_type IS '投诉对象类型：notes-笔记，chat-聊天，help-帮办，dynamic-动态，other-其他';
COMMENT ON COLUMN ext_user_feedbacks.target_id IS '投诉对象ID';
COMMENT ON COLUMN ext_user_feedbacks.source_tag IS '来源标签';
COMMENT ON COLUMN ext_user_feedbacks.status IS '审核状态：1-未审核，2-已审核处理，3-无法认定';
COMMENT ON COLUMN ext_user_feedbacks.reviewer_id IS '审核人ID，关联管理员表';
COMMENT ON COLUMN ext_user_feedbacks.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_feedbacks.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_feedbacks_updated_at
    BEFORE UPDATE ON ext_user_feedbacks
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_feedbacks (type, content, user_id, target_type, target_id, source_tag, status, reviewer_id) VALUES
(1, '投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉投诉', 12345678, '笔记', 123456, '来自笔记投诉', 1, 0),
(1, 'ID:12345678  来自勋办投诉', 12345678, 'help', 123456, '来自勋办投诉', 2, 3),
(2, 'ID:12345678  来自勋办投诉', 12345678, 'help', 123456, '来自勋办投诉', 1, 0),
(1, 'ID:12345678  来自勋办投诉', 12345678, 'system', 0, '来自勋办投诉', 3, 3),
(2, '其他', 12345678, 'other', 0, '无法认定', 3, 3);

-- 创建视图
CREATE VIEW ext_user_feedbacks_v AS
SELECT
    f.id,
    f.type,
    CASE f.type
        WHEN 1 THEN '投诉'
        WHEN 2 THEN '建议'
        ELSE '未知'
    END as type_name,
    f.content,
    f.user_id,
    f.target_type,
    f.target_id,
    f.source_tag,
    f.status,
    CASE f.status
        WHEN 1 THEN '未审核'
        WHEN 2 THEN '已审核处理'
        WHEN 3 THEN '无法认定'
        ELSE '未知'
    END as status_name,
    f.reviewer_id,
    f.created_at,
    f.updated_at
FROM ext_user_feedbacks f;
