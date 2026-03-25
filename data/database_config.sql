-- 数据库配置脚本
-- 用于设置数据库连接和基本配置

-- 创建数据库（如果不存在）
-- 注意：需要超级用户权限执行
-- CREATE DATABASE synapse_admin_db;

-- 连接到数据库
-- \c synapse_admin_db;

-- 设置时区
SET timezone = 'Asia/Shanghai';

-- 设置字符编码
SET client_encoding = 'UTF8';

-- 创建扩展（如果需要）
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
-- CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- 显示当前配置
SELECT 
    'Database Configuration' as info,
    current_database() as database_name,
    current_user as current_user,
    version() as postgres_version,
    current_setting('timezone') as timezone,
    current_setting('client_encoding') as encoding; 