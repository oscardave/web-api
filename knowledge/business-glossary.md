# 业务术语词典

> 本文件定义项目中所有业务术语、缩写和核心概念。是 Agent 理解业务需求的参考基准。
> 冲突时以 `docs/engineering-rules.md` 为准。

---

## 用户与身份

| 术语 | 说明 | 关联 |
|------|------|------|
| **MXID** | Matrix 用户 ID，格式 `@username:homeserver`，全局唯一 | `users.name` |
| **ext_user_id** | `ext_users.id`，业务层用户主键，与 MXID 一一对应 | `ext_users` 表 |
| **access_token** | Synapse 颁发的登录 Token，用于 C 端 API 鉴权 | HTTP Header `Authorization` |
| **Admin Token** | 后台管理员登录 Token | `admins` 表 |
| **资料同步** | 昵称/头像修改后，`ProfileSyncService` 同步到所有冗余字段 | `ext_users`, `ext_circle_members` 等 |
| **手机/邮箱绑定** | 用户绑定验证手机号或邮箱，支持后续找回密码 | `ext_users.phone`, `ext_users.email` |

## 社交与内容

| 术语 | 说明 | 关联 |
|------|------|------|
| **圈子** (Circle) | 类似社区/群组，有公开/私密类型 | `ext_circles`, `ext_circle_members` |
| **帮办** | 圈子内的内容发布功能 | `ext_circle_posts` 相关 |
| **笔记** (Note) | 用户发布的内容，支持图文 | `ext_notes` |
| **超级笔记** | 特殊笔记类型，受会员等级限制，有独立的墙展示 | `ext_super_notes` |
| **聊天分组** | 用户自定义的聊天会话分组 | `ext_chat_groups` |
| **徽章** (Badge) | 圈子可配置的展示徽章 | `ext_circle_badges` |

## 经济系统

| 术语 | 说明 | 关联 |
|------|------|------|
| **账变推送** | 积分/诚信保余额变动，通过 Redis Pub/Sub → WebSocket 推送到客户端 | `AccountChangeNotifyService` |
| **积分** | 用户虚拟货币，可通过活动获取 | `ext_user_accounts` |
| **诚信保** | 用户信用保证金 | `ext_user_accounts` |
| **VIP / 会员** | 会员等级系统，影响功能权限（如超级笔记） | `ext_user_vips` |
| **会员兑换** | 使用积分兑换 VIP 等级 | 兑换接口 |

## 技术术语

| 术语 | 说明 | 关联 |
|------|------|------|
| **Synapse** | Matrix 协议的 Python 实现，IM 核心服务器 | 共用 PostgreSQL 数据库 |
| **Synapse Admin API** | Synapse 提供的管理接口，web-api 通过 HTTP 调用 | `SynapseAdminService` |
| **ext_ 前缀表** | web-api 在 Synapse 数据库中新增的业务扩展表，不影响 Synapse 核心表 | 所有 `ext_*` Model |
| **web-ht** | 管理后台前端项目 | 调用 `/ht/v1/` 接口 |
| **web-app** | C 端 App 前端项目 | 调用 `api/v1/` 接口 |
| **Hyperf** | PHP 协程框架（基于 Swoole），项目主框架 | `composer.json` |
| **Swoole** | PHP 协程扩展，提供常驻内存 + 协程能力 | 运行时环境 |

## 架构缩写

| 缩写 | 全称 | 说明 |
|------|------|------|
| **Dao** | Data Access Object | 数据访问层，静态方法，返回 `['error','data']` |
| **DTO** | Data Transfer Object | 数据传输对象（本项目用 array 替代） |
| **WS** | WebSocket | 长连接推送 |
| **SSOT** | Single Source of Truth | 单一事实来源 |
