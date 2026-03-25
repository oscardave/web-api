-- 一用户一靓号：历史数据修复
-- 将每个用户下多条 status=1（正常使用）的靓号只保留一条，其余置为 status=2（已回收）；
-- 保留规则：优先保留 ext_users.vanity_badge_id 指向的那条，否则保留 id 最大的那条。
-- 最后确保 ext_users.vanity_badge_id 指向该用户唯一保留的 status=1 记录。

-- 1) 将“非保留”的 status=1 记录置为已回收
WITH keep AS (
  SELECT DISTINCT ON (uvn.user_id) uvn.user_id, uvn.id
  FROM ext_user_vanity_numbers uvn
  LEFT JOIN ext_users u ON u.id = uvn.user_id AND u.vanity_badge_id = uvn.id AND u.vanity_badge_id > 0
  WHERE uvn.status = 1
  ORDER BY uvn.user_id, (CASE WHEN u.id IS NOT NULL THEN 0 ELSE 1 END), uvn.id DESC
)
UPDATE ext_user_vanity_numbers uvn
SET status = 2
FROM keep k
WHERE uvn.user_id = k.user_id AND uvn.id != k.id AND uvn.status = 1;

-- 2) 确保 ext_users.vanity_badge_id 指向该用户唯一保留的 status=1 靓号（无则置 0）
UPDATE ext_users u
SET vanity_badge_id = COALESCE(
  (SELECT uvn.id FROM ext_user_vanity_numbers uvn WHERE uvn.user_id = u.id AND uvn.status = 1 LIMIT 1),
  0
);
