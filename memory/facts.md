# Memory: Facts

> 客观事实记录。非观点、非偏好——可验证的技术和环境事实。
> 维护规则：追加模式，过期事实标记 `[archived]` 而非删除。
> 最大活跃条目：50 条。超出时归档到 `memory/archive/facts-YYYY.md`。

---

## 技术栈事实

### fact-001
- **类型**: infra
- **日期**: 2026-03-26
- **状态**: active
- **内容**: 项目使用 PHP 8.1+ / Hyperf ~3.0 / Swoole >=5.0 作为运行时框架

### fact-002
- **类型**: infra
- **日期**: 2026-03-26
- **状态**: active
- **内容**: 数据库为 PostgreSQL，与 Synapse 共用同一实例，业务表以 `ext_` 前缀区分

### fact-003
- **类型**: infra
- **日期**: 2026-03-26
- **状态**: active
- **内容**: 缓存使用 Redis，通过 `hyperf/redis` + `hyperf/cache` 组件

### fact-004
- **类型**: infra
- **日期**: 2026-03-26
- **状态**: active
- **内容**: HTTP 服务默认端口 9005，WebSocket 服务默认端口 9509

### fact-005
- **类型**: constraint
- **日期**: 2026-03-26
- **状态**: active
- **内容**: Swoole 常驻内存模型，禁止全局可变状态（global 变量、类静态可变属性）

### fact-006
- **类型**: env
- **日期**: 2026-03-26
- **状态**: active
- **内容**: Synapse Admin API 通过 HTTP 内部调用，web-api 作为业务网关封装

### fact-007
- **类型**: constraint
- **日期**: 2026-03-26
- **状态**: active
- **内容**: Backend/Frontend/Internal 三域互斥，不可跨域引用 Controller 或 Dao

### fact-008
- **类型**: api
- **日期**: 2026-03-26
- **状态**: active
- **内容**: Backend 接口前缀 `/ht/v1/`，Frontend 接口前缀 `api/v1/`，Internal 接口前缀 `/internal/`

---

当前活跃条目：8 / 50
