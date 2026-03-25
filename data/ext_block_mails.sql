-- 封禁邮件表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_block_mails_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建封禁邮件表
DROP TABLE IF EXISTS ext_block_mails;
CREATE TABLE IF NOT EXISTS ext_block_mails (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_block_mails_id_seq'),
    mail VARCHAR(100) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_block_mails_mail ON ext_block_mails(mail);

-- 创建注释
COMMENT ON TABLE ext_block_mails IS '封禁邮件表，存储系统中封禁的邮箱地址';
COMMENT ON COLUMN ext_block_mails.id IS '封禁邮件唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_block_mails.mail IS '封禁邮箱地址';
COMMENT ON COLUMN ext_block_mails.remark IS '封禁邮箱备注';
COMMENT ON COLUMN ext_block_mails.status IS '封禁邮箱状态：2-已封禁，1-已解封';
COMMENT ON COLUMN ext_block_mails.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_block_mails.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_block_mails_updated_at
    BEFORE UPDATE ON ext_block_mails
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_block_mails (mail, remark, status) VALUES
('spam@example.com', '垃圾邮件发送者', 2),
('fake@example.com', '虚假邮箱地址', 2),
('test@example.com', '测试邮箱', 2),
('admin@example.com', '管理员邮箱', 2),
('user@example.com', '用户邮箱', 2),
('support@example.com', '客服邮箱', 2);
