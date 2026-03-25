# 聊天列表 API 接口文档

## 概述

聊天列表 API 提供了获取用户相关聊天房间列表的功能，包括用户创建的聊天室、管理的聊天（群主/管理员）以及加入的聊天。每个接口都会返回房间的基本信息、最后一条消息、头像等信息。

**基础路径**: `/api/v1/chats`

**认证方式**: 需要在请求头中携带有效的 Token（通过 `UsersCache::getUserByRequest` 验证）

**请求头格式**:
```
Authorization: Bearer <token>
```

**说明**: 
- 请求头名称为 `Authorization`（不区分大小写）
- Token 可以直接放在 Authorization 头中，也可以使用 `Bearer ` 前缀
- Token 格式示例: `syt_xxx_xxx_xxx`（Synapse 格式的 access token）

---

## 通用响应格式

### 成功响应

```json
{
  "code": 0,
  "message": "",
  "data": {
    "list": [],
    "total": 0,
    "page": 1,
    "limit": 50
  }
}
```

### 错误响应

```json
{
  "code": 500,
  "message": "错误信息"
}
```

### Token 过期响应

```json
{
  "code": 501,
  "message": "无效请求"
}
```

---

## 接口列表

### 1. 获取我创建的聊天室

获取当前用户创建的所有聊天室列表，按最后消息时间倒序排列。

**请求方式**: `GET` 或 `POST`

**请求路径**: `/api/v1/chats/created`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，从1开始，默认1 |
| limit | integer | 否 | 每页数量，默认50，最大100 |

**请求示例**:

```bash
GET /api/v1/chats/created?page=1&limit=50
```

或

