#!/bin/bash

# 聊天列表接口测试脚本
# 测试 ChatsControllers 的三个接口：
# 1. 我创建的聊天室 - /api/v1/chats/created
# 2. 我管理的聊天 - /api/v1/chats/managed
# 3. 我加入的聊天 - /api/v1/chats/joined

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# Token 文件路径（优先使用 token-synapse.txt，其次 token.txt）
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TOKEN_FILE_SYNAPSE="${SCRIPT_DIR}/token-synapse.txt"
TOKEN_FILE="${SCRIPT_DIR}/token.txt"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
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

print_section() {
    echo -e "${CYAN}[SECTION]${NC} $1"
}

# 读取 Token
if [ -f "$TOKEN_FILE_SYNAPSE" ]; then
    TOKEN=$(cat "$TOKEN_FILE_SYNAPSE" | tr -d '\n\r ')
    print_info "使用 Token 文件: token-synapse.txt"
elif [ -f "$TOKEN_FILE" ]; then
    TOKEN=$(cat "$TOKEN_FILE" | tr -d '\n\r ')
    print_info "使用 Token 文件: token.txt"
else
    print_error "未找到 Token 文件！"
    echo "请先运行登录脚本获取 Token："
    echo "  ./login-synapse-test300.sh"
    echo "  或"
    echo "  ./login-test300.sh"
    exit 1
fi

if [ -z "$TOKEN" ]; then
    print_error "Token 为空！"
    exit 1
fi

print_info "Token: ${TOKEN:0:20}..."
echo ""

# 解析参数
PAGE=${1:-1}
LIMIT=${2:-50}

print_info "=========================================="
print_info "    聊天列表接口测试脚本"
print_info "=========================================="
print_info "页码: $PAGE"
print_info "每页数量: $LIMIT"
echo ""

# 测试函数
test_api() {
    local api_name=$1
    local api_path=$2
    local method=${3:-GET}
    
    print_section "测试接口: $api_name"
    print_info "路径: $api_path"
    print_info "方法: $method"
    
    local url="${HOST}${api_path}?page=${PAGE}&limit=${LIMIT}"
    
    if [ "$method" = "POST" ]; then
        RESPONSE=$(curl -s -X POST "${url}" \
          -H "authorization: Bearer ${TOKEN}" \
          -H "content-type: application/json" \
          -w "\n%{http_code}")
    else
        RESPONSE=$(curl -s -X GET "${url}" \
          -H "authorization: Bearer ${TOKEN}" \
          -H "content-type: application/json" \
          -w "\n%{http_code}")
    fi
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    BODY=$(echo "$RESPONSE" | sed '$d')
    
    echo ""
    if [ "$HTTP_CODE" = "200" ]; then
        print_success "请求成功 (HTTP $HTTP_CODE)"
        
        # 解析响应数据
        CODE=$(echo "$BODY" | grep -o '"code":[0-9]*' | cut -d':' -f2)
        TOTAL=$(echo "$BODY" | grep -o '"total":[0-9]*' | cut -d':' -f2)
        LIST_COUNT=$(echo "$BODY" | python3 -c "import sys, json; data=json.load(sys.stdin); print(len(data.get('data', {}).get('list', [])))" 2>/dev/null || echo "0")
        
        if [ "$CODE" = "0" ]; then
            print_success "业务成功 (code: $CODE)"
            print_info "总记录数: ${TOTAL:-0}"
            print_info "当前页记录数: ${LIST_COUNT:-0}"
            
            # 显示前几条记录的关键信息
            if [ "$LIST_COUNT" -gt 0 ]; then
                echo ""
                print_info "前3条记录预览:"
                echo "$BODY" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    rooms = data.get('data', {}).get('list', [])[:3]
    for i, room in enumerate(rooms, 1):
        room_id = room.get('room_id', 'N/A')
        room_name = room.get('room_name', 'N/A')
        last_msg = room.get('last_message', {})
        last_msg_body = last_msg.get('body', '') if last_msg else '无消息'
        last_msg_time = last_msg.get('origin_server_ts', 0) if last_msg else 0
        print(f'  {i}. 房间ID: {room_id}')
        print(f'     房间名: {room_name}')
        print(f'     最后消息: {last_msg_body[:50]}')
        print(f'     消息时间: {last_msg_time}')
        print()
except Exception as e:
    print(f'解析错误: {e}')
" 2>/dev/null || echo "  无法解析记录详情"
            else
                print_warning "当前页无记录"
            fi
        else
            MESSAGE=$(echo "$BODY" | grep -o '"message":"[^"]*' | cut -d'"' -f4)
            print_error "业务失败 (code: $CODE)"
            if [ -n "$MESSAGE" ]; then
                print_error "错误信息: $MESSAGE"
            fi
        fi
    else
        print_error "请求失败 (HTTP $HTTP_CODE)"
        ERROR_CODE=$(echo "$BODY" | grep -o '"errcode":"[^"]*' | cut -d'"' -f4)
        ERROR_MSG=$(echo "$BODY" | grep -o '"error":"[^"]*' | cut -d'"' -f4)
        if [ -n "$ERROR_CODE" ]; then
            print_error "错误代码: $ERROR_CODE"
        fi
        if [ -n "$ERROR_MSG" ]; then
            print_error "错误信息: $ERROR_MSG"
        fi
    fi
    
    echo ""
    print_info "完整响应:"
    echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
    echo ""
    echo "----------------------------------------"
    echo ""
}

# 测试三个接口
test_api "我创建的聊天室" "/api/v1/chats/created" "GET"
test_api "我管理的聊天" "/api/v1/chats/managed" "GET"
test_api "我加入的聊天" "/api/v1/chats/joined" "GET"

print_success "=========================================="
print_success "所有接口测试完成"
print_success "=========================================="
