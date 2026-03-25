-- 用户积分表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_points_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户积分表
DROP TABLE IF EXISTS ext_user_points;
CREATE TABLE IF NOT EXISTS ext_user_points (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_points_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    points_amount INTEGER NOT NULL DEFAULT 0,
    points_type SMALLINT NOT NULL DEFAULT 1,
    operation_type SMALLINT NOT NULL DEFAULT 1,
    source_id VARCHAR(100) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    expire_time TIMESTAMP WITH TIME ZONE,

    -- 系统字段
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_points_user_id ON ext_user_points(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_points_points_type ON ext_user_points(points_type);
CREATE INDEX IF NOT EXISTS idx_ext_user_points_operation_type ON ext_user_points(operation_type);
CREATE INDEX IF NOT EXISTS idx_ext_user_points_status ON ext_user_points(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_points_created_at ON ext_user_points(created_at);
CREATE INDEX IF NOT EXISTS idx_ext_user_points_user_id_status ON ext_user_points(user_id, status);
CREATE INDEX IF NOT EXISTS idx_ext_user_points_source_id ON ext_user_points(source_id);

-- 创建注释
COMMENT ON TABLE ext_user_points IS '用户积分表，存储用户积分变动记录';
COMMENT ON COLUMN ext_user_points.id IS '积分记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_points.user_id IS '用户ID，关联用户表';
COMMENT ON COLUMN ext_user_points.points_amount IS '积分数量（正数为获得，负数为消费）';
COMMENT ON COLUMN ext_user_points.points_type IS '积分类型：1-充值积分，2-消费积分，3-奖励积分，4-冻结积分，5-解冻积分，6-系统调整';
COMMENT ON COLUMN ext_user_points.operation_type IS '操作类型：1-获得积分，2-消费积分，3-冻结积分，4-解冻积分';
COMMENT ON COLUMN ext_user_points.source_id IS '来源ID（订单ID、活动ID等）';
COMMENT ON COLUMN ext_user_points.description IS '积分变动描述';
COMMENT ON COLUMN ext_user_points.status IS '积分状态：1-正常，2-冻结，3-已消费，4-已过期';
COMMENT ON COLUMN ext_user_points.expire_time IS '积分过期时间';
COMMENT ON COLUMN ext_user_points.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_points.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_points_updated_at
    BEFORE UPDATE ON ext_user_points
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_points (user_id, points_amount, points_type, operation_type, source_id, description, status, expire_time) VALUES
(10001, 1000, 1, 1, 'ORDER_001', '用户充值获得积分', 1, '2025-12-31 23:59:59'),
(10001, -100, 2, 2, 'ORDER_002', '购买商品消费积分', 3, NULL),
(10001, 50, 3, 1, 'ACTIVITY_001', '签到奖励积分', 1, '2025-12-31 23:59:59'),
(10001, -200, 4, 3, 'VIOLATION_001', '违规行为冻结积分', 2, NULL),
(10002, 500, 1, 1, 'ORDER_003', '用户充值获得积分', 1, '2025-12-31 23:59:59'),
(10002, -80, 2, 2, 'ORDER_004', '购买服务消费积分', 3, NULL),
(10002, 30, 3, 1, 'ACTIVITY_002', '活动奖励积分', 1, '2025-12-31 23:59:59'),
(10003, 200, 6, 1, 'ADMIN_001', '管理员调整积分', 1, NULL),
(10003, -50, 6, 2, 'ADMIN_002', '管理员扣除积分', 3, NULL),
(10001, 200, 5, 4, 'UNFREEZE_001', '解冻返还积分', 1, '2025-12-31 23:59:59');