```json
POST /api/v1/chats/created
{
  "page": 1,
  "limit": 50
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "list": [
      {
        "room_id": "!abc123:example.com",
        "room_name": "我的聊天室",
        "avatar_url": "https://example.com/avatar.png",
        "creator": "@user1:example.com",
        "creator_user_id": "user1",
        "creator_nickname": "用户1",
        "is_public": true,
        "join_rules": "public",
        "topic": "这是一个测试聊天室",
        "joined_members": 10,
        "invited_members": 2,
        "created_ts": 1640995200000,
        "last_message": {
          "event_id": "$event123:example.com",
          "sender": "@user2:example.com",
          "sender_nickname": "用户2",
          "origin_server_ts": 1641081600000,
          "body": "这是一条测试消息",
          "msgtype": "m.text",
          "content": {
            "body": "这是一条测试消息",
            "msgtype": "m.text"
          }
        },
        "last_message_time": 1641081600000
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 50
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| list | array | 聊天室列表 |
| list[].room_id | string | 房间ID |
| list[].room_name | string | 房间名称 |
| list[].avatar_url | string | 房间头像URL（优先使用 ext_groups.group_avatar_url） |
| list[].creator | string | 创建者 Matrix 用户ID |
| list[].creator_user_id | string | 创建者用户ID |
| list[].creator_nickname | string | 创建者昵称 |
| list[].is_public | boolean | 是否公开 |
| list[].join_rules | string | 加入规则 |
| list[].topic | string | 房间主题 |
| list[].joined_members | integer | 已加入成员数 |
| list[].invited_members | integer | 已邀请成员数 |
| list[].created_ts | integer | 创建时间戳（毫秒） |
| list[].last_message | object\|null | 最后一条消息，如果没有消息则为 null |
| list[].last_message.event_id | string | 事件ID |
| list[].last_message.sender | string | 发送者 Matrix 用户ID |
| list[].last_message.sender_nickname | string | 发送者昵称 |
| list[].last_message.origin_server_ts | integer | 消息时间戳（毫秒） |
| list[].last_message.body | string | 消息内容 |
| list[].last_message.msgtype | string | 消息类型（如 m.text） |
| list[].last_message.content | object | 消息完整内容（JSON对象） |
| list[].last_message_time | integer | 最后消息时间戳（毫秒），如果没有消息则为 0 |
| total | integer | 总记录数 |
| page | integer | 当前页码 |
| limit | integer | 每页数量 |

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 用户不存在

**排序规则**: 按最后消息时间倒序排列（最新消息在前），如果没有消息则排在最后。

---

### 2. 获取我管理的聊天

获取当前用户作为群主或管理员的所有聊天列表，按最后消息时间倒序排列。

**请求方式**: `GET` 或 `POST`

**请求路径**: `/api/v1/chats/managed`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，从1开始，默认1 |
| limit | integer | 否 | 每页数量，默认50，最大100 |

**请求示例**:

```bash
GET /api/v1/chats/managed?page=1&limit=50
```

或

```json
POST /api/v1/chats/managed
{
  "page": 1,
  "limit": 50
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "list": [
      {
        "room_id": "!def456:example.com",
        "room_name": "管理群组",
        "avatar_url": "https://example.com/group-avatar.png",
        "role_type": "owner",
        "member_status": "active",
        "creator": "@user1:example.com",
        "creator_user_id": "user1",
        "creator_nickname": "用户1",
        "is_public": false,
        "join_rules": "invite",
        "topic": "这是一个管理群组",
        "joined_members": 25,
        "invited_members": 5,
        "created_ts": 1640995200000,
        "last_message": {
          "event_id": "$event456:example.com",
          "sender": "@user3:example.com",
          "sender_nickname": "用户3",
          "origin_server_ts": 1641081600000,
          "body": "管理员消息",
          "msgtype": "m.text",
          "content": {
            "body": "管理员消息",
            "msgtype": "m.text"
          }
        },
        "last_message_time": 1641081600000
      },
      {
        "room_id": "!ghi789:example.com",
        "room_name": "另一个管理群组",
        "avatar_url": "",
        "role_type": "admin",
        "member_status": "active",
        "creator": "@user4:example.com",
        "creator_user_id": "user4",
        "creator_nickname": "用户4",
        "is_public": true,
        "join_rules": "public",
        "topic": "",
        "joined_members": 50,
        "invited_members": 0,
        "created_ts": 1640995300000,
        "last_message": null,
        "last_message_time": 0
      }
    ],
    "total": 2,
    "page": 1,
    "limit": 50
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| list | array | 聊天列表 |
| list[].room_id | string | 房间ID |
| list[].room_name | string | 房间名称 |
| list[].avatar_url | string | 房间头像URL（优先使用 ext_groups.group_avatar_url） |
| list[].role_type | string | 用户角色类型：owner（群主）或 admin（管理员） |
| list[].member_status | string | 成员状态：active（正常） |
| list[].creator | string | 创建者 Matrix 用户ID |
| list[].creator_user_id | string | 创建者用户ID |
| list[].creator_nickname | string | 创建者昵称 |
| list[].is_public | boolean | 是否公开 |
| list[].join_rules | string | 加入规则 |
| list[].topic | string | 房间主题 |
| list[].joined_members | integer | 已加入成员数 |
| list[].invited_members | integer | 已邀请成员数 |
| list[].created_ts | integer | 创建时间戳（毫秒） |
| list[].last_message | object\|null | 最后一条消息，如果没有消息则为 null |
| list[].last_message.event_id | string | 事件ID |
| list[].last_message.sender | string | 发送者 Matrix 用户ID |
| list[].last_message.sender_nickname | string | 发送者昵称 |
| list[].last_message.origin_server_ts | integer | 消息时间戳（毫秒） |
| list[].last_message.body | string | 消息内容 |
| list[].last_message.msgtype | string | 消息类型（如 m.text） |
| list[].last_message.content | object | 消息完整内容（JSON对象） |
| list[].last_message_time | integer | 最后消息时间戳（毫秒），如果没有消息则为 0 |
| total | integer | 总记录数 |
| page | integer | 当前页码 |
| limit | integer | 每页数量 |

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 用户不存在

**说明**: 
- 此接口返回用户作为群主（owner）或管理员（admin）的所有聊天
- 只返回成员状态为 active（正常）的聊天
- 排序规则：按最后消息时间倒序排列（最新消息在前），如果没有消息则排在最后

---

### 3. 获取我加入的聊天

获取当前用户加入的所有聊天列表（包括创建的、管理的和普通成员），按最后消息时间倒序排列。

**请求方式**: `GET` 或 `POST`

**请求路径**: `/api/v1/chats/joined`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，从1开始，默认1 |
| limit | integer | 否 | 每页数量，默认50，最大100 |

**请求示例**:

```bash
GET /api/v1/chats/joined?page=1&limit=50
```

或

```json
POST /api/v1/chats/joined
{
  "page": 1,
  "limit": 50
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "list": [
      {
        "room_id": "!jkl012:example.com",
        "room_name": "加入的群组",
        "avatar_url": "https://example.com/avatar2.png",
        "creator": "@user5:example.com",
        "creator_user_id": "user5",
        "creator_nickname": "用户5",
        "is_public": true,
        "join_rules": "public",
        "topic": "欢迎加入",
        "joined_members": 100,
        "invited_members": 0,
        "created_ts": 1640995400000,
        "last_message": {
          "event_id": "$event789:example.com",
          "sender": "@user6:example.com",
          "sender_nickname": "用户6",
          "origin_server_ts": 1641081700000,
          "body": "大家好！",
          "msgtype": "m.text",
          "content": {
            "body": "大家好！",
            "msgtype": "m.text"
          }
        },
        "last_message_time": 1641081700000
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 50
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| list | array | 聊天列表 |
| list[].room_id | string | 房间ID |
| list[].room_name | string | 房间名称 |
| list[].avatar_url | string | 房间头像URL（优先使用 ext_groups.group_avatar_url） |
| list[].creator | string | 创建者 Matrix 用户ID |
| list[].creator_user_id | string | 创建者用户ID |
| list[].creator_nickname | string | 创建者昵称 |
| list[].is_public | boolean | 是否公开 |
| list[].join_rules | string | 加入规则 |
| list[].topic | string | 房间主题 |
| list[].joined_members | integer | 已加入成员数 |
| list[].invited_members | integer | 已邀请成员数 |
| list[].created_ts | integer | 创建时间戳（毫秒） |
| list[].last_message | object\|null | 最后一条消息，如果没有消息则为 null |
| list[].last_message.event_id | string | 事件ID |
| list[].last_message.sender | string | 发送者 Matrix 用户ID |
| list[].last_message.sender_nickname | string | 发送者昵称 |
| list[].last_message.origin_server_ts | integer | 消息时间戳（毫秒） |
| list[].last_message.body | string | 消息内容 |
| list[].last_message.msgtype | string | 消息类型（如 m.text） |
| list[].last_message.content | object | 消息完整内容（JSON对象） |
| list[].last_message_time | integer | 最后消息时间戳（毫秒），如果没有消息则为 0 |
| total | integer | 总记录数 |
| page | integer | 当前页码 |
| limit | integer | 每页数量 |

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 用户不存在

**说明**: 
- 此接口返回用户加入的所有聊天（包括作为创建者、管理员或普通成员）
- 只返回成员状态为 join 的聊天
- 排序规则：按最后消息时间倒序排列（最新消息在前），如果没有消息则排在最后

---

## 通用说明

### 分页说明

- 所有接口都支持分页参数 `page` 和 `limit`
- `page` 从 1 开始计数
- `limit` 默认值为 50，最大值为 100
- 如果 `limit` 超过 100，系统会自动限制为 100

### 排序说明

- 所有接口都按最后消息时间倒序排列（最新消息在前）
- 如果没有最后消息，则 `last_message` 为 `null`，`last_message_time` 为 `0`
- 没有消息的聊天会排在列表最后

### 头像获取规则

- 优先使用 `ext_groups.group_avatar_url` 字段
- 如果 `ext_groups` 表中没有对应记录或 `group_avatar_url` 为空，则返回空字符串
- 前端可以根据需要设置默认头像

### 消息类型说明

`last_message.msgtype` 常见值：
- `m.text` - 文本消息
- `m.image` - 图片消息
- `m.video` - 视频消息
- `m.audio` - 音频消息
- `m.file` - 文件消息
- `m.location` - 位置消息
- `m.emote` - 表情消息
- 其他 Matrix 协议支持的消息类型

### 时间戳说明

- 所有时间戳均为毫秒级 Unix 时间戳
- `created_ts` - 房间创建时间
- `origin_server_ts` - 消息发送时间
- `last_message_time` - 最后消息时间（与 `last_message.origin_server_ts` 相同）

---

## 使用示例

### cURL 示例

```bash
# 获取我创建的聊天室
curl -X GET "https://api.example.com/api/v1/chats/created?page=1&limit=50" \
  -H "Authorization: Bearer syt_xxx_xxx_xxx"

# 获取我管理的聊天
curl -X POST "https://api.example.com/api/v1/chats/managed" \
  -H "Authorization: Bearer syt_xxx_xxx_xxx" \
  -H "Content-Type: application/json" \
  -d '{"page": 1, "limit": 50}'

# 获取我加入的聊天
curl -X GET "https://api.example.com/api/v1/chats/joined?page=1&limit=50" \
  -H "Authorization: Bearer syt_xxx_xxx_xxx"
```

### JavaScript 示例

```javascript
// 获取我创建的聊天室
async function getCreatedRooms(page = 1, limit = 50) {
  const response = await fetch(`/api/v1/chats/created?page=${page}&limit=${limit}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  const data = await response.json();
  return data;
}

// 获取我管理的聊天
async function getManagedRooms(page = 1, limit = 50) {
  const response = await fetch('/api/v1/chats/managed', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ page, limit })
  });
  const data = await response.json();
  return data;
}

// 获取我加入的聊天
async function getJoinedRooms(page = 1, limit = 50) {
  const response = await fetch(`/api/v1/chats/joined?page=${page}&limit=${limit}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  const data = await response.json();
  return data;
}
```

---

## 注意事项

1. **性能考虑**: 由于需要查询最后一条消息，当房间数量较多时，查询可能会较慢。建议合理使用分页。

2. **数据一致性**: 最后消息数据来自 `chats_v` 视图，如果消息数据有延迟，可能会影响排序结果。

3. **空数据处理**: 如果房间没有消息，`last_message` 字段为 `null`，`last_message_time` 为 `0`。

4. **权限说明**: 
   - "我创建的聊天室" 只返回用户作为创建者的房间
   - "我管理的聊天" 返回用户作为群主或管理员的房间（不包括仅作为创建者但不是群主/管理员的房间）
   - "我加入的聊天" 返回用户加入的所有房间（包括创建的、管理的和普通成员）

5. **头像字段**: `avatar_url` 字段优先从 `ext_groups` 表获取，如果不存在则返回空字符串。前端需要处理空头像的情况。

---

## 更新日志

- 2026-01-25: 初始版本，添加三个聊天列表接口
