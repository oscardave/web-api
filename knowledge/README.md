# web-api 项目知识库

> 本知识库由 AI 自动分析代码库生成，覆盖项目架构、技术栈、接口、数据库、业务逻辑和开发规范。  
> 最后更新：2026-03-26

---

## 📚 文档目录

| 文档 | 说明 |
|------|------|
| [01 · 项目总览与架构](./01-项目总览与架构.md) | 系统定位、整体架构图、目录结构、三大子系统关系 |
| [02 · 技术栈详解](./02-技术栈详解.md) | Hyperf 3、Swoole 5、PostgreSQL、Redis、WebSocket 等技术详解 |
| [03 · API 接口全览](./03-API接口全览.md) | C 端前台、管理后台、内部接口、WebSocket 接口完整列表 |
| [04 · 数据库模型说明](./04-数据库模型说明.md) | 核心表结构、字段说明、数据关系图 |
| [05 · 业务逻辑详解](./05-业务逻辑详解.md) | 注册/登录、资料同步、账变推送、圈子、笔记、VIP 等核心流程 |
| [06 · 核心模块与开发规范](./06-核心模块与开发规范.md) | 分层架构、Controller/Dao/Model 规范、缓存、中间件、开发速查 |
| [业务术语词典](./business-glossary.md) | 核心业务术语、缩写和概念定义 |

---

## 🏗️ 项目一句话定位

**web-api** 是基于 **Hyperf 3 + Swoole 5** 构建的高性能 PHP 协程后端，作为 Matrix IM 系统（Synapse）的**业务网关**，同时服务 C 端 App 和管理后台（web-ht），提供用户、圈子、笔记、聊天分组、账变推送等业务能力。

---

## ⚡ 快速上手

### 环境要求
- PHP 8.1+
- Swoole 5.0+
- PostgreSQL（与 Synapse 共库）
- Redis

### 启动服务
```bash
# 开发模式（热重载）
php bin/hyperf.php server:watch

# 生产模式
php bin/hyperf.php start
```

### 关键端口
| 服务 | 端口 |
|------|------|
| HTTP API | `APP_PORT`（默认 9005） |
| WebSocket | `WS_PORT`（默认 9509） |

---

## 🔑 核心概念速查

| 概念 | 说明 |
|------|------|
| **MXID** | Matrix 用户 ID，格式 `@username:homeserver` |
| **ext_user_id** | `ext_users.id`，业务层主键，与 MXID 一一对应 |
| **access_token** | Synapse 颁发的登录 Token，用于 API 鉴权 |
| **ext_ 前缀表** | web-api 新增的业务扩展表，不影响 Synapse 核心表 |
| **账变推送** | 积分/诚信保变动 → Redis Pub/Sub → WebSocket → 客户端 |
| **资料同步** | 昵称/头像修改后，ProfileSyncService 同步到所有冗余字段 |
| **圈子** | 类似社区/群组，有公开/私密类型，支持帮办内容发布 |
| **超级笔记** | 特殊笔记类型，受会员等级限制，有独立的墙展示 |

---

## 🗂️ 关键文件速查

| 文件 | 说明 |
|------|------|
| `composer.json` | 依赖声明（Hyperf 组件、Swoole 等） |
| `config/autoload/server.php` | HTTP/WS 服务端口配置 |
| `config/autoload/databases.php` | PostgreSQL 连接池配置 |
| `config/autoload/redis.php` | Redis 连接配置 |
| `app/Http/Frontend/Controllers/` | C 端接口控制器 |
| `app/Http/Backend/Controllers/` | 管理后台接口控制器 |
| `app/Model/` | Eloquent ORM 模型（~60个） |
| `app/Service/AccountChangeNotifyService.php` | 账变推送服务 |
| `app/WebSocket/AccountPushHandler.php` | WebSocket 连接管理 |
| `app/Crontab/` | 定时任务（7个） |
| `docs/AI开发文档.md` | 原始 AI 对接文档（系统架构概述） |

