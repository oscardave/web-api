#!/usr/bin/env bash
set -euo pipefail

# ─── AI Gatekeeper · 入场闸门 ───
# 每次对话首轮必须执行，加载项目状态并输出定轨模板。

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

fail() { echo -e "${RED}✗ GATE BLOCKED: $1${NC}" >&2; exit 1; }
info() { echo -e "${CYAN}$1${NC}"; }
warn() { echo -e "${YELLOW}$1${NC}"; }
header() { echo -e "\n${BOLD}═══ $1 ═══${NC}"; }

# ─── 0. 基本环境检查 ───
[[ -f composer.json ]] || fail "未找到 composer.json，请在项目根目录执行"
git rev-parse --is-inside-work-tree &>/dev/null || fail "当前目录不是 Git 仓库"

echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║     AI Gatekeeper · 入场闸门 v1.0       ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"

# ─── 1. 项目状态快照 ───
header "项目状态"

BRANCH=$(git branch --show-current 2>/dev/null || echo "detached")
LAST_COMMITS=$(git log --oneline -5 2>/dev/null || echo "(无提交记录)")
DIRTY_COUNT=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
STAGED_COUNT=$(git diff --cached --name-only 2>/dev/null | wc -l | tr -d ' ')
PHP_VERSION=$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo "未检测到")

echo "分支:      $BRANCH"
echo "PHP 版本:  $PHP_VERSION"
echo "未提交变更: $DIRTY_COUNT 个文件"
echo "已暂存:    $STAGED_COUNT 个文件"
echo ""
echo "最近 5 条提交:"
echo "$LAST_COMMITS" | sed 's/^/  /'

# ─── 2. 工具链状态 ───
header "工具链检查"

check_tool() {
    local name="$1" path="$2"
    if [[ -f "$path" ]]; then
        echo -e "  ${GREEN}✓${NC} $name"
    else
        warn "  ⚠ $name 缺失 ($path)"
    fi
}

check_tool "PHPStan 配置"       "phpstan.neon"
check_tool "PHP-CS-Fixer 配置"  ".php-cs-fixer.php"
check_tool "EditorConfig"       ".editorconfig"
check_tool "Sentinel 规则"      ".cursor/rules/sentinel.mdc"

# ─── 3. 必读规范清单 ───
header "必读规范"

DOCS=(
    "docs/engineering-rules.md|架构约定与编码规范"
    "docs/code-patterns.md|现有代码模式参考"
    "docs/ai-lessons-learned.md|历史踩坑记录"
    "docs/rules-index.md|规则加载索引（SSOT）"
    "AGENTS.md|Agent 入口与知识导航"
    "knowledge/business-glossary.md|业务术语词典"
)

for entry in "${DOCS[@]}"; do
    IFS='|' read -r path desc <<< "$entry"
    if [[ -f "$path" ]]; then
        echo -e "  ${GREEN}✓${NC} $desc → $path"
    else
        warn "  ⚠ $desc → $path (未创建)"
    fi
done

echo ""
info "▸ Sentinel 常驻规则已通过 .cursor/rules/sentinel.mdc 自动加载"
info "▸ 编码红线与架构约定见 Sentinel 区块 3-4"

# ─── 4. 定轨报告模板 ───
header "定轨模板（请在回复中填写）"

cat <<'TEMPLATE'

┌─────────────────────────────────────┐
│ 任务类型: [ bugfix | feature | refactor | docs | config ]
│ 涉及层级: [ Controller | Dao | Service | Model | Config | Script ]
│ 涉及域:   [ Backend | Frontend | Internal | 跨域 ]
│ 变更规模: [ S (≤2文件) | M (多文件/跨Dao) | L (新模块/跨层级) ]
│ 预期产物: (简述)
└─────────────────────────────────────┘

TEMPLATE

# ─── 5. 下一步提示 ───
header "下一步"
echo "1. 根据上方模板明确任务范围"
echo "2. 写代码前执行: bash scripts/ai-plan.sh [S|M|L]"
echo "3. 代码完成后执行: bash scripts/ai-deliver.sh"
echo ""

echo -e "${GREEN}✓ 入场闸门通过${NC}"
exit 0
