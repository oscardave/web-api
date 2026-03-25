# Shell 脚本使用说明

本目录包含用于测试 API 接口的 Shell 脚本。

## 脚本列表

### 1. login-synapse.sh - Synapse 原生登录脚本

使用 Matrix 协议的原生登录接口（`/_matrix/client/v3/login`）获取 access_token。

**用法**:
```bash
./login-synapse.sh <user_id> <password> [device_id]
```

**参数说明**:
- `user_id` - Matrix 用户ID，格式: `@user:domain.com` 或 `user:domain.com`
- `password` - 密码
- `device_id` - 设备ID（可选，不提供则自动生成）

**示例**:
```bash
# 使用完整 Matrix 用户ID登录
./login-synapse.sh '@u10010:im-sq01.bleiworc.xyz' aa123123

# 指定设备ID
./login-synapse.sh '@u10010:im-sq01.bleiworc.xyz' aa123123 DEVICE123
```

**特点**:
- 使用 Synapse 原生 Matrix 协议接口
- 自动保存 access_token 到 `token-synapse.txt`
- 同时保存完整响应到 `token-synapse.json`
- 支持自动生成设备ID

**Token 保存位置**:
- `shell/token-synapse.txt` - 仅 access_token
- `shell/token-synapse.json` - 完整响应（包含 user_id, device_id 等）

---

### 2. login-synapse-test300.sh - test300 快速登录脚本

使用默认账号 test300 快速登录 Synapse。

**用法**:
```bash
./login-synapse-test300.sh [device_id]
```

**说明**:
- 默认用户ID: `@test300:im-sq01.bleiworc.xyz`
- 默认密码: `aa123123`
- 如果该用户ID不存在，请使用 `login-synapse.sh` 并提供正确的 Matrix 用户ID

**注意**: Matrix 用户ID格式通常是 `@u<数字>:<域名>`，例如 `@u10010:im-sq01.bleiworc.xyz`

---

### 3. login.sh - 自定义登录脚本

使用自定义登录接口（`/api/v1/login/login`）获取 access_token。

**用法**:
```bash
./login.sh <phone_or_email> <password> [verify_code]
```

**特点**:
- 支持手机号或邮箱登录
- 自动发送验证码（如果未提供）
- Token 保存到 `token.txt`

---

### 4. login-test300.sh - test300 快速登录脚本

使用默认账号 test300 快速登录（自定义接口）。

**用法**:
```bash
./login-test300.sh
```

---

### 5. register.sh - 命令行参数注册脚本

通过命令行参数传递注册信息，适合自动化脚本调用。

**用法**:
```bash
./register.sh <account> <password> <confirm_password> <mobile> [invite_code] [action]
```

**参数说明**:
- `account` - 账号（6-16位，只能字母数字，必填）
- `password` - 密码（必填）
- `confirm_password` - 确认密码（必填）
- `mobile` - 手机号码（必填）
- `invite_code` - 邀请码（可选，默认: 空字符串）
- `action` - 短信动作（可选，默认: register）

**示例**:
```bash
# 不使用邀请码
./register.sh testuser123 mypassword mypassword 13800138000

# 使用邀请码
./register.sh testuser123 mypassword mypassword 13800138000 INVITE123
```

**特点**:
- 自动验证账号格式
- 自动验证密码一致性
- 自动发送短信验证码
- 交互式输入验证码
- 自动完成注册

---

### 2. register-interactive.sh - 交互式注册脚本

通过交互式提示输入注册信息，适合手动操作。

**用法**:
```bash
./register-interactive.sh
```

**使用流程**:
1. 输入账号（6-16位字母或数字）
2. 输入密码（隐藏输入）
3. 再次输入密码确认（隐藏输入）
4. 输入手机号（11位数字）
5. 输入邀请码（可选，直接回车跳过）
6. 输入短信动作（可选，直接回车使用默认值）
7. 确认信息
8. 自动发送短信验证码
9. 输入收到的验证码
10. 完成注册

**特点**:
- 友好的交互式界面
- 实时验证输入格式
- 密码隐藏输入
- 信息确认步骤
- 彩色输出提示

**示例**:
```bash
$ ./register-interactive.sh
[INFO] ==========================================
[INFO]         用户注册脚本
[INFO] ==========================================

[INPUT] 请输入账号（6-16位字母或数字）:
testuser123
[INPUT] 请输入密码:
[INPUT] 请再次输入密码确认:
[INPUT] 请输入手机号（11位数字）:
13800138000
[INPUT] 请输入邀请码:
INVITE123
[INPUT] 请输入短信动作（可选，直接回车使用默认值 register）:

[INFO] ==========================================
[INFO] 注册信息确认
[INFO] ==========================================
[INFO] 账号: testuser123
[INFO] 手机号: 13800138000
[INFO] 邀请码: INVITE123
[INFO] 短信动作: register

[WARNING] 确认以上信息无误？(y/n)
y

[INFO] 步骤1: 发送短信验证码...
[INFO] 短信验证码已发送，请查看手机短信

[WARNING] 请输入收到的验证码:
123456

[INFO] 步骤2: 注册用户...
[INFO] ==========================================
[INFO] 注册成功！(HTTP 200)
[INFO] ==========================================
```

