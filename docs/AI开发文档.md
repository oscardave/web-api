## 一、仓库整体说明

本仓库下主要有三个子项目，它们共同构成整套 IM 系统：

- **synapse**：Matrix 协议的聊天服务器（Python 实现），负责账号、房间、消息、设备、Admin API 等所有即时通讯核心能力。
- **web-api**：后端网关与业务服务（PHP Hyperf 3），对内操作 PostgreSQL 与 Synapse，对外为管理后台和 C 端提供 HTTP API。
- **web-ht**：管理后台前端（Vue3 + Vite），用于运营/客服查看和管理用户、圈子、聊天消息等。

简化关系：

- C 端 App/Web →（Matrix 协议）→ **synapse**
- 管理后台页面 **web-ht** →（`/api/...`）→ **web-api Backend** →（数据库 & Synapse Admin API）→ **synapse**

---

## 二、各项目简介与技术栈

### 2.1 synapse（Matrix 聊天服务器）

- **作用**：真正的 IM 服务器，实现登录、注册、发送消息、房间管理、设备/Token 管理等。
- **技术栈**：
  - 语言：Python 3
  - 框架：Matrix 官方 homeserver「Synapse」
  - 存储：PostgreSQL（消息、用户、房间、关系等）
  - 配置：`synapse/synapse/homeserver.yaml`
- **关键点**：
  - 登录、设备、消息发送等逻辑都在 Synapse 内部，不要直接改数据库。
  - 对外提供 **REST Admin API**，如：
    - `/_synapse/admin/v1/users/:user_id/shadow_ban`（禁言 / 取消禁言）
    - `/_synapse/admin/v2/users/:user_id`（修改 user，包括 `deactivated`，并可让设备下线）
  - web-api 与 Synapse 的交互，应 **优先使用 Admin API**，避免绕过缓存直接改表。

### 2.2 web-api（后端服务）

- **作用**：
  - 对 C 端：提供部分业务 API（例如用户扩展信息、笔记、聊天室列表等）。
  - 对管理后台：提供统一的 Backend 接口（`/ht/v1/...`），聚合 PostgreSQL 与 Synapse Admin API。
- **技术栈**：
  - 语言：PHP 8.1+
  - 框架：Hyperf 3
  - 数据库：PostgreSQL（与 synapse 共库，但表有业务扩展，如 `ext_users`、`ext_user_extends` 等）
  - HTTP 客户端：Guzzle / 自行封装的 HttpClient，部分 Admin API 使用 `stream_socket_client` 直连 Synapse
  - 配置：`.env`（数据库、Redis、Synapse Admin API 地址和 token 等）
- **路由约定**：
  - 管理后台 Backend：统一前缀 `#[Controller(prefix: "/ht/v1/...")]`
  - C 端 Frontend API：统一前缀 `#[Controller(prefix: "api/v1/...")]`
  - 管理后台前端通过 `/api/module/action` 请求，经 Nginx/Vite 映射到 `http://web-api:9508/ht/v1/module/action`。
- **C 端登录与注册**：
  - web-api 提供 C 端登录/注册接口，涉及 `app/Http/Frontend/Controllers/LoginController.php`（C 端登录/注册路由）、`app/Http/Frontend/Dao/Index.php` 中的 `login`、`register` 等方法。C 端也可走 **Synapse**（Matrix 协议）登录/注册。
  - 修改 C 端用户密码、踢下线等，请通过 **Synapse Admin API** 或管理后台接口（如 `userExts/setPassword`）完成。

### 2.3 web-ht（管理后台前端）

- **作用**：运营端管理界面，如用户列表 `/ext-users/index`、圈子列表 `/ext-circles/all`、聊天记录 `/ext-chats/chats` 等。
- **技术栈**：
  - Vue 3 + Vite
  - TypeScript
  - Element Plus
  - Pinia、TailwindCSS
- **路由与页面**：
  - 视图统一放在 `web-ht/src/views/...` 下，例如：
    - 用户：`src/views/ext-users/users`
    - 圈子：`src/views/ext-circles/all`
    - 聊天记录：`src/views/ext-chats/chats`
  - API 定义统一放在 `src/api/*.ts` 中，例如 `src/api/extusers.ts`、`src/api/synchats.ts`。

---

## 三、三者之间的典型调用链

以「管理后台禁言用户」为例：

