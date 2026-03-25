#!/bin/bash

# 用户注册脚本
# 包含发送短信验证码和注册用户两个步骤
# URL: https://im-sq01.chunquqiulai.top/api/v1/register

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 打印带颜色的消息
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# 检查参数
if [ $# -lt 4 ]; then
    print_error "参数不足"
    echo "用法: $0 <account> <password> <confirm_password> <mobile> [invite_code] [action]"
    echo ""
    echo "参数说明:"
    echo "  account          - 账号（6-16位，只能字母数字）"
    echo "  password         - 密码"
    echo "  confirm_password - 确认密码"
    echo "  mobile           - 手机号码"
    echo "  invite_code      - 邀请码（可选，默认: 空字符串）"
    echo "  action           - 短信动作（可选，默认: register）"
    echo ""
    echo "示例:"
    echo "  $0 testuser123 mypassword mypassword 13800138000"
    echo "  $0 testuser123 mypassword mypassword 13800138000 INVITE123"
    exit 1
fi

ACCOUNT=$1
PASSWORD=$2
CONFIRM_PASSWORD=$3
MOBILE=$4
INVITE_CODE=${5:-""}  # 邀请码可选，默认为空字符串
ACTION=${6:-"register"}

# 验证账号格式
if ! [[ "$ACCOUNT" =~ ^[a-zA-Z0-9]{6,16}$ ]]; then
    print_error "账号格式错误：必须是6-16位字母或数字"
    exit 1
fi

# 验证密码是否一致
if [ "$PASSWORD" != "$CONFIRM_PASSWORD" ]; then
    print_error "密码和确认密码不一致"
    exit 1
fi

print_info "开始注册流程..."
print_info "账号: $ACCOUNT"
print_info "手机号: $MOBILE"
if [ -n "$INVITE_CODE" ]; then
    print_info "邀请码: $INVITE_CODE"
else
    print_info "邀请码: (空)"
fi
echo ""

# 步骤1: 发送短信验证码
print_info "步骤1: 发送短信验证码..."

SMS_URL="${HOST}/api/v1/register/sms?action=${ACTION}&mobile=${MOBILE}"

print_info "请求URL: $SMS_URL"

SMS_RESPONSE=$(curl -s -X GET "${SMS_URL}" \
  -H "content-type: application/json" \
  -w "\n%{http_code}")

HTTP_CODE=$(echo "$SMS_RESPONSE" | tail -n1)
BODY=$(echo "$SMS_RESPONSE" | sed '$d')

if [ "$HTTP_CODE" != "200" ]; then
    print_error "发送短信验证码失败 (HTTP $HTTP_CODE)"
    echo "响应: $BODY"
    exit 1
fi

print_info "短信验证码已发送，请查看手机短信"
echo "响应: $BODY"
echo ""

# 步骤2: 等待用户输入验证码
print_warning "请输入收到的验证码:"
read -r CODE

if [ -z "$CODE" ]; then
    print_error "验证码不能为空"
    exit 1
fi

echo ""

# 步骤3: 注册用户
print_info "步骤2: 注册用户..."

REG_URL="${HOST}/api/v1/register/reg"

# 构建POST数据
POST_DATA=$(cat <<EOF
{
  "account": "${ACCOUNT}",
  "password": "${PASSWORD}",
  "confirm_password": "${CONFIRM_PASSWORD}",
  "mobile": "${MOBILE}",
  "invite": "${INVITE_CODE}",
  "code": "${CODE}"
}
EOF
)

print_info "请求URL: $REG_URL"
print_info "请求数据: $POST_DATA"

REG_RESPONSE=$(curl -s -X POST "${REG_URL}" \
  -H "content-type: application/json" \
  -d "${POST_DATA}" \
  -w "\n%{http_code}")

HTTP_CODE=$(echo "$REG_RESPONSE" | tail -n1)
BODY=$(echo "$REG_RESPONSE" | sed '$d')

echo ""
if [ "$HTTP_CODE" = "200" ]; then
    print_info "注册成功！(HTTP $HTTP_CODE)"
    echo "响应: $BODY"
else
    print_error "注册失败 (HTTP $HTTP_CODE)"
    echo "响应: $BODY"
    exit 1
fi
