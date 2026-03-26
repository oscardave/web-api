# 规则加载索引

> 本文件是规则/文档加载的 **SSOT 路由表**。`sentinel.mdc` 提供行为规则，本文件映射具体场景到具体文档。
> 冲突时：本文件 < `sentinel.mdc`（sentinel 为最终权威）。

---

## 按阶段加载

### 首轮（入场闸门后）

| 文档 | 用途 | 必读 |
|------|------|------|
| `.cursor/rules/sentinel.mdc` | 行为规则（自动加载） | 始终 |
| `docs/engineering-rules.md` | 工程规范 SSOT | 始终 |
| `AGENTS.md` | Agent 入口 + 知识导航 | 始终 |

### 分析/澄清

| 文档 | 用途 | 必读 |
|------|------|------|
| `knowledge/01-项目总览与架构.md` | 系统架构 | 涉及架构 |
| `knowledge/03-API接口全览.md` | 接口列表 | 涉及接口 |
| `knowledge/05-业务逻辑详解.md` | 业务流程 | 涉及业务 |
| `knowledge/business-glossary.md`（待建） | 术语定义 | 涉及业务 |
| `memory/decisions.md`（待建） | 已冻结决策 | 涉及架构决策 |

### 编码执行

| 文档 | S 级 | M/L 级 |
|------|------|--------|
| `docs/engineering-rules.md` | 必读 | 必读 |
| `docs/code-patterns.md` | 必读 | 必读 |
| `docs/ai-lessons-learned.md` | 推荐 | 必读 |
| `knowledge/06-核心模块与开发规范.md` | — | 必读 |
| `knowledge/04-数据库模型说明.md` | — | 涉及 DB 时 |

### 交付（deliver 闸门前）

| 文档 | 用途 | 必读 |
|------|------|------|
| `docs/engineering-rules.md` §1 | 核心红线自检 | 始终 |
| `sentinel.mdc` §5 | 反模式检查 | 始终 |

---

## 按场景加载

| 场景 | 加载文档 |
|------|----------|
| Backend 接口开发 | `engineering-rules.md` §3.1, `code-patterns.md` Backend 部分 |
| Frontend 接口开发 | `engineering-rules.md` §3.2, `code-patterns.md` Frontend 部分 |
| Dao 层开发 | `engineering-rules.md` §4, `code-patterns.md` Dao 部分 |
| WebSocket 相关 | `knowledge/02-技术栈详解.md`, `docs/前端WebSocket账变推送对接文档.md` |
| 数据库/Model | `knowledge/04-数据库模型说明.md`, `engineering-rules.md` §8 |
| Synapse 集成 | `knowledge/01-项目总览与架构.md`, `docs/AI开发文档.md` |
| 圈子/笔记业务 | `knowledge/05-业务逻辑详解.md` 相关章节 |
| 调试排错 | `docs/ai-lessons-learned.md`, `memory/facts.md`（待建） |

---

## SSOT 维护

每条规范/约定只有**一个权威出处**。修改时只改源头，其他文件引用之。

| 策略 | 权威出处 | 引用方 |
|------|----------|--------|
| 架构约定 | `sentinel.mdc` §3 | `engineering-rules.md` §2 |
| 编码红线 | `sentinel.mdc` §4 | `engineering-rules.md` §1 |
| 三闸门流程 | `sentinel.mdc` §1 | `AGENTS.md`, 本文件 |
| S/M/L 定义 | `sentinel.mdc` §1 | `ai-plan.sh` |
| JSON 信封 | `sentinel.mdc` §3 | `engineering-rules.md` §3, `code-patterns.md` |
| 命名规范 | `engineering-rules.md` §7 | `code-patterns.md` |
| 业务术语 | `knowledge/business-glossary.md`（待建） | `knowledge/05-*` 等 |
| 已冻结决策 | `memory/decisions.md`（待建） | — |
| 规则路由 | 本文件 | `AGENTS.md` 引用 |

---

## 权威层级

```
sentinel.mdc > docs/ > knowledge/ > memory/
```

冲突时高层级优先。从 `.mdc` 到具体文档最多 **一跳**。
