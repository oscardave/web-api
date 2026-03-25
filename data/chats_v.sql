DROP VIEW IF EXISTS chats_v;

CREATE VIEW chats_v AS 
select 
    e.event_id,
    p.user_id,
    p.displayname as nickname,
    p.full_user_id,
    e.room_id,
    e.sender,
    e.contains_url,
    e.origin_server_ts,
    e.received_ts,
    ej.json as content,
    ej.internal_metadata,
    rss.name as room_name
from events e
left join event_json ej on e.event_id = ej.event_id
left join profiles p on e.sender = p.full_user_id
left join room_stats_state rss on e.room_id = rss.room_id
where e.type = 'm.room.message';