1. 管理后台（`web-ht`）在 `/ext-users/index` 中点击「处罚 → 禁止聊天」：
   - 调用前端 API：`setUserExtPenalty(user_id, chat_prohibited, ...)`
   - 实际请求：`POST /api/userExts/setPenalty`
2. Nginx / Vite 代理：
   - 把 `/api/userExts/setPenalty` 转发为 `POST http://web-api:9508/ht/v1/userExts/setPenalty`
3. `web-api` Backend：
   - `UserExtsController::setPenalty` → `UserExtDao::setPenalty`：
     - 更新 `ext_users.chat_prohibited` 等业务字段；
     - 通过统一方法 `applyChatProhibit()` 调用 Synapse Admin API：
       - `POST/DELETE /_synapse/admin/v1/users/:user_id/shadow_ban`
4. Synapse：
   - 更新自身用户状态（`shadow_banned`）并清理缓存，后续 C 端发消息会被偷偷丢弃（shadow ban 效果）。

类似地，「暂停服务」会通过 `userExts/changeStatus` 调用 Synapse Admin API `PUT /_synapse/admin/v2/users/:user_id` 设置 `deactivated`，从而踢下线并禁止登录。

---

## 四、主要目录结构与职责

### 4.1 synapse 关键目录

- `synapse/synapse/homeserver.yaml`  
  Synapse 主配置文件：端口、数据库、registration secret、server_name、public_baseurl 等。
- `synapse/synapse/synapse/rest/admin/`  
  Admin API 的 Python 实现，例如：
  - `users.py`：用户管理相关 Admin 接口（deactivate、shadow_ban 等）。
- `synapse/synapse/synapse/storage/`  
  存储层，封装对 PostgreSQL 的访问，包含 `users`、`events`、`room` 等表的操作。
- **注意**：业务侧一般不直接改 Synapse 代码，优先通过 Admin API 与之交互；若确需改动，请同步更新配置与文档。

### 4.2 web-api 关键目录

- `web-api/app/Http/Backend/Controllers/`  
  管理后台 Backend 控制器：
  - 统一返回格式：`['success' => bool, 'message' => string|null, 'data' => mixed]`，通过 `BackendController::responseData/responseError/responseSuccess` 封装。
  - 示例：
    - `UserExtsController.php`：用户扩展管理（列表、详情、状态变更、处罚等）。
    - `ChatsController.php`：聊天消息列表 `/ht/v1/chats/list`。
    - `CirclesController.php`：圈子列表与更新（`list`、`updateSettings`、`updateInfo`），详见下文 5.3。
    - `CircleUsersController.php`：圈子成员列表（`list`，支持 `circle_id`、`keyword` 模糊查询），详见 5.3。
- `web-api/app/Http/Backend/Dao/`  
  Dao 层，封装数据库 / 外部服务访问逻辑，返回 `['error' => string, 'data' => mixed]`：
  - `UserExtDao.php`：`ext_users` / `ext_user_extends` 相关操作；同步账号状态到 Synapse（deactivated、shadow_ban 等）。
  - `ChatDao.php`：在视图 `chats_v` 上查询聊天消息列表，支持按用户、房间名、消息内容搜索。
  - `CircleDao.php`：圈子表 `ext_circles` 的列表、更新设置、更新基本信息（`updateInfo`）。
  - `CircleUserDao.php`：圈子成员表 `ext_circle_users` 的列表，支持按 `keyword` 同时模糊 `user_nickname`/`user_id`/`user_vanity_id`。
- `web-api/app/Http/Backend/Validations/`  
  入参校验：
  - 如 `UserExtValidation.php`、`ChatValidation.php`，用于对 `user_id`、分页参数等做基本验证。
- `web-api/app/Http/Frontend/Controllers/`  
  C 端接口控制器（前缀 `api/v1/...`），如：
  - `UsersController.php`、`NotesController.php`、`ChatsControllers.php`。
  - `LoginController.php`：C 端登录/注册路由。
- `web-api/app/Http/Frontend/Dao/`  
  C 端业务 Dao，如：
  - `Users.php`：C 端用户资料、笔记权限等。
  - `UserPermissions.php`：好友/笔记访问权限控制。
  - `Index.php`：C 端登录/注册等方法的 Dao 实现（如 `login`、`register`）。
- `web-api/app/Service/`  
  公用服务（不启事务，由调用方纳入事务）：
  - `AccountChangeTriggerService.php`：账变触发，如诚信保余额变化后更新 ext_users.credit_badge_id。
