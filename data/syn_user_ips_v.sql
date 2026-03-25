DROP VIEW IF EXISTS syn_user_ips_v;

CREATE VIEW syn_user_ips_v AS
SELECT user_id, device_id, ip, user_agent, last_seen
FROM user_ips
WHERE last_seen IN (
    SELECT max(last_seen) AS last_seen
    FROM user_ips
    GROUP BY user_id
);
