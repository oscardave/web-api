#!/usr/bin/env bash
set -euo pipefail

###############################################################################
# ai-deliver.sh — 交付闸门（6 道检查链）
#
# 代码修改完成后执行。任一检查失败 → exit 1，阻断交付。
# 仅扫描本次变更的 PHP 文件（基于 git diff）。
###############################################################################

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

PASS_COUNT=0
FAIL_COUNT=0
WARN_COUNT=0
TOTAL_CHECKS=6

print_header() {
    echo ""
    echo -e "${BOLD}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}║          ai-deliver.sh · 交付闸门 (6 道检查)        ║${NC}"
    echo -e "${BOLD}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
}

check_pass() {
    echo -e "  ${GREEN}✔ PASS${NC}  $1"
    PASS_COUNT=$((PASS_COUNT + 1))
}

check_fail() {
    echo -e "  ${RED}✘ FAIL${NC}  $1"
    FAIL_COUNT=$((FAIL_COUNT + 1))
}

check_warn() {
    echo -e "  ${YELLOW}⚠ WARN${NC}  $1"
    WARN_COUNT=$((WARN_COUNT + 1))
    PASS_COUNT=$((PASS_COUNT + 1))
}

section() {
    echo ""
    echo -e "${CYAN}── [$1/6] $2${NC}"
}

# ---------------------------------------------------------------------------
# Collect changed PHP files (staged + unstaged, relative to project root)
# ---------------------------------------------------------------------------
get_changed_php_files() {
    {
        git diff --name-only --diff-filter=ACMR HEAD -- '*.php' 2>/dev/null
        git diff --name-only --diff-filter=ACMR --cached HEAD -- '*.php' 2>/dev/null
        git diff --name-only --diff-filter=ACMR -- '*.php' 2>/dev/null
    } | sort -u
}

get_app_changed_php_files() {
    get_changed_php_files | grep -v '^scripts/' || true
}

print_header

CHANGED_PHP=$(get_app_changed_php_files)

if [[ -z "$CHANGED_PHP" ]]; then
    echo -e "${YELLOW}没有检测到 PHP 文件变更（scripts/ 目录除外），跳过检查。${NC}"
    echo ""
    exit 0
fi

FILE_COUNT=$(echo "$CHANGED_PHP" | wc -l | tr -d ' ')
echo -e "检测到 ${BOLD}${FILE_COUNT}${NC} 个变更的 PHP 文件："
echo "$CHANGED_PHP" | while read -r f; do echo "  · $f"; done

###############################################################################
# CHECK 1: 调试代码拦截
###############################################################################
section 1 "调试代码拦截 (var_dump / print_r / dd / dump / echo)"

check1_failed=false
violations=""

while IFS= read -r file; do
    [[ -f "$file" ]] || continue

    file_hits=""

    # var_dump( / print_r( as statements
    hits=$(grep -nE '^[[:space:]]*(var_dump|print_r)[[:space:]]*\(' "$file" 2>/dev/null || true)
    [[ -n "$hits" ]] && file_hits+="$hits"$'\n'

    # dd( / dump( — exclude words ending in dd/dump (e.g. method_add)
    # Match lines where dd( or dump( appears preceded by space, line-start, or non-alpha
    hits=$(grep -nE '(^|[^a-zA-Z_])(dd|dump)[[:space:]]*\(' "$file" 2>/dev/null || true)
    [[ -n "$hits" ]] && file_hits+="$hits"$'\n'

    # Standalone echo statements
    hits=$(grep -nE '^[[:space:]]*echo[[:space:]]' "$file" 2>/dev/null || true)
    [[ -n "$hits" ]] && file_hits+="$hits"$'\n'

    # Commented-out debug code (echo uses space not parens)
    hits=$(grep -nE '^[[:space:]]*(//|#)[[:space:]]*(var_dump|print_r|dd|dump)[[:space:]]*\(' "$file" 2>/dev/null || true)
    [[ -n "$hits" ]] && file_hits+="$hits"$'\n'
    hits=$(grep -nE '^[[:space:]]*(//|#)[[:space:]]*echo[[:space:]]' "$file" 2>/dev/null || true)
    [[ -n "$hits" ]] && file_hits+="$hits"$'\n'

    file_hits=$(echo "$file_hits" | sed '/^$/d' | sort -t: -k1,1n -u)

    if [[ -n "$file_hits" ]]; then
        check1_failed=true
        violations+="  ${file}:"$'\n'
        while IFS= read -r line; do
            violations+="    ${line}"$'\n'
        done <<< "$file_hits"
    fi
