#!/bin/bash

# 用户登录脚本
# 用于获取 access_token 并保存到文件
# URL: https://im-sq01.chunquqiulai.top/api/v1/login

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# Token 保存文件
TOKEN_FILE="$(dirname "$0")/token.txt"

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

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# 检查参数
if [ $# -lt 2 ]; then
    print_error "参数不足"
    echo "用法: $0 <phone_or_email> <password> [verify_code]"
    echo ""
    echo "参数说明:"
    echo "  phone_or_email - 手机号或邮箱"
    echo "  password       - 密码"
    echo "  verify_code    - 验证码（可选，如果不提供会自动发送）"
    echo ""
    echo "示例:"
    echo "  $0 13800138000 mypassword"
    echo "  $0 13800138000 mypassword 123456"
    echo ""
    echo "默认账号: test300 / aa123123"
    exit 1
fi

PHONE_OR_EMAIL=$1
PASSWORD=$2
VERIFY_CODE=$3

# 如果没有提供验证码，先发送验证码
if [ -z "$VERIFY_CODE" ]; then
    print_info "=========================================="
    print_info "        用户登录脚本"
    print_info "=========================================="
    echo ""
    
    print_info "步骤1: 发送登录验证码..."
    
    # 判断是手机号还是邮箱
    if [[ "$PHONE_OR_EMAIL" =~ ^[0-9]{11}$ ]]; then
        CONTACT_TYPE="phone"
        MOBILE="$PHONE_OR_EMAIL"
        SMS_URL="${HOST}/api/v1/index/phone-verify-code"
        POST_DATA="{\"phone\":\"${PHONE_OR_EMAIL}\",\"verify_type\":\"login\",\"request_ip\":\"127.0.0.1\"}"
    elif [[ "$PHONE_OR_EMAIL" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
        CONTACT_TYPE="mail"
        MOBILE="$PHONE_OR_EMAIL"
        SMS_URL="${HOST}/api/v1/index/email-verify-code"
        POST_DATA="{\"email\":\"${PHONE_OR_EMAIL}\",\"verify_type\":\"login\",\"request_ip\":\"127.0.0.1\"}"
    else
        print_error "格式错误：请输入有效的手机号（11位数字）或邮箱地址"
        print_warning "提示：如果 test300 是账号名，请输入对应的手机号或邮箱"
        exit 1
    fi
    
    print_info "请求URL: $SMS_URL"
    print_info "请求数据: $POST_DATA"
    
    SMS_RESPONSE=$(curl -s -X POST "${SMS_URL}" \
      -H "content-type: application/json" \
      -d "${POST_DATA}" \
      -w "\n%{http_code}")
    
    HTTP_CODE=$(echo "$SMS_RESPONSE" | tail -n1)
    BODY=$(echo "$SMS_RESPONSE" | sed '$d')
    
    if [ "$HTTP_CODE" != "200" ]; then
        print_error "发送验证码失败 (HTTP $HTTP_CODE)"
        echo "响应: $BODY"
        exit 1
    fi
    
    print_success "验证码已发送，请查看${CONTACT_TYPE}消息"
    echo "响应: $BODY"
    echo ""
    
    # 等待用户输入验证码
    print_warning "请输入收到的验证码:"
    read -r VERIFY_CODE
    
    if [ -z "$VERIFY_CODE" ]; then
        print_error "验证码不能为空"
        exit 1
    fi
    
    echo ""
fi

# 步骤2: 登录
print_info "步骤2: 用户登录..."

LOGIN_URL="${HOST}/api/v1/login/login"

# 构建POST数据（登录接口使用 mobile 字段）
POST_DATA=$(cat <<EOF
{
  "mobile": "${PHONE_OR_EMAIL}",
  "password": "${PASSWORD}",
  "code": "${VERIFY_CODE}"
}
EOF
)

print_info "请求URL: $LOGIN_URL"
print_info "请求数据: $POST_DATA"

LOGIN_RESPONSE=$(curl -s -X POST "${LOGIN_URL}" \
  -H "content-type: application/json" \
  -d "${POST_DATA}" \
  -w "\n%{http_code}")

HTTP_CODE=$(echo "$LOGIN_RESPONSE" | tail -n1)
BODY=$(echo "$LOGIN_RESPONSE" | sed '$d')

echo ""
if [ "$HTTP_CODE" = "200" ]; then
    # 解析响应获取 access_token
    ACCESS_TOKEN=$(echo "$BODY" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
    USER_ID=$(echo "$BODY" | grep -o '"user_id":"[^"]*' | cut -d'"' -f4)
    DEVICE_ID=$(echo "$BODY" | grep -o '"device_id":"[^"]*' | cut -d'"' -f4)
    
    if [ -n "$ACCESS_TOKEN" ]; then
        # 保存 token 到文件
        echo "$ACCESS_TOKEN" > "$TOKEN_FILE"
        print_success "=========================================="
        print_success "登录成功！(HTTP $HTTP_CODE)"
        print_success "=========================================="
        echo ""
        print_info "用户ID: $USER_ID"
        print_info "设备ID: $DEVICE_ID"
        print_info "Access Token: $ACCESS_TOKEN"
        echo ""
        print_success "Token 已保存到: $TOKEN_FILE"
        echo ""
        echo "响应: $BODY"
    else
        print_warning "登录成功，但无法解析 access_token"
        echo "响应: $BODY"
        # 尝试保存完整响应
        echo "$BODY" > "${TOKEN_FILE}.json"
        print_info "完整响应已保存到: ${TOKEN_FILE}.json"
    fi
else
    print_error "登录失败 (HTTP $HTTP_CODE)"
    echo "响应: $BODY"
    exit 1
fi
