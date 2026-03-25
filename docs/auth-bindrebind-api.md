# 用户认证、绑定与换绑 API 接口文档

## 概述

本文档涵盖 C 端前台以下接口：

1. **注册**：手机号/邮箱 + 验证码注册
2. **登录**：账号/靓号/手机号/邮箱 + 密码登录
3. **退出登录**：单设备退出 / 全设备退出
4. **发送验证码**：手机验证码 / 邮箱验证码
5. **绑定**：绑定手机号 / 绑定邮箱
6. **换绑**：换绑手机号（两步） / 换绑邮箱（两步）
7. **找回密码**：重置密码

**基础路径**: `/api/v1`

**认证方式**: 部分接口需在请求头中携带有效的 Token（下方标注"需认证"）

**请求头格式**:
```
Authorization: Bearer <token>
```

**说明**:
- Token 格式示例: `syt_xxx_xxx_xxx`（Synapse 格式的 access token）
- Token 可直接放在 Authorization 头中，也可使用 `Bearer ` 前缀

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

---

## 一、发送验证码

发送验证码接口为后续注册、绑定、换绑、找回密码等流程的前置步骤。通过 `verify_type` 参数区分验证码用途。

### verify_type 取值说明

| verify_type | 用途 | 说明 |
|---|---|---|
| `register` | 注册 | 注册时发送验证码 |
| `login` | 登录 | 登录时发送验证码（如有需要） |
| `reset_password` | 找回密码 | 重置密码时发送验证码 |
| `bind_account` | 绑定 | 首次绑定手机/邮箱时发送验证码 |
| `rebind_verify_old` | 换绑-验证旧号 | 换绑第一步：向旧手机/邮箱发送验证码 |
| `rebind_bind_new` | 换绑-绑定新号 | 换绑第二步：向新手机/邮箱发送验证码 |

### 1.1 发送手机验证码

**请求方式**: `POST`

**请求路径**: `/api/v1/index/phone-verify-code`

**是否需要认证**: 否（注册/找回密码时无需登录；绑定/换绑时前端应自行在 `verify_type` 中传入正确的类型）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| phone | string | 是 | 手机号（支持 E.164 格式如 `+8613800138000`，或中国 11 位手机号） |
| verify_type | string | 是 | 验证码用途，见上表 |
| country_code | string | 否 | 国家区号（如 `86`、`1`），不传默认中国 |

**请求示例**:

```bash
POST /api/v1/index/phone-verify-code
Content-Type: application/json

{
  "phone": "13800138000",
  "verify_type": "register",
  "country_code": "86"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "验证码发送成功"
}
```

**常见错误**:
- `手机号不能为空`
- `手机号格式不正确`
- `验证码发送过于频繁，请5分钟后再试`：60 秒内同一手机号+同一 verify_type 只能发送一次

---

### 1.2 发送邮箱验证码

**请求方式**: `POST`

**请求路径**: `/api/v1/index/email-verify-code`

**是否需要认证**: 否

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| email | string | 是 | 邮箱地址 |
| verify_type | string | 是 | 验证码用途，见上表 |

**请求示例**:

```bash
POST /api/v1/index/email-verify-code
Content-Type: application/json

{
  "email": "user@example.com",
  "verify_type": "register"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "验证码发送成功"
}
```

**常见错误**:
- `邮箱地址不能为空`
- `邮箱地址格式不正确`
- `验证码发送过于频繁，请5分钟后再试`

---

## 二、注册

### 2.1 用户注册

支持手机号或邮箱注册，需先调用发送验证码接口（`verify_type=register`）。

**请求方式**: `POST`

**请求路径**: `/api/v1/index/register`

**是否需要认证**: 否

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| username | string | 是 | 用户名（2-100字符，仅支持小写字母、数字、`.` `_` `-`） |
| password | string | 是 | 密码（6-32位） |
| verify_code | string | 是 | 验证码（6位数字） |
| phone | string | 二选一 | 手机号（与 email 二选一） |
| email | string | 二选一 | 邮箱（与 phone 二选一） |
| country_code | string | 否 | 国家区号（手机注册时使用） |
| device_id | string | 否 | 设备 ID，不传则服务端自动生成 |
| device_name | string | 否 | 设备名称 |
| gender | string | 否 | 性别（`male`/`female`/`other`），不传默认 `unknown` |
| invite_code | string | 否 | 邀请码（邀请人的 ext_users.id） |

**请求示例**:

