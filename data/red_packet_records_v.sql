DROP VIEW IF EXISTS red_packet_records_v;

CREATE VIEW red_packet_records_v AS 
select 
    rpr.packet_id,
    p.user_id,
    p.displayname as nickname,
    rpr.amount,
    rpr.created_ts as record_created_ts,
    rp.packet_type,
    rp.total_amount,
    rp.remaining_amount,
    rp.remaining_count,
    rp.message,
    rp.status,
    rp.created_ts,
    r.name as room_name
from red_packet_records rpr
left join red_packets rp on rpr.packet_id = rp.packet_id
left join profiles p on rpr.user_id = p.full_user_id
left join room_stats_state r on rp.room_id = r.room_id;