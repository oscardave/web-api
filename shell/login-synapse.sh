#!/bin/bash

# Synapse 原生登录脚本
# 使用 Matrix 协议的原生登录接口获取 access_token
# URL: https://im-sq01.chunquqiulai.top/_matrix/client/v3/login

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# Token 保存文件
TOKEN_FILE="$(dirname "$0")/token-synapse.txt"

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
    echo "用法: $0 <user_id> <password> [device_id]"
    echo ""
    echo "参数说明:"
    echo "  user_id   - Matrix 用户ID，格式: @user:domain.com 或 user:domain.com"
    echo "  password  - 密码"
    echo "  device_id - 设备ID（可选，不提供则自动生成）"
    echo ""
    echo "示例:"
    echo "  $0 '@test300:im-sq01.bleiworc.xyz' aa123123"
    echo "  $0 '@u10010:im-sq01.bleiworc.xyz' mypassword"
    echo ""
    echo "默认账号: @test300:im-sq01.bleiworc.xyz / aa123123"
    exit 1
fi

USER_ID=$1
PASSWORD=$2
DEVICE_ID=$3

# 确保 user_id 格式正确（添加 @ 前缀如果缺失）
if [[ ! "$USER_ID" =~ ^@ ]]; then
    USER_ID="@${USER_ID}"
fi

# 如果没有提供 device_id，使用空字符串（让服务器自动生成）
DEVICE_PARAM=""
if [ -n "$DEVICE_ID" ]; then
    DEVICE_PARAM="\"device_id\": \"${DEVICE_ID}\","
fi

print_info "=========================================="
print_info "    Synapse 原生登录脚本"
print_info "=========================================="
echo ""
print_info "用户ID: $USER_ID"
print_info "设备ID: ${DEVICE_ID:-'(自动生成)'}"
echo ""

# Synapse 登录接口
LOGIN_URL="${HOST}/_matrix/client/v3/login"

# 构建POST数据（使用 Matrix 协议标准格式）
if [ -n "$DEVICE_ID" ]; then
    # 有 device_id 的情况
    POST_DATA=$(cat <<EOF
{
  "type": "m.login.password",
  "identifier": {
    "type": "m.id.user",
    "user": "${USER_ID}"
  },
  "password": "${PASSWORD}",
  "device_id": "${DEVICE_ID}"
}
EOF
)
else
    # 没有 device_id 的情况
    POST_DATA=$(cat <<EOF
{
  "type": "m.login.password",
  "identifier": {
    "type": "m.id.user",
    "user": "${USER_ID}"
  },
  "password": "${PASSWORD}"
}
EOF
)
fi

print_info "请求URL: $LOGIN_URL"
print_info "请求数据:"
# 验证 JSON 格式
if ! echo "$POST_DATA" | python3 -m json.tool > /dev/null 2>&1; then
    print_error "JSON 格式验证失败！"
    echo "$POST_DATA"
    exit 1
fi
echo "$POST_DATA" | python3 -m json.tool 2>/dev/null || echo "$POST_DATA"
echo ""

# 使用临时文件确保 JSON 正确传递
TEMP_JSON=$(mktemp)
echo "$POST_DATA" > "$TEMP_JSON"

LOGIN_RESPONSE=$(curl -s -k -X POST "${LOGIN_URL}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  --data-binary "@${TEMP_JSON}" \
  -w "\n%{http_code}")

# 清理临时文件
rm -f "$TEMP_JSON"

HTTP_CODE=$(echo "$LOGIN_RESPONSE" | tail -n1)
BODY=$(echo "$LOGIN_RESPONSE" | sed '$d')

echo ""
if [ "$HTTP_CODE" = "200" ]; then
    # 解析响应获取 access_token
    ACCESS_TOKEN=$(echo "$BODY" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
    RESPONSE_USER_ID=$(echo "$BODY" | grep -o '"user_id":"[^"]*' | cut -d'"' -f4)
    RESPONSE_DEVICE_ID=$(echo "$BODY" | grep -o '"device_id":"[^"]*' | cut -d'"' -f4)
    
    if [ -n "$ACCESS_TOKEN" ]; then
        # 保存 token 到文件
        echo "$ACCESS_TOKEN" > "$TOKEN_FILE"
        
        # 同时保存完整响应到 JSON 文件
        echo "$BODY" | python3 -m json.tool 2>/dev/null > "${TOKEN_FILE%.txt}.json" || echo "$BODY" > "${TOKEN_FILE%.txt}.json"
        
        print_success "=========================================="
        print_success "登录成功！(HTTP $HTTP_CODE)"
        print_success "=========================================="
        echo ""
        print_info "用户ID: $RESPONSE_USER_ID"
        print_info "设备ID: $RESPONSE_DEVICE_ID"
        print_info "Access Token: $ACCESS_TOKEN"
        echo ""
        print_success "Token 已保存到: $TOKEN_FILE"
        print_info "完整响应已保存到: ${TOKEN_FILE%.txt}.json"
        echo ""
        echo "响应内容:"
        echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
    else
        print_warning "登录成功，但无法解析 access_token"
        echo "响应: $BODY"
        # 尝试保存完整响应
        echo "$BODY" > "${TOKEN_FILE%.txt}.json"
        print_info "完整响应已保存到: ${TOKEN_FILE%.txt}.json"
    fi
else
    print_error "登录失败 (HTTP $HTTP_CODE)"
    echo "响应: $BODY"
    
    # 尝试解析错误信息
    ERROR_MSG=$(echo "$BODY" | grep -o '"error":"[^"]*' | cut -d'"' -f4)
    ERROR_CODE=$(echo "$BODY" | grep -o '"errcode":"[^"]*' | cut -d'"' -f4)
    
    if [ -n "$ERROR_CODE" ]; then
        print_error "错误代码: $ERROR_CODE"
    fi
    if [ -n "$ERROR_MSG" ]; then
        print_error "错误信息: $ERROR_MSG"
    fi
    
    exit 1
fi
