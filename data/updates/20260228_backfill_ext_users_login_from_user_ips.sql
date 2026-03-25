-- 一次性回填：将 Synapse user_ips 中每个用户最后一次出现的 IP 与时间写入 ext_users
-- 仅更新 ext_users 中已存在的 user_id（与 user_ips.user_id 对应）
-- 执行一次即可，后续登录由 Synapse 登录接口更新

UPDATE ext_users eu
SET
  login_ip = LEFT(u.ip, 50),
  last_login_time = to_timestamp(u.last_seen / 1000.0) AT TIME ZONE 'UTC'
FROM (
  SELECT DISTINCT ON (user_id) user_id, ip, last_seen
  FROM user_ips
  ORDER BY user_id, last_seen DESC
) u
WHERE eu.user_id = u.user_id;
