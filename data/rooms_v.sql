DROP VIEW IF EXISTS rooms_v;

CREATE VIEW rooms_v AS 
select 
    r.room_id,
    r.is_public,
    r.creator,
    p.user_id as creator_user_id,
    p.displayname as creator_nickname,
    rss.name as room_name,
    rss.join_rules,
    rss.topic,
    rsc.joined_members,
    rsc.invited_members,
    rsc.left_members,
    rsc.banned_members,
    rsc.knocked_members,
    e.origin_server_ts AS created_ts
from rooms r 
left join room_stats_state rss on r.room_id = rss.room_id
left join room_stats_current rsc on r.room_id = rsc.room_id
left join profiles p on r.creator = p.full_user_id
left join (select room_id, min(origin_server_ts) as origin_server_ts from events where type = 'm.room.create' group by room_id) e on r.room_id = e.room_id;