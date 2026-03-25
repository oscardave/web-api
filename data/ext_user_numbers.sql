-- 用户靓号表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_vanity_number_types_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE SEQUENCE IF NOT EXISTS ext_user_vanity_numbers_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建靓号类型表
DROP TABLE IF EXISTS ext_vanity_number_types;
CREATE TABLE IF NOT EXISTS ext_vanity_number_types (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_vanity_number_types_id_seq'),
    type_name VARCHAR(100) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    digit_counts JSONB NOT NULL DEFAULT '[]',
    points_cost INTEGER NOT NULL DEFAULT 0,
    discount_type VARCHAR(50) NOT NULL DEFAULT '无折扣',
    invite_discount_rules JSONB NOT NULL DEFAULT '{}',
    member_discount_rules JSONB NOT NULL DEFAULT '{}',
    is_hidden_forbidden_sale BOOLEAN NOT NULL DEFAULT FALSE,
    is_frontend_display_default BOOLEAN NOT NULL DEFAULT TRUE,
    account_type VARCHAR(50) NOT NULL DEFAULT '普通用户',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建用户靓号表
DROP TABLE IF EXISTS ext_user_vanity_numbers;
CREATE TABLE IF NOT EXISTS ext_user_vanity_numbers (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_vanity_numbers_id_seq'),
    user_id BIGINT NOT NULL,
    vanity_number VARCHAR(20) NOT NULL,
    type_id BIGINT NOT NULL,
    is_in_selected_pool BOOLEAN NOT NULL DEFAULT FALSE,
    is_frontend_display BOOLEAN NOT NULL DEFAULT TRUE,
    points_paid INTEGER NOT NULL DEFAULT 0,
    discount_applied NUMERIC(5,2) NOT NULL DEFAULT 1.00,
    purchase_method VARCHAR(50) NOT NULL DEFAULT '积分兑换',
    purchase_reason VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_vanity_number_types_name ON ext_vanity_number_types(type_name);
CREATE INDEX IF NOT EXISTS idx_ext_vanity_number_types_status ON ext_vanity_number_types(status);

CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_vanity_numbers_number ON ext_user_vanity_numbers(vanity_number);
CREATE INDEX IF NOT EXISTS idx_ext_user_vanity_numbers_user_id ON ext_user_vanity_numbers(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_vanity_numbers_type_id ON ext_user_vanity_numbers(type_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_vanity_numbers_status ON ext_user_vanity_numbers(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_vanity_numbers_selected_pool ON ext_user_vanity_numbers(is_in_selected_pool);
CREATE INDEX IF NOT EXISTS idx_ext_user_vanity_numbers_frontend_display ON ext_user_vanity_numbers(is_frontend_display);

-- 创建外键约束
ALTER TABLE ext_user_vanity_numbers
ADD CONSTRAINT fk_ext_user_vanity_numbers_type_id
FOREIGN KEY (type_id) REFERENCES ext_vanity_number_types(id) ON DELETE RESTRICT;

-- 创建注释
COMMENT ON TABLE ext_vanity_number_types IS '靓号类型表，存储靓号类型的配置信息';
COMMENT ON COLUMN ext_vanity_number_types.id IS '靓号类型唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_vanity_number_types.type_name IS '靓号类型名称，如"内部保留号禁售"、"零元购限时邀请福利靓号"';
COMMENT ON COLUMN ext_vanity_number_types.description IS '靓号类型描述';
COMMENT ON COLUMN ext_vanity_number_types.digit_counts IS '支持的位数，JSON数组格式，如[5,6,7,8,9]';
COMMENT ON COLUMN ext_vanity_number_types.points_cost IS '积分兑换成本';
COMMENT ON COLUMN ext_vanity_number_types.discount_type IS '折扣类型，如"无折扣"、"邀请折扣"等';
COMMENT ON COLUMN ext_vanity_number_types.invite_discount_rules IS '邀请好友折扣规则，JSON格式存储邀请人数和对应折扣';
COMMENT ON COLUMN ext_vanity_number_types.member_discount_rules IS '会员购买折扣规则，JSON格式存储会员等级和对应折扣';
COMMENT ON COLUMN ext_vanity_number_types.is_hidden_forbidden_sale IS '是否隐藏禁售';
COMMENT ON COLUMN ext_vanity_number_types.is_frontend_display_default IS '是否默认前端显示';
COMMENT ON COLUMN ext_vanity_number_types.account_type IS '账号类型，如"普通用户"、"VIP用户"等';
COMMENT ON COLUMN ext_vanity_number_types.status IS '靓号类型状态：1-启用，2-禁用';
COMMENT ON COLUMN ext_vanity_number_types.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_vanity_number_types.updated_at IS '记录更新时间，自动设置为当前时间';

COMMENT ON TABLE ext_user_vanity_numbers IS '用户靓号表，存储用户与靓号的分配关系';
COMMENT ON COLUMN ext_user_vanity_numbers.id IS '用户靓号唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_vanity_numbers.user_id IS '用户ID';
COMMENT ON COLUMN ext_user_vanity_numbers.vanity_number IS '靓号，如"6655665"、"11111"等';
COMMENT ON COLUMN ext_user_vanity_numbers.type_id IS '靓号类型ID，关联ext_vanity_number_types表';
COMMENT ON COLUMN ext_user_vanity_numbers.is_in_selected_pool IS '是否在精选靓号池';
COMMENT ON COLUMN ext_user_vanity_numbers.is_frontend_display IS '是否前端显示';
COMMENT ON COLUMN ext_user_vanity_numbers.points_paid IS '实际支付的积分';
COMMENT ON COLUMN ext_user_vanity_numbers.discount_applied IS '应用的折扣，如0.8表示8折';
COMMENT ON COLUMN ext_user_vanity_numbers.purchase_method IS '购买方式，如"积分兑换"、"零元购"、"邀请福利"等';
COMMENT ON COLUMN ext_user_vanity_numbers.purchase_reason IS '购买原因或备注';
COMMENT ON COLUMN ext_user_vanity_numbers.status IS '用户靓号状态：1-正常使用，2-已回收，3-已转让';
COMMENT ON COLUMN ext_user_vanity_numbers.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_vanity_numbers.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_vanity_number_types_updated_at
    BEFORE UPDATE ON ext_vanity_number_types
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_ext_user_vanity_numbers_updated_at
    BEFORE UPDATE ON ext_user_vanity_numbers
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 创建视图，获取用户靓号及类型信息
CREATE OR REPLACE VIEW ext_user_vanity_numbers_v AS
SELECT
    uvn.id,
    uvn.user_id,
    uvn.vanity_number,
    uvn.type_id,
    vnt.type_name,
    vnt.description as type_description,
    vnt.digit_counts,
    vnt.points_cost as type_points_cost,
    vnt.discount_type,
    vnt.invite_discount_rules,
    vnt.member_discount_rules,
    vnt.is_hidden_forbidden_sale as type_is_hidden_forbidden_sale,
    vnt.is_frontend_display_default as type_is_frontend_display_default,
    vnt.account_type,
    vnt.status as type_status,
    uvn.is_in_selected_pool,
    uvn.is_frontend_display,
    uvn.points_paid,
    uvn.discount_applied,
    uvn.purchase_method,
    uvn.purchase_reason,
    uvn.status,
    uvn.created_at,
    uvn.updated_at
FROM ext_user_vanity_numbers uvn
LEFT JOIN ext_vanity_number_types vnt ON uvn.type_id = vnt.id;

-- 创建视图注释
COMMENT ON VIEW ext_user_vanity_numbers_v IS '用户靓号视图，包含用户靓号信息和对应的类型信息';

-- 插入测试数据 - 靓号类型
INSERT INTO ext_vanity_number_types (type_name, description, digit_counts, points_cost, discount_type, invite_discount_rules, member_discount_rules, is_hidden_forbidden_sale, is_frontend_display_default, account_type) VALUES
('内部保留号禁售', '内部保留号禁售，限时限量特供', '[5,6,7,8,9]', 8888, '无折扣', '{}', '{}', TRUE, FALSE, '普通用户'),
('零元购限时邀请福利靓号', '零元购限时邀请福利靓号，邀请好友入圈即可享受', '[9]', 1998, '邀请折扣',
 '{"5": 0.8, "10": 0.7, "30": 0.5, "50": 0.01}',
 '{"初级会员": 0.9, "高级会员": 0.85, "超级会员": 0.8, "至尊会员": 0.7}',
 FALSE, TRUE, '普通用户');

-- 插入测试数据 - 用户靓号
INSERT INTO ext_user_vanity_numbers (user_id, vanity_number, type_id, is_in_selected_pool, is_frontend_display, points_paid, discount_applied, purchase_method, purchase_reason) VALUES
(1001, '6655665', 10000, FALSE, FALSE, 8888, 1.00, '积分兑换', '后台直接分配'),
(1002, '11111', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1003, '22222', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1004, '33333', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1005, '44444', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1006, '55555', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1007, '66666', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1008, '77777', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1009, '88888', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1010, '99999', 10000, TRUE, FALSE, 0, 1.00, '内部保留', '精选靓号池'),
(1011, '235685524', 10001, FALSE, TRUE, 1598, 0.8, '邀请福利', '邀请5个好友入圈'),
(1012, '955663844', 10001, FALSE, TRUE, 1998, 1.00, '积分兑换', '直接积分购买'),
(1013, '652564651', 10001, FALSE, TRUE, 1399, 0.7, '邀请福利', '邀请10个好友入圈'),
(1014, '955454533', 10001, FALSE, TRUE, 999, 0.5, '邀请福利', '邀请30个好友入圈'),
(1015, '566454654', 10001, FALSE, TRUE, 20, 0.01, '邀请福利', '邀请50个好友入圈');