- `web-api/data/*.sql`  
  数据库表结构与视图定义：
  - `ext_users.sql`、`ext_user_extends.sql`、`ext_user_permissions.sql`、`chats_v.sql` 等。
- `web-api/.env`  
  项目运行配置：
  - 数据库连接、Redis、IM 域名、`SYNAPSE_ADMIN_API_URL`、`SYNAPSE_ADMIN_ACCESS_TOKEN` 等。

### 4.3 web-ht 关键目录

- `web-ht/src/views/`  
  所有页面视图：
  - `ext-users/users/`：用户列表、详情、处罚弹窗等。
  - `ext-circles/all/`：圈子列表。
  - `ext-chats/chats/`：聊天消息列表。
- `web-ht/src/api/`  
  封装所有后端调用：
  - `extusers.ts`：`getUserExtsList`、`getUserExt`、`changeUserExtStatus`、`setUserExtPenalty` 等。
  - `synchats.ts`：`getChatsList`、`getChatRoomsList` 等。
- `web-ht/管理后台开发文档..md`  
  管理后台专用的开发/排查文档，记录了模块路由、状态字段含义、排查思路等（需与本 AI 文档保持一致认识）。

---

## 五、开发新功能的推荐流程

### 5.1 判断改动范围

1. **仅前端展示调整**（例如加一列、改显示文本）：
   - 多数只需修改 `web-ht/src/views/...` 下的页面/`hook.tsx`。
2. **管理后台新增功能**（例如新增用户操作按钮、报表）：
   - 需要同时修改：
     - `web-ht`：新增 API 调用 + UI 视图。
     - `web-api Backend`：新增 Controller + Dao + Validation，实现实际业务逻辑。
3. **IM 核心行为变更**（登录、发消息、禁言、生效时机等）：
   - 尽量只通过 `web-api` 去调 Synapse Admin API。
   - 不直接对 Synapse 的 `users` / `events` 等表做手工更新（否则缓存不同步，行为异常）。

### 5.2 管理后台功能开发模板（示例）

以「新增某个用户操作」为例：

1. **后端（web-api Backend）**
   - 在 `app/Http/Backend/Controllers` 新增或扩展控制器方法：
     - 使用 `#[Controller(prefix: "/ht/v1/...")]` + `#[PostMapping(path: "...")]` 注解；
     - 从 `$request->all()` 提取参数，交给 Validation 类校验；
     - 调 Dao 层方法，统一返回 `responseData/responseError/responseSuccess`。
   - 在 `app/Http/Backend/Dao` 中新增 Dao 方法：
     - 只做数据层（DB/Admin API/缓存）操作；
     - 返回 `['error' => string, 'data' => mixed]`。
   - 在 `app/Http/Backend/Validations` 新增或补充校验逻辑。

2. **前端（web-ht）**
   - 在 `src/api/*.ts` 中新增对应的 API 封装（利用 `http.request`，路径为 `/api/module/action`）。
   - 在对应页面的 `hook.tsx` 中：
     - 引入该 API；
     - 在合适的按钮/操作中调用，并根据返回的 `success/message` 做提示和 UI 更新。
   - 在 `index.vue` 中：
     - 添加按钮、搜索条件、表格列等 UI 元素；
     - 使用 `PureTableBar`、`pure-table` 配合分页、刷新。

3. **必要时更新文档**
   - 若功能影响用户状态、处罚逻辑、消息流转等核心行为，请同步更新：
     - `web-ht/管理后台开发文档..md`
     - 当前 `AI开发文档.md`（如新增了新的约定/规范）

### 5.3 圈子与圈子成员：接口与页面要点

管理后台「圈子管理 → 全部圈子」及「成员」弹窗涉及以下接口与前端约定，便于后续扩展或排查。

**后端（web-api Backend）**

- **CirclesController**（`/ht/v1/circles`）
  - `POST list`：圈子列表，入参含 `circle_name`、`owner_vanity_id`、`circle_status`、`circle_type`、分页等；返回含 `current_member_count`、`today_new_members`、`daily_active_members`、`help_posts_24h` 等统计字段。
  - `POST updateSettings`：更新圈子设置（邀请码数量、入圈/帮办限制、成员权限等）。
  - `POST updateInfo`：更新圈子基本信息（`circle_name`、`circle_description`、`circle_announcement`、`circle_avatar_url`、`member_limit`、`circle_type`）。
