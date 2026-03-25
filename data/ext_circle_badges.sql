-- 圈子徽章表创建脚本
-- 用于管理后台配置圈子徽章，用户可被分配所属圈子徽章

CREATE SEQUENCE IF NOT EXISTS ext_circle_badges_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

DROP TABLE IF EXISTS ext_circle_badges;
CREATE TABLE IF NOT EXISTS ext_circle_badges (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_circle_badges_id_seq'),
    name VARCHAR(100) NOT NULL DEFAULT '',
    icon_url VARCHAR(500) NOT NULL DEFAULT '',
    remark TEXT NOT NULL DEFAULT '',
    sort_order INTEGER NOT NULL DEFAULT 0,
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ext_circle_badges_status ON ext_circle_badges(status);
CREATE INDEX IF NOT EXISTS idx_ext_circle_badges_sort_order ON ext_circle_badges(sort_order);

COMMENT ON TABLE ext_circle_badges IS '圈子徽章表，存储圈子徽章配置';
COMMENT ON COLUMN ext_circle_badges.id IS '徽章唯一标识符，自增主键';
COMMENT ON COLUMN ext_circle_badges.name IS '徽章名称';
COMMENT ON COLUMN ext_circle_badges.icon_url IS '徽章图标URL';
COMMENT ON COLUMN ext_circle_badges.remark IS '备注';
COMMENT ON COLUMN ext_circle_badges.sort_order IS '排序权重，数值越小越靠前';
COMMENT ON COLUMN ext_circle_badges.status IS '状态：1-启用，2-禁用';
COMMENT ON COLUMN ext_circle_badges.created_at IS '创建时间';
COMMENT ON COLUMN ext_circle_badges.updated_at IS '更新时间';

CREATE TRIGGER update_ext_circle_badges_updated_at
    BEFORE UPDATE ON ext_circle_badges
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 初始圈子徽章数据（id 从 1000 开始）
INSERT INTO ext_circle_badges (id, name, icon_url, remark, sort_order, status) VALUES
(1000, '天使用户', '', '', 1, 1),
(1001, '荣誉体验官', '', '', 2, 1),
(1002, '创始圈友', '', '', 3, 1),
(1003, '圈中长老', '', '', 4, 1)
ON CONFLICT (id) DO NOTHING;

-- 同步序列，避免后续自增与已有 id 冲突
SELECT setval('ext_circle_badges_id_seq', (SELECT COALESCE(MAX(id), 1000) FROM ext_circle_badges));
