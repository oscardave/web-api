DROP VIEW IF EXISTS user_devices_v;

CREATE VIEW user_devices_v AS
SELECT 
    d.user_id AS full_user_id, d.device_id, d.last_seen, d.display_name AS device_name, d.ip, d.user_agent, d.hidden,
    p.displayname AS nickname, p.user_id
FROM devices AS d INNER JOIN profiles AS p ON d.user_id = p.full_user_id;