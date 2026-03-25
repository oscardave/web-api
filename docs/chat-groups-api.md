# 聊天分组 API 接口文档

## 概述

聊天分组 API 提供了对用户聊天分组的完整管理功能，包括分组的创建、修改、删除，以及将聊天房间添加到分组或从分组中移除等操作。

**基础路径**: `/api/v1/chat-groups`

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
  "data": {}
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

### 1. 获取分组列表

获取当前用户的所有聊天分组列表。

**请求方式**: `GET` 或 `POST`

**请求路径**: `/api/v1/chat-groups/list`

**请求参数**: 无

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": [
    {
      "id": 10001,
      "user_id": 123,
      "icon": "https://example.com/icon.png",
      "name": "工作群组",
      "chat_count": 5,
      "participant_count": 25,
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-15 15:30:00"
    },
    {
      "id": 10002,
      "user_id": 123,
      "icon": "",
      "name": "朋友",
      "chat_count": 3,
      "participant_count": 12,
      "created_at": "2024-01-02 11:00:00",
      "updated_at": "2024-01-10 14:20:00"
    }
  ]
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| id | integer | 分组ID |
| user_id | integer | 用户ID |
| icon | string | 分组图标URL |
| name | string | 分组名称 |
| chat_count | integer | 分组内聊天数量 |
| participant_count | integer | 分组内参与者总数 |
| created_at | string | 创建时间 |
| updated_at | string | 更新时间 |

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 用户不存在

---

### 2. 创建分组

创建一个新的聊天分组。

**请求方式**: `POST`

**请求路径**: `/api/v1/chat-groups/create`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| name | string | 是 | 分组名称（最大200字符） |
| icon | string | 否 | 分组图标URL |

**请求示例**:

```json
{
  "name": "工作群组",
  "icon": "https://example.com/icon.png"
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "id": 10001,
    "user_id": 123,
    "icon": "https://example.com/icon.png",
    "name": "工作群组",
    "chat_count": 0,
    "participant_count": 0,
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-01 10:00:00"
  }
}
```

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 分组名称不能为空
- `code: 500` - 分组名称过长（最大200字符）
- `code: 500` - 创建分组失败

---

### 3. 修改分组

修改已存在的聊天分组信息。

**请求方式**: `POST`

**请求路径**: `/api/v1/chat-groups/update`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 分组ID |
| name | string | 否 | 分组名称（最大200字符） |
| icon | string | 否 | 分组图标URL |

**注意**: `name` 和 `icon` 至少需要提供一个。

**请求示例**:

```json
{
  "id": 10001,
  "name": "新的分组名称",
  "icon": "https://example.com/new-icon.png"
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "id": 10001,
    "user_id": 123,
    "icon": "https://example.com/new-icon.png",
    "name": "新的分组名称",
    "chat_count": 5,
    "participant_count": 25,
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-15 16:00:00"
  }
}
```

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 分组ID不能为空
- `code: 500` - 至少需要提供一个要修改的字段
- `code: 500` - 分组不存在或无权限
- `code: 500` - 分组名称不能为空
- `code: 500` - 分组名称过长（最大200字符）
- `code: 500` - 更新分组失败

---

### 4. 删除分组

删除指定的聊天分组（软删除）。

**请求方式**: `POST`

**请求路径**: `/api/v1/chat-groups/delete`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 分组ID |

**请求示例**:

```json
{
  "id": 10001
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "删除成功"
}
```

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 分组ID不能为空
- `code: 500` - 分组不存在或无权限
- `code: 500` - 删除分组失败

---

### 5. 添加聊天到分组

将指定的聊天房间添加到分组中。

**请求方式**: `POST`

**请求路径**: `/api/v1/chat-groups/add-room`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_id | integer | 是 | 分组ID |
| room_id | string | 是 | 房间ID |

**请求示例**:

```json
{
  "group_id": 10001,
  "room_id": "!abc123:example.com"
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "添加成功"
}
```

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 分组ID不能为空
- `code: 500` - 房间ID不能为空
- `code: 500` - 分组不存在或无权限
- `code: 500` - 房间已在该分组中
- `code: 500` - 添加房间失败

---

### 6. 从分组移除聊天

将指定的聊天房间从分组中移除。

**请求方式**: `POST`

**请求路径**: `/api/v1/chat-groups/remove-room`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_id | integer | 是 | 分组ID |
| room_id | string | 是 | 房间ID |

**请求示例**:

