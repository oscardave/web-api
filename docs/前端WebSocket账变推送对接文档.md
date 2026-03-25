# 前端 WebSocket 账变推送对接文档

## 一、概述

当用户在**后台**发生积分或诚信保余额变动时（如：后台上分/下分、兑换、诚信保申请审核/执行等），或用户在**前台**完成会员等级兑换后，服务端会通过 WebSocket 向该用户推送一条「账变」消息。前端连接本 WebSocket 并监听该消息，可实时刷新积分、诚信保等账户信息，无需轮询。

**推送触发场景**（任一会导致积分/诚信保变动的操作成功后都会推送）：

- 后台：账户管理 → 上分 / 下分（积分或诚信保）
- 后台：兑换管理 → 新增兑换（会员等级兑换、诚信保兑换）
- 后台：诚信保申请 → 用户申请取回、审核拒绝、执行解冻转积分
- 前台：用户端会员等级兑换接口调用成功

---

## 二、连接信息

| 项目 | 说明 |
|------|------|
| **路径** | `/ws`（与现有 API 同域，见下方示例） |
| **协议** | 生产环境建议 `wss://`，开发可用 `ws://` |
| **鉴权** | 必须携带当前用户的 access_token，否则握手返回 401 |

### 连接地址示例

- 开发：`ws://your-api-host/ws`
- 生产：`wss://your-api-host/ws`

与现有 HTTP API 使用同一域名与端口，仅 path 为 `/ws`，由 Nginx 转发到 WebSocket 服务端口。

---

## 三、鉴权方式

握手阶段必须通过 **请求头** 传递 Token。**不再接受** URL query `?token=`（避免 token 泄露到服务器访问日志及 Referer）。

### Authorization 请求头（唯一方式）

在 WebSocket 握手时设置 Header：

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

即登录后拿到的 `access_token`（与请求 `/api/v1/...` 时使用的 Bearer Token 一致）。

> **注意**：若仍使用 `?token=` 参数连接，服务端将返回 HTTP 400 并拒绝握手。

**鉴权失败**：服务端返回 HTTP 401，Body 为 `token required via Authorization header` 或 `invalid token`，连接不会升级为 WebSocket。

---

## 四、服务端下发的消息格式

服务端**仅会主动下发**账变通知，不回复客户端发来的业务报文（可忽略客户端发送的 message 或仅做心跳）。

### 账变推送消息（type = account_change）

| 字段 | 类型 | 说明 |
|------|------|------|
| type | string | 固定为 `"account_change"` |
| user_id | number | 发生账变的用户 ID（ext_users.id，即业务侧用户主键） |
| ts | number | 服务端时间戳（秒） |

**示例：**

```json
{
  "type": "account_change",
  "user_id": 12345,
  "ts": 1710123456
}
```

前端应判断：若当前登录用户对应的 `ext_user_id`（或接口返回的 user_id）与消息中的 `user_id` 一致，则视为「当前用户的账变」，再拉取最新账户数据并更新 UI。

---

## 五、前端处理流程建议

1. **建立连接**  
   使用当前用户的 `access_token`，通过 `Authorization: Bearer` Header 连接 `/ws`。

2. **收到 `account_change`**  
   - 解析 JSON，检查 `type === "account_change"`。  
   - 比较 `user_id` 与当前用户的业务用户 ID（ext_user_id）；若一致，则执行步骤 3。

3. **刷新账户数据**  
   调用现有 HTTP 接口获取最新余额并更新界面，例如：  
   `GET /api/v1/users/account`（需带 Bearer Token）。  
   该接口返回的 `point_amount`、`credit_amount`、`credit_frozen_amount` 等即为最新值。

4. **断线重连**  
   连接断开后（如网络波动、长时间无操作被 Nginx 超时等），建议按策略重连（如指数退避），并重新带 token 握手。

5. **登出**  
   用户登出时关闭 WebSocket 连接，并停止重连。

---

## 六、调用示例

### JavaScript / Web

```javascript
const accessToken = 'YOUR_ACCESS_TOKEN'; // 登录后保存的 token
const apiHost = 'https://your-domain.com';
const wsUrl = `${apiHost.replace(/^https?:/, 'wss:').replace(/^http/, 'ws')}/ws`;

// 原生 WebSocket 不支持自定义 Header，需使用 Sec-WebSocket-Protocol 传递 Token
// 或改用支持自定义 Header 的库（如 socket.io-client、reconnecting-websocket + fetch 握手等）
const ws = new WebSocket(wsUrl, [`Bearer.${accessToken}`]);

ws.onopen = () => {
  console.log('WebSocket 已连接');
};

ws.onmessage = (event) => {
  try {
    const data = JSON.parse(event.data);
    if (data.type === 'account_change') {
      console.log('账变通知', data.user_id, data.ts);
      fetch(`${apiHost}/api/v1/users/account`, {
        headers: { Authorization: `Bearer ${accessToken}` },
      })
        .then((res) => res.json())
        .then((res) => {
          if (res.point_amount !== undefined) {
            // 更新界面：积分、诚信保等
          }
        });
    }
  } catch (e) {
    console.warn('解析 WS 消息失败', e);
  }
};

ws.onclose = (event) => {
  console.log('WebSocket 关闭', event.code, event.reason);
  // 可在此做重连
};

ws.onerror = (err) => {
  console.error('WebSocket 错误', err);
};
```

### Flutter / Dart（示意）

```dart
import 'package:web_socket_channel/web_socket_channel.dart';

final token = 'YOUR_ACCESS_TOKEN';
final wsUrl = Uri.parse('wss://your-domain.com/ws');

// web_socket_channel 支持自定义 Header
final channel = IOWebSocketChannel.connect(
  wsUrl,
  headers: {'Authorization': 'Bearer $token'},
);

channel.stream.listen(
  (data) {
    final map = jsonDecode(data) as Map<String, dynamic>?;
    if (map?['type'] == 'account_change') {
      final userId = map!['user_id'] as int?;
      // 若为当前用户，则请求 GET /api/v1/users/account 更新积分/诚信保
    }
  },
  onError: (err) => print('WS error: $err'),
  onDone: () => print('WS closed'),
  cancelOnError: false,
);

// 登出或页面销毁时
// channel.sink.close();
```

---

## 七、注意事项

1. **Token 安全**  
   Token 仅通过 `Authorization` Header 传递，不再经 URL query 暴露。生产环境建议使用 `wss://`。

2. **连接时机**  
   建议在用户登录成功后再建立 WebSocket；登出或 token 失效时关闭连接并停止重连。

3. **多端/多连接**  
   同一用户多设备或多标签页可各自维持一条连接，每条连接都会收到该用户的 `account_change` 推送。

4. **仅账变推送**  
   当前服务端仅发送 `type: "account_change"` 一种业务消息；其他 type 可忽略或预留扩展。

5. **超时与重连**  
   Nginx 侧已配置较长 `proxy_read_timeout` / `proxy_send_timeout`；若仍断线，建议前端实现重连与退避策略。

---

## 八、版本记录

| 日期 | 说明 |
|------|------|
| 2025-03-11 | 初始版本，账变 WebSocket 推送对接说明 |