done <<< "$CHANGED_PHP"

if $check1_failed; then
    check_fail "发现调试代码或注释掉的调试代码"
    echo "$violations"
else
    check_pass "无调试代码残留"
fi

###############################################################################
# CHECK 2: PHPStan 静态分析
###############################################################################
section 2 "PHPStan 静态分析"

PHPSTAN_BIN="$PROJECT_ROOT/vendor/bin/phpstan"

if [[ ! -x "$PHPSTAN_BIN" ]]; then
    check_warn "PHPStan 未安装 (vendor/bin/phpstan 不存在)，跳过。请运行 composer install"
else
    phpstan_files=()
    while IFS= read -r file; do
        [[ -f "$file" ]] && phpstan_files+=("$file")
    done <<< "$CHANGED_PHP"

    if [[ ${#phpstan_files[@]} -eq 0 ]]; then
        check_pass "无需分析的文件"
    else
        phpstan_output=$("$PHPSTAN_BIN" analyse \
            --memory-limit=256M \
            --no-progress \
            --error-format=table \
            "${phpstan_files[@]}" 2>&1) || true

        if echo "$phpstan_output" | grep -q '\[OK\] No errors'; then
            check_pass "PHPStan 分析通过"
        else
            error_count=$(echo "$phpstan_output" | grep -oE 'Found [0-9]+ error' | grep -oE '[0-9]+' || echo "0")
            if [[ "$error_count" -gt 0 ]]; then
                check_fail "PHPStan 发现 ${error_count} 个错误"
                echo "$phpstan_output" | tail -40
            else
                check_pass "PHPStan 分析通过"
            fi
        fi
    fi
fi

###############################################################################
# CHECK 3: PHP-CS-Fixer 代码风格
###############################################################################
section 3 "PHP-CS-Fixer 代码风格检查"

CSFIXER_BIN="$PROJECT_ROOT/vendor/bin/php-cs-fixer"

CSFIXER_CONFIG=""
if [[ -f "$PROJECT_ROOT/.php-cs-fixer.php" ]]; then
    CSFIXER_CONFIG="$PROJECT_ROOT/.php-cs-fixer.php"
elif [[ -f "$PROJECT_ROOT/.php_cs" ]]; then
    CSFIXER_CONFIG="$PROJECT_ROOT/.php_cs"
fi

if [[ ! -x "$CSFIXER_BIN" ]]; then
    check_warn "PHP-CS-Fixer 未安装 (vendor/bin/php-cs-fixer 不存在)，跳过。请运行 composer install"
else
    csfixer_args=("fix" "--dry-run" "--diff" "--using-cache=no")
    if [[ -n "$CSFIXER_CONFIG" ]]; then
        csfixer_args+=("--config=$CSFIXER_CONFIG")
    fi

    csfixer_failed=false
    csfixer_output=""

    while IFS= read -r file; do
        [[ -f "$file" ]] || continue
        result=$("$CSFIXER_BIN" "${csfixer_args[@]}" "$file" 2>&1) || true
        if echo "$result" | grep -qE '^[[:space:]]*[0-9]+\) '; then
            csfixer_failed=true
            csfixer_output+="$result"$'\n'
        fi
    done <<< "$CHANGED_PHP"

    if $csfixer_failed; then
        check_fail "代码风格不符合规范，请运行 vendor/bin/php-cs-fixer fix"
        echo "$csfixer_output" | head -60
    else
        check_pass "代码风格检查通过"
    fi
fi

###############################################################################
# CHECK 4: 路径边界检查 (Backend / Frontend / Internal 域隔离)
###############################################################################
section 4 "路径边界检查 (域隔离)"

check4_failed=false
boundary_violations=""

while IFS= read -r file; do
    [[ -f "$file" ]] || continue

    case "$file" in
        app/Http/Backend/*)
            hits=$(grep -nE 'use[[:space:]]+App\\Http\\Frontend\\' "$file" 2>/dev/null || true)
            hits2=$(grep -nE 'use[[:space:]]+App\\Http\\Internal\\' "$file" 2>/dev/null || true)
            if [[ -n "$hits" ]] || [[ -n "$hits2" ]]; then
                check4_failed=true
                boundary_violations+="  ${file} (Backend 引用了 Frontend/Internal):"$'\n'
                [[ -n "$hits" ]] && while IFS= read -r l; do boundary_violations+="    ${l}"$'\n'; done <<< "$hits"
                [[ -n "$hits2" ]] && while IFS= read -r l; do boundary_violations+="    ${l}"$'\n'; done <<< "$hits2"
            fi
            ;;
        app/Http/Frontend/*)
            hits=$(grep -nE 'use[[:space:]]+App\\Http\\Backend\\' "$file" 2>/dev/null || true)
            hits2=$(grep -nE 'use[[:space:]]+App\\Http\\Internal\\' "$file" 2>/dev/null || true)
            if [[ -n "$hits" ]] || [[ -n "$hits2" ]]; then
                check4_failed=true
                boundary_violations+="  ${file} (Frontend 引用了 Backend/Internal):"$'\n'
                [[ -n "$hits" ]] && while IFS= read -r l; do boundary_violations+="    ${l}"$'\n'; done <<< "$hits"
                [[ -n "$hits2" ]] && while IFS= read -r l; do boundary_violations+="    ${l}"$'\n'; done <<< "$hits2"
            fi
            ;;
        app/Http/Internal/*)
            hits=$(grep -nE 'use[[:space:]]+App\\Http\\Backend\\' "$file" 2>/dev/null || true)
            hits2=$(grep -nE 'use[[:space:]]+App\\Http\\Frontend\\' "$file" 2>/dev/null || true)
            if [[ -n "$hits" ]] || [[ -n "$hits2" ]]; then
                check4_failed=true
                boundary_violations+="  ${file} (Internal 引用了 Backend/Frontend):"$'\n'
                [[ -n "$hits" ]] && while IFS= read -r l; do boundary_violations+="    ${l}"$'\n'; done <<< "$hits"
                [[ -n "$hits2" ]] && while IFS= read -r l; do boundary_violations+="    ${l}"$'\n'; done <<< "$hits2"
            fi
            ;;
    esac
done <<< "$CHANGED_PHP"

if $check4_failed; then
    check_fail "检测到跨域引用"
    echo "$boundary_violations"
else
    check_pass "域隔离边界正常"
fi

###############################################################################
# CHECK 5: 响应格式一致性 (Backend vs Frontend 信封)
###############################################################################
section 5 "响应格式一致性 (信封方法检查)"

check5_failed=false
envelope_violations=""

while IFS= read -r file; do
    [[ -f "$file" ]] || continue

    case "$file" in
        app/Http/Frontend/Controllers/*)
            hits=$(grep -nE '\$this->(responseData|responseError|responseSuccess)[[:space:]]*\(' "$file" 2>/dev/null || true)
            if [[ -n "$hits" ]]; then
                check5_failed=true
                envelope_violations+="  ${file} (Frontend 使用了 Backend 信封方法):"$'\n'
                while IFS= read -r l; do envelope_violations+="    ${l}"$'\n'; done <<< "$hits"
            fi
            ;;
        app/Http/Backend/Controllers/*)
            # Skip the base class itself — it defines these methods
            [[ "$file" == *"BackendController.php" ]] && continue
            hits=$(grep -nE '(self|static)::(jsonResult|jsonErr|jsonOk|jsonArray|jsonObject|jsonExpireErr)[[:space:]]*\(' "$file" 2>/dev/null || true)
            if [[ -n "$hits" ]]; then
                check5_failed=true
                envelope_violations+="  ${file} (Backend 使用了 Frontend 信封方法):"$'\n'
                while IFS= read -r l; do envelope_violations+="    ${l}"$'\n'; done <<< "$hits"
            fi
            ;;
    esac
done <<< "$CHANGED_PHP"

if $check5_failed; then
    check_fail "检测到信封方法混用"
    echo "$envelope_violations"
else
    check_pass "响应信封使用正确"
fi

###############################################################################
# CHECK 6: Swoole 安全扫描 (全局状态 / 协程安全)
###############################################################################
section 6 "Swoole 安全扫描 (全局状态 / 协程安全)"

check6_failed=false
swoole_violations=""

while IFS= read -r file; do
    [[ -f "$file" ]] || continue

    # Model static properties ($table, $fillable, $casts, etc.) are standard Eloquent
    case "$file" in
        app/Model/*) continue ;;
    esac

    all_swoole=""

    # 1. global keyword
    hits=$(grep -nE '^[[:space:]]*global[[:space:]]+\$' "$file" 2>/dev/null || true)
    if [[ -n "$hits" ]]; then
        all_swoole+="    [global 关键字]"$'\n'
        while IFS= read -r l; do all_swoole+="      ${l}"$'\n'; done <<< "$hits"
    fi

    # 2. Mutable static properties (exclude: static function, known-safe config props)
    hits=$(grep -nE '^[[:space:]]*(public|protected|private)[[:space:]]+static[[:space:]]+' "$file" 2>/dev/null || true)
    if [[ -n "$hits" ]]; then
        # Filter out static functions, readonly, and known-safe declarative properties
        hits=$(echo "$hits" | grep -vE '(static[[:space:]]+function|static[[:space:]]+readonly)' || true)
        hits=$(echo "$hits" | grep -vE '\$(validations|appName|instance|middlewares)\b' || true)
        hits=$(echo "$hits" | sed '/^$/d')
    fi
    if [[ -n "$hits" ]]; then
        all_swoole+="    [可变 static 属性 — 协程不安全]"$'\n'
        while IFS= read -r l; do all_swoole+="      ${l}"$'\n'; done <<< "$hits"
    fi

    # 3. $_SESSION / $_GLOBALS usage
    hits=$(grep -nE '\$_(SESSION|GLOBALS)[[:space:]]*\[' "$file" 2>/dev/null || true)
    if [[ -n "$hits" ]]; then
        all_swoole+="    [\$_SESSION/\$_GLOBALS — Swoole 下无效]"$'\n'
        while IFS= read -r l; do all_swoole+="      ${l}"$'\n'; done <<< "$hits"
    fi

    if [[ -n "$all_swoole" ]]; then
        check6_failed=true
        swoole_violations+="  ${file}:"$'\n'
        swoole_violations+="${all_swoole}"
    fi
done <<< "$CHANGED_PHP"

if $check6_failed; then
    check_fail "检测到协程不安全的全局状态"
    echo "$swoole_violations"
else
    check_pass "无全局状态 / 协程安全风险"
fi

###############################################################################
# SUMMARY
###############################################################################
echo ""
echo -e "${BOLD}══════════════════════════════════════════════════════${NC}"
echo -e "  通过: ${GREEN}${PASS_COUNT}${NC}  失败: ${RED}${FAIL_COUNT}${NC}  警告: ${YELLOW}${WARN_COUNT}${NC}  共计: ${TOTAL_CHECKS}"
echo -e "${BOLD}══════════════════════════════════════════════════════${NC}"

if [[ $FAIL_COUNT -gt 0 ]]; then
    echo ""
    echo -e "${RED}${BOLD}✘ 交付闸门未通过 — 请修复上述 ${FAIL_COUNT} 个失败项后重新执行${NC}"
    echo ""
    exit 1
fi

echo ""
echo -e "${GREEN}${BOLD}✔ 交付闸门全部通过${NC}"
echo ""
exit 0
