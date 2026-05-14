# SUOJIAN 前端 API 约定

## 一、API 调用模式

| 模式 | 用途 | 文件 |
|------|------|------|
| `utils/http.js` | **所有视图内直调** | 所有 views/*/*.vue |
| `api/request.js` | **API 模块基类** | 所有 api/*.js |
| 原生 axios | **仅 login**（需跳过拦截器） | api/index.js |

### 禁止的模式
- ❌ CDN axios (`cdn.jsdelivr.net/npm/axios`) — 不带 token
- ❌ 视图内写 axios 调用 — 必须通过 api/ 模块或 utils/http

## 二、Token 传递

- 存储：`sessionStorage.getItem('token')`
- 方式：`Authorization: Bearer <token>` header
- 位置：`utils/http.js` 的 request interceptor 自动附加
- login 例外：使用原生 axios（此时无 token，因为是获取 token）

## 三、响应格式

```
后端返回: { code: 0, msg: "success", data: { ... } }
         ↓
request.js 拦截器: code===0 → 返回 data (解包)
         ↓
API 模块 (api/*.js): 直接 return res（res 已= data）
         ↓
视图: 直接使用 res.xxx
```

### ⚠️ 不要二次解包
```js
// ❌ 错误 — 拦截器已解包
const res = await api.get('/xxx')
if (res.code === 0) return res.data  // res.code === undefined！

// ✅ 正确
const res = await api.get('/xxx')
return res  // 拦截器已解包
```

## 四、POST 请求

ThinkPHP 3.2 的 `I()` 只读 `$_POST`/`$_GET`，**不支持 JSON body**。

```js
// ✅ 正确
const params = new URLSearchParams({ field1: val1, field2: val2 })
api.post('/url', params.toString(), {
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
})

// ❌ 错误
api.post('/url', { field1: val1 })
```

## 五、错误处理

```js
// 视图级别
try {
  const data = await someApi()
  // 更新数据
} catch (e) {
  console.error('[模块名] 操作失败:', e)  // 必须 log
  ElMessage.error('加载失败')             // 友好的用户提示
}
```

## 六、API 模块组织结构

```
src/api/
├── index.js          # 统一导出 + login（原生axios）
├── request.js        # axios 实例 + 拦截器
├── article.js        # 文章 + 分类 + 评论相关
├── dashboard.js      # 仪表盘
├── users.js          # 用户管理
├── coupons.js        # 优惠券
├── packages.js       # 套餐
├── leads.js          # 线索
├── homeworks.js      # 作业
├── enrollments.js    # 招生
├── marketings.js     # 营销
├── feedbacks.js      # 反馈
├── finance.js        # 财务
├── stats.js          # 统计
├── bubbles.js        # 冒泡
├── comment.js        # 评论（独立接口）
├── settings.js       # 系统设置
```

所有视图 `import { xxx } from '../../api'`（通过 index.js 统一导出）。