```bash
POST /api/v1/index/register
Content-Type: application/json

{
  "username": "zhangsan",
  "password": "abc123456",
  "verify_code": "123456",
  "phone": "13800138000",
  "country_code": "86",
  "device_id": "ABCD1234",
  "device_name": "iPhone 15"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "user_id": "@zhangsan:im-sq01.bleiworc.xyz",
    "access_token": "syt_xxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "home_server": "im-sq01.bleiworc.xyz",
    "device_id": "ABCD1234"
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|---|---|---|
| user_id | string | Matrix 用户 ID |
| access_token | string | 登录令牌，后续请求需携带 |
| home_server | string | 服务器域名 |
| device_id | string | 设备 ID |

**常见错误**:
- `请提供手机号或邮箱`
- `请提供用户名`
- `用户名已被使用`
- `验证码无效、已过期或已使用`
- `该手机号已注册` / `该邮箱已注册`
- `密码长度必须在6-32位之间`

---

## 三、登录

### 3.1 用户登录

支持账号（用户名）、靓号、手机号、邮箱 + 密码登录。

**请求方式**: `POST`

**请求路径**: `/api/v1/login/login`

**是否需要认证**: 否

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| identifier | string | 是 | 登录标识：用户名、靓号、手机号或邮箱 |
| password | string | 是 | 密码 |
| device_id | string | 否 | 设备 ID，不传则服务端自动生成 |
| device_name | string | 否 | 设备名称 |

> **兼容说明**: 也可使用 `phone` 或 `email` 字段代替 `identifier`，后端会自动兼容。

**请求示例**:

```bash
POST /api/v1/login/login
Content-Type: application/json