- **CircleUsersController**（`/ht/v1/circleUsers`）
  - `POST list`：圈子成员列表。必传或常用：`circle_id`；可选：`keyword`（同时模糊 `user_nickname`、`user_id`、`user_vanity_id`）、`role_type`、`member_status`、`join_method`、分页。返回字段与 `ext_circle_users` 一致（如 `user_avatar_url`、`can_view_help`、`can_post_help`、`help_post_count`、`join_time`、`last_activity_time` 等）。

**前端（web-ht）**

- **全部圈子列表**（`src/views/ext-circles/all/`）
  - 列表请求：`getCirclesList` → `POST /api/circles/list`。
  - **修改圈子**：操作列「修改」打开 `EditDialog`。弹窗底部「确定」需与表单提交统一：在 `addDialog` 时配置 `beforeSure`，通过 `ref` 调用 `EditDialog.handleSubmit()`（该方法需返回 `Promise<boolean>`，内部调用 `onSubmit(form)` 请求 `updateCircleInfo`）；仅在返回 `true` 时调用 `done()` 关闭弹窗。表单内「保存」按钮同样调用 `handleSubmit`，与确定按钮共用同一套提交逻辑。
  - **成员管理**：操作列「成员」打开 `MembersDialog`，传入当前行 `row` 作为 `circleData`（即列表接口返回的圈子对象，已含上述统计字段）。
- **成员管理弹窗**（`MembersDialog.vue`）
  - **上部分统计**：直接使用 `circleData` 的真实字段，不做二次请求：今日新增 → `circleData.today_new_members`，今日帮办发布 → `circleData.help_posts_24h`，今日活跃 → `circleData.daily_active_members`，目前总人数 → `circleData.current_member_count`。
  - **成员列表**：请求 `getCircleUsersList`（`POST /api/circleUsers/list`），必传 `circle_id: circleData.circle_id`，可选 `keyword`（搜索框）、`page`、`pageSize`。表格字段与后端返回一致（头像 `user_avatar_url`，帮办权限 `can_view_help`/`can_post_help`，成员状态与后端 `member_status` 及权限组合后的展示规则见组件内 `getMemberStatusDisplay`）。

**约定小结**

- 弹窗底部「确定」若需触发表单提交：使用 `beforeSure` + 子组件 `ref.handleSubmit()` 返回 Promise，成功后再 `done()`。
- 成员管理上部分统计依赖列表行数据，故从「全部圈子」进入时无需再请求圈子详情；若将来有独立成员页（无列表行），再考虑增加圈子详情接口或复用 `circles/list` 单条查询。

**圈子统计定时刷新**

- 上述统计字段（成员总数、今日新增、日活、24h 帮办数）由 **定时任务** 每 2 分钟写入 `ext_circles`，而非实时在业务请求里更新。
- 定时任务：`App\Crontab\CircleStatsSync`，规则 `0 */2 * * * *`（每 2 分钟），在 `config/autoload/crontab.php` 中需 `'enable' => true` 才会执行。
- 统计逻辑在 `CircleDao::refreshCircleStats` / `refreshAllCirclesStats`：成员相关来自 `ext_circle_users`（status=1、member_status=active；今日新增用 join_time>=CURRENT_DATE，日活用 last_activity_time>=CURRENT_DATE）；24h 帮办数来自 **ext_circle_content**（created 为 Unix 时间戳，与 Frontend 帮办发布一致）。会员数量 `current_member_count_vip` 暂写 0，待业务定义后再对接。

**圈子审核与待审/拒绝列表**

- **ext_circles.status**：0=待审核，1=已通过，2=无效，3=已拒绝，4=已取消；默认 0。审核相关字段：`apply_time`、`reviewed_at`、`reject_reason`。
- **全部圈子**（`/ext-circles/all`）：调用 `circles/list`，后端仅查 `status=1`（已通过）。
- **待审列表**（`/ext-circles/pending`）：调用 `circles/pendingList`（status=0），操作栏有详情、通过、拒绝；通过调 `circles/approve`，拒绝调 `circles/reject`（入参含 circle_id、reject_reason）。
- **拒绝列表**（`/ext-circles/rejected`）：调用 `circles/rejectedList`（status=3），操作栏仅详情，详情与待审列表共用同一详情弹窗（DetailDialog）。
- 开发或联调时若上下文断开，请先阅读仓库根目录 **AI当前任务说明.md**，其中包含当前进度与待办清单。

