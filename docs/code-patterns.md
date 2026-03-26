# 黄金参考代码模式

> 本文档从现有代码库中提炼而来，是 AI 生成代码时的**权威参考**。
> 所有新代码必须遵循这些模式，不得自行发明新结构。

---

## 1. Backend Controller

**源文件**：`app/Http/Backend/Controllers/UserReportsController.php`

**要点**：

- 继承 `BackendController`
- 使用 `#[Controller(prefix: "/ht/v1/资源名")]` 注解
- 使用 `#[PostMapping(path: "方法名")]` 注解
- 调用链：提取参数 → Validation → Dao → 响应
- 响应方法：`$this->responseData($data)` / `$this->responseError($msg)` / `$this->responseSuccess($msg)`
- 返回信封格式：`{ "success": bool, "message": ?string, "data": mixed }`
- 整体包裹 `try/catch`

```php
<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserReportDao;
use App\Http\Backend\Validations\UserReportValidation;

#[Controller(prefix: "/ht/v1/userReports")]
class UserReportsController extends BackendController
{
    #[PostMapping(path: "list")]
    public function list(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            // 1) 提取并规范化参数
            $requestData = [
                'username' => $data['username'] ?? '',
                'report_date_start' => $data['report_date_start'] ?? '',
                'report_date_end' => $data['report_date_end'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            // 2) Validation
            $validationError = UserReportValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            // 3) Dao
            $result = UserReportDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            // 4) 返回数据
            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
```

---

## 2. Frontend Controller

**源文件**：`app/Http/Frontend/Controllers/UserFeedbacksController.php`

**要点**：

- 继承 `FrontendController`（已 `use BaseJsonTrait`）
- 使用 `#[Controller(prefix: "api/v1/资源名")]` 注解（注意**无前导斜杠**）
- 需鉴权的接口先调 `$this->getAuthenticatedUser($request)`
- 响应方法：`self::jsonResult($data)` / `self::jsonErr($msg)` / `self::jsonOk($msg)` / `self::jsonArray($data, $msg)`
- 返回信封格式：`{ "code": int, "message": string, "data"?: mixed }`
- **绝对不可**使用 `$this->responseData()` 等 Backend 方法

```php
<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Frontend\Dao\UserFeedbacks as UserFeedbacksDao;

#[Controller(prefix: "api/v1/feedbacks")]
class UserFeedbacksController extends FrontendController
{
    #[PostMapping(path: "submit")]
    public function submit(RequestInterface $request): mixed
    {
        // 1) 鉴权
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();

        // 2) 参数校验
        if (!isset($data['type'])) {
            return self::jsonErr('Missing key \'type\'');
        }
        if (!isset($data['content'])) {
            return self::jsonErr('Missing key \'content\'');
        }

        // 3) 提取参数
        $type = (int)($data['type'] ?? 0);
        $content = $data['content'] ?? '';

        // 4) 调用 Dao
        $result = UserFeedbacksDao::submitFeedback(
            $auth['user_id'],
            $type,
            $content
        );

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonArray($result['data'], '反馈提交成功');
    }
}
```

---

## 3. Backend Dao

**源文件**：`app/Http/Backend/Dao/UserReportDao.php`

**要点**：

- 命名空间：`App\Http\Backend\Dao`
- 所有方法为 `public static`
- 返回值签名：`array{error: string, data?: mixed}`
  - `error` 空字符串 `''` = 成功
  - `error` 非空 = 失败信息
- SQL 查询使用 `Hyperf\DbConnection\Db` 查询构建器
- 分页查询模式：先 count → 再 limit/offset → 返回 `records` + `total`

```php
<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserReportDao
{
    /**
     * @param array $request
     * @return array{error: string, data: array{records: array, total: int}}
     */
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_reports')
            ->select('id', 'report_date', 'new_users_today', 'total_users_cumulative')
            ->orderBy('report_date', 'DESC');

        if (!empty($request['report_date_start'])) {
            $query->where('report_date', '>=', $request['report_date_start']);
        }
        if (!empty($request['report_date_end'])) {
            $query->where('report_date', '<=', $request['report_date_end']);
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get()->toArray();

        return [
            'error' => '',
            'data' => [
                'records' => $rows,
                'total' => $total,
            ],
        ];
    }
}
```

---

## 4. Frontend Dao

**源文件**：`app/Http/Frontend/Dao/UserFeedbacks.php`

**要点**：

