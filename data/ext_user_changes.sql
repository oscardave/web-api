-- 用户账变记录表创建脚本
-- change_type: 1-积分 2-诚信保
-- operation_type: 1-上分 2-下分 3-冻结
-- 设置默认起始索引为 10000

CREATE SEQUENCE IF NOT EXISTS ext_user_changes_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

DROP TABLE IF EXISTS ext_user_changes;
CREATE TABLE ext_user_changes (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_changes_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    change_type SMALLINT NOT NULL DEFAULT 1,
    operation_type SMALLINT NOT NULL DEFAULT 1,
    amount INTEGER NOT NULL DEFAULT 0,
    balance_before INTEGER NOT NULL DEFAULT 0,
    balance_after INTEGER NOT NULL DEFAULT 0,
    source_id VARCHAR(100) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ext_user_changes_user_id ON ext_user_changes(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_changes_change_type ON ext_user_changes(change_type);
CREATE INDEX IF NOT EXISTS idx_ext_user_changes_operation_type ON ext_user_changes(operation_type);
CREATE INDEX IF NOT EXISTS idx_ext_user_changes_status ON ext_user_changes(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_changes_created_at ON ext_user_changes(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_changes_user_id_created_at ON ext_user_changes(user_id, created_at);

COMMENT ON TABLE ext_user_changes IS '用户账变记录表';
COMMENT ON COLUMN ext_user_changes.id IS '记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_changes.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_user_changes.change_type IS '变动类型：1-积分 2-诚信保';
COMMENT ON COLUMN ext_user_changes.operation_type IS '操作类型：1-上分 2-下分 3-冻结 4-解冻';
COMMENT ON COLUMN ext_user_changes.amount IS '变动金额（正数）';
COMMENT ON COLUMN ext_user_changes.balance_before IS '账变前余额';
COMMENT ON COLUMN ext_user_changes.balance_after IS '账变后余额';
COMMENT ON COLUMN ext_user_changes.source_id IS '来源ID';
COMMENT ON COLUMN ext_user_changes.description IS '说明';
COMMENT ON COLUMN ext_user_changes.status IS '状态：1-正常 2-冻结等';
COMMENT ON COLUMN ext_user_changes.created_at IS '记录创建时间';
COMMENT ON COLUMN ext_user_changes.updated_at IS '记录更新时间';

CREATE TRIGGER update_ext_user_changes_updated_at
    BEFORE UPDATE ON ext_user_changes
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 清理旧表 ext_user_points：删除触发器、索引并清空数据（若表存在）
DO $$
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'ext_user_points') THEN
    DROP TRIGGER IF EXISTS update_ext_user_points_updated_at ON ext_user_points;
    DROP INDEX IF EXISTS idx_ext_user_points_user_id;
    DROP INDEX IF EXISTS idx_ext_user_points_points_type;
    DROP INDEX IF EXISTS idx_ext_user_points_operation_type;
    DROP INDEX IF EXISTS idx_ext_user_points_status;
    DROP INDEX IF EXISTS idx_ext_user_points_created_at;
    DROP INDEX IF EXISTS idx_ext_user_points_user_id_status;
    DROP INDEX IF EXISTS idx_ext_user_points_source_id;
    TRUNCATE TABLE ext_user_points;
  END IF;
END $$;


