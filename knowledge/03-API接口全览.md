# 03 · API 接口全览

> 路由注册方式：**PHP 8 注解路由**（`#[Controller]`、`#[GetMapping]`、`#[PostMapping]`、`#[PutMapping]`）
> 所有路由以注解自动注册，`config/routes.php` 仅保留注释示例。

---

## 一、C 端前台接口（`api/v1/...`）

### 1.1 登录模块 `api/v1/login`

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `api/v1/login/login` | 用户登录（账号/靓号/手机/邮箱 + 密码，与 Synapse 对接） |
| POST | `api/v1/login/logout` | 单设备退出（Bearer token） |
| POST | `api/v1/login/logout-all` | 所有设备退出 |
| POST | `api/v1/login/forgot-password` | 忘记密码（TODO） |

### 1.2 注册模块 `api/v1/register`

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `api/v1/register/reg` | 用户注册（账号+邀请码+验证码+密码） |
| POST | `api/v1/register/check-available` | 检查用户名/手机号是否可用 |
| GET  | `api/v1/register/is_reg_off` | 注册开关状态 |
| GET  | `api/v1/register/sms` | 发送 SMS 验证码 |
| GET  | `api/v1/register/is_sms_off` | SMS 功能开关 |
| POST | `api/v1/register/find-password` | 找回密码第一步（TODO） |
| POST | `api/v1/register/send-password-reset-code` | 发送重置密码验证码（TODO） |
| POST | `api/v1/register/verify-password-reset-code` | 验证重置密码验证码（TODO） |
| POST | `api/v1/register/reset-password` | 重置密码（TODO） |

### 1.3 用户资料模块 `api/v1/users`

| 方法 | 路径 | 说明 |
|------|------|------|
| GET  | `api/v1/users/profile` | 获取用户资料（支持 ?id=、?user_id= 查他人） |
| PUT  | `api/v1/users/profile/displayname` | 设置显示名（兼容 Synapse） |
| PUT  | `api/v1/users/profile/avatar_url` | 设置头像（兼容 Synapse） |
| POST/PUT | `api/v1/users/profile/nickname` | 设置昵称（同步多表） |
| POST | `api/v1/users/gender` | 设置性别 |
| POST | `api/v1/users/sign` | 设置个性签名 |
| GET  | `api/v1/users/account` | 获取积分/诚信保账户余额 |
| POST | `api/v1/users/remark` | 设置备注（自己或对他人） |
| POST | `api/v1/users/chat-prohibited` | 设置聊天禁用 |
| POST | `api/v1/users/note-permission` | 设置笔记访问权限 |
| POST | `api/v1/users/bind-phone` | 绑定手机号 |
| POST | `api/v1/users/bind-email` | 绑定邮箱 |
| POST | `api/v1/users/rebind-phone/verify-old` | 换绑手机第一步（验证旧号） |
| POST | `api/v1/users/rebind-phone/bind-new` | 换绑手机第二步（绑新号） |
| POST | `api/v1/users/rebind-email/verify-old` | 换绑邮箱第一步 |
| POST | `api/v1/users/rebind-email/bind-new` | 换绑邮箱第二步 |

### 1.4 圈子模块 `api/v1/circles`

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `api/v1/circles/create` | 创建圈子（需审核） |
| GET  | `api/v1/circles/index` | 圈子首页（已加入圈子内容流） |
| GET/POST | `api/v1/circles/circle_user_setting` | 圈子个人设置（城市过滤、订阅圈子） |
| GET  | `api/v1/circles/circle_mine` | 我的圈子（加入/管理/申请） |
| POST | `api/v1/circles/circle_invite` | 邀请用户加入圈子 |
| GET  | `api/v1/circles/circle_invite_list` | 我的邀请列表 |
| POST | `api/v1/circles/circle_invite_confirm` | 确认邀请 |
| POST | `api/v1/circles/circle_apply` | 申请加入圈子 |
| GET  | `api/v1/circles/circle_search` | 搜索公开圈子 |
| GET  | `api/v1/circles/circle_detail` | 圈子详情（内容/公告/荣誉榜） |
| POST | `api/v1/circles/circle_detail_setting` | 圈子详情设置（管理员） |
| GET  | `api/v1/circles/circle_check_list` | 入圈审核列表 |
| POST | `api/v1/circles/circle_check_handle` | 入圈审核处理 |
| GET  | `api/v1/circles/circle_block_list` | 圈子成员列表 |
| POST | `api/v1/circles/circle_block_handle` | 成员权限管理 |
| GET  | `api/v1/circles/circle_notice_list` | 圈子公告列表 |
| POST | `api/v1/circles/circle_notice_update` | 发布/编辑公告 |
| POST | `api/v1/circles/circle_notice_delete` | 删除公告 |
| POST | `api/v1/circles/circle_content_report` | 投诉帮办内容 |
| POST | `api/v1/circles/circle_content_delete` | 删除帮办内容 |
| POST | `api/v1/circles/circle_content_create` | 发布帮办内容 |
| GET  | `api/v1/circles/circle_mine_list` | 我有发帖权限的圈子列表 |
| POST | `api/v1/circles/quit` | 退出圈子 |