- 命名空间：`App\Http\Frontend\Dao`
- 遵循与 Backend Dao 相同的 `['error' => '', 'data' => ...]` 返回契约
- 业务校验放在 Dao 内部（如类型合法性、内容非空）
- 数据库操作包裹 `try/catch`，异常时返回 error 而非抛出

```php
<?php
declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;

class UserFeedbacks
{
    /**
     * @param string $matrixUserId
     * @param int $type
     * @param string $content
     * @return array{error: string, data: array}
     */
    public static function submitFeedback(
        string $matrixUserId,
        int $type,
        string $content
    ): array {
        if ($type >= 100 || $type < 0) {
            return ['error' => '反馈类型无效', 'data' => []];
        }

        $content = trim($content);
        if (empty($content)) {
            return ['error' => '反馈内容不能为空', 'data' => []];
        }

        $extUser = Db::table('ext_users')
            ->select('id')
            ->where('user_id', $matrixUserId)
            ->where('status', 1)
            ->first();

        if (!$extUser) {
            return ['error' => '用户不存在', 'data' => []];
        }

        try {
            $feedbackId = Db::table('ext_user_feedbacks')->insertGetId([
                'type' => $type,
                'content' => $content,
                'user_id' => $extUser->id,
                'status' => 1,
            ]);

            return [
                'error' => '',
                'data' => [
                    'id' => $feedbackId,
                    'type' => $type,
                    'content' => $content,
                ],
            ];
        } catch (\Exception $e) {
            return ['error' => '提交失败：' . $e->getMessage(), 'data' => []];
        }
    }
}
```

---

## 5. Validation

**源文件**：`app/Http/Backend/Validations/IndexValidation.php`

**要点**：

- 命名空间：`App\Http\Backend\Validations` 或 `App\Http\Frontend\Validations`
- 单一静态方法 `validate(array $request): string`
- 返回空字符串 `''` 表示通过
- 返回非空字符串 = 具体的中文错误信息
- 正则常量使用 `private const` 定义
- **不允许空 validate 存根**（要么有实际校验逻辑，要么不创建 Validation 类）

```php
<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class IndexValidation
{
    private const NAME_REGEX = '/^[a-zA-Z][a-zA-Z0-9]*$/';
    private const VERIFY_CODE_REGEX = '/^[0-9]{4}$/';

    /**
     * @param array $request
     * @return string 空字符串表示验证通过
     */
    public static function validate(array $request): string
    {
        $name = $request['name'] ?? '';
        $password = $request['password'] ?? '';
        $verifyCode = $request['verifyCode'] ?? '';

        if (strlen($name) < 6 || strlen($name) > 20) {
            return '用户名称必须是 6-20 位';
        }

        if (!preg_match(self::NAME_REGEX, $name)) {
            return '用户名称必须是英文字母开头只能有字母和数字';
        }

        if (strlen($password) < 6 || strlen($password) > 20) {
            return '密码必须是 6-20 位';
        }

        if (strlen($verifyCode) != 4) {
            return '验证码必须是4位数字';
        } elseif (!preg_match(self::VERIFY_CODE_REGEX, $verifyCode)) {
            return '验证码必须是数字';
        }

        return '';
    }
}
```

---

## 6. Model

**源文件**：`app/Model/Admin.php`

**要点**：

- 继承 `App\Model\Model`（项目基类，已含 `Cacheable` trait）
- 使用 `@property` 注解声明所有字段及类型
- 显式声明 `$table`、`$fillable`、`$casts`
- 时间戳列名如非默认 `created_at`/`updated_at`，需重写 `CREATED_AT`/`UPDATED_AT` 常量
- `$dateFormat = 'U'` 表示 Unix 时间戳存储
- 业务方法返回布尔值，保持语义清晰

```php
<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $last_login_time
 * @property string $last_login_ip
 * @property int $status
 * @property int $role_id
 * @property int $create_time
 * @property int $update_time
 * @property string $allow_ip
 */
class Admin extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected ?string $table = 'admin';

    protected array $fillable = [];

    protected array $casts = [
        'id' => 'integer',
        'last_login_time' => 'integer',
        'status' => 'integer',
        'role_id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    protected ?string $dateFormat = 'U';

    public function allowLogin(string $visitIP): bool
    {
        if (!$this->enable()) {
            return false;
        }

        $arr = explode(',', $this->allow_ip);
        foreach ($arr as $r) {
            $allowIP = trim($r);
            if ($allowIP == $visitIP) {
                return true;
            }
        }

        return false;
    }

    public function enable(): bool
    {
        return $this->status == 1;
    }
}
```

