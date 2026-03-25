#!/bin/bash

# Synapse 原生登录脚本 - 使用默认账号 test300
# 使用 Matrix 协议的原生登录接口获取 access_token

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# 默认账号配置
# 注意：test300 是账号名，需要转换为完整的 Matrix 用户ID
# 格式通常是: @u<数字>:<域名> 或 @<账号名>:<域名>
DEFAULT_USER_ID="@test300:im-sq01.chunquqiulai.top"
DEFAULT_PASSWORD="aa123123"

# 颜色输出
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_info "使用默认账号 test300 登录 Synapse"
print_warning "如果 @test300:im-sq01.bleiworc.xyz 不存在，请提供正确的 Matrix 用户ID"
print_warning "格式: @u<数字>:im-sq01.bleiworc.xyz 或 @<账号名>:im-sq01.bleiworc.xyz"
echo ""

# 调用主登录脚本
"${SCRIPT_DIR}/login-synapse.sh" "$DEFAULT_USER_ID" "$DEFAULT_PASSWORD" "$@"