### 1.5 笔记模块 `api/v1/notes`

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `api/v1/notes/create` | 创建/编辑笔记（type: 1=普通 2=编辑 3=超级笔记） |
| GET  | `api/v1/notes/index` | 我的笔记列表（支持置顶） |
| GET  | `api/v1/notes/group` | 按分组查询笔记 |
| GET  | `api/v1/notes/search` | 搜索笔记 |
| POST | `api/v1/notes/pin` | 置顶/取消置顶 |
| GET  | `api/v1/notes/update` | 获取笔记详情（编辑前） |
| POST | `api/v1/notes/remark` | 设置笔记备注 |
| POST | `api/v1/notes/remark_friend` | 设置笔记好友备注 |
| POST | `api/v1/notes/delete` | 删除笔记 |
| POST | `api/v1/notes/move_group` | 移动笔记到分组 |
| GET  | `api/v1/notes/group_list` | 笔记分组列表 |
| POST | `api/v1/notes/group_update` | 创建/编辑/删除笔记分组 |
| GET  | `api/v1/notes/detail` | 笔记详情（含权限验证） |
| GET  | `api/v1/notes/super` | 超级笔记列表（含今日浏览统计） |
| POST | `api/v1/notes/remove` | 下架笔记（status=2） |
| POST | `api/v1/notes/label_update` | 添加标签 |
| GET  | `api/v1/notes/label_list` | 标签列表 |
| POST | `api/v1/notes/label_manage` | 管理标签（增/改/删） |
| GET  | `api/v1/notes/super_manage` | 超级笔记管理 |
| GET  | `api/v1/notes/wall` | 笔记墙（看好友笔记） |
| GET  | `api/v1/notes/wall/detail` | 好友笔记详情 |

### 1.6 聊天分组模块 `api/v1/chat-groups`

| 方法 | 路径 | 说明 |
|------|------|------|
| GET/POST | `api/v1/chat-groups/list` | 分组列表 |
| POST | `api/v1/chat-groups/create` | 创建分组 |
| POST | `api/v1/chat-groups/update` | 修改分组 |
| POST | `api/v1/chat-groups/delete` | 删除分组 |
| POST | `api/v1/chat-groups/add-room` | 添加聊天到分组 |
| POST | `api/v1/chat-groups/remove-room` | 从分组移除聊天 |
| GET/POST | `api/v1/chat-groups/rooms` | 分组内的聊天列表 |

### 1.7 聊天室模块 `api/v1/chats`

| 方法 | 路径 | 说明 |
|------|------|------|
| GET/POST | `api/v1/chats/created` | 我创建的聊天室 |
| GET/POST | `api/v1/chats/managed` | 我管理的聊天（群主+管理员） |
| GET/POST | `api/v1/chats/joined` | 我加入的聊天 |

### 1.8 其他前台接口

| 模块 | 前缀 | 说明 |
|------|------|------|
| VIP 会员 | `api/v1/vip` | 积分兑换会员等级 |
| 全局配置 | `api/v1/configs` | 获取 App 配置 |
| 上传 | `api/v1/upload` | 文件/图片上传 |
| 客服 | `api/v1/customer-service` | 客服列表 |
| 反馈 | `api/v1/user-feedbacks` | 用户反馈 |
| 权限 | `api/v1/user-permissions` | 用户权限设置 |
| App 信息 | `api/v1/app` | App 版本、基础信息 |

---

## 二、管理后台接口（`/ht/v1/...`）

### 2.1 核心管理

| 模块 | 前缀 | 说明 |
|------|------|------|
| 索引/配置 | `/ht/v1/index` | 首页、全局配置（域名等） |
| 管理员 | `/ht/v1/admins` | 管理员 CRUD |
| 角色 | `/ht/v1/adminRoles` | 角色与权限 |
| 菜单 | `/ht/v1/adminMenus` | 后台菜单管理 |
| 操作日志 | `/ht/v1/adminLogs` | 管理员操作日志 |

