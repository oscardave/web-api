#!/bin/bash

# 聊天分组 API 测试脚本
# 用于测试 /api/v1/chat-groups/rooms 接口

# 配置
BASE_URL="https://im-sq01.chunquqiulai.top"
TOKEN="syt_dGVzdDE4NjA_jSXEZUXIXcOnHdQFrVDy_1C7sFG"
GROUP_ID=10006

echo "=========================================="
echo "聊天分组 API 测试脚本"
echo "=========================================="
echo ""

# 测试1: GET 请求 - 使用查询参数
echo "【测试1】GET 请求 - 使用查询参数"
echo "URL: ${BASE_URL}/api/v1/chat-groups/rooms?id=${GROUP_ID}"
echo "Method: GET"
echo "Headers: Authorization: Bearer ${TOKEN}"
echo ""

RESPONSE1=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -X GET \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  "${BASE_URL}/api/v1/chat-groups/rooms?id=${GROUP_ID}")

HTTP_CODE1=$(echo "$RESPONSE1" | grep "HTTP_CODE" | cut -d: -f2)
BODY1=$(echo "$RESPONSE1" | sed '/HTTP_CODE/d')

echo "响应状态码: ${HTTP_CODE1}"
echo "响应内容:"
echo "$BODY1" | jq . 2>/dev/null || echo "$BODY1"
echo ""
echo "----------------------------------------"
echo ""

# 测试2: POST 请求 - 使用请求体
echo "【测试2】POST 请求 - 使用请求体"
echo "URL: ${BASE_URL}/api/v1/chat-groups/rooms"
echo "Method: POST"
echo "Headers: Authorization: Bearer ${TOKEN}"
echo "Body: {\"id\": ${GROUP_ID}}"
echo ""

RESPONSE2=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -X POST \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"id\": ${GROUP_ID}}" \
  "${BASE_URL}/api/v1/chat-groups/rooms")

HTTP_CODE2=$(echo "$RESPONSE2" | grep "HTTP_CODE" | cut -d: -f2)
BODY2=$(echo "$RESPONSE2" | sed '/HTTP_CODE/d')

echo "响应状态码: ${HTTP_CODE2}"
echo "响应内容:"
echo "$BODY2" | jq . 2>/dev/null || echo "$BODY2"
echo ""
echo "----------------------------------------"
echo ""

# 测试3: 测试其他接口（list）确认路由是否正常
echo "【测试3】测试分组列表接口（确认路由是否正常）"
echo "URL: ${BASE_URL}/api/v1/chat-groups/list"
echo "Method: GET"
echo ""

RESPONSE3=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -X GET \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  "${BASE_URL}/api/v1/chat-groups/list")

HTTP_CODE3=$(echo "$RESPONSE3" | grep "HTTP_CODE" | cut -d: -f2)
BODY3=$(echo "$RESPONSE3" | sed '/HTTP_CODE/d')

echo "响应状态码: ${HTTP_CODE3}"
echo "响应内容:"
echo "$BODY3" | jq . 2>/dev/null || echo "$BODY3"
echo ""
echo "----------------------------------------"
echo ""

# 测试4: 测试基础路径是否存在
echo "【测试4】测试基础路径（检查路由前缀）"
echo "URL: ${BASE_URL}/api/v1/chat-groups"
echo "Method: GET"
echo ""

RESPONSE4=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -X GET \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  "${BASE_URL}/api/v1/chat-groups")

HTTP_CODE4=$(echo "$RESPONSE4" | grep "HTTP_CODE" | cut -d: -f2)
BODY4=$(echo "$RESPONSE4" | sed '/HTTP_CODE/d')

echo "响应状态码: ${HTTP_CODE4}"
echo "响应内容:"
echo "$BODY4" | jq . 2>/dev/null || echo "$BODY4"
echo ""
echo "----------------------------------------"
echo ""

# 测试5: 详细调试信息
echo "【测试5】详细调试信息（-v 模式）"
echo ""

curl -v \
  -X GET \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  "${BASE_URL}/api/v1/chat-groups/rooms?id=${GROUP_ID}" \
  2>&1 | grep -E "(< HTTP|< |> GET|> Authorization)"

echo ""
echo "=========================================="
echo "测试完成"
echo "=========================================="

# 分析结果
echo ""
echo "【结果分析】"
if [ "$HTTP_CODE1" = "404" ] && [ "$HTTP_CODE2" = "404" ]; then
  echo "❌ rooms 接口返回 404，可能的原因："
  echo "   1. 路由未正确注册（检查 Hyperf 路由扫描）"
  echo "   2. 服务器配置问题（nginx/反向代理配置）"
  echo "   3. 路径大小写问题（尝试 /api/v1/chat-groups/Rooms）"
  echo "   4. 控制器注解未生效"
elif [ "$HTTP_CODE3" = "200" ]; then
  echo "✅ list 接口正常，说明路由前缀正确"
  echo "❌ rooms 接口可能存在问题"
else
  echo "⚠️  所有接口都返回错误，可能是认证或服务器配置问题"
fi
