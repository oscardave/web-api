-- 用户备注表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_remarks_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户备注表
DROP TABLE IF EXISTS ext_user_remarks;
CREATE TABLE IF NOT EXISTS ext_user_remarks (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_remarks_id_seq'),
    user_id VARCHAR(255) NOT NULL DEFAULT '',                        -- 保存备注的用户ID（a用户，matrix_id格式）
    remark_user_id VARCHAR(255) NOT NULL DEFAULT '',                  -- 被备注的用户ID（b用户，matrix_id格式）
    remark_name VARCHAR(255) NOT NULL DEFAULT '',                    -- 备注名称（如：大大、小小）
    
    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                              -- 记录状态：1-有效，2-无效
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,   -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP    -- 记录更新时间
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_remarks_user_pair ON ext_user_remarks(user_id, remark_user_id) WHERE status = 1;
CREATE INDEX IF NOT EXISTS idx_ext_user_remarks_user_id ON ext_user_remarks(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_remarks_remark_user_id ON ext_user_remarks(remark_user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_remarks_status ON ext_user_remarks(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_remarks_created_at ON ext_user_remarks(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_remarks_updated_at ON ext_user_remarks(updated_at);

-- 创建注释
COMMENT ON TABLE ext_user_remarks IS '用户备注表，存储用户对其他用户的备注信息，例如用户a对用户b的备注为"大大"，用户c对用户b的备注为"小小"';
COMMENT ON COLUMN ext_user_remarks.id IS '备注记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_remarks.user_id IS '保存备注的用户ID（a用户，matrix_id格式，如：@user1:example.com），关联ext_users表的user_id字段';
COMMENT ON COLUMN ext_user_remarks.remark_user_id IS '被备注的用户ID（b用户，matrix_id格式，如：@user2:example.com），关联ext_users表的user_id字段';
COMMENT ON COLUMN ext_user_remarks.remark_name IS '备注名称，用户自定义的对其他用户的称呼（如：大大、小小等）';
COMMENT ON COLUMN ext_user_remarks.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_user_remarks.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_remarks.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_remarks_updated_at
    BEFORE UPDATE ON ext_user_remarks
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_remarks (user_id, remark_user_id, remark_name, status) VALUES
('@user1:example.com', '@user2:example.com', '大大', 1),
('@user1:example.com', '@user3:example.com', '老铁', 1),
('@user3:example.com', '@user2:example.com', '小小', 1),
('@user2:example.com', '@user1:example.com', '老板', 1),
('@user4:example.com', '@user2:example.com', '客户A', 1),
('@user5:example.com', '@user2:example.com', 'VIP用户', 1);