### 2.2 用户管理

| 模块 | 前缀 | 说明 |
|------|------|------|
| 用户列表 | `/ht/v1/users` | 用户查询、设为管理员 |
| 用户扩展信息 | `/ht/v1/userExts` | 封禁、禁言、改密码、等级等 |
| 用户账户 | `/ht/v1/userAccounts` | 积分/诚信保账户管理 |
| 用户相册 | `/ht/v1/userAlbums` | 相册 CRUD |
| 用户徽章 | `/ht/v1/userBadges` | 徽章配置 |
| 用户变更记录 | `/ht/v1/userChanges` | 用户信息变更日志 |
| 用户设备 | `/ht/v1/userDevices` | 设备管理 |
| 用户担保 | `/ht/v1/userEnsures` | 担保记录 |
| 用户兑换记录 | `/ht/v1/userExchanges` | VIP 兑换记录 |
| 用户反馈 | `/ht/v1/userFeedbacks` | 用户反馈管理 |
| 用户帮助请求 | `/ht/v1/userHelpRequests` | 帮助请求 |
| 用户帮助 | `/ht/v1/userHelps` | 帮助内容 |
| 用户等级 | `/ht/v1/userLevels` | 会员等级配置 |
| 用户备注 | `/ht/v1/userNotes` | 用户备注管理（后台） |
| 用户举报 | `/ht/v1/userReports` | 举报处理 |
| 用户靓号 | `/ht/v1/userVanityNumbers` | 靓号管理 |
| 验证码记录 | `/ht/v1/userVerifyCodes` | 验证码查询 |
| 用户钱包 | `/ht/v1/userWallets` | 钱包管理 |

### 2.3 圈子管理

| 模块 | 前缀 | 说明 |
|------|------|------|
| 圈子列表 | `/ht/v1/circles` | 圈子审核、管理 |
| 徽章配置 | `/ht/v1/circleBadges` | 圈子徽章 |
| 圈子成员 | `/ht/v1/circleUsers` | 成员管理 |

### 2.4 聊天/IM 管理

| 模块 | 前缀 | 说明 |
|------|------|------|
| 聊天室 | `/ht/v1/chatRooms` | 聊天室列表 |
| 聊天消息 | `/ht/v1/chats` | 消息查看 |
| 群组扩展 | `/ht/v1/groupExts` | 群组扩展信息 |

### 2.5 配置管理

| 模块 | 前缀 | 说明 |
|------|------|------|
| 通知 | `/ht/v1/notices` | 系统通知 |
| 帮助分类 | `/ht/v1/helpCategories` | 帮助文档分类 |
| 帮助内容 | `/ht/v1/helps` | 帮助文档 |
| 参数组 | `/ht/v1/parameterGroups` | 参数分组 |
| 参数配置 | `/ht/v1/parameters` | 系统参数 |
| 省市区 | `/ht/v1/provinces` `/ht/v1/cities` `/ht/v1/areas` | 地区数据 |
| 黑名单 IP | `/ht/v1/blockIps` | IP 封禁 |
| 黑名单邮箱 | `/ht/v1/blockMails` | 邮箱封禁 |
| 黑名单手机 | `/ht/v1/blockPhones` | 手机封禁 |
| 黑名单用户名 | `/ht/v1/blockUsernames` | 用户名封禁 |
| App 版本 | `/ht/v1/appVersions` | 版本管理 |
| 上传 | `/ht/v1/upload` | 后台上传 |

### 2.6 红包管理
| 模块 | 前缀 | 说明 |
|------|------|------|
| 红包 | `/ht/v1/redPackets` | 红包管理 |
| 红包记录 | `/ht/v1/redPacketRecords` | 红包领取记录 |

---

## 三、内部接口（`/internal/...`）

| 路径 | 说明 |
|------|------|
| `/internal/limits/*` | 限流配置（IP 白名单 `CheckInternalAccess` 中间件） |

---

## 四、WebSocket 接口

- **地址**：`ws://host:9509`
- **握手**：`Authorization: Bearer <access_token>` （Header）
- **建立连接后**：服务端订阅 Redis `account_change` 频道，有账变事件时 Push 给对应用户
- **消息格式**：`{"user_id": {ext_user_id}, "ts": {timestamp}, ...附加数据}`

