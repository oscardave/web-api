# RAG Pipeline 构建产物

> 此目录包含由 `scripts/rag/` 生成的 RAG 索引和检索数据。
> **不要手动编辑**这些文件——它们由 pipeline 自动生成。

## 文件说明

| 文件 | 说明 | Git 追踪 |
|------|------|----------|
| `rag-index-manifest.json` | 源文件清单（路径、层级、模块、hash） | ✓ 追踪 |
| `rag-chunks.json` | 分块内容（MCP Server 的数据源） | ✓ 追踪 |
| `rag-vectordb.jsonl` | JSONL 导出（供向量数据库导入） | ✓ 追踪 |
| `rag-health.json` | 质量报告（文件数、chunk 数、健康分） | ✓ 追踪 |

## 重建步骤

```bash
# 一键重建全部
npm run rag:update

# 或分步执行
npm run rag:index     # 1. 收集源文件清单
npm run rag:chunks    # 2. 分块
npm run rag:export    # 3. 导出 JSONL
npm run rag:check     # 4. 质量检查
```

## 何时需要重建

- 修改了 `knowledge/`、`docs/`、`memory/` 中的文档
- 修改了 `.cursor/rules/` 中的规则
- 添加或删除了知识文件
- `rag-health.json` 显示源文件缺失

## 数据源

Pipeline 索引以下目录（定义在 `scripts/rag/lib/doc-paths.js`）：

| 目录 | 层级 (layer) | 文件类型 |
|------|-------------|----------|
| `.cursor/rules/` | `rules` | `.mdc` |
| `knowledge/` | `business` | `.md`, `.yaml`, `.yml` |
| `docs/` | `business` | `.md` |
| `memory/` | `business` | `.md` |

排除目录：`node_modules`, `vendor`, `ai-pipeline`, `archive`, `deprecated`