---

### 3. chat-groups.create.sh - 创建聊天分组

创建聊天分组的测试脚本。

**用法**:
```bash
./chat-groups.create.sh
```

**说明**:
- 需要修改脚本中的 Token 和 POST 数据
- Token 格式: `Bearer syt_xxx_xxx_xxx`

---

### 4. customer-service.list.sh - 获取客服列表

获取客服用户列表的测试脚本。

**用法**:
```bash
./customer-service.list.sh
```

**说明**:
- 需要修改脚本中的 Token
- Token 格式: `Bearer syt_xxx_xxx_xxx`

---

## API 地址配置

所有脚本使用的 API 基础地址为:
```
https://im-sq01.chunquqiulai.top
```

如需修改，请编辑脚本中的 `HOST` 变量。

---

## 认证说明

大部分 API 接口需要 Token 认证，Token 格式:
```
Authorization: Bearer syt_xxx_xxx_xxx
```

注册接口 (`/api/v1/register/*`) 不需要 Token 认证。

---

## 注意事项

1. **账号格式要求**:
   - 长度: 6-16 位
   - 只能包含字母和数字
   - 不能包含中文

2. **密码要求**:
   - 密码和确认密码必须一致

3. **手机号格式**:
   - 11 位数字

4. **验证码**:
   - 验证码通过短信发送
   - 需要在收到验证码后输入

5. **脚本权限**:
   - 确保脚本有执行权限: `chmod +x script.sh`

---

## 错误处理

脚本会检查以下错误:
- 参数不足
- 账号格式错误
- 密码不一致
- 手机号格式错误
- HTTP 请求失败
- 验证码为空

所有错误都会以红色 `[ERROR]` 标记显示。

---

## 依赖要求

- `bash` (版本 4.0+)
- `curl` (用于 HTTP 请求)
- `sed` (用于文本处理)

大多数 Linux/macOS 系统都预装了这些工具。

---

## Synapse 登录 vs 自定义登录

### Synapse 原生登录 (`login-synapse.sh`)
- **接口**: `/_matrix/client/v3/login`
- **协议**: Matrix 协议标准
- **用户ID格式**: `@user:domain.com`
- **优点**: 标准协议，兼容性好
- **Token文件**: `token-synapse.txt`

### 自定义登录 (`login.sh`)
- **接口**: `/api/v1/login/login`
- **协议**: 自定义接口
- **登录方式**: 手机号/邮箱 + 验证码
- **优点**: 支持验证码验证，更安全
- **Token文件**: `token.txt`

---

---

### 6. chats-test.sh - 聊天列表接口综合测试脚本

测试 ChatsControllers 的三个接口的综合脚本。

**用法**:
```bash
./chats-test.sh [page] [limit]
```

**参数说明**:
- `page` - 页码，默认1
- `limit` - 每页数量，默认50

**示例**:
```bash
# 使用默认参数
./chats-test.sh

# 指定页码和每页数量
./chats-test.sh 1 20
```

**特点**:
- 自动从 token-synapse.txt 或 token.txt 读取 Token
- 依次测试三个接口
- 显示详细的测试结果和响应数据
- 自动解析并显示关键信息（总记录数、记录预览等）

---

### 7. chats-created.sh - 测试我创建的聊天室

单独测试"我创建的聊天室"接口。

**用法**:
```bash
./chats-created.sh [page] [limit]
```

**示例**:
```bash
./chats-created.sh
./chats-created.sh 1 20
```

---

### 8. chats-managed.sh - 测试我管理的聊天

单独测试"我管理的聊天"接口。

**用法**:
```bash
./chats-managed.sh [page] [limit]
```

**示例**:
```bash
./chats-managed.sh
./chats-managed.sh 1 20
```

---

### 9. chats-joined.sh - 测试我加入的聊天

单独测试"我加入的聊天"接口。

**用法**:
```bash
./chats-joined.sh [page] [limit]
```

**示例**:
```bash
./chats-joined.sh
./chats-joined.sh 1 20
```

---

## Token 文件说明

脚本会自动查找以下 Token 文件（按优先级）：
1. `token-synapse.txt` - Synapse 原生登录获取的 Token
2. `token.txt` - 自定义登录接口获取的 Token

如果找不到 Token 文件，脚本会提示先运行登录脚本。

---

## 更新日志

- 2026-01-25: 添加聊天列表接口测试脚本（chats-test.sh, chats-created.sh, chats-managed.sh, chats-joined.sh）
- 2026-01-25: 添加 Synapse 原生登录脚本
- 2026-01-25: 添加注册脚本（命令行参数版本和交互式版本）
