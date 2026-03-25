-- 为 ext_user_levels 增加好友上限字段（按用户等级限制好友人数）
-- 执行前请确认表 ext_user_levels 已存在

ALTER TABLE ext_user_levels ADD COLUMN IF NOT EXISTS friend_limit INTEGER NOT NULL DEFAULT 100;
COMMENT ON COLUMN ext_user_levels.friend_limit IS '好友上限人数，-1 表示不限制';

-- 为已有等级设置默认值（可按业务调整）
UPDATE ext_user_levels SET friend_limit = 100 WHERE friend_limit IS NULL;
