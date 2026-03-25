-- 帮助表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_helps_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建帮助表
DROP TABLE IF EXISTS ext_helps;
CREATE TABLE IF NOT EXISTS ext_helps (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_helps_id_seq'),
    category_id BIGINT NOT NULL DEFAULT 0,
    title VARCHAR(100) NOT NULL DEFAULT '',
    content TEXT NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_helps_category_id ON ext_helps(category_id);
CREATE INDEX IF NOT EXISTS idx_ext_helps_status ON ext_helps(status);
CREATE INDEX IF NOT EXISTS idx_ext_helps_sort_order ON ext_helps(sort_order);
CREATE INDEX IF NOT EXISTS idx_ext_helps_title ON ext_helps(title);

-- 创建注释
COMMENT ON TABLE ext_helps IS '帮助表，存储系统中帮助信息';
COMMENT ON COLUMN ext_helps.id IS '帮助唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_helps.category_id IS '帮助分类ID';
COMMENT ON COLUMN ext_helps.title IS '帮助标题';
COMMENT ON COLUMN ext_helps.content IS '帮助内容';
COMMENT ON COLUMN ext_helps.status IS '帮助状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_helps.sort_order IS '排序字段，数值越小排序越靠前';
COMMENT ON COLUMN ext_helps.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_helps.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_helps_updated_at
    BEFORE UPDATE ON ext_helps
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_helps (category_id, title, content, status, sort_order) VALUES
(10000, '如何注册账户', '用户可以通过手机号码或邮箱地址注册新账户。注册时需要填写基本信息，包括用户名、密码等。注册成功后即可登录使用系统。', 1, 1),
(10000, '如何修改密码', '用户可以在个人设置页面修改登录密码。点击"安全设置"->"修改密码"，输入当前密码和新密码即可完成修改。', 1, 2),
(10000, '如何绑定手机号', '在个人资料页面，点击"绑定手机号"，输入手机号码并获取验证码，验证通过后即可完成绑定。', 1, 3),
(10001, '如何使用搜索功能', '系统提供强大的搜索功能，用户可以在搜索框中输入关键词，系统会智能匹配相关内容。支持模糊搜索和精确搜索。', 1, 1),
(10001, '如何上传文件', '用户可以在相应页面点击"上传"按钮，选择本地文件进行上传。支持多种文件格式，单个文件大小不超过10MB。', 1, 2),
(10001, '如何设置个人资料', '点击右上角头像，选择"个人资料"，可以修改头像、昵称、邮箱等个人信息。修改后需要保存才能生效。', 1, 3),
(10002, '如何充值', '用户可以通过支付宝、微信支付等方式进行充值。在"我的钱包"页面点击"充值"，选择充值金额和支付方式即可。', 1, 1),
(10002, '如何提现', '在"我的钱包"页面点击"提现"，输入提现金额和银行卡信息，提交申请后会在1-3个工作日内到账。', 1, 2),
(10002, '如何查看交易记录', '在"我的钱包"页面可以查看所有充值、消费、提现等交易记录，支持按时间筛选和导出。', 1, 3),
(10003, '如何开启两步验证', '在"安全设置"页面开启两步验证功能，绑定手机号或邮箱，登录时需要输入验证码，提高账户安全性。', 1, 1),
(10003, '如何设置登录保护', '可以设置登录保护，包括异地登录提醒、异常登录拦截等功能，有效保护账户安全。', 1, 2),
(10004, '忘记密码怎么办', '在登录页面点击"忘记密码"，输入注册时的手机号或邮箱，系统会发送重置密码的链接到您的手机或邮箱。', 1, 1),
(10004, '账户被锁定怎么办', '如果账户被锁定，请联系客服人员，提供身份证明信息，客服会协助您解锁账户。', 1, 2),
(10004, '如何联系客服', '您可以通过在线客服、客服电话、邮箱等多种方式联系我们的客服团队，我们会及时为您解决问题。', 1, 3);

create view ext_helps_v as
select
    h.id,
    h.category_id,
    h.title,
    c.name as category_name,
    h.content,
    h.status,
    h.sort_order,
    h.created_at,
    h.updated_at
from ext_helps h
left join ext_help_categories c on h.category_id = c.id;
