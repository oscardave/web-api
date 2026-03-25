-- 用户拉黑关系表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_blocks_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户拉黑关系表
DROP TABLE IF EXISTS ext_user_blocks;
CREATE TABLE IF NOT EXISTS ext_user_blocks (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_blocks_id_seq'),
    blocker_user_id VARCHAR(255) NOT NULL DEFAULT '',              -- 拉黑者用户ID
    blocked_user_id VARCHAR(255) NOT NULL DEFAULT '',               -- 被拉黑者用户ID
    remark VARCHAR(255) NOT NULL DEFAULT '',                        -- 拉黑备注
    status SMALLINT NOT NULL DEFAULT 1,                             -- 拉黑状态：1-拉黑中，2-已解除拉黑
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,  -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP   -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_blocks_user_pair ON ext_user_blocks(blocker_user_id, blocked_user_id) WHERE status = 1;
CREATE INDEX IF NOT EXISTS idx_ext_user_blocks_blocker ON ext_user_blocks(blocker_user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_blocks_blocked ON ext_user_blocks(blocked_user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_blocks_status ON ext_user_blocks(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_blocks_created_at ON ext_user_blocks(created_at);

-- 创建注释
COMMENT ON TABLE ext_user_blocks IS '用户拉黑关系表，存储用户之间的拉黑关系，记录哪些用户拉黑了另外的哪些用户';
COMMENT ON COLUMN ext_user_blocks.id IS '拉黑关系唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_blocks.blocker_user_id IS '拉黑者用户ID，关联ext_users表的user_id字段';
COMMENT ON COLUMN ext_user_blocks.blocked_user_id IS '被拉黑者用户ID，关联ext_users表的user_id字段';
COMMENT ON COLUMN ext_user_blocks.remark IS '拉黑备注信息';
COMMENT ON COLUMN ext_user_blocks.status IS '拉黑状态：1-拉黑中，2-已解除拉黑';
COMMENT ON COLUMN ext_user_blocks.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_blocks.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_blocks_updated_at
    BEFORE UPDATE ON ext_user_blocks
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_blocks (blocker_user_id, blocked_user_id, remark, status) VALUES
('@user1:example.com', '@user2:example.com', '用户1拉黑用户2', 1),
('@user1:example.com', '@user3:example.com', '用户1拉黑用户3', 1),
('@user2:example.com', '@user4:example.com', '用户2拉黑用户4', 1),
('@user3:example.com', '@user1:example.com', '用户3拉黑用户1', 1),
('@user4:example.com', '@user2:example.com', '用户4拉黑用户2（已解除）', 2);
