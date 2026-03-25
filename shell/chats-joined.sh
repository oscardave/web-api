#!/bin/bash

# 测试接口：我加入的聊天
# URL: https://im-sq01.chunquqiulai.top/api/v1/chats/joined

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# Token 文件路径
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TOKEN_FILE_SYNAPSE="${SCRIPT_DIR}/token-synapse.txt"
TOKEN_FILE="${SCRIPT_DIR}/token.txt"

# 读取 Token
if [ -f "$TOKEN_FILE_SYNAPSE" ]; then
    TOKEN=$(cat "$TOKEN_FILE_SYNAPSE" | tr -d '\n\r ')
elif [ -f "$TOKEN_FILE" ]; then
    TOKEN=$(cat "$TOKEN_FILE" | tr -d '\n\r ')
else
    echo "错误: 未找到 Token 文件！"
    echo "请先运行登录脚本获取 Token"
    exit 1
fi

# 参数
PAGE=${1:-1}
LIMIT=${2:-50}

# 请求 URL
URL="${HOST}/api/v1/chats/joined?page=${PAGE}&limit=${LIMIT}"

echo "测试接口: 我加入的聊天"
echo "请求URL: $URL"
echo ""

curl -X GET "${URL}" \
  -H "authorization: Bearer ${TOKEN}" \
  -H "content-type: application/json" \
  -s | python3 -m json.tool 2>/dev/null || curl -X GET "${URL}" \
  -H "authorization: Bearer ${TOKEN}" \
  -H "content-type: application/json" \
  -s

echo ""
