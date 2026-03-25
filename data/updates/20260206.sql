-- 给 syn_admins 表增加 remark 列
ALTER TABLE syn_admins ADD COLUMN remark TEXT NOT NULL DEFAULT '';