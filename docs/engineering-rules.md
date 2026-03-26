# 工程规范

> 本文档基于现有代码推导，是项目的 **single source of truth**。所有新代码必须遵守。

---

## 1. 核心红线（违反即阻断）

| # | 规则 | 说明 |
|---|------|------|
| 1 | `declare(strict_types=1)` | 每个 PHP 文件首行必须声明 |
| 2 | 类型声明 | 新方法必须有参数类型和返回类型 |
| 3 | 禁止调试代码 | `var_dump` / `print_r` / `echo` / `dd` / `dump` 禁止出现在 `scripts/` 以外的目录 |
| 4 | 禁止注释调试 | `// print_r(...)` / `// echo ...` 等注释掉的调试代码也不允许 |
| 5 | 依赖方向单向 | Controller → Dao / Validation / Service，禁止反向引用 |
| 6 | 域隔离 | Backend / Frontend / Internal 三域互斥，不可跨域引用 Controller 或 Dao |
| 7 | Controller 禁止写 SQL | 所有 `Db::` 调用必须在 Dao 层 |

---

## 2. 三层架构

```
Controller → Dao → DB / External
               ↘ Service（可选跨域层）
```

### 目录映射

| 路径 | 职责 |
|------|------|
| `app/Http/Backend/Controllers/` | 后台 Controller（路由前缀 `/ht/v1/…`） |
| `app/Http/Backend/Dao/` | 后台 Dao |
| `app/Http/Backend/Validations/` | 后台 Validation |
| `app/Http/Frontend/Controllers/` | 前台 Controller（路由前缀 `api/v1/…`） |
| `app/Http/Frontend/Dao/` | 前台 Dao |
| `app/Http/Internal/Controllers/` | 内部 Controller |
| `app/Service/` | 跨域 Service（可选层） |
| `app/Model/` | Eloquent Model |

### 依赖方向约束

```
Backend Controller  ─→  Backend Dao  ─→  Model / Db
                    ─→  Backend Validation
                    ─→  Service

Frontend Controller ─→  Frontend Dao ─→  Model / Db
                    ─→  Service
```

- Controller **只能**引用同域 Dao / Validation，或 `app/Service/`。
- Dao **不得**引用 Controller。
- Backend Dao **不得**引用 Frontend Dao（反之亦然）。

---

## 3. 两套 JSON 响应信封

Backend 和 Frontend 使用**不同**的响应格式，**严禁混用**。

### 3.1 Backend（`/ht/v1/`）

继承 `BackendController`，使用实例方法：

```php
// 成功（带数据）
return $this->responseData($data);
// → { "success": true, "message": null, "data": mixed }

// 成功（不带数据）
return $this->responseSuccess('操作成功');
// → { "success": true, "message": "操作成功", "data": null }

// 失败
return $this->responseError('错误信息');
// → { "success": false, "message": "错误信息", "data": null }
```

### 3.2 Frontend（`api/v1/`）

使用 `BaseJsonTrait`，调用静态方法：

```php
// 成功（带数据）
return self::jsonResult($data);
// → { "code": 0, "message": "", "data": array }

// 成功（不带数据）
return self::jsonOk('操作成功');
// → { "code": 0, "message": "操作成功" }

// 失败
return self::jsonErr('错误信息', 500);
// → { "code": 500, "message": "错误信息" }
```

### 速查表

| 场景 | Backend 方法 | Frontend 方法 |
|------|-------------|---------------|
| 返回数据 | `$this->responseData($data)` | `self::jsonResult($data)` |
| 仅返回成功 | `$this->responseSuccess($msg)` | `self::jsonOk($msg)` |
| 返回错误 | `$this->responseError($msg)` | `self::jsonErr($msg, $code)` |

---

## 4. Dao 规范

- **全部使用静态方法**，Dao 类不需要实例化。
- **统一返回格式**：`['error' => string, 'data' => ...]`
  - `error` 为空字符串 `''` 表示成功
  - `error` 非空表示失败，值为错误消息
- **SQL 查询**使用 `Hyperf\DbConnection\Db` 的 Query Builder 或 raw SQL。
- **复杂查询**建议添加 PHPDoc 标注返回结构：`@return array{error: string, data?: ...}`

示例：

```php
public static function list(array $request): array
{
    $query = Db::table('some_table')->orderBy('id', 'DESC');

    // 条件过滤
    if (!empty($request['keyword'])) {
        $query->where('name', 'like', '%' . $request['keyword'] . '%');
    }

    // 分页
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
```

---

## 5. Validation 规范

- 提供 `public static function validate(array $request): string` 方法。
- 返回空字符串 `''` 表示验证通过。
- 返回非空字符串表示第一条错误消息。
- 常量正则放在类顶部用 `private const` 声明。
- **不允许空的 validate 存根**——要么实现完整逻辑，要么不创建 Validation 类。