**诚信保申请与延期执行**

- **ext_user_ensures.status**：1=未审核，2=已审核待执行，3=执行中，4=已完成，5=已拒绝。同意后先变为 2 并写入 **approval_time**（审核通过时间），不立即做账变。
- **延期执行**：配置 `config/autoload/ext_finances.php` 中的 **credit_fetch_timeout**（秒，默认 600 即 10 分钟）。定时任务 `App\Crontab\EnsureExecuteSync` 每 5 分钟执行，扫描 status=2 且 `approval_time + credit_fetch_timeout` 已过的记录，执行解冻转积分（2 条账变）并将状态改为 4（已完成）。环境变量 `CREDIT_FETCH_TIMEOUT` 可覆盖默认值。
- **拒绝**：立即生效，status=5，扣减冻结、增加诚信保余额、写 1 条账变。

**账变触发与诚信保徽章**

- 公用逻辑在 **App\Service\AccountChangeTriggerService**：`triggerAfterCreditChange(int $userId)` 根据当前用户诚信保余额匹配诚信徽章（ext_user_badges type=2、status=1，按 integrity_score_required 降序取满足条件的最高档），更新 ext_users.credit_badge_id。**不内部启事务**，由调用方在已有事务内调用（如诚信保兑换 create 写完账变后、commit 前调用），以保证与账变、账户更新同一事务。

**用户兑换（ext-finances/exchanges）**

- **兑换类型**：1=会员等级兑换，2=诚信保兑换。新增时默认诚信保兑换。
- **会员等级兑换**：仅展示 ext_user_levels 中 is_exchangeable=true 的等级；等级高低依据 sort，sort 越大越高级；向下、同级不允许，只能向上兑换；价格通过 **POST /ht/v1/userExchanges/calcLevelUpgradePrice**（入参 user_id、target_level_id）计算：非会员（当前 payment_point=0）时价格为目标 payment_point 全价，会员升级时价格为「(目标 payment_point - 当前 payment_point) / 当前等级 validity_period × 剩余天数」（validity_period 为 0 时按 30 天），剩余天数来自 ext_users.member_expiration_time 与今天的差值；会员已过期（剩余天数≤0）时无法按差价升级。管理后台新增兑换时，会员等级下拉仅展示 sort 大于当前用户等级的选项。create 时校验价格一致、积分足够后扣积分、写 1 条积分下分账变、更新 ext_users.member_level_id 与 member_expiration_time。
- **诚信保兑换**：扣积分、加诚信保、写 2 条账变（积分下分、诚信保上分），然后调用 AccountChangeTriggerService::triggerAfterCreditChange 更新诚信徽章。
- **ext_user_exchanges.payment_method** 为 smallint：1=积分兑换，2=现金支付，3=混合支付。

**会员过期定时任务**

- 定时任务 `App\Crontab\MemberExpirationSync` 每 5 分钟执行，将 `ext_users` 中 `member_expiration_time` 已过期的会员降级为普通用户（member_level_id=10000，member_expiration_time=null）。

**ext_users 表字段约定（与 ID 关联）**

- 会员与徽章已改为 ID 关联，**不再使用**旧字段 member_level_type、identity_badge、vanity_id_badge、platform_certification_badge（已删除）。
- **当前约定**：`member_level_id`（对应 ext_user_levels.id）、`member_expiration_time`、`identity_badge_id`（ext_user_badges.id）、`vanity_badge_id`（ext_user_vanity_numbers.id）、`credit_badge_id`（诚信徽章，ext_user_badges type=2）、`pure_badge_enabled`（是否纯发徽章）、`circle_badge_id`（圈子徽章，ext_circle_badges.id）。Backend userExts/detail、updateMembership 与 Frontend 用户资料接口已按上述字段读写；等级名称、靓号判断等由 ID 查关联表得到。

---

## 六、问题排查建议

### 6.1 用户登录 / 状态相关

- 登录失败或被踢：
  - 首先确认 Synapse 中 `users.deactivated` 是否为 1（可通过数据库或 Admin API 查看）。
  - 检查管理后台是否设置为「暂停服务」（suspended），或是否在别处误调用了 `syncDeactivateToSynapse`。
- 禁止聊天无效：
  - 前端处罚或状态修改后，确认：
    - `ext_users.chat_prohibited` 是否为 true；
    - Synapse 的 Admin API `shadow_ban` 是否成功调用（`UserExtDao::applyChatProhibit` / `syncShadowBanToSynapse` 日志）。
