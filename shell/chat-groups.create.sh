#!/bin/bash

# 创建聊天组的 curl 请求脚本
# URL: https://im-sq01.chunquqiulai.top/api/v1/chat-groups/create

# 主机名配置
HOST="https://im-sq01.chunquqiulai.top"

# POST 数据
POST_DATA='{"name":"afg","icon":"mxc://im-sq01.chunquqiulai.top/aoWDumjoUZmvyxYigckbnACa"}'

# 请求 URL
URL="${HOST}/api/v1/chat-groups/create"

curl -X POST "${URL}" \
  -H "authorization: Bearer syt_dGVzdDE4NjA_mMZQLePGwRCwKohUvDqV_2JiDWK" \
  -H "content-type: application/json" \
  -d "${POST_DATA}" \
  -v
