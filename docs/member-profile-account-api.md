# 会员等级、用户资料与账户余额 API 接口文档

## 概述

本文档涵盖 C 端前台接口中的以下三类接口：

1. **会员等级**：获取等级列表、兑换会员等级
2. **用户资料（Profile）**：获取用户资料（含头像、积分余额、会员信息等）
3. **刷新账户余额**：从 `ext_user_accounts` 获取用户最新积分/诚信保余额

**基础路径**: `/api/v1`

**认证方式**: 所有接口均需在请求头中携带有效的 Token

**请求头格式**:
```
Authorization: Bearer <token>
```

**说明**:
- 请求头名称为 `Authorization`（不区分大小写）
- Token 可直接放在 Authorization 头中，也可使用 `Bearer ` 前缀
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

### Token 过期/无效响应

```json
{
  "code": 501,
  "message": "无效请求"
}
```

---

## 一、会员等级接口

### 1.1 获取用户等级列表

获取所有启用的用户等级列表（`ext_user_levels` 表 `status=1`），用于 C 端下拉选择、展示等。

**请求方式**: `GET`

**请求路径**: `/api/v1/configs/levels`

**请求参数**: 无

**请求示例**:

```bash
GET /api/v1/configs/levels
Authorization: Bearer <token>
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "levels": [
      {
        "id": 10001,
        "name": "初级会员",
        "avatar_url": "https://example.com/level1.png"
      },
      {
        "id": 10002,
        "name": "高级会员",
        "avatar_url": "https://example.com/level2.png"
      },
      {
        "id": 10003,
        "name": "超级会员",
        "avatar_url": "https://example.com/level3.png"
      }
    ]
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| data.levels | array | 等级列表 |
| data.levels[].id | integer | 等级 ID |
| data.levels[].name | string | 等级名称 |
| data.levels[].avatar_url | string | 等级头像 URL |

---

### 1.2 兑换会员等级

使用积分兑换会员等级。仅支持 10001（初级）、10002（高级）、10003（超级），且目标等级 `is_exchangeable=true`。与当前等级相同时直接返回成功不扣费。

**请求方式**: `POST`

**请求路径**: `/api/v1/vip/exchangeMemberLevel`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| level_id | integer | 是 | 目标等级 ID（10001/10002/10003） |

**请求示例**:

```bash
POST /api/v1/vip/exchangeMemberLevel
Authorization: Bearer <token>
Content-Type: application/json

{
  "level_id": 10002
}
```

**响应示例（成功）**:

```json
{
  "code": 0,
  "message": "兑换成功"
}
```

**响应示例（失败）**:

```json
{
  "code": 500,
  "message": "积分余额不足"
}
```

**常见错误**:
- `请选择要兑换的会员等级`：未传或 level_id 无效
- `等级ID无效，仅支持初级(10001)、高级(10002)、超级(10003)`：等级不在可兑换范围
- `该等级不可兑换`：目标等级 is_exchangeable=false
- `积分余额不足`：积分不足以支付兑换价格
- `只能兑换更高等级`：目标等级低于或等于当前等级

---

## 二、用户资料（Profile）接口

### 2.1 获取用户资料

获取用户完整资料，支持获取当前登录用户或指定用户的资料。查看自己资料时返回积分余额、诚信保余额等账户信息。

**请求方式**: `GET`

**请求路径**: `/api/v1/users/profile`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer/string | 否 | ext_users.id（数字）或 user_id（Matrix ID），不传则获取当前用户 |
| user_id | string | 否 | Matrix 用户 ID，如 @u10010:im.example.com |

**请求示例**:

```bash
# 获取当前登录用户资料
GET /api/v1/users/profile
Authorization: Bearer <token>

# 获取指定用户资料（通过 id）
GET /api/v1/users/profile?id=12345
Authorization: Bearer <token>

