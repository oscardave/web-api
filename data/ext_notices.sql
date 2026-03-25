-- 公告表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_notices_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建公告表
DROP TABLE IF EXISTS ext_notices;
CREATE TABLE IF NOT EXISTS ext_notices (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_notices_id_seq'),
    title VARCHAR(100) NOT NULL DEFAULT '',
    content TEXT NOT NULL DEFAULT '',
    type SMALLINT NOT NULL DEFAULT 1,
    status SMALLINT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    start_time TIMESTAMP WITH TIME ZONE DEFAULT NULL,
    end_time TIMESTAMP WITH TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_notices_title ON ext_notices(title);
CREATE INDEX IF NOT EXISTS idx_ext_notices_type ON ext_notices(type);
CREATE INDEX IF NOT EXISTS idx_ext_notices_status ON ext_notices(status);
CREATE INDEX IF NOT EXISTS idx_ext_notices_sort_order ON ext_notices(sort_order);
CREATE INDEX IF NOT EXISTS idx_ext_notices_time_range ON ext_notices(start_time, end_time);

-- 创建注释
COMMENT ON TABLE ext_notices IS '公告表，存储系统中公告信息';
COMMENT ON COLUMN ext_notices.id IS '公告唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_notices.title IS '公告标题';
COMMENT ON COLUMN ext_notices.content IS '公告内容';
COMMENT ON COLUMN ext_notices.type IS '公告类型：1-系统公告，2-维护公告，3-活动公告';
COMMENT ON COLUMN ext_notices.status IS '公告状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_notices.sort_order IS '排序字段，数值越小排序越靠前';
COMMENT ON COLUMN ext_notices.start_time IS '公告开始时间，NULL表示立即生效';
COMMENT ON COLUMN ext_notices.end_time IS '公告结束时间，NULL表示永久有效';
COMMENT ON COLUMN ext_notices.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_notices.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_notices_updated_at
    BEFORE UPDATE ON ext_notices
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_notices (title, content, type, status, sort_order, start_time, end_time) VALUES
('系统维护通知', '系统将于今晚22:00-24:00进行维护升级，期间可能影响正常使用，请提前做好准备。', 2, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP + INTERVAL '7 days'),
('新功能上线', '我们很高兴地宣布，新版本功能已正式上线！包括用户界面优化、性能提升等多项改进。', 1, 1, 2, CURRENT_TIMESTAMP, NULL),
('春节活动公告', '春节期间，我们将举办一系列精彩活动，包括签到送积分、限时优惠等，详情请查看活动页面。', 3, 1, 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP + INTERVAL '30 days'),
('安全提醒', '请用户注意保护个人账户安全，不要将密码告知他人，如发现异常请及时联系客服。', 1, 1, 4, CURRENT_TIMESTAMP, NULL),
('服务条款更新', '我们的服务条款已更新，主要涉及隐私保护和使用规范，请用户仔细阅读。', 1, 1, 5, CURRENT_TIMESTAMP, NULL);
