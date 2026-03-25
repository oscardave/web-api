DROP VIEW IF EXISTS user_wallets_v;

CREATE VIEW user_wallets_v AS
SELECT
    p.user_id,
    u.admin,
    u.is_guest,
    u.shadow_banned,
    u.creation_ts,
    w.balance,
    w.updated_ts,
    p.displayname as nickname
FROM users u
LEFT JOIN user_wallets w ON u.name = w.user_id
LEFT JOIN profiles p ON u.name = p.full_user_id;