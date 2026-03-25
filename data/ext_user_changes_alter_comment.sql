-- 将 operation_type 注释从 3 种类型更新为 4 种（增加 4-解冻）
COMMENT ON COLUMN ext_user_changes.operation_type IS '操作类型：1-上分 2-下分 3-冻结 4-解冻';
