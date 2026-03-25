# 圈子徽章列表接口 - 前端对接文档

## 一、接口概述

提供**圈子徽章**配置列表，供 C 端展示用户所属圈子徽章图标。数据来源于表 `ext_circle_badges`（仅返回 status=1 的启用徽章）。

**典型使用场景**：用户资料（profile）接口返回 `circle_badge_id`，前端调用本接口获取完整徽章列表，再根据 `circle_badge_id` 查找对应徽章的 `icon_url` 进行展示。

---

## 二、接口详情

### 2.1 圈子徽章列表

| 项目 | 说明 |
|------|------|
| **请求方式** | GET |
| **接口路径** | `/api/v1/configs/circleBadges` |
| **鉴权方式** | Bearer Token（Header） |

### 2.2 请求头

| 参数名 | 必填 | 说明 |
|--------|------|------|
| Authorization | 是 | `Bearer <access_token>`，登录后获取的 access_token |

### 2.3 成功响应

```json
{
  "code": 0,
  "message": "",
  "data": {
    "badges": [
      {
        "id": 1000,
        "name": "天使用户",
        "icon_url": "/static/badges/circle/angel_user.png",
        "remark": "天使用户专属徽章",
        "sort_order": 1
      },
      {
        "id": 1001,
        "name": "荣誉体验官",
        "icon_url": "/static/badges/circle/honor_trial.png",
        "remark": "",
        "sort_order": 2
      }
    ]
  }
}
```

### 2.4 data.badges[] 字段说明

| 字段 | 类型 | 说明 |
|------|------|------|
| id | integer | 徽章 ID，与 profile 接口的 `circle_badge_id` 对应 |
| name | string | 徽章名称 |
| icon_url | string | 徽章图标 URL（相对路径，需拼接域名或 CDN 前缀） |
| remark | string | 备注说明 |
| sort_order | integer | 排序权重，数值越小越靠前 |

### 2.5 失败响应

```json
{
  "code": 500,
  "message": "错误信息"
}
```

### 2.6 常见错误

| message | 说明 |
|---------|------|
| Access token is required / 未登录或 token 无效 | 未携带或无效的 Authorization |

---

## 三、与 profile 接口的配合使用

### 3.1 数据流

1. 调用 **GET /api/v1/users/profile** 获取用户资料，其中包含 `circle_badge_id`、`circle_badge_name`
2. 调用 **GET /api/v1/configs/circleBadges** 获取圈子徽章列表（含 `icon_url`）
3. 根据 `circle_badge_id` 在 `badges` 数组中查找对应项，取 `icon_url` 展示图标

### 3.2 profile 返回的圈子徽章字段

| 字段 | 类型 | 说明 |
|------|------|------|
| circle_badge_id | integer | 用户所属圈子徽章 ID，0 表示无 |
| circle_badge_name | string | 圈子徽章名称（可直接展示，无需查表） |

### 3.3 前端展示逻辑示例

```javascript
// 1. 获取用户 profile
const profileRes = await fetch('/api/v1/users/profile?id=' + userId, {
  headers: { 'Authorization': 'Bearer ' + accessToken }
});
const { data: profile } = await profileRes.json();

// 2. 获取圈子徽章列表（可缓存，列表变更不频繁）
const badgesRes = await fetch('/api/v1/configs/circleBadges', {
  headers: { 'Authorization': 'Bearer ' + accessToken }
});
const { data: { badges } } = await badgesRes.json();

// 3. 根据 circle_badge_id 查找图标
const circleBadgeId = profile.circle_badge_id || 0;
const badge = badges.find(b => b.id === circleBadgeId);
const iconUrl = badge ? (CDN_PREFIX + badge.icon_url) : null;

// 4. 展示
// - 若有 iconUrl，显示徽章图标
// - 若无，可显示 circle_badge_name 或隐藏
```

---

## 四、调用示例

### 4.1 cURL

```bash
curl -X GET "https://your-domain.com/api/v1/configs/circleBadges" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### 4.2 JavaScript (fetch)

```javascript
const res = await fetch('/api/v1/configs/circleBadges', {
  headers: { 'Authorization': 'Bearer ' + accessToken }
});
const { code, data } = await res.json();
if (code === 0) {
  const circleBadges = data.badges; // 圈子徽章列表
}
```

### 4.3 建议

- **缓存**：圈子徽章列表变更频率低，建议前端缓存（如内存或本地存储），减少重复请求
- **图标拼接**：`icon_url` 为相对路径，展示时需拼接静态资源域名或 CDN 前缀

---

## 五、版本记录

| 日期 | 说明 |
|------|------|
| 2025-03-11 | 初始版本，圈子徽章列表接口说明 |