{
  "identifier": "13800138000",
  "password": "abc123456",
  "device_id": "ABCD1234",
  "device_name": "iPhone 15"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "user_id": "@zhangsan:im-sq01.bleiworc.xyz",
    "access_token": "syt_xxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "home_server": "im-sq01.bleiworc.xyz",
    "device_id": "ABCD1234"
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|---|---|---|
| user_id | string | Matrix 用户 ID |
| access_token | string | 登录令牌 |
| home_server | string | 服务器域名 |
| device_id | string | 设备 ID |

**常见错误**:
- `请输入账号、靓号、手机号或邮箱`
- `密码不能为空`
- `用户不存在`
- `密码错误`

---

## 四、退出登录

### 4.1 退出当前设备

退出当前设备登录，删除当前设备的 access_token 和设备记录。

**请求方式**: `POST`

**请求路径**: `/api/v1/login/logout`

**是否需要认证**: 是

**请求参数**: 无

**请求示例**:

```bash
POST /api/v1/login/logout
Authorization: Bearer <token>
```

**成功响应**:

```json
{
  "code": 0,
  "message": "",
  "data": {}
}
```

---

### 4.2 退出所有设备

退出所有设备登录，删除该用户的所有 access_token 和设备记录。

**请求方式**: `POST`

**请求路径**: `/api/v1/login/logout-all`

**是否需要认证**: 是

**请求参数**: 无

**请求示例**:

```bash
POST /api/v1/login/logout-all
Authorization: Bearer <token>
```

**成功响应**:

```json
{
  "code": 0,
  "message": "",
  "data": {}
}
```

---

## 五、绑定手机/邮箱

用于首次绑定手机号或邮箱（用户当前未绑定对应联系方式）。

### 5.1 绑定手机号

需先调用 **发送手机验证码** 接口（`verify_type=bind_account`），向目标手机号发送验证码。

**请求方式**: `POST`

**请求路径**: `/api/v1/users/bind-phone`

**是否需要认证**: 是

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| phone | string | 是 | 手机号 |
| verify_code | string | 是 | 6 位验证码 |
| country_code | string | 否 | 国家区号 |

**请求示例**:

```bash
POST /api/v1/users/bind-phone
Authorization: Bearer <token>
Content-Type: application/json

{
  "phone": "13800138000",
  "verify_code": "123456",
  "country_code": "86"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "绑定成功"
}
```

**常见错误**:
- `手机号不能为空`
- `手机号格式不正确`
- `验证码不能为空`
- `验证码无效、已过期或已使用`
- `该手机号已被其他用户绑定`

---

### 5.2 绑定邮箱

需先调用 **发送邮箱验证码** 接口（`verify_type=bind_account`），向目标邮箱发送验证码。

**请求方式**: `POST`

**请求路径**: `/api/v1/users/bind-email`

**是否需要认证**: 是

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| email | string | 是 | 邮箱地址 |
| verify_code | string | 是 | 6 位验证码 |

**请求示例**:

```bash
POST /api/v1/users/bind-email
Authorization: Bearer <token>
Content-Type: application/json

{
  "email": "user@example.com",
  "verify_code": "123456"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "绑定成功"
}
```

**常见错误**:
- `邮箱不能为空`
- `邮箱格式不正确`
- `验证码不能为空`
- `验证码无效、已过期或已使用`
- `该邮箱已被其他用户绑定`

---

## 六、换绑手机/邮箱

换绑为两步操作：第一步验证旧联系方式的验证码，第二步提交新联系方式的验证码完成换绑。两步之间有 10 分钟有效期。

### 6.1 换绑手机号

#### 第一步：验证旧手机

先调用 **发送手机验证码** 接口，向当前绑定的旧手机号发送验证码（`verify_type=rebind_verify_old`），然后提交验证。

**请求方式**: `POST`

**请求路径**: `/api/v1/users/rebind-phone/verify-old`

**是否需要认证**: 是

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| verify_code | string | 是 | 旧手机收到的 6 位验证码 |

> **注意**: 不需要传手机号，后端自动读取当前用户绑定的旧手机号进行验证。

**请求示例**:

```bash
POST /api/v1/users/rebind-phone/verify-old
Authorization: Bearer <token>
Content-Type: application/json

{
  "verify_code": "123456"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "rebind_token": "a1b2c3d4e5f6..."
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|---|---|---|
| rebind_token | string | 换绑凭证，第二步需携带，10 分钟内有效 |

**常见错误**:
- `验证码不能为空`
- `当前账号未绑定手机号`
- `验证码无效、已过期或已使用`

---

#### 第二步：绑定新手机

先调用 **发送手机验证码** 接口，向新手机号发送验证码（`verify_type=rebind_bind_new`），然后携带第一步返回的 `rebind_token` 提交。

**请求方式**: `POST`

**请求路径**: `/api/v1/users/rebind-phone/bind-new`

**是否需要认证**: 是

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| phone | string | 是 | 新手机号 |
| verify_code | string | 是 | 新手机收到的 6 位验证码 |
| rebind_token | string | 是 | 第一步返回的换绑凭证 |
| country_code | string | 否 | 国家区号 |

**请求示例**:

```bash
POST /api/v1/users/rebind-phone/bind-new
Authorization: Bearer <token>
Content-Type: application/json

{
  "phone": "13900139000",
  "verify_code": "654321",
  "rebind_token": "a1b2c3d4e5f6...",
  "country_code": "86"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "换绑成功"
}
```

**常见错误**:
- `新手机号不能为空`
- `手机号格式不正确`
- `验证码不能为空`
- `缺少换绑凭证，请先完成旧手机验证`
- `换绑会话已过期，请重新验证旧手机`：rebind_token 超过 10 分钟
- `换绑凭证无效`
- `验证码无效、已过期或已使用`
- `该手机号已被其他用户绑定`

---

### 6.2 换绑邮箱

#### 第一步：验证旧邮箱

先调用 **发送邮箱验证码** 接口，向当前绑定的旧邮箱发送验证码（`verify_type=rebind_verify_old`），然后提交验证。

**请求方式**: `POST`

**请求路径**: `/api/v1/users/rebind-email/verify-old`

**是否需要认证**: 是

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| verify_code | string | 是 | 旧邮箱收到的 6 位验证码 |

> **注意**: 不需要传邮箱地址，后端自动读取当前用户绑定的旧邮箱进行验证。

**请求示例**:

```bash
POST /api/v1/users/rebind-email/verify-old
Authorization: Bearer <token>
Content-Type: application/json

{
  "verify_code": "123456"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "",
  "data": {
    "rebind_token": "a1b2c3d4e5f6..."
  }
}
```

**响应字段说明**:

| 字段 | 类型 | 说明 |
|---|---|---|
| rebind_token | string | 换绑凭证，第二步需携带，10 分钟内有效 |

**常见错误**:
- `验证码不能为空`
- `当前账号未绑定邮箱`
- `验证码无效、已过期或已使用`

---

#### 第二步：绑定新邮箱

先调用 **发送邮箱验证码** 接口，向新邮箱发送验证码（`verify_type=rebind_bind_new`），然后携带第一步返回的 `rebind_token` 提交。

**请求方式**: `POST`

**请求路径**: `/api/v1/users/rebind-email/bind-new`

**是否需要认证**: 是

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| email | string | 是 | 新邮箱地址 |
| verify_code | string | 是 | 新邮箱收到的 6 位验证码 |
| rebind_token | string | 是 | 第一步返回的换绑凭证 |

**请求示例**:

```bash
POST /api/v1/users/rebind-email/bind-new
Authorization: Bearer <token>
Content-Type: application/json

{
  "email": "new@example.com",
  "verify_code": "654321",
  "rebind_token": "a1b2c3d4e5f6..."
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "换绑成功"
}
```

**常见错误**:
- `新邮箱不能为空`
- `邮箱格式不正确`
- `验证码不能为空`
- `缺少换绑凭证，请先完成旧邮箱验证`
- `换绑会话已过期，请重新验证旧邮箱`：rebind_token 超过 10 分钟
- `换绑凭证无效`
- `验证码无效、已过期或已使用`
- `该邮箱已被其他用户绑定`

---

## 七、找回密码（重置密码）

通过手机号或邮箱重置密码。需先调用发送验证码接口（`verify_type=reset_password`）。

### 7.1 重置密码

**请求方式**: `POST`

**请求路径**: `/api/v1/index/reset-password`

**是否需要认证**: 否

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|---|---|---|---|
| phone | string | 二选一 | 绑定的手机号（与 email 二选一） |
| email | string | 二选一 | 绑定的邮箱（与 phone 二选一） |
| verify_code | string | 是 | 6 位验证码 |
| new_password | string | 是 | 新密码（6-32位） |
| country_code | string | 否 | 国家区号（手机号重置时使用） |

**请求示例**:

```bash
POST /api/v1/index/reset-password
Content-Type: application/json

{
  "phone": "13800138000",
  "verify_code": "123456",
  "new_password": "newpass123"
}
```

**成功响应**:

```json
{
  "code": 0,
  "message": "密码重置成功"
}
```

**常见错误**:
- `请提供手机号或邮箱`
- `验证码不能为空`
- `新密码不能为空`
- `密码长度必须在6-32位之间`
- `验证码无效、已过期或已使用`
- `用户不存在`

---

## 换绑完整流程图

### 换绑手机号流程

```
1. 前端调用 POST /api/v1/index/phone-verify-code
   参数: { phone: "旧手机号", verify_type: "rebind_verify_old" }
   → 旧手机收到验证码

2. 前端调用 POST /api/v1/users/rebind-phone/verify-old
   参数: { verify_code: "旧手机验证码" }
   → 返回 { rebind_token: "xxx" }

3. 前端调用 POST /api/v1/index/phone-verify-code
   参数: { phone: "新手机号", verify_type: "rebind_bind_new" }
   → 新手机收到验证码

4. 前端调用 POST /api/v1/users/rebind-phone/bind-new
   参数: { phone: "新手机号", verify_code: "新手机验证码", rebind_token: "xxx" }
   → 换绑成功
```

### 换绑邮箱流程

```
1. 前端调用 POST /api/v1/index/email-verify-code
   参数: { email: "旧邮箱", verify_type: "rebind_verify_old" }
   → 旧邮箱收到验证码

2. 前端调用 POST /api/v1/users/rebind-email/verify-old
   参数: { verify_code: "旧邮箱验证码" }
   → 返回 { rebind_token: "xxx" }

3. 前端调用 POST /api/v1/index/email-verify-code
   参数: { email: "新邮箱", verify_type: "rebind_bind_new" }
   → 新邮箱收到验证码

4. 前端调用 POST /api/v1/users/rebind-email/bind-new
   参数: { email: "新邮箱", verify_code: "新邮箱验证码", rebind_token: "xxx" }
   → 换绑成功
```

---

## 接口汇总

| 接口 | 方法 | 路径 | 需认证 | 说明 |
|---|---|---|---|---|
| 发送手机验证码 | POST | /api/v1/index/phone-verify-code | 否 | verify_type 区分用途 |
| 发送邮箱验证码 | POST | /api/v1/index/email-verify-code | 否 | verify_type 区分用途 |
| 注册 | POST | /api/v1/index/register | 否 | 手机/邮箱+验证码注册 |
| 登录 | POST | /api/v1/login/login | 否 | 账号/靓号/手机/邮箱+密码 |
| 退出当前设备 | POST | /api/v1/login/logout | 是 | 单设备退出 |
| 退出所有设备 | POST | /api/v1/login/logout-all | 是 | 全设备退出 |
| 绑定手机号 | POST | /api/v1/users/bind-phone | 是 | 首次绑定 |
| 绑定邮箱 | POST | /api/v1/users/bind-email | 是 | 首次绑定 |
| 换绑手机-验证旧号 | POST | /api/v1/users/rebind-phone/verify-old | 是 | 第一步 |
| 换绑手机-绑定新号 | POST | /api/v1/users/rebind-phone/bind-new | 是 | 第二步 |
| 换绑邮箱-验证旧号 | POST | /api/v1/users/rebind-email/verify-old | 是 | 第一步 |
| 换绑邮箱-绑定新号 | POST | /api/v1/users/rebind-email/bind-new | 是 | 第二步 |
| 重置密码 | POST | /api/v1/index/reset-password | 否 | 手机/邮箱+验证码重置 |
