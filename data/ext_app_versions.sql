-- APP版本管理表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_app_versions_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建APP版本表
DROP TABLE IF EXISTS ext_app_versions;
CREATE TABLE IF NOT EXISTS ext_app_versions (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_app_versions_id_seq'),
    client_type SMALLINT NOT NULL DEFAULT 1,
    release_type SMALLINT NOT NULL DEFAULT 1,
    appstore_url VARCHAR(500) NOT NULL DEFAULT '',
    version_number VARCHAR(20) NOT NULL DEFAULT '',
    release_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    upgrade_type SMALLINT NOT NULL DEFAULT 1,
    upgrade_message TEXT NOT NULL DEFAULT '',
    min_compatible_version VARCHAR(20) NOT NULL DEFAULT '',
    version_status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_app_versions_version ON ext_app_versions(client_type, version_number);
CREATE INDEX IF NOT EXISTS idx_ext_app_versions_client_type ON ext_app_versions(client_type);
CREATE INDEX IF NOT EXISTS idx_ext_app_versions_release_type ON ext_app_versions(release_type);
CREATE INDEX IF NOT EXISTS idx_ext_app_versions_upgrade_type ON ext_app_versions(upgrade_type);
CREATE INDEX IF NOT EXISTS idx_ext_app_versions_status ON ext_app_versions(version_status);
CREATE INDEX IF NOT EXISTS idx_ext_app_versions_release_time ON ext_app_versions(release_time);

-- 创建注释
COMMENT ON TABLE ext_app_versions IS 'APP版本管理表，存储应用版本信息';
COMMENT ON COLUMN ext_app_versions.id IS '版本唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_app_versions.client_type IS '客户端类型：1-iOS，2-Android';
COMMENT ON COLUMN ext_app_versions.release_type IS '发布类型：1-appstore，2-testflight';
COMMENT ON COLUMN ext_app_versions.appstore_url IS 'App Store地址';
COMMENT ON COLUMN ext_app_versions.version_number IS '版本号，如V1.0.3';
COMMENT ON COLUMN ext_app_versions.release_time IS '发布时间';
COMMENT ON COLUMN ext_app_versions.upgrade_type IS '升级类型：1-强制升级，2-强提示升级，3-弱提示升级，4-新APP上架';
COMMENT ON COLUMN ext_app_versions.upgrade_message IS '升级提示语';
COMMENT ON COLUMN ext_app_versions.min_compatible_version IS '最低兼容版本';
COMMENT ON COLUMN ext_app_versions.version_status IS '版本状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_app_versions.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_app_versions.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_app_versions_updated_at
    BEFORE UPDATE ON ext_app_versions
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_app_versions (client_type, release_type, appstore_url, version_number, release_time, upgrade_type, upgrade_message, min_compatible_version, version_status) VALUES
(1, 1, 'https://apps.apple.com/app/example', 'V1.0.3', '2021-12-17 10:21:54', 3, '这里是升级提示内容语,在APP弹窗上显示可换...', 'V1.0.0', 1),
(1, 1, 'https://apps.apple.com/app/example', 'V1.0.2', '2021-12-17 10:21:54', 2, '这里是升级提示内容语,在APP弹窗上显示可换...', 'V1.0.0', 1),
(1, 1, 'https://apps.apple.com/app/example', 'V1.0.1', '2021-12-17 10:21:54', 1, '这里是升级提示内容语,在APP弹窗上显示可换...', 'V1.0.0', 1),
(1, 1, 'https://apps.apple.com/app/example', 'V1.0.0', '2021-12-17 10:21:54', 4, '这里是升级提示内容语,在APP弹窗上显示可换...', 'V1.0.0', 1);

-- 创建视图
CREATE VIEW ext_app_versions_v AS
SELECT
    av.id,
    av.client_type,
    CASE av.client_type
        WHEN 1 THEN 'IOS'
        WHEN 2 THEN '安卓'
        ELSE '未知'
    END as client_type_name,
    av.release_type,
    CASE av.release_type
        WHEN 1 THEN 'appstore'
        WHEN 2 THEN 'testflight'
        ELSE '未知'
    END as release_type_name,
    av.appstore_url,
    av.version_number,
    av.release_time,
    av.upgrade_type,
    CASE av.upgrade_type
        WHEN 1 THEN '强制升级'
        WHEN 2 THEN '强提示升级'
        WHEN 3 THEN '弱提示升级'
        WHEN 4 THEN '新APP上架'
        ELSE '未知'
    END as upgrade_type_name,
    av.upgrade_message,
    av.min_compatible_version,
    av.version_status,
    CASE av.version_status
        WHEN 1 THEN '正常'
        WHEN 2 THEN '禁用'
        ELSE '未知'
    END as version_status_name,
    av.created_at,
    av.updated_at
FROM ext_app_versions av;
