DROP VIEW IF EXISTS syn_user_devices_v;

CREATE VIEW syn_user_devices_v AS
SELECT d.user_id, d.device_id, d.last_seen, d.display_name AS device_name, d.ip, d.user_agent
FROM devices AS d INNER JOIN (
    SELECT user_id, max(last_seen) AS last_seen 
    FROM devices 
    GROUP BY user_id
) AS t ON d.user_id = t.user_id AND d.last_seen = t.last_seen
;