-- 为 ext_user_feedbacks 表添加 images 字段
-- 用于保存图片，JSON格式，默认是 '[]'

ALTER TABLE ext_user_feedbacks 
ADD COLUMN IF NOT EXISTS images TEXT NOT NULL DEFAULT '[]';

-- 添加字段注释
COMMENT ON COLUMN ext_user_feedbacks.images IS '图片JSON数组，存储图片URL列表，默认空数组';

