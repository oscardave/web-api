#!/bin/bash

# 快速登录脚本 - 使用默认账号 test300
# 用于获取 access_token 并保存到文件
# 
# 注意：test300 是账号名，登录需要手机号或邮箱
# 如果 test300 对应的手机号未知，请使用 login.sh 并提供完整手机号

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# duidui aa123123

print_info "使用默认账号 test300 登录"
print_warning "注意：登录接口需要手机号或邮箱，test300 是账号名"
print_warning "请输入 test300 对应的手机号（11位数字）或邮箱："
read -r CONTACT

if [ -z "$CONTACT" ]; then
    echo -e "${RED}[ERROR]${NC} 手机号或邮箱不能为空"
    exit 1
fi

# 调用主登录脚本
"${SCRIPT_DIR}/login.sh" "$CONTACT" "aa123123" "$@"
