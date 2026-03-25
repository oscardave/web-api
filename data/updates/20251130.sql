-- 为 ext_users 表添加性别字段
-- 如果字段已存在则跳过，避免重复添加

DO $$
BEGIN
    -- 检查字段是否存在，如果不存在则添加
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'ext_users' 
        AND column_name = 'gender'
    ) THEN
        -- 添加性别字段
        ALTER TABLE ext_users 
        ADD COLUMN gender VARCHAR(20) NOT NULL DEFAULT 'male';
        
        -- 添加字段注释
        COMMENT ON COLUMN ext_users.gender IS '性别：male-男, female-女, other-其他';
    END IF;
END $$;