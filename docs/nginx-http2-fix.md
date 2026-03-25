# 修复 ERR_HTTP2_PROTOCOL_ERROR 200 (OK)

## 现象

- 前端请求 `POST /api/circles/list` 返回 **200 OK**，但浏览器报 **net::ERR_HTTP2_PROTOCOL_ERROR**，Axios 提示 Network Error。
- 后端日志显示请求已处理成功、有数据返回。
- 常见于**响应体较大**的接口（如圈子列表 20 条 × 多字段）。

## 原因

在 HTTP/2 下，当代理（如 Nginx）把后端（如 Hyperf）的响应转发给客户端时，若缓冲/分块配置不当，或与后端通信方式不匹配，会在传输响应体过程中断开流，导致协议错误。客户端收到 200 头但拿不到完整 body。

## 解决思路

### 1. 推荐：对该 API 上游使用 HTTP/1.1

在 Nginx 里对转发到 web-api 的 `upstream` 使用 **HTTP/1.1**，避免 HTTP/2 带来的问题。

```nginx
upstream web_api {
    server 127.0.0.1:9508;  # 或你的 Hyperf 服务地址
    keepalive 32;
}

server {
    listen 443 ssl http2;
    server_name ht-sq01-xxx.chunquqiulai.top;

    location /api/ {
        proxy_pass http://web_api/ht/v1/;   # 根据实际重写规则调整
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # 适当加大缓冲，避免大响应被截断
        proxy_buffer_size 128k;
        proxy_buffers 4 256k;
        proxy_busy_buffers_size 256k;
        proxy_temp_file_write_size 256k;
    }
}
```

要点：

- 对**客户端**仍可用 `listen 443 ssl http2`（浏览器到 Nginx 仍是 HTTP/2）。
- Nginx 到后端用 `proxy_http_version 1.1`，即**上游使用 HTTP/1.1**，避免与后端/Swoole 在 HTTP/2 上的兼容问题。
- `proxy_buffer_size` / `proxy_buffers` 适当调大，保证大响应能完整缓冲再发给客户端。

### 2. 若必须全程 HTTP/2：加大代理缓冲

若不能改上游为 HTTP/1.1，可先尝试只加大缓冲：

```nginx
location /api/ {
    proxy_pass http://web_api/ht/v1/;
    proxy_http_version 1.1;
    proxy_set_header Connection "";
    # 大响应需要更大缓冲
    proxy_buffer_size 128k;
    proxy_buffers 8 256k;
    proxy_busy_buffers_size 256k;
    proxy_temp_file_write_size 256k;
    proxy_read_timeout 60s;
    proxy_send_timeout 60s;
}
```

### 3. 后端可选：减小 list 响应体（缓解用）

若暂时无法改 Nginx，可先减小 `circles/list` 的响应体积，看是否还报错（例如只返回列表展示必需字段、或先减小 pageSize）。  
这只能缓解，根本解决仍需按上面 1 或 2 调整代理。

## 如何确认

- 调整后再次打开「全部圈子」列表，不应再出现 `ERR_HTTP2_PROTOCOL_ERROR`，且能正常看到 `{ success, message, data }` 和列表数据。
- 在浏览器 Network 里看该请求：Status 200，Response 里为完整 JSON。
