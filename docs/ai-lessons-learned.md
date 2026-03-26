# AI 踩坑记录

> AI 写代码过程中遇到的问题及修复方式。每次踩坑后在此追加条目，避免重复犯错。

---

## 模板

每条记录使用以下格式：

```markdown
### YYYY-MM-DD · 简短标题

**场景**：什么任务触发了这个问题
**问题**：具体发生了什么错误
**根因**：为什么会出错
**修复**：如何修复的
**教训**：以后如何避免
```

---

## 记录

### 2026-03-26 · Backend/Frontend 信封混用

**场景**：为 Frontend Controller 编写新接口
**问题**：错误使用了 `$this->responseData()` 而非 `self::jsonResult()`，返回格式与前端约定不一致
**根因**：未区分 Backend 继承 `BackendController`（实例方法）和 Frontend 使用 `BaseJsonTrait`（静态方法）
**修复**：Frontend Controller 统一使用 `self::jsonResult()` / `self::jsonErr()` / `self::jsonOk()`
**教训**：写代码前先确认 Controller 属于哪个域（Backend/Frontend/Internal），查阅 `docs/engineering-rules.md` §3

---

### 2026-03-26 · 跨域 Dao 引用

**场景**：Backend Controller 需要查询用户数据
**问题**：引用了 `App\Http\Frontend\Dao\UserDao`，违反域隔离原则
**根因**：不了解三域互斥规则
**修复**：在 Backend Dao 层创建独立的查询方法，或使用 `app/Service/` 作为跨域桥梁
**教训**：Backend/Frontend/Internal 三域的 Controller 和 Dao 不可交叉引用，共享逻辑放 Service 层

---

### 2026-03-26 · Swoole 全局状态污染

**场景**：使用类静态属性缓存配置数据
**问题**：协程环境下多个请求共享同一个 Worker 进程，静态属性在请求间互相污染
**根因**：Swoole 常驻内存模型与 FPM 请求隔离模型不同
**修复**：使用 `Hyperf\Utils\Context` 存储请求级数据，或使用 DI 容器
**教训**：禁止 `global` 变量和类静态可变属性；Dao 的静态方法安全是因为只用局部变量

---

### 2026-03-26 · 缺少 declare(strict_types=1)

**场景**：创建新的 PHP 文件
**问题**：忘记在文件首行添加 `declare(strict_types=1)`，PHPStan 检查失败
**根因**：模板/习惯缺失
**修复**：补充声明
**教训**：每个 PHP 文件第一行必须是 `declare(strict_types=1)`，`ai-deliver.sh` 会自动检查

---

### 2026-03-26 · Controller 内写 SQL

**场景**：快速实现一个简单查询接口
**问题**：直接在 Controller 方法内使用 `Db::table()->...`，违反分层原则
**根因**：图省事跳过 Dao 层
**修复**：将 SQL 查询移至对应 Dao 的静态方法中
**教训**：所有数据库操作必须在 Dao 层，Controller 只负责参数提取、验证调用和响应封装

---

### 2026-03-26 · Dao 返回格式不统一

**场景**：新建 Dao 方法
**问题**：返回了 `['success' => true, 'data' => ...]` 而非约定格式
**根因**：未查阅 Dao 规范
**修复**：统一使用 `['error' => '', 'data' => ...]`，`error` 空字符串表示成功
**教训**：Dao 返回格式是强约定：`['error' => string, 'data' => ...]`，见 `docs/engineering-rules.md` §4