---

## 7. Service（可选跨域层）

**源文件**：`app/Service/ProfileSyncService.php`

**要点**：

- 命名空间：`App\Service`
- 用于**跨域逻辑**（多张表、多个业务域协同）
- 方法为 `public static`，返回 `void` 或标准 `['error' => '', 'data' => ...]`
- 不内部启事务，由调用方管理事务
- Service 仅被 Controller 或 Dao 调用，不反向引用 Controller

```php
<?php
declare(strict_types=1);

namespace App\Service;

use Hyperf\DbConnection\Db;

class ProfileSyncService
{
    /**
     * 同步头像到所有展示头像的业务表
     *
     * @param string $matrixUserId Matrix 用户 ID
     * @param string $avatarUrl 头像 URL
     */
    public static function syncAvatar(string $matrixUserId, string $avatarUrl): void
    {
        $avatarForTables = $avatarUrl === '' ? '' : $avatarUrl;
        Db::table('ext_circle_users')
            ->where('user_id', $matrixUserId)
            ->update(['user_avatar_url' => $avatarForTables]);
        Db::table('ext_group_users')
            ->where('user_id', $matrixUserId)
            ->update(['user_avatar_url' => $avatarForTables]);
        // ... 其他业务表
    }
}
```

---

## 8. 响应信封速查

### Backend（`/ht/v1/` 路由 — 继承 `BackendController`）

| 方法 | 场景 | 输出 |
| --- | --- | --- |
| `$this->responseData($data)` | 成功，有数据 | `{ "success": true, "message": null, "data": ... }` |
| `$this->responseError($msg)` | 失败 | `{ "success": false, "message": "...", "data": null }` |
| `$this->responseSuccess($msg)` | 成功，无数据 | `{ "success": true, "message": "...", "data": null }` |

### Frontend（`api/v1/` 路由 — 使用 `BaseJsonTrait`）

| 方法 | 场景 | 输出 |
| --- | --- | --- |
| `self::jsonResult($data)` / `self::jsonArray($data, $msg)` | 成功，有数据 | `{ "code": 0, "message": "", "data": [...] }` |
| `self::jsonOk($msg)` | 成功，无数据 | `{ "code": 0, "message": "..." }` |
| `self::jsonErr($msg)` | 失败 | `{ "code": 500, "message": "..." }` |
| `self::jsonExpireErr($msg)` | Token 过期 | `{ "code": 501, "message": "..." }` |

> **红线**：Backend Controller 内禁止使用 `self::jsonResult` 等 Frontend 方法；Frontend Controller 内禁止使用 `$this->responseData` 等 Backend 方法。

---

## 9. 命名规范

| 类型 | 规则 | 示例 |
| --- | --- | --- |
| Controller | PascalCase 复数 + Controller | `UserReportsController` |
| Dao（Backend） | PascalCase + Dao 后缀 | `UserReportDao` |
| Dao（Frontend） | PascalCase（资源名） | `UserFeedbacks`、`Users` |
| Validation | PascalCase + Validation 后缀 | `IndexValidation`、`UserReportValidation` |
| Model | PascalCase 单数 | `Admin`、`User` |
| Service | PascalCase + Service 后缀 | `ProfileSyncService` |

---

## 10. 目录映射

```
app/Http/Backend/Controllers/   → 后台 Controller（prefix: /ht/v1/…）
app/Http/Backend/Dao/           → 后台 Dao
app/Http/Backend/Validations/   → 后台 Validation
app/Http/Frontend/Controllers/  → 前台 Controller（prefix: api/v1/…）
app/Http/Frontend/Dao/          → 前台 Dao
app/Http/Internal/Controllers/  → 内部 Controller
app/Service/                    → 跨域 Service（可选层）
app/Model/                      → Eloquent Model
```

---

## 11. 常见调用链

### Backend 查询列表

```
Controller.list()
  → Validation::validate($requestData)
  → Dao::list($requestData)
  → $this->responseData($result['data'])
```

### Frontend 提交数据

```
Controller.submit()
  → $this->getAuthenticatedUser($request)
  → 参数校验
  → Dao::submitXxx(...)
  → self::jsonArray($result['data'], '成功消息')
```

### 跨域同步

```
Controller / Dao
  → Service::syncXxx(...)   ← 仅在需要跨多张业务表时使用
```