# 获取指定用户资料（通过 user_id）
GET /api/v1/users/profile?user_id=@u10010:im.example.com
Authorization: Bearer <token>
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "id": 12345,
    "user_id": "@u10010:im.example.com",
    "displayname": "张三",
    "avatar_url": "https://example.com/avatars/user123.png",
    "nickname": "张三",
    "personal_description": "个性签名",
    "remark": "",
    "remark_name": "",
    "chat_prohibited": false,
    "note_creation_prohibited": false,
    "note_access": true,
    "bound_phone": "",
    "bound_email": "u10010@im.example.com",
    "gender": "unknown",
    "is_vanity": false,
    "vanity_number": "0",
    "reputation_value": 0,
    "has_pending_ensure_application": false,
    "member_level_id": 10001,
    "member_level_type": "初级会员",
    "member_level_name": "初级会员",
    "member_expiration_time": "2025-12-31 23:59:59",
    "identity_badge_id": 0,
    "identity_badge_name": "",
    "credit_badge_id": 0,
    "credit_badge_name": "",
    "integrity_score_required": 0,
    "point_amount": 1000,
    "point_frozen_amount": 0,
    "credit_amount": 500,
    "credit_frozen_amount": 0,
    "is_blocked": false
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| id | integer | ext_users 数字 ID |
| user_id | string | Matrix 用户 ID |
| displayname | string | 显示名 |
| avatar_url | string | 头像 URL（HTTP 路径，供 C 端拼接展示） |
| nickname | string | 昵称 |
| personal_description | string | 个性签名 |
| remark | string | 自己的备注 |
| remark_name | string | 当前用户对目标用户的备注名（查看他人时） |
| chat_prohibited | boolean | 是否禁止聊天 |
| note_creation_prohibited | boolean | 是否禁止创建笔记 |
| note_access | boolean | 当前用户对目标用户的笔记访问权限 |
| bound_phone | string | 绑定手机 |
| bound_email | string | 绑定邮箱 |
| gender | string | 性别（unknown/male/female） |
| is_vanity | boolean | 是否靓号 |
| vanity_number | string | 靓号数值（如 "10086"、"888888"，无靓号时为 "0"） |
| reputation_value | integer | 信誉值 |
| has_pending_ensure_application | boolean | 是否有待审核的诚信保申请 |
| member_level_id | integer | 会员等级 ID |
| member_level_type | string | 会员等级名称 |
| member_level_name | string | 会员等级名称（同 member_level_type） |
| member_expiration_time | string/null | 会员到期时间 |
| identity_badge_id | integer | 身份徽章 ID |
| identity_badge_name | string | 身份徽章名称 |
| credit_badge_id | integer | 诚信徽章 ID |
| credit_badge_name | string | 诚信徽章名称 |
| integrity_score_required | integer | 当前佩戴的诚信徽章所需诚信保分值（来自 ext_user_badges，未佩戴时为 0） |
| pure_badge_enabled | boolean | 是否纯发徽章 |
| circle_badge_id | integer | 圈子徽章 ID，对应 ext_circle_badges.id |
| circle_badge_name | string | 圈子徽章名称 |
| point_amount | integer | 积分余额（仅查看自己时返回，查看他人时为 0） |
| point_frozen_amount | integer | 冻结积分（仅查看自己时返回） |
| credit_amount | integer | 诚信保余额（仅查看自己时返回） |
| credit_frozen_amount | integer | 冻结诚信保（仅查看自己时返回） |
| is_blocked | boolean | 当前用户是否已拉黑目标用户 |

---

## 三、刷新账户余额接口

### 3.1 获取用户最新账户信息

从 `ext_user_accounts` 表获取当前登录用户的最新积分、诚信保等账户信息。用于在积分/诚信保发生变动后，前端主动刷新余额展示。

**请求方式**: `GET`

**请求路径**: `/api/v1/users/account`

**请求参数**: 无

**请求示例**:

```bash
GET /api/v1/users/account
Authorization: Bearer <token>
```

**响应示例**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "point_amount": 1000,
    "point_frozen_amount": 0,
    "credit_amount": 500,
    "credit_frozen_amount": 0,
    "updated_at": "2025-03-09 12:00:00"
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|------|------|------|
| point_amount | integer | 积分余额 |
| point_frozen_amount | integer | 冻结积分 |
| credit_amount | integer | 诚信保余额 |
| credit_frozen_amount | integer | 冻结诚信保 |
| updated_at | string/null | 账户记录更新时间 |

---

## 接口汇总

| 接口 | 方法 | 路径 | 说明 |
|------|------|------|------|
| 获取等级列表 | GET | /api/v1/configs/levels | 用户等级下拉/展示 |
| 兑换会员等级 | POST | /api/v1/vip/exchangeMemberLevel | 积分兑换会员 |
| 获取用户资料 | GET | /api/v1/users/profile | 含头像、积分、会员信息 |
| 刷新账户余额 | GET | /api/v1/users/account | 获取最新积分/诚信保 |
