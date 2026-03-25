-- 省份表创建脚本
-- 设置默认起始索引为 1000

-- 创建序列，起始值为 1000
CREATE SEQUENCE IF NOT EXISTS ext_provinces_id_seq
    START WITH 1000
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- 创建省份表
DROP TABLE IF EXISTS ext_provinces;
CREATE TABLE IF NOT EXISTS ext_provinces (
    id BIGINT PRIMARY KEY DEFAULT nextval('ext_provinces_id_seq'),
    code VARCHAR(50) NOT NULL DEFAULT '',
    name VARCHAR(50) NOT NULL DEFAULT '',
    remark VARCHAR(255) NOT NULL DEFAULT '',
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE UNIQUE INDEX IF NOT EXISTS idx_ext_provinces_code ON ext_provinces(code);

-- 创建注释
COMMENT ON TABLE ext_provinces IS '省份表，存储系统中省份信息';
COMMENT ON COLUMN ext_provinces.id IS '省份唯一标识符，自增主键，起始值为1000';
COMMENT ON COLUMN ext_provinces.code IS '省份编码';
COMMENT ON COLUMN ext_provinces.name IS '省份名称';
COMMENT ON COLUMN ext_provinces.remark IS '省份备注';
COMMENT ON COLUMN ext_provinces.status IS '省份状态：1-正常，2-禁用';
COMMENT ON COLUMN ext_provinces.created_at IS '记录创建时间，自动设置为当前时间';
COMMENT ON COLUMN ext_provinces.updated_at IS '记录更新时间，自动设置为当前时间';

-- 创建触发器，自动更新更新时间
CREATE TRIGGER update_ext_provinces_updated_at
    BEFORE UPDATE ON ext_provinces
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 插入测试数据
INSERT INTO ext_provinces (code, name, remark, status) VALUES
('11', '北京市', '北京市', 1),
('12', '天津市', '天津市', 1),
('13', '河北省', '河北省', 1),
('14', '山西省', '山西省', 1),
('15', '内蒙古自治区', '内蒙古自治区', 1),
('21', '辽宁省', '辽宁省', 1),
('22', '吉林省', '吉林省', 1),
('23', '黑龙江省', '黑龙江省', 1),
('31', '上海市', '上海市', 1),
('32', '江苏省', '江苏省', 1),
('33', '浙江省', '浙江省', 1),
('34', '安徽省', '安徽省', 1),
('35', '福建省', '福建省', 1),
('36', '江西省', '江西省', 1),
('37', '山东省', '山东省', 1),
('41', '河南省', '河南省', 1),
('42', '湖北省', '湖北省', 1),
('43', '湖南省', '湖南省', 1),
('44', '广东省', '广东省', 1),
('45', '广西壮族自治区', '广西壮族自治区', 1),
('46', '海南省', '海南省', 1),
('50', '重庆市', '重庆市', 1),
('51', '四川省', '四川省', 1),
('52', '贵州省', '贵州省', 1),
('53', '云南省', '云南省', 1),
('54', '西藏自治区', '西藏自治区', 1),
('61', '陕西省', '陕西省', 1),
('62', '甘肃省', '甘肃省', 1),
('63', '青海省', '青海省', 1),
('64', '宁夏回族自治区', '宁夏回族自治区', 1),
('65', '新疆维吾尔自治区', '新疆维吾尔自治区', 1);