示例：

```php
class SomeValidation
{
    private const NAME_REGEX = '/^[a-zA-Z][a-zA-Z0-9]*$/';

    public static function validate(array $request): string
    {
        $name = $request['name'] ?? '';
        if (strlen($name) < 2 || strlen($name) > 50) {
            return '名称长度必须在 2-50 位之间';
        }
        if (!preg_match(self::NAME_REGEX, $name)) {
            return '名称格式不正确';
        }
        return '';
    }
}
```

---

## 6. Controller 标准流程

Backend Controller 的标准 Action 流程：

```php
#[PostMapping(path: "list")]
public function list(RequestInterface $request)
{
    try {
        $data = $request->all();
        if (empty($data)) {
            return $this->responseError('无效的请求体');
        }

        // 1. 提取并规整请求参数
        $requestData = [
            'keyword' => $data['keyword'] ?? '',
            'page' => (int)($data['page'] ?? 1),
            'pageSize' => (int)($data['pageSize'] ?? 10),
        ];

        // 2. 调用 Validation
        $validationError = SomeValidation::validate($requestData);
        if ($validationError !== '') {
            return $this->responseError($validationError);
        }

        // 3. 调用 Dao
        $result = SomeDao::list($requestData);
        if ($result['error'] !== '') {
            return $this->responseError($result['error']);
        }

        // 4. 返回数据
        return $this->responseData($result['data']);
    } catch (\Exception $e) {
        return $this->responseError($e->getMessage());
    }
}
```

---

## 7. 命名规范

| 类型 | 命名规则 | 示例 |
|------|---------|------|
| Controller | PascalCase 复数 + `Controller` | `UserReportsController` / `AdminsController` |
| Dao | PascalCase 单数 + `Dao` | `UserReportDao` / `AdminDao` |
| Validation | PascalCase 单数 + `Validation` | `UserReportValidation` / `IndexValidation` |
| Model | PascalCase 单数（映射表名） | `Admin` / `User` / `BlockedIp` |
| Service | PascalCase 描述性名称 + `Service` | `ProfileSyncService` / `SynapseMediaService` |

### 文件位置

| 类型 | 路径 |
|------|------|
| Backend Controller | `app/Http/Backend/Controllers/{Name}Controller.php` |
| Backend Dao | `app/Http/Backend/Dao/{Name}Dao.php` |
| Backend Validation | `app/Http/Backend/Validations/{Name}Validation.php` |
| Frontend Controller | `app/Http/Frontend/Controllers/{Name}Controller.php` |
| Frontend Dao | `app/Http/Frontend/Dao/{Name}Dao.php` |
| Model | `app/Model/{Name}.php` |
| Service | `app/Service/{Name}Service.php` |

---

## 8. Model 规范

- 继承 `Hyperf\DbConnection\Model\Model`（或项目 base Model）。
- 显式声明 `$table`（不依赖框架猜测）。
- 如使用非标准时间戳列名，显式覆盖 `CREATED_AT` / `UPDATED_AT` 常量。
- 使用 `$casts` 声明字段类型转换。
- 日期格式统一使用 Unix 时间戳：`protected string $dateFormat = 'U';`

---

## 9. 路由注解

使用 Hyperf 注解路由（非配置文件路由）：

```php
#[Controller(prefix: "/ht/v1/resourceName")]   // Backend
#[Controller(prefix: "api/v1/resourceName")]    // Frontend

#[PostMapping(path: "actionName")]
#[GetMapping(path: "actionName")]
```

---

## 10. 技术栈参考

| 组件 | 版本 / 说明 |
|------|------------|
| PHP | ^8.1 |
| 框架 | Hyperf ~3.0（基于 Swoole） |
| 运行时 | Swoole >=5.0 |
| 数据库 | PostgreSQL（通过 `hyperf/database-pgsql`） |
| 缓存 | Redis（通过 `hyperf/redis` + `hyperf/cache`） |
| 搜索 | Elasticsearch（通过 `hyperf/elasticsearch`） |
| 模板 | Blade（通过 `duncan3dc/blade`） |
| 静态分析 | PHPStan |
| 代码风格 | PHP-CS-Fixer（PSR-12 基线） |

### Swoole 注意事项

由于运行在 Swoole 协程环境下：
- **禁止使用全局可变状态**（`global` 变量、类静态可变属性）——协程间会互相污染。
- Dao 使用静态方法是安全的，因为方法内的局部变量在每次调用时独立分配。
- 需要共享状态时使用 `Hyperf\Utils\Context`（协程上下文）或依赖注入。
