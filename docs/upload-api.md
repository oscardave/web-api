# 上传接口说明

## 概述

当前 web-api 提供的上传接口为**后台接口**，路径前缀为 `/ht/v1/upload`，需后台鉴权。若 C 端/前台也需要上传能力，可复用同一逻辑或由网关/代理转发至该接口（以实际部署与鉴权方式为准）。

---

## 上传图片

### 基本信息

| 项目 | 说明 |
|------|------|
| 请求方式 | `POST` |
| 请求路径 | `/ht/v1/upload/image` |
| Content-Type | `multipart/form-data` |
| 鉴权 | 后台鉴权（见 Backend 鉴权约定） |

### 请求参数

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| file | File | 是 | 上传的图片文件（表单字段名默认为 `file`） |
| field | string | 否 | 表单文件字段名，不传则使用 `file` |

### 约束说明

- **仅允许图片**：服务端按文件内容检测 MIME，仅允许以下类型：
  - PNG（image/png）
  - JPEG/JPG（image/jpeg、image/jpg）
  - GIF（image/gif）
  - BMP（image/bmp）
  - WebP（image/webp）
- **大小限制**：单文件约 **5MB**（5,242,880 字节）。
- **存储路径**：保存到 `UPLOAD_DIR` 配置目录（默认 `public/uploads`）下按月份子目录 `YYYYMM`，文件名格式：`YmdHisT{4位随机}.{扩展名}`，接口返回的为**相对路径**，如 `/uploads/202503/20250310120000Tabcd.jpg`。

### 请求示例

```bash
# 使用 curl（字段名为 file）
curl -X POST "https://your-domain/ht/v1/upload/image" \
  -H "Authorization: Bearer <后台 token>" \
  -F "file=@/path/to/image.jpg"

# 指定字段名为 avatar
curl -X POST "https://your-domain/ht/v1/upload/image" \
  -H "Authorization: Bearer <后台 token>" \
  -F "field=avatar" \
  -F "avatar=@/path/to/image.png"
```

### 响应说明

**成功（HTTP 200）：**

```json
{
  "code": 0,
  "message": "",
  "data": {
    "url": "/uploads/202503/20250310120000Tabcd.jpg",
    "fileName": "/uploads/202503/20250310120000Tabcd.jpg",
    "fileSize": 123456
  }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| url | string | 相对路径，用于展示或写入业务表时拼接域名 |
| fileName | string | 与 url 一致，相对路径 |
| fileSize | integer | 文件大小（字节） |

**失败（如 HTTP 200 但 code 非 0 或 4xx）：**

```json
{
  "code": 非0,
  "message": "错误描述，如：上传文件大小超出限制",
  "data": null
}
```

常见错误信息示例：

- `无法获取上传文件信息`：未收到有效文件或字段名不符
- `上传文件无效`：文件对象无效
- `上传文件大小超出限制`：超过约 5MB
- `无法识别文件类型`：无法根据内容识别 MIME
- `不允许的文件类型: xxx`：非允许的图片类型
- `上传目录不可用`：服务端存储目录不可写
- `文件保存失败`：写入磁盘失败

### 使用说明

- 前端展示时需将返回的 `url` 与站点域名（或配置的静态资源域名）拼接，例如：`https://your-domain/uploads/202503/xxx.jpg`。
- 若业务接口（如修改头像）需要“路径”，一般传该相对路径（如 `url` 或 `fileName`）即可，具体以各业务接口文档为准。

---

## 其他说明

- **通用上传逻辑**：实现位于 `App\Common\Uploader`，支持 `imageOnly` 控制是否仅允许图片；当前对外仅暴露了“仅图片”的上传接口。
- **扩展类型**：若需支持非图片（如 mp4），需在后台新增接口并调用 `Uploader::upload($request, $field, false)`，且需确认大小限制（约 20MB）与安全策略。
