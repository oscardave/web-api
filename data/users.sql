synapse_sq01> \d users;
+----------------------------+----------+-------------------------+
| Column                     | Type     | Modifiers               |
|----------------------------+----------+-------------------------|
| name                       | text     |                         |
| password_hash              | text     |                         |
| creation_ts                | bigint   |                         |
| admin                      | smallint |  not null default 0     |
| upgrade_ts                 | bigint   |                         |
| is_guest                   | smallint |  not null default 0     |
| appservice_id              | text     |                         |
| consent_version            | text     |                         |
| consent_server_notice_sent | text     |                         |
| user_type                  | text     |                         |
| deactivated                | smallint |  not null default 0     |
| shadow_banned              | boolean  |                         |
| consent_ts                 | bigint   |                         |
| approved                   | boolean  |                         |
| locked                     | boolean  |  not null default false |
| suspended                  | boolean  |  not null default false |
+----------------------------+----------+-------------------------+
Indexes:
    "users_name_key" UNIQUE CONSTRAINT, btree (name)
    "users_creation_ts" btree (creation_ts)
Referenced by:
    TABLE "users_to_send_full_presence_to" CONSTRAINT "users_to_send_full_presence_to_user_id_fkey" FOREIGN KEY (user_id) REFERENCES users(name)
    TABLE "per_user_experimental_features" CONSTRAINT "per_user_experimental_features_user_id_fkey" FOREIGN KEY (user_id) REFERENCES users(name)
    TABLE "user_wallets" CONSTRAINT "user_wallets_user_id_fkey" FOREIGN KEY (user_id) REFERENCES users(name)

