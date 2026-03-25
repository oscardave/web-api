DROP VIEW IF EXISTS red_packets_v;

CREATE VIEW red_packets_v AS 
select 
    rp.packet_id, 
    rp.sender_id, 
    ps.user_id as sender_user_id,
    ps.displayname AS sender_nickname,
    rp.room_id, 
    rp.packet_type,
    rp.receiver_id,
    pr.user_id as receiver_user_id,
    pr.displayname AS receiver_nickname,
    rp.total_amount,
    rp.per_amount,
    rp.total_count,
    rp.remaining_amount,
    rp.remaining_count,
    rp.message, 
    rp.status, 
    rp.expire_ts,
    rp.created_ts,
    r.name as room_name
FROM red_packets rp
LEFT JOIN profiles ps ON rp.sender_id = ps.full_user_id
LEFT JOIN profiles pr ON rp.receiver_id = pr.full_user_id
LEFT JOIN room_stats_state r ON rp.room_id = r.room_id; 

