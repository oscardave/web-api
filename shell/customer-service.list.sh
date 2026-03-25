#!/bin/bash

# 获取客服用户列表的 curl 请求脚本
# URL: https://im-sq01.chunquqiulai.top/api/v1/customer-service/list

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# 请求 URL
URL="${HOST}/api/v1/customer-service/list"

curl -X GET "${URL}" \
  -H "authorization: Bearer syt_dGVzdDE4NjA_mMZQLePGwRCwKohUvDqV_2JiDWK" \
  -H "content-type: application/json" \
  -v
