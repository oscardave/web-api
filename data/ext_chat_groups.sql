-- 聊天分组表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_chat_groups_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建聊天分组表
DROP TABLE IF EXISTS ext_chat_groups;
CREATE TABLE IF NOT EXISTS ext_chat_groups (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_chat_groups_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,                                 -- 创建者ID（关联 ext_users.id）
    icon TEXT NOT NULL DEFAULT '',                                      -- 分组图标URL
    name VARCHAR(200) NOT NULL DEFAULT '',                              -- 分组名称（可修改）
    
    -- 统计字段（不可直接修改，通过计算得出）
    chat_count INTEGER NOT NULL DEFAULT 0,                              -- 分组内聊天数量（统计字段）
    participant_count INTEGER NOT NULL DEFAULT 0,                       -- 分组内参与者总数（统计字段）
    
    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                                 -- 记录状态：1-有效，2-无效
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,     -- 记录创建时间
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP      -- 记录更新时间
);

-- 创建分组房间关联表序列
CREATE SEQUENCE IF NOT EXISTS ext_chat_group_rooms_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建分组房间关联表
DROP TABLE IF EXISTS ext_chat_group_rooms;
CREATE TABLE IF NOT EXISTS ext_chat_group_rooms (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_chat_group_rooms_id_seq'),
    group_id BIGINT NOT NULL DEFAULT 0,                                 -- 分组ID（关联 ext_chat_groups.id）
    room_id VARCHAR(255) NOT NULL DEFAULT '',                           -- 房间ID（关联 rooms.room_id）
    
    -- 系统字段
    status SMALLINT NOT NULL DEFAULT 1,                                 -- 记录状态：1-有效，2-无效
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP      -- 记录创建时间
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_chat_groups_user_id ON ext_chat_groups(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_chat_groups_status ON ext_chat_groups(status);
CREATE INDEX IF NOT EXISTS idx_ext_chat_groups_updated_at ON ext_chat_groups(updated_at);
CREATE INDEX IF NOT EXISTS idx_ext_chat_group_rooms_group_id ON ext_chat_group_rooms(group_id);
CREATE INDEX IF NOT EXISTS idx_ext_chat_group_rooms_room_id ON ext_chat_group_rooms(room_id);
CREATE INDEX IF NOT EXISTS idx_ext_chat_group_rooms_status ON ext_chat_group_rooms(status);
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_chat_group_rooms_unique ON ext_chat_group_rooms(group_id, room_id) WHERE status = 1;

-- 创建注释
COMMENT ON TABLE ext_chat_groups IS '聊天分组表，存储用户自定义的聊天分组信息';
COMMENT ON COLUMN ext_chat_groups.id IS '分组唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_chat_groups.user_id IS '创建者ID，关联 ext_users.id，表示该分组的创建者';
COMMENT ON COLUMN ext_chat_groups.icon IS '分组图标URL或标识';
COMMENT ON COLUMN ext_chat_groups.name IS '分组名称，用户可以修改';
COMMENT ON COLUMN ext_chat_groups.chat_count IS '分组内聊天数量，统计字段，通过 ext_chat_group_rooms 表计算得出，不可直接修改';
COMMENT ON COLUMN ext_chat_groups.participant_count IS '分组内参与者总数，统计字段，通过该分组下所有房间的参与者数量求和得出，不可直接修改';
COMMENT ON COLUMN ext_chat_groups.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_chat_groups.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_chat_groups.updated_at IS '记录更新时间，自动设置为当前时间';

COMMENT ON TABLE ext_chat_group_rooms IS '分组房间关联表，存储分组与聊天房间的多对多关系';
COMMENT ON COLUMN ext_chat_group_rooms.id IS '关联记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_chat_group_rooms.group_id IS '分组ID，关联 ext_chat_groups.id';
COMMENT ON COLUMN ext_chat_group_rooms.room_id IS '房间ID，关联 rooms.room_id';
COMMENT ON COLUMN ext_chat_group_rooms.status IS '记录状态：1-有效，2-无效';
COMMENT ON COLUMN ext_chat_group_rooms.created_at IS '记录创建时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_chat_groups_updated_at
    BEFORE UPDATE ON ext_chat_groups
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();
