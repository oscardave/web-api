#!/usr/bin/env bash
set -euo pipefail

# ─── AI Knowledge Sync · 知识漂移检测 ───
# 检测核心代码变更时是否有对应的 knowledge/ 或 docs/ 更新。
# 用法：bash scripts/ai-knowledge-sync.sh [--warn-only]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

fail() { echo -e "${RED}✗ KNOWLEDGE SYNC BLOCKED: $1${NC}" >&2; exit 1; }
info() { echo -e "${CYAN}$1${NC}"; }
warn() { echo -e "${YELLOW}$1${NC}"; }
header() { echo -e "\n${BOLD}═══ $1 ═══${NC}"; }

WARN_ONLY=false
[[ "${1:-}" == "--warn-only" ]] && WARN_ONLY=true

echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  AI Knowledge Sync · 知识漂移检测 v1.0  ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"

# ─── 1. 收集变更文件 ───
CHANGED_FILES=$(git diff --name-only HEAD 2>/dev/null || true)
STAGED_FILES=$(git diff --cached --name-only 2>/dev/null || true)
ALL_CHANGED=$(echo -e "${CHANGED_FILES}\n${STAGED_FILES}" | sort -u | grep -v '^$' || true)

if [[ -z "$ALL_CHANGED" ]]; then
    echo -e "${GREEN}✓ 无文件变更，跳过知识同步检查${NC}"
    exit 0
fi

# ─── 2. 检测核心代码路径变更 ───
CORE_PATTERNS=(
    "^app/Http/Backend/Controllers/"
    "^app/Http/Backend/Dao/"
    "^app/Http/Frontend/Controllers/"
    "^app/Http/Frontend/Dao/"
    "^app/Http/Internal/Controllers/"
    "^app/Service/"
    "^app/Model/"
    "^config/"
)

CORE_CHANGES=()
for file in $ALL_CHANGED; do
    for pattern in "${CORE_PATTERNS[@]}"; do
        if echo "$file" | grep -qE "$pattern"; then
            CORE_CHANGES+=("$file")
            break
        fi
    done
done

if [[ ${#CORE_CHANGES[@]} -eq 0 ]]; then
    echo -e "${GREEN}✓ 无核心代码路径变更，跳过知识同步检查${NC}"
    exit 0
fi

header "检测到核心代码变更"
for f in "${CORE_CHANGES[@]}"; do
    echo "  · $f"
done

# ─── 3. 检测知识文档是否同步更新 ───
KNOWLEDGE_PATTERNS=(
    "^knowledge/"
    "^docs/business-"
    "^docs/ai-lessons-learned"
    "^docs/engineering-rules"
    "^docs/code-patterns"
    "^memory/"
)

HAS_KNOWLEDGE_UPDATE=false
for file in $ALL_CHANGED; do
    for pattern in "${KNOWLEDGE_PATTERNS[@]}"; do
        if echo "$file" | grep -qE "$pattern"; then
            HAS_KNOWLEDGE_UPDATE=true
            break 2
        fi
    done
done

if $HAS_KNOWLEDGE_UPDATE; then
    echo -e "\n${GREEN}✓ 检测到知识文档同步更新，通过${NC}"
    exit 0
fi

# ─── 4. 分析变更影响 ───
header "知识漂移风险分析"

DRIFT_SIGNALS=0

for layer_label in "Backend Controllers:app/Http/Backend/Controllers/" \
                   "Backend Dao:app/Http/Backend/Dao/" \
                   "Frontend Controllers:app/Http/Frontend/Controllers/" \
                   "Frontend Dao:app/Http/Frontend/Dao/" \
                   "Internal Controllers:app/Http/Internal/Controllers/" \
                   "Service:app/Service/" \
                   "Model:app/Model/" \
                   "Config:config/"; do
    IFS=':' read -r label path <<< "$layer_label"
    count=0
    for f in "${CORE_CHANGES[@]}"; do
        [[ "$f" == "$path"* ]] && ((count++)) || true
    done
    if [[ $count -gt 0 ]]; then
        warn "  ⚠ $label: $count 个文件变更"
        ((DRIFT_SIGNALS += count))
    fi
done

echo ""
echo "  漂移信号总数: $DRIFT_SIGNALS"

# ─── 5. 输出建议 ───
header "建议同步的知识文档"

echo "  检查以下文档是否需要更新："
echo ""

for f in "${CORE_CHANGES[@]}"; do
    case "$f" in
        app/Http/Backend/Controllers/*|app/Http/Backend/Dao/*)
            echo "  → knowledge/03-API接口全览.md （后台接口）"
            break ;;
    esac
done

for f in "${CORE_CHANGES[@]}"; do
    case "$f" in
        app/Http/Frontend/Controllers/*|app/Http/Frontend/Dao/*)
            echo "  → knowledge/03-API接口全览.md （前台接口）"
            echo "  → knowledge/05-业务逻辑详解.md （业务流程）"
            break ;;
    esac
done

for f in "${CORE_CHANGES[@]}"; do
    case "$f" in
        app/Model/*)
            echo "  → knowledge/04-数据库模型说明.md （模型变更）"
            break ;;
    esac
done

for f in "${CORE_CHANGES[@]}"; do
    case "$f" in
        app/Service/*)
            echo "  → knowledge/05-业务逻辑详解.md （Service 层变更）"
            echo "  → knowledge/06-核心模块与开发规范.md"
            break ;;
    esac
done

for f in "${CORE_CHANGES[@]}"; do
    case "$f" in
        config/*)
            echo "  → knowledge/02-技术栈详解.md （配置变更）"
            break ;;
    esac
done

echo ""
echo "  通用建议："
echo "  → docs/ai-lessons-learned.md （如遇到坑点）"
echo "  → knowledge/business-glossary.md （如涉及新业务概念）"

# ─── 6. 阻断或警告 ───
echo ""
if $WARN_ONLY; then
    warn "⚠ 知识漂移警告（--warn-only 模式，不阻断）"
    warn "  建议在提交前更新相关知识文档"
    exit 0
else
    echo -e "${RED}✗ 知识漂移检测未通过${NC}"
    echo ""
    echo "  核心代码有 $DRIFT_SIGNALS 个变更，但未检测到 knowledge/ 或 docs/ 的同步更新。"
    echo ""
    echo "  解决方案："
    echo "  1. 更新上述建议的知识文档"
    echo "  2. 如果是纯重构无行为变更，在 knowledge/ 中添加说明文件"
    echo "  3. S 级变更可用 --warn-only 模式：bash scripts/ai-knowledge-sync.sh --warn-only"
    echo ""
    exit 1
fi
