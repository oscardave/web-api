# web-api · AI Agent 指引

> 本文件是 Cursor Agent 的项目级入口。所有 Agent 首次进入项目时应阅读此文件。

## 项目简介

**web-api** 是基于 Hyperf 3 + Swoole 5 的高性能 PHP 协程后端，作为 Matrix IM 系统（Synapse）的业务网关。

## 知识层级

优先级从高到低，冲突时高层级优先：

| 层级 | 路径 | 说明 |
|------|------|------|
| **L1 — 行为规则** | `.cursor/rules/sentinel.mdc` | 三闸门、架构约定、编码红线（always-on） |
| **L2 — 规范文档** | `docs/` | 工程规范 SSOT，新代码必须遵守 |
| **L3 — 参考知识** | `knowledge/` | AI 生成的项目知识库，冲突时 `docs/` 优先 |
| **L4 — 积累记忆** | `memory/` | 人工维护的事实、决策、偏好、经验 |

## 必读清单

进入项目后，按任务类型加载对应文档：

| 任务类型 | 必读 | 按需 |
|----------|------|------|
| 所有任务 | `sentinel.mdc`（自动加载） | `docs/rules-index.md`（规则路由） |
| 编码 (S) | `docs/engineering-rules.md`, `docs/code-patterns.md` | `docs/ai-lessons-learned.md` |
| 编码 (M/L) | 以上全部 + `knowledge/` 相关章节 | `knowledge/business-glossary.md` |
| 调试 | `docs/ai-lessons-learned.md`, `knowledge/05-业务逻辑详解.md` | 相关 `docs/*_对接文档.md` |
| 新功能 | `knowledge/01` ~ `06` 全部 | `memory/decisions.md` |

## 知识库目录

| 文档 | 说明 |
|------|------|
| [01 · 项目总览与架构](knowledge/01-项目总览与架构.md) | 系统定位、架构图、目录结构 |
| [02 · 技术栈详解](knowledge/02-技术栈详解.md) | Hyperf/Swoole/PostgreSQL/Redis |
| [03 · API 接口全览](knowledge/03-API接口全览.md) | 前台/后台/内部接口列表 |
| [04 · 数据库模型说明](knowledge/04-数据库模型说明.md) | 核心表结构、字段说明 |
| [05 · 业务逻辑详解](knowledge/05-业务逻辑详解.md) | 注册/登录/账变/圈子等核心流程 |
| [06 · 核心模块与开发规范](knowledge/06-核心模块与开发规范.md) | 分层架构、开发速查 |

## 闸门工作流

```
bash scripts/ai-gatekeeper.sh     # 入场（每次对话首轮）
bash scripts/ai-plan.sh [S|M|L]   # 规划（写代码前）
bash scripts/ai-deliver.sh        # 交付（代码完成后）
bash scripts/ai-knowledge-sync.sh # 知识同步（核心代码变更时）
```

## RAG 检索（可用时）

当 MCP RAG Server 可用时，Agent 可通过 `query_evidence` 工具按需检索知识库，无需全量加载文档。

回退链路：RAG MCP → SemanticSearch → Grep + Read
