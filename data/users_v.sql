-- v1
DROP VIEW IF EXISTS users_v;

CREATE VIEW users_v AS
SELECT u.name, u.creation_ts, u.admin, u.is_guest, u.deactivated, u.shadow_banned, u.consent_ts, u.approved, u.locked, u.suspended,
p.displayname AS nickname, p.avatar_url,
s.device_id, s.last_seen, s.device_name, s.ip, s.user_agent
FROM users AS u
left JOIN profiles AS p ON u.name = p.full_user_id
left JOIN syn_user_devices_v AS s ON u.name = s.user_id


-- v2
DROP VIEW IF EXISTS users_v;

CREATE VIEW users_v AS
SELECT
    u.name, u.creation_ts, u.admin, u.is_guest, u.deactivated, u.shadow_banned, u.consent_ts, u.approved, u.locked, u.suspended,
    e.level, e.score,
    p.displayname AS nickname, p.avatar_url,
    s.device_id, s.last_seen, s.device_name, s.ip, s.user_agent
FROM users AS  u
INNER JOIN ext_users AS e ON u.name = e.user_id
INNER JOIN profiles AS p ON u.name = p.full_user_id
INNER JOIN syn_user_devices_v AS s ON u.name = s.user_id;
