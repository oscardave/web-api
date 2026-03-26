# Memory: Decisions

> 已冻结的架构和技术决策索引。一旦冻结，不轻易变更。
> 变更冻结决策需要记录原因和替代方案评估。

---

## 已冻结决策

| ID | 决策内容 | 冻结时间 | 来源 |
|----|----------|----------|------|
| decision-001 | 三层架构：Controller → Dao → DB，Service 为可选跨域层 | 2026-03-26 | sentinel.mdc §3 |
| decision-002 | Backend/Frontend 使用不同 JSON 信封，严禁混用 | 2026-03-26 | sentinel.mdc §3 |
| decision-003 | Dao 使用静态方法，返回 `['error','data']` 统一格式 | 2026-03-26 | engineering-rules §4 |
| decision-004 | 所有 PHP 文件必须 `declare(strict_types=1)` | 2026-03-26 | sentinel.mdc §4 |
| decision-005 | 业务扩展表使用 `ext_` 前缀，不修改 Synapse 核心表 | 2026-03-26 | knowledge/04 |
| decision-006 | 三域（Backend/Frontend/Internal）互斥隔离 | 2026-03-26 | sentinel.mdc §3 |
| decision-007 | 账变推送链路：Redis Pub/Sub → WebSocket → 客户端 | 2026-03-26 | knowledge/05 |
| decision-008 | 日期格式统一使用 Unix 时间戳（`$dateFormat = 'U'`） | 2026-03-26 | engineering-rules §8 |
| decision-009 | RAG 工具链使用 Node.js（MCP SDK 原生支持，不影响 PHP 生产） | 2026-03-26 | 本次 RAG 计划 |
| decision-010 | Memory 使用 Markdown 文件方案（轻量、git 可追踪） | 2026-03-26 | 本次 RAG 计划 |

---

## 决策变更记录

（暂无变更）
