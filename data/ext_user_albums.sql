-- 用户相册表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_albums_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户相册表
DROP TABLE IF EXISTS ext_user_albums;
CREATE TABLE IF NOT EXISTS ext_user_albums (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_albums_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    name VARCHAR(100) NOT NULL DEFAULT '',
    cover_url VARCHAR(500) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',

    -- 相册属性
    type SMALLINT NOT NULL DEFAULT 1,
    photo_count INTEGER NOT NULL DEFAULT 0,
    max_photo_count INTEGER NOT NULL DEFAULT 100,
    sort_order INTEGER NOT NULL DEFAULT 0,

    -- 权限设置
    allow_comment BOOLEAN NOT NULL DEFAULT true,
    allow_download BOOLEAN NOT NULL DEFAULT false,

    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_albums_user_id ON ext_user_albums(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_albums_type ON ext_user_albums(type);
CREATE INDEX IF NOT EXISTS idx_ext_user_albums_status ON ext_user_albums(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_albums_sort_order ON ext_user_albums(sort_order);
CREATE INDEX IF NOT EXISTS idx_ext_user_albums_user_id_status ON ext_user_albums(user_id, status);

-- 创建注释
COMMENT ON TABLE ext_user_albums IS '用户相册表，存储用户创建的相册信息';
COMMENT ON COLUMN ext_user_albums.id IS '相册唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_albums.user_id IS '所属用户ID，关联用户表';
COMMENT ON COLUMN ext_user_albums.name IS '相册名称';
COMMENT ON COLUMN ext_user_albums.cover_url IS '相册封面图URL';
COMMENT ON COLUMN ext_user_albums.description IS '相册描述';
COMMENT ON COLUMN ext_user_albums.type IS '相册类型：1-公开相册，2-私密相册，3-好友可见';
COMMENT ON COLUMN ext_user_albums.photo_count IS '相册内照片数量';
COMMENT ON COLUMN ext_user_albums.max_photo_count IS '最大允许照片数量';
COMMENT ON COLUMN ext_user_albums.sort_order IS '排序权重';
COMMENT ON COLUMN ext_user_albums.allow_comment IS '是否允许评论';
COMMENT ON COLUMN ext_user_albums.allow_download IS '是否允许下载';
COMMENT ON COLUMN ext_user_albums.status IS '相册状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_user_albums.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_albums.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_albums_updated_at
    BEFORE UPDATE ON ext_user_albums
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_albums (user_id, name, cover_url, description, type, photo_count, max_photo_count, sort_order, allow_comment, allow_download, status) VALUES
(10001, '我的生活', '/static/albums/covers/life_default.jpg', '记录生活的美好瞬间', 1, 0, 100, 1, true, false, 1),
(10001, '旅行足迹', '/static/albums/covers/travel_default.jpg', '记录我的旅行回忆', 1, 0, 200, 2, true, true, 1),
(10001, '私密相册', '/static/albums/covers/private_default.jpg', '仅自己可见的照片', 2, 0, 50, 3, false, false, 1),
(10002, '工作日常', '/static/albums/covers/work_default.jpg', '工作相关照片', 3, 0, 100, 1, true, false, 1);
