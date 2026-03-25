-- 用户等级表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_levels_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户等级表
DROP TABLE IF EXISTS ext_user_levels;
CREATE TABLE IF NOT EXISTS ext_user_levels (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_levels_id_seq'),
    name VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    validity_period INTEGER NOT NULL DEFAULT 0,
    payment_point INTEGER NOT NULL DEFAULT 0,
    translation_enabled BOOLEAN NOT NULL DEFAULT false,
    max_groups_joined INTEGER NOT NULL DEFAULT 0,
    max_groups_created INTEGER NOT NULL DEFAULT 0,
    max_members_in_group INTEGER NOT NULL DEFAULT 0,
    max_assisted_city INTEGER NOT NULL DEFAULT 0,
    max_notes_created INTEGER NOT NULL DEFAULT 0,
    max_notes_per_wall INTEGER NOT NULL DEFAULT 0,
    max_super_note_wall INTEGER NOT NULL DEFAULT 0,
    avatar_frame_enabled BOOLEAN NOT NULL DEFAULT false,
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_levels_name ON ext_user_levels(name);
CREATE INDEX IF NOT EXISTS idx_ext_user_levels_status ON ext_user_levels(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_levels_created_at ON ext_user_levels(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_levels_payment_point ON ext_user_levels(payment_point);

-- 创建注释
COMMENT ON TABLE ext_user_levels IS '用户等级表，定义系统中不同用户等级的权限、限制和功能';
COMMENT ON COLUMN ext_user_levels.id IS '用户等级唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_levels.name IS '用户等级名称，唯一，如：普通用户、初级会员、高级会员等';
COMMENT ON COLUMN ext_user_levels.remark IS '用户等级备注，如：普通用户，享受基本服务';
COMMENT ON COLUMN ext_user_levels.description IS '用户等级描述，详细说明等级的权限范围和限制';
COMMENT ON COLUMN ext_user_levels.validity_period IS '有效期，以天为单位';
COMMENT ON COLUMN ext_user_levels.payment_point IS '付费积分，0表示免费';
COMMENT ON COLUMN ext_user_levels.translation_enabled IS '是否支持聊天消息中英文翻译';
COMMENT ON COLUMN ext_user_levels.max_groups_joined IS '进群数上限，-1表示无限制';
COMMENT ON COLUMN ext_user_levels.max_groups_created IS '创群数上限，-1表示无限制';
COMMENT ON COLUMN ext_user_levels.max_members_in_group IS '创群人数上限';
COMMENT ON COLUMN ext_user_levels.max_assisted_city IS '接收帮办城市数上限';
COMMENT ON COLUMN ext_user_levels.max_notes_created IS '创建笔记数量上限，-1表示无限制';
COMMENT ON COLUMN ext_user_levels.max_notes_per_wall IS '单个超级笔记墙上传笔记个数上限';
COMMENT ON COLUMN ext_user_levels.max_super_note_wall IS '超级笔记墙生成链接数量上限';
COMMENT ON COLUMN ext_user_levels.avatar_frame_enabled IS '是否有头像框';
COMMENT ON COLUMN ext_user_levels.status IS '等级状态：0-禁用，1-启用';
COMMENT ON COLUMN ext_user_levels.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_levels.updated_at IS '记录更新时间，通过触发器自动更新';

-- 创建触发器
CREATE TRIGGER update_ext_user_levels_updated_at
    BEFORE UPDATE ON ext_user_levels
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入初始数据
INSERT INTO ext_user_levels (
    name,
    remark,
    description,
    validity_period,
    payment_point,
    translation_enabled,
    max_groups_joined,
    max_groups_created,
    max_members_in_group,
    max_assisted_city,
    max_notes_created,
    max_notes_per_wall,
    max_super_note_wall,
    avatar_frame_enabled,
    status
) VALUES
('普通用户', '普通用户', '基础功能用户，享受基本服务', 0, 0, false, 5, 5, 100, 1, 20, 0, 0, false, 1),
('初级会员', '初级会员', '初级付费会员，享受更多功能', 0, 98, true, 50, 50, 200, 3, 100, 20, 5, false, 1),
('高级会员', '高级会员', '高级付费会员，享受高级功能', 0, 188, true, 100, 100, 3000, 10, 200, 50, 10, true, 1),
('超级会员', '超级会员', '超级付费会员，享受超级功能', 31, 298, true, -1, -1, 3000, 10, -1, 50, 10, true, 1),
('至尊会员', '至尊会员', '至尊付费会员，享受至尊功能', 365, 0, true, -1, -1, 10000, 10, -1, 100, 50, true, 1),
('纯发用户', '纯发用户', '纯发功能用户，专注内容发布', 90, 0, true, 50, 50, 500, 3, -1, 100, 50, false, 1);

-- 增加字段: 顺序优先级 
alter table ext_user_levels add column sort INT NOT NULL DEFAULT 0;
COMMENT ON COLUMN ext_user_levels.sort IS '顺序优先级';
update ext_user_levels set sort = 1 where id = 10000;
update ext_user_levels set sort = 2 where id = 10001;
update ext_user_levels set sort = 3 where id = 10002;
update ext_user_levels set sort = 4 where id = 10003;
update ext_user_levels set sort = 5 where id = 10004;

-- 增加字段: 是否允许兑换
alter table ext_user_levels add column is_exchangeable BOOLEAN NOT NULL DEFAULT false;
COMMENT ON COLUMN ext_user_levels.is_exchangeable IS '是否允许兑换';
update ext_user_levels set is_exchangeable = true where id >= 10001 and id < 10005;

-- 增加字段: 头像URL
alter table ext_user_levels add column avatar_url VARCHAR(255) NOT NULL DEFAULT '';
COMMENT ON COLUMN ext_user_levels.avatar_url IS '头像URL';