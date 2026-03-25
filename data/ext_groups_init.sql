-- ext_groups 初始化脚本
-- 基于现有 rooms_v 视图，将尚未在 ext_groups 中的房间批量插入
-- 可重复执行（幂等），仅插入不存在的 room_id

-- 清空 ext_groups 表
truncate table ext_groups;

-- 插入 ext_groups 表
INSERT INTO ext_groups (
    group_id,
    group_name,
    owner_id,
    owner_nickname,
    current_member_count,
    member_limit,
    join_restriction_type,
    group_status,
    status,
    created_time,
    last_activity_time
)
SELECT
    rv.room_id,
    COALESCE(rv.room_name, ''),
    COALESCE(rv.creator, ''),
    COALESCE(rv.creator_nickname, ''),
    COALESCE(rv.joined_members, 0)::int,
    200,
    CASE
        WHEN rv.join_rules IN ('restricted', 'knock') THEN 'specified'
        ELSE 'unlimited'
    END,
    'normal',
    1,
    to_timestamp(COALESCE(rv.created_ts, extract(epoch from now()))),
    now()
FROM (
    SELECT
        r.room_id,
        rss.name AS room_name,
        r.creator,
        p.displayname AS creator_nickname,
        rsc.joined_members,
        rss.join_rules,
        e.origin_server_ts AS created_ts
    FROM rooms r
    INNER JOIN room_stats_current rsc ON r.room_id = rsc.room_id
    LEFT JOIN room_stats_state rss ON r.room_id = rss.room_id
    LEFT JOIN profiles p ON r.creator = p.full_user_id
    LEFT JOIN (
        SELECT room_id, min(origin_server_ts) AS origin_server_ts
        FROM events
        WHERE type = 'm.room.create'
        GROUP BY room_id
    ) e ON r.room_id = e.room_id
) rv
LEFT JOIN ext_groups eg ON rv.room_id = eg.group_id AND eg.status = 1
WHERE eg.id IS NULL;
