--
-- This file is licensed under the Affero General Public License (AGPL) version 3.
--
-- Copyright (C) 2024 New Vector, Ltd
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as
-- published by the Free Software Foundation, either version 3 of the
-- License, or (at your option) any later version.
--
-- See the GNU Affero General Public License for more details:
-- <https://www.gnu.org/licenses/agpl-3.0.html>.
--
-- Originally licensed under the Apache License, Version 2.0:
-- <http://www.apache.org/licenses/LICENSE-2.0>.
--
-- [This file includes modifications made by New Vector Limited]
--

-- 用户钱包表
CREATE TABLE IF NOT EXISTS user_wallets (
    user_id TEXT PRIMARY KEY,
    balance INTEGER NOT NULL DEFAULT 0,  -- 余额（以分为单位）
    created_ts BIGINT NOT NULL,
    updated_ts BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(name)
);

-- 红包表
CREATE TABLE IF NOT EXISTS red_packets (
    packet_id TEXT PRIMARY KEY,
    sender_id TEXT NOT NULL,
    room_id TEXT,
    event_id TEXT,
    packet_type TEXT NOT NULL, -- PRIVATE, LUCKY, NORMAL, EXCLUSIVE
    receiver_id TEXT, -- 用于私发和专属红包
    total_amount INTEGER NOT NULL, -- 总金额（分）
    per_amount INTEGER, -- 每个红包金额（用于普通红包）
    total_count INTEGER NOT NULL, -- 红包个数
    remaining_amount INTEGER NOT NULL, -- 剩余金额
    remaining_count INTEGER NOT NULL, -- 剩余个数
    message TEXT, -- 祝福语
    status TEXT NOT NULL DEFAULT 'PENDING', -- PENDING, COMPLETED, EXPIRED, REFUNDED
    expire_ts BIGINT NOT NULL, -- 过期时间戳
    created_ts BIGINT NOT NULL, -- 创建时间戳
    updated_ts BIGINT NOT NULL -- 更新时间戳
);

-- 红包领取记录表
CREATE TABLE IF NOT EXISTS red_packet_records (
    record_id TEXT PRIMARY KEY,
    packet_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    amount INTEGER NOT NULL, -- 抢到的金额（分）
    created_ts BIGINT NOT NULL,
    FOREIGN KEY (packet_id) REFERENCES red_packets(packet_id) ON DELETE CASCADE
);

-- 索引
CREATE INDEX IF NOT EXISTS red_packets_room_id_idx ON red_packets(room_id);
CREATE INDEX IF NOT EXISTS red_packets_sender_id_idx ON red_packets(sender_id);
CREATE INDEX IF NOT EXISTS red_packets_status_idx ON red_packets(status);
CREATE INDEX IF NOT EXISTS red_packets_expire_ts_idx ON red_packets(expire_ts);
CREATE INDEX IF NOT EXISTS red_packets_event_id_idx ON red_packets(event_id);
CREATE INDEX IF NOT EXISTS red_packet_records_packet_id_idx ON red_packet_records(packet_id);
CREATE INDEX IF NOT EXISTS red_packet_records_user_id_idx ON red_packet_records(user_id);

-- 唯一约束：防止同一用户重复抢同一个红包
CREATE UNIQUE INDEX IF NOT EXISTS red_packet_records_packet_user_unique_idx 
ON red_packet_records(packet_id, user_id); 