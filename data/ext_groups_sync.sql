-- ext_groups 同步触发器
-- 监听 Synapse 的 room_stats_current 和 room_stats_state 表，
-- 自动同步 ext_groups 的插入与更新
-- 依赖：ext_groups 表已存在，update_updated_at_column 函数已存在

-- ========== 1. room_stats_current 触发器：新增房间时插入 ext_groups，成员变化时更新 ==========

CREATE OR REPLACE FUNCTION ext_groups_sync_on_room_stats_current()
RETURNS TRIGGER AS $$
DECLARE
    v_owner_id TEXT;
    v_owner_nickname TEXT;
    v_group_name TEXT;
    v_join_restriction TEXT;
BEGIN
    IF TG_OP = 'INSERT' THEN
        -- 新房间：从 rooms、room_stats_state、profiles 获取初始数据并插入 ext_groups
        SELECT r.creator, COALESCE(p.displayname, ''), COALESCE(rss.name, ''), rss.join_rules
        INTO v_owner_id, v_owner_nickname, v_group_name, v_join_restriction
        FROM rooms r
        LEFT JOIN room_stats_state rss ON r.room_id = rss.room_id
        LEFT JOIN profiles p ON r.creator = p.full_user_id
        WHERE r.room_id = NEW.room_id;

        INSERT INTO ext_groups (
            group_id,
            group_name,
            owner_id,
            owner_nickname,
            current_member_count,
            join_restriction_type,
            last_activity_time
        ) VALUES (
            NEW.room_id,
            COALESCE(v_group_name, ''),
            COALESCE(v_owner_id, ''),
            COALESCE(v_owner_nickname, ''),
            COALESCE(NEW.joined_members, 0),
            CASE WHEN v_join_restriction IN ('restricted', 'knock') THEN 'specified' ELSE 'unlimited' END,
            now()
        )
        ON CONFLICT (group_id) DO NOTHING;  -- 若 group_id 已存在（唯一索引）则跳过

    ELSIF TG_OP = 'UPDATE' THEN
        -- 成员变化：更新 current_member_count 和 last_activity_time
        UPDATE ext_groups
        SET
            current_member_count = COALESCE(NEW.joined_members, 0),
            last_activity_time = now()
        WHERE group_id = NEW.room_id AND status = 1;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 若已存在则先删除，再创建
DROP TRIGGER IF EXISTS trg_ext_groups_on_room_stats_current ON room_stats_current;
CREATE TRIGGER trg_ext_groups_on_room_stats_current
    AFTER INSERT OR UPDATE ON room_stats_current
    FOR EACH ROW
    EXECUTE PROCEDURE ext_groups_sync_on_room_stats_current();

-- ========== 2. room_stats_state 触发器：房间名称、join_rules 变化时更新 ext_groups ==========

CREATE OR REPLACE FUNCTION ext_groups_sync_on_room_stats_state()
RETURNS TRIGGER AS $$
BEGIN
    -- 仅更新 group_name 和 join_restriction_type，不覆盖后台可编辑的其他字段
    UPDATE ext_groups
    SET
        group_name = COALESCE(NEW.name, group_name),
        join_restriction_type = CASE
            WHEN NEW.join_rules IN ('restricted', 'knock') THEN 'specified'
            WHEN NEW.join_rules IS NOT NULL THEN 'unlimited'
            ELSE join_restriction_type
        END
    WHERE group_id = NEW.room_id AND status = 1;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_ext_groups_on_room_stats_state ON room_stats_state;
CREATE TRIGGER trg_ext_groups_on_room_stats_state
    AFTER INSERT OR UPDATE OF name, join_rules ON room_stats_state
    FOR EACH ROW
    EXECUTE PROCEDURE ext_groups_sync_on_room_stats_state();
