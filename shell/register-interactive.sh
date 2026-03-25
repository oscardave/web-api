#!/bin/bash

# 交互式用户注册脚本
# URL: https://im-sq01.chunquqiulai.top/api/v1/register

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_prompt() {
    echo -e "${BLUE}[INPUT]${NC} $1"
}

# 验证账号格式
validate_account() {
    local account=$1
    if ! [[ "$account" =~ ^[a-zA-Z0-9]{6,16}$ ]]; then
        print_error "账号格式错误：必须是6-16位字母或数字"
        return 1
    fi
    return 0
}

# 验证手机号格式（简单验证）
validate_mobile() {
    local mobile=$1
    if ! [[ "$mobile" =~ ^[0-9]{11}$ ]]; then
        print_error "手机号格式错误：必须是11位数字"
        return 1
    fi
    return 0
}

print_info "=========================================="
print_info "        用户注册脚本"
print_info "=========================================="
echo ""

# 输入账号
while true; do
    print_prompt "请输入账号（6-16位字母或数字）:"
    read -r ACCOUNT
    if validate_account "$ACCOUNT"; then
        break
    fi
done

# 输入密码
print_prompt "请输入密码:"
read -rs PASSWORD
echo ""

if [ -z "$PASSWORD" ]; then
    print_error "密码不能为空"
    exit 1
fi

# 确认密码
print_prompt "请再次输入密码确认:"
read -rs CONFIRM_PASSWORD
echo ""

if [ "$PASSWORD" != "$CONFIRM_PASSWORD" ]; then
    print_error "两次输入的密码不一致"
    exit 1
fi

# 输入手机号
while true; do
    print_prompt "请输入手机号（11位数字）:"
    read -r MOBILE
    if validate_mobile "$MOBILE"; then
        break
    fi
done

# 输入邀请码（可选）
print_prompt "请输入邀请码（可选，直接回车跳过）:"
read -r INVITE_CODE
INVITE_CODE=${INVITE_CODE:-""}  # 如果为空则设置为空字符串

# 输入短信动作（可选）
print_prompt "请输入短信动作（可选，直接回车使用默认值 register）:"
read -r ACTION
ACTION=${ACTION:-"register"}

echo ""
print_info "=========================================="
print_info "注册信息确认"
print_info "=========================================="
print_info "账号: $ACCOUNT"
print_info "手机号: $MOBILE"
if [ -n "$INVITE_CODE" ]; then
    print_info "邀请码: $INVITE_CODE"
else
    print_info "邀请码: (空)"
fi
print_info "短信动作: $ACTION"
echo ""

print_warning "确认以上信息无误？(y/n)"
read -r CONFIRM
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    print_info "已取消注册"
    exit 0
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

REG_RESPONSE=$(curl -s -X POST "${REG_URL}" \
  -H "content-type: application/json" \
  -d "${POST_DATA}" \
  -w "\n%{http_code}")

HTTP_CODE=$(echo "$REG_RESPONSE" | tail -n1)
BODY=$(echo "$REG_RESPONSE" | sed '$d')

echo ""
if [ "$HTTP_CODE" = "200" ]; then
    print_info "=========================================="
    print_info "注册成功！(HTTP $HTTP_CODE)"
    print_info "=========================================="
    echo "响应: $BODY"
else
    print_error "注册失败 (HTTP $HTTP_CODE)"
    echo "响应: $BODY"
    exit 1
fi