- 禁止创建笔记无效：
  - 检查 `ext_users.note_creation_prohibited`；
  - 确认 `NotesController::createNote` 里禁止逻辑是否被命中（根据 type 和当前用户 ID）。

### 6.2 聊天消息搜索

- `/ext-chats/chats` 页面：
  - 若按「消息内容」搜索不到：
    - 确认消息是否为 `m.text/m.image/m.video` 且 `content.body / content.url` 包含关键词；
    - 后端 `ChatDao::list` 已改为对 `content::jsonb->'content'->>'body' / 'url'` 做 `ILIKE`，若需要支持更多 msgtype，可在这里继续扩展。

### 6.3 圈子与成员相关

- **全部圈子 - 修改弹窗点「确定」无反应**：检查是否配置了 `beforeSure`，且通过 `ref` 调用子组件 `handleSubmit()`，并在成功时调用 `done()` 关闭弹窗；子组件 `handleSubmit` 需返回 `Promise<boolean>` 并真正请求 `updateCircleInfo`。
- **成员管理弹窗上部分统计为 0 或不对**：确认打开弹窗时传入的 `circleData` 来自列表接口的当前行（含 `current_member_count`、`today_new_members`、`daily_active_members`、`help_posts_24h`）；列表接口见 `CircleDao::list` 的 select 字段。
- **成员列表为空或未请求**：确认已传 `circle_id`（来自 `circleData.circle_id`）；搜索框对应后端 `circleUsers/list` 的 `keyword` 参数（模糊昵称/ID/靓号）。

---

## 七、编码规范与注释要求

### 7.1 通用规范

- **命名**：
  - PHP：类名使用大驼峰（`UserExtDao`），方法与变量使用小驼峰（`changeStatus`、`noteCreationProhibited`）。
  - TypeScript/Vue：组件/Hook 文件使用大驼峰或语义名（`useUsers`、`UserList`），变量使用小驼峰。
  - SQL：表名与字段名尽量使用小写 + 下划线（`ext_user_extends`、`note_creation_prohibited`）。
- **错误返回**：
  - Backend Dao：始终返回 `['error' => string, 'data' => mixed]`，不直接 `echo/exit`。
  - Backend Controller：统一用 `responseData/responseError/responseSuccess` 封装 JSON。
  - 前端：优先使用 `message(..., { type: 'success' | 'error' })` 提示。

### 7.2 注释要求

- **新增函数必须有基本注释**：
  - PHP：
    - 在公共方法（Controller / Dao / Validation / Service）上方添加简要 PHPDoc，说明 **用途、入参要点、返回结构**，示例：
      - `/** 修改用户扩展状态，并同步登录/聊天/笔记相关标记 */`
  - TypeScript / Vue：
    - 对复杂的 hook 或关键回调（例如涉及多个接口调用、状态联动）用行内注释说明「做什么」和「为什么」。
- **关键业务节点必须有说明性注释**：
  - 涉及以下行为时，务必写清楚原因与影响：
    - 与 Synapse Admin API 的任何交互（禁言、停用账号、踢设备等）；
    - 同时更新多张表（如 `ext_users` + `ext_user_extends` + Synapse 表）；
    - 对「用户状态」含义有影响的逻辑（如 account_status 与实际登录/聊天/笔记权限的映射）。
  - 注释重点是 **业务意图和约束**，不需要逐行解释语法。

### 7.3 变更 Synapse 行为的原则

- 能用 **Admin API** 解决的场景，尽量不要直接写 Synapse 的业务表。
- 若不得不写表（如迁移脚本、初始化数据），务必：
  - 查 Synapse 源码确认字段实际用途与缓存策略；
  - 在脚本或文档中注明「这是一次性脚本，正常运行时不要调用」。

---

## 八、总结

本文件是给「人类开发者 + AI 助手」共用的高层开发说明，重点在于：

- 明确 **synapse / web-api / web-ht** 三者的角色与边界；
-.unity "用户状态""处罚""聊天/笔记权限" 等核心概念的来源与同步方式；
- 给出开发新功能与排查问题的标准路径；
- 约定基本的编码与注释规范，方便后续维护与 AI 辅助开发。

后续若有新增模块（如新扩展表、新管理页面、新 Admin API 封装），请在对应项目内补充模块级文档，并视情况同步更新本文件的相关小节。

