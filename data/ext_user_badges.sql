-- 用户徽章表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_badges_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户徽章表
DROP TABLE IF EXISTS ext_user_badges;
CREATE TABLE IF NOT EXISTS ext_user_badges (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_badges_id_seq'),
    name VARCHAR(100) NOT NULL DEFAULT '',
    type SMALLINT NOT NULL DEFAULT 1,
    icon_url VARCHAR(500) NOT NULL DEFAULT '',

    -- 兑换和会员要求
    points_exchange_cost INTEGER NOT NULL DEFAULT 0,
    membership_level_required INTEGER NOT NULL DEFAULT 0,

    -- 诚信相关要求
    integrity_score_required INTEGER NOT NULL DEFAULT 0,
    invited_users_required INTEGER NOT NULL DEFAULT 0,
    monthly_help_posts_required INTEGER NOT NULL DEFAULT 0,

    -- 其他属性
    is_exchangeable BOOLEAN NOT NULL DEFAULT false,
    description TEXT NOT NULL DEFAULT '',
    sort_order INTEGER NOT NULL DEFAULT 0,

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_badges_type ON ext_user_badges(type);
CREATE INDEX IF NOT EXISTS idx_ext_user_badges_status ON ext_user_badges(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_badges_sort_order ON ext_user_badges(sort_order);
CREATE INDEX IF NOT EXISTS idx_ext_user_badges_is_exchangeable ON ext_user_badges(is_exchangeable);

-- 创建注释
COMMENT ON TABLE ext_user_badges IS '用户徽章表，存储系统中所有可用的用户徽章信息';
COMMENT ON COLUMN ext_user_badges.id IS '徽章唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_badges.name IS '徽章名称';
COMMENT ON COLUMN ext_user_badges.type IS '徽章类型：1-用户身份徽章，2-诚信徽章';
COMMENT ON COLUMN ext_user_badges.icon_url IS '徽章图标URL';
COMMENT ON COLUMN ext_user_badges.points_exchange_cost IS '积分兑换所需积分';
COMMENT ON COLUMN ext_user_badges.membership_level_required IS '会员等级要求';
COMMENT ON COLUMN ext_user_badges.integrity_score_required IS '当前诚信保分值要求';
COMMENT ON COLUMN ext_user_badges.invited_users_required IS '累计邀请入圈人数要求';
COMMENT ON COLUMN ext_user_badges.monthly_help_posts_required IS '月发布帮办数量要求';
COMMENT ON COLUMN ext_user_badges.is_exchangeable IS '能否被兑换';
COMMENT ON COLUMN ext_user_badges.description IS '徽章描述';
COMMENT ON COLUMN ext_user_badges.sort_order IS '排序权重';
COMMENT ON COLUMN ext_user_badges.status IS '徽章状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_user_badges.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_badges.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_badges_updated_at
    BEFORE UPDATE ON ext_user_badges
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据 - 用户身份徽章
INSERT INTO ext_user_badges (name, type, icon_url, points_exchange_cost, membership_level_required, integrity_score_required, invited_users_required, monthly_help_posts_required, is_exchangeable, description, sort_order, status) VALUES
('纯发用户', 1, '/static/badges/identity/pure_user.png', 0, 0, 0, 0, 0, false, '纯发用户身份徽章，用于标识基础用户身份', 1, 1),
('圈中长老', 1, '/static/badges/identity/elder.png', 0, 0, 0, 0, 0, false, '圈中长老身份徽章，用于标识资深用户', 2, 1),
('官方客服', 1, '/static/badges/identity/customer_service.png', 0, 0, 0, 0, 0, false, '官方客服身份徽章，用于标识官方客服人员', 3, 1);

-- 插入测试数据 - 诚信徽章
INSERT INTO ext_user_badges (name, type, icon_url, points_exchange_cost, membership_level_required, integrity_score_required, invited_users_required, monthly_help_posts_required, is_exchangeable, description, sort_order, status) VALUES
('银盾担保', 2, '/static/badges/integrity/silver_shield.png', 0, 0, 500, 0, 0, false, '银盾担保徽章，需要500诚信保分值', 1, 1),
('金盾担保', 2, '/static/badges/integrity/gold_shield.png', 0, 0, 1000, 0, 0, false, '金盾担保徽章，需要1000诚信保分值', 2, 1),
('黑奢担保', 2, '/static/badges/integrity/black_luxury.png', 0, 0, 3000, 0, 0, false, '黑奢担保徽章，需要3000诚信保分值', 3, 1);
