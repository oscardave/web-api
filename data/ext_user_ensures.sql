-- 用户诚信保申请表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_ensures_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户诚信保申请表
DROP TABLE IF EXISTS ext_user_ensures;
CREATE TABLE IF NOT EXISTS ext_user_ensures (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_ensures_id_seq'),
    user_id BIGINT NOT NULL DEFAULT 0,
    user_name VARCHAR(100) NOT NULL DEFAULT '',
    user_level VARCHAR(50) NOT NULL DEFAULT '',
    points_to_retrieve INTEGER NOT NULL DEFAULT 0,
    application_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- 审核相关
    status SMALLINT NOT NULL DEFAULT 1,
    approver_id BIGINT NOT NULL DEFAULT 0,
    approval_time TIMESTAMP WITH TIME ZONE,

    -- 申请和拒绝理由
    reason_for_application TEXT NOT NULL DEFAULT '',
    reason_for_rejection TEXT NOT NULL DEFAULT '',

    -- 系统字段
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS idx_ext_user_ensures_user_id ON ext_user_ensures(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_ensures_status ON ext_user_ensures(status);
CREATE INDEX IF NOT EXISTS idx_ext_user_ensures_approver_id ON ext_user_ensures(approver_id);
CREATE INDEX IF NOT EXISTS idx_ext_user_ensures_application_time ON ext_user_ensures(application_time);
CREATE INDEX IF NOT EXISTS idx_ext_user_ensures_user_id_status ON ext_user_ensures(user_id, status);

-- 创建注释
COMMENT ON TABLE ext_user_ensures IS '用户诚信保申请表，存储用户申请取回被冻结积分的记录';
COMMENT ON COLUMN ext_user_ensures.id IS '申请唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_ensures.user_id IS '申请用户ID，关联用户表';
COMMENT ON COLUMN ext_user_ensures.user_name IS '申请用户名';
COMMENT ON COLUMN ext_user_ensures.user_level IS '用户等级：初级会员、纯发用户等';
COMMENT ON COLUMN ext_user_ensures.points_to_retrieve IS '申请取回金额';
COMMENT ON COLUMN ext_user_ensures.application_time IS '申请时间';
COMMENT ON COLUMN ext_user_ensures.status IS '申请状态：1-未审核，2-已审核待执行, 3-执行中, 4-已完成, 5-已拒绝';
COMMENT ON COLUMN ext_user_ensures.approver_id IS '审核人ID，关联管理员表';
COMMENT ON COLUMN ext_user_ensures.approval_time IS '审核时间（同意时写入，即审核通过时间，用于延迟执行）';
COMMENT ON COLUMN ext_user_ensures.reason_for_application IS '申请理由';
COMMENT ON COLUMN ext_user_ensures.reason_for_rejection IS '拒绝理由';
COMMENT ON COLUMN ext_user_ensures.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_ensures.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_ensures_updated_at
    BEFORE UPDATE ON ext_user_ensures
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();