```json
{
  "group_id": 10001,
  "room_id": "!abc123:example.com"
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "移除成功"
}
```

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 分组ID不能为空
- `code: 500` - 房间ID不能为空
- `code: 500` - 分组不存在或无权限
- `code: 500` - 房间不在该分组中
- `code: 500` - 移除房间失败

---

### 7. 获取分组中的聊天列表

获取指定分组中的所有聊天房间列表，包含每个房间的最后一条消息信息。

**请求方式**: `GET` 或 `POST`

**请求路径**: `/api/v1/chat-groups/rooms`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 分组ID |

**请求示例**:

```
GET /api/v1/chat-groups/rooms?id=10001
```

或

```json
{
  "id": 10001
}
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": [
    {
      "room_id": "!abc123:example.com",
      "added_at": "2024-01-10 10:00:00",
      "is_public": true,
      "creator": "@user1:example.com",
      "creator_user_id": "user1",
      "creator_nickname": "用户1",
      "room_name": "工作讨论组",
      "join_rules": "invite",
      "topic": "工作相关讨论",
      "joined_members": 15,
      "invited_members": 2,
      "left_members": 1,
      "banned_members": 0,
      "knocked_members": 0,
      "created_ts": 1704067200000,
      "last_message": {
        "event_id": "$event123:example.com",
        "sender": "@user2:example.com",
        "sender_nickname": "用户2",
        "origin_server_ts": 1704153600000,
        "body": "这是最后一条消息内容",
        "msgtype": "m.text",
        "content": {
          "body": "这是最后一条消息内容",
          "msgtype": "m.text",
          "format": "org.matrix.custom.html",
          "formatted_body": "<p>这是最后一条消息内容</p>"
        }
      }
    },
    {
      "room_id": "!def456:example.com",
      "added_at": "2024-01-08 14:30:00",
      "is_public": false,
      "creator": "@user3:example.com",
      "creator_user_id": "user3",
      "creator_nickname": "用户3",
      "room_name": "私人聊天",
      "join_rules": "invite",
      "topic": "",
      "joined_members": 2,
      "invited_members": 0,
      "left_members": 0,
      "banned_members": 0,
      "knocked_members": 0,
      "created_ts": 1704067200000,
      "last_message": null
    }
  ]
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| room_id | string | 房间ID |
| added_at | string | 添加到分组的时间 |
| is_public | boolean | 是否公开 |
| creator | string | 创建者用户ID |
| creator_user_id | string | 创建者用户名 |
| creator_nickname | string | 创建者昵称 |
| room_name | string | 房间名称 |
| join_rules | string | 加入规则 |
| topic | string | 房间主题 |
| joined_members | integer | 已加入成员数 |
| invited_members | integer | 已邀请成员数 |
| left_members | integer | 已离开成员数 |
| banned_members | integer | 已封禁成员数 |
| knocked_members | integer | 已敲门成员数 |
| created_ts | integer | 房间创建时间戳（毫秒） |
| last_message | object\|null | 最后一条消息信息 |

**last_message 字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| event_id | string | 消息事件ID |
| sender | string | 发送者用户ID |
| sender_nickname | string | 发送者昵称 |
| origin_server_ts | integer | 消息时间戳（毫秒） |
| body | string | 消息文本内容 |
| msgtype | string | 消息类型（如 m.text, m.image 等） |
| content | object | 完整的消息内容 JSON |

**错误响应**:

- `code: 501` - Token 无效或过期
- `code: 500` - 分组ID不能为空
- `code: 500` - 分组不存在或无权限

---

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 500 | 业务错误（参数错误、业务逻辑错误等） |
| 501 | Token 无效或过期 |

---

## 注意事项

1. **认证**: 所有接口都需要在请求头中携带有效的 Token
2. **权限**: 用户只能操作自己创建的分组
3. **软删除**: 删除分组和移除房间都是软删除操作，数据不会真正删除
4. **缓存**: 分组列表和统计信息有缓存机制，缓存时间为1小时
5. **统计字段**: `chat_count` 和 `participant_count` 是实时计算的统计字段，不能直接修改
6. **消息类型**: `last_message.msgtype` 可能的值包括：
   - `m.text` - 文本消息
   - `m.image` - 图片消息
   - `m.video` - 视频消息
   - `m.file` - 文件消息
   - `m.emote` - 表情消息
   - `m.notice` - 通知消息
   - 其他 Matrix 消息类型

---

## 更新日志

- 2024-01-15: 初始版本，包含分组的基本 CRUD 操作和房间管理功能
- 2024-01-15: 添加获取分组中聊天列表接口，包含最后一条消息信息
