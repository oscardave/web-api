-- 用户报表统计表创建脚本
-- 设置默认起始索引为 10000

-- 创建序列，起始值为 10000
CREATE SEQUENCE IF NOT EXISTS ext_user_reports_id_seq
    START WITH 10000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建用户报表统计表
DROP TABLE IF EXISTS ext_user_reports;
CREATE TABLE IF NOT EXISTS ext_user_reports (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_user_reports_id_seq'),
    report_date DATE NOT NULL,
    new_users_today INTEGER NOT NULL DEFAULT 0,
    total_users_cumulative INTEGER NOT NULL DEFAULT 0,
    login_users_today INTEGER NOT NULL DEFAULT 0,
    login_devices_today INTEGER NOT NULL DEFAULT 0,
    super_seat_visits_today INTEGER NOT NULL DEFAULT 0,
    notes_created_today INTEGER NOT NULL DEFAULT 0,
    total_notes_cumulative INTEGER NOT NULL DEFAULT 0,
    new_members_today INTEGER NOT NULL DEFAULT 0,
    current_members INTEGER NOT NULL DEFAULT 0,
    expired_members INTEGER NOT NULL DEFAULT 0,
    remark VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_user_reports_date ON ext_user_reports(report_date);
CREATE INDEX IF NOT EXISTS idx_ext_user_reports_created_at ON ext_user_reports(created_at);

-- 创建注释
COMMENT ON TABLE ext_user_reports IS '用户报表统计表，存储系统中用户相关的各项统计指标数据';
COMMENT ON COLUMN ext_user_reports.id IS '报表记录唯一标识符，自增主键，起始值为10000';
COMMENT ON COLUMN ext_user_reports.report_date IS '统计报表日期，唯一，格式为YYYY-MM-DD';
COMMENT ON COLUMN ext_user_reports.new_users_today IS '今日新注册用户数';
COMMENT ON COLUMN ext_user_reports.total_users_cumulative IS '累计注册用户总数';
COMMENT ON COLUMN ext_user_reports.login_users_today IS '今日登录用户数';
COMMENT ON COLUMN ext_user_reports.login_devices_today IS '今日登录设备数';
COMMENT ON COLUMN ext_user_reports.super_seat_visits_today IS '今日超级坐席访问人次';
COMMENT ON COLUMN ext_user_reports.notes_created_today IS '今日笔记创建数';
COMMENT ON COLUMN ext_user_reports.total_notes_cumulative IS '笔记存储累计总数';
COMMENT ON COLUMN ext_user_reports.new_members_today IS '今日新增会员人数';
COMMENT ON COLUMN ext_user_reports.current_members IS '当前会员人数';
COMMENT ON COLUMN ext_user_reports.expired_members IS '过期会员人数';
COMMENT ON COLUMN ext_user_reports.remark IS '备注信息';
COMMENT ON COLUMN ext_user_reports.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_user_reports.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_user_reports_updated_at
    BEFORE UPDATE ON ext_user_reports
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_user_reports (report_date, new_users_today, total_users_cumulative, login_users_today, login_devices_today, super_seat_visits_today, notes_created_today, total_notes_cumulative, new_members_today, current_members, expired_members, remark) VALUES
('2024-01-01', 25, 1250, 180, 220, 45, 89, 15600, 12, 89, 5, '新年第一天数据'),
('2024-01-02', 18, 1268, 165, 198, 38, 76, 15676, 8, 97, 3, '正常工作日数据'),
('2024-01-03', 22, 1290, 172, 205, 42, 82, 15758, 15, 112, 2, '用户增长良好'),
('2024-01-04', 19, 1309, 158, 189, 35, 71, 15829, 11, 123, 4, '稳定增长期'),
('2024-01-05', 24, 1333, 185, 218, 48, 95, 15924, 13, 136, 1, '周末活跃度高');
