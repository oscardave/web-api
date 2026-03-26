#!/usr/bin/env bash
set -euo pipefail

###############################################################################
# ai-plan.sh — 规划闸门
# 用法: bash scripts/ai-plan.sh [S|M|L]
#
#   S  ≤2 文件变更 — 快速通过
#   M  多文件 或 跨 Dao 引用 — 输出影响分析模板
#   L  新模块 / 新 Controller / 跨层级变更 — 要求完整设计文档
###############################################################################

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

header() { echo -e "\n${BLUE}━━━ $1 ━━━${NC}"; }
ok()     { echo -e "${GREEN}✔ $1${NC}"; }
warn()   { echo -e "${YELLOW}⚠ $1${NC}"; }
fail()   { echo -e "${RED}✖ $1${NC}"; }

LEVEL="${1:-}"

if [[ -z "$LEVEL" ]]; then
  fail "缺少变更级别参数"
  echo "用法: bash scripts/ai-plan.sh [S|M|L]"
  echo ""
  echo "  S  ≤2 文件变更"
  echo "  M  多文件 或 跨 Dao 引用"
  echo "  L  新模块 / 新 Controller / 跨层级变更"
  exit 1
fi

LEVEL=$(echo "$LEVEL" | tr '[:lower:]' '[:upper:]')

if [[ "$LEVEL" != "S" && "$LEVEL" != "M" && "$LEVEL" != "L" ]]; then
  fail "无效级别: ${LEVEL}（必须为 S / M / L）"
  exit 1
fi

header "规划闸门 · 变更级别: $LEVEL"

# ---------------------------------------------------------------------------
# Level S — 快速通过
# ---------------------------------------------------------------------------
if [[ "$LEVEL" == "S" ]]; then
  ok "级别 S：≤2 文件变更，快速通过"
  echo ""
  echo "📋 注意事项："
  echo "   • 实际变更不得超过 2 个文件"
  echo "   • 若发现范围超出，须重新以 M 或 L 级别运行本脚本"
  echo ""
  ok "规划闸门通过"
  exit 0
fi

# ---------------------------------------------------------------------------
# Level M — 影响分析
# ---------------------------------------------------------------------------
if [[ "$LEVEL" == "M" ]]; then
  warn "级别 M：多文件变更 / 跨 Dao 引用"
  echo ""

  if [[ -f "docs/engineering-rules.md" ]]; then
    ok "docs/engineering-rules.md 存在 — 请确认已阅读"
  else
    warn "docs/engineering-rules.md 不存在 — 请参阅 .cursor/rules/sentinel.mdc 中的架构约定"
  fi

  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "📋 影响分析模板（请在规划中回答以下问题）："
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "1. 变更涉及的文件清单（预估）："
  echo "   - Controllers: ..."
  echo "   - Dao:         ..."
  echo "   - Models:      ..."
  echo "   - Validations: ..."
  echo "   - 其他:        ..."
  echo ""
  echo "2. 涉及的域（Backend / Frontend / Internal）："
  echo "   - [ ] 仅 Backend"
  echo "   - [ ] 仅 Frontend"
  echo "   - [ ] 仅 Internal"
  echo "   - [ ] 多域（需特别注意隔离）"
  echo ""
  echo "3. Dao 依赖关系："
  echo "   - 是否新增 Dao？         是/否"
  echo "   - 是否修改已有 Dao 签名？ 是/否"
  echo "   - 跨 Dao 调用链:         ..."
  echo ""
  echo "4. 响应格式确认："
  echo "   - Backend → responseData / responseError / responseSuccess"
  echo "   - Frontend → jsonResult / jsonErr / jsonOk"
  echo ""
  echo "5. 风险点："
  echo "   - ..."
  echo ""
  ok "规划闸门通过 — 请按模板完成影响分析后再开始编码"
  exit 0
fi

# ---------------------------------------------------------------------------
# Level L — 完整设计文档
# ---------------------------------------------------------------------------
if [[ "$LEVEL" == "L" ]]; then
  warn "级别 L：新模块 / 新 Controller / 跨层级变更"
  echo ""

  if [[ -f "docs/engineering-rules.md" ]]; then
    ok "docs/engineering-rules.md 存在 — 请确认已阅读"
  else
    warn "docs/engineering-rules.md 不存在 — 请参阅 .cursor/rules/sentinel.mdc 中的架构约定"
  fi

  if [[ -f "docs/code-patterns.md" ]]; then
    ok "docs/code-patterns.md 存在 — 请参考黄金模式"
  else
    warn "docs/code-patterns.md 不存在 — 请参考现有最佳实践代码"
  fi

  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "📋 完整设计文档模板（必须在编码前完成）："
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "## 1. 变更概述"
  echo "   - 目标: ..."
  echo "   - 背景: ..."
  echo ""
  echo "## 2. 文件清单"
  echo "   | 文件路径 | 操作(新建/修改/删除) | 说明 |"
  echo "   |---------|---------------------|------|"
  echo "   | ...     | ...                 | ...  |"
  echo ""
  echo "## 3. 架构设计"
  echo "   - 所属域: Backend / Frontend / Internal"
  echo "   - 层级关系: Controller → Dao → ..."
  echo "   - 新增路由:"
  echo "     - [METHOD] /path → Controller@action"
  echo ""
  echo "## 4. 数据模型"
  echo "   - 新增/修改的表: ..."
  echo "   - 新增/修改的 Model: ..."
  echo "   - 字段变更: ..."
  echo ""
  echo "## 5. Dao 设计"
  echo "   - 新增 Dao 方法签名:"
  echo "     - ClassName::methodName(type \$param): array"
  echo "   - 返回格式: ['error' => '', 'data' => ...]"
  echo ""
  echo "## 6. 响应格式"
  echo "   - Backend: responseData / responseError / responseSuccess"
  echo "   - Frontend: jsonResult / jsonErr / jsonOk"
  echo ""
  echo "## 7. 依赖分析"
  echo "   - 上游影响（谁调用了被修改的代码）: ..."
  echo "   - 下游影响（修改的代码调用了谁）: ..."
  echo ""
  echo "## 8. 风险与回滚"
  echo "   - 主要风险: ..."
  echo "   - 回滚方案: ..."
  echo ""
  ok "规划闸门通过 — 请按模板完成设计文档后再开始编码"
  exit 0
fi
