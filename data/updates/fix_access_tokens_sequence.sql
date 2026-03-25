-- 一次性修复：access_tokens 主键 id 序列与表中已有数据不同步导致
-- "duplicate key ... violates unique constraint access_tokens_pkey"（如 id=1126 已存在）报错。
-- 常见于导入数据、手工指定 id 插入后未调大序列。
-- 执行后新插入会从 max(id)+1 开始。web-api 登录/注册在遇此错误时也会自动 setval 并重试一次，仍建议在库上执行本脚本以根治。

SELECT setval(
    pg_get_serial_sequence('access_tokens', 'id'),
    COALESCE((SELECT MAX(id) FROM access_tokens), 1)
);
