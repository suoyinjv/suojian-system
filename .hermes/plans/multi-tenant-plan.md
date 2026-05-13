# SchoolCMS 轻量多租户改造方案（方案 B）
## 预计工时：3-5 人天

---

## 一、架构决策

```
                      ┌─────────────────────┐
                      │   Nginx 泛域名解析    │
                      │  *.yourdomain.com    │
                      └────────┬────────────┘
                               │ Host 头
                      ┌────────▼────────────┐
                      │   tenant.php 入口    │
                      │  识别域名→campus_id  │
                      └────────┬────────────┘
                               │
                      ┌────────▼────────────┐
                      │  CommonController    │
                      │  自动追加 campus_id  │
                      │  过滤所有 DB 查询     │
                      └─────────────────────┘
```

### 租户识别方式（选一种）

| 方式 | 复杂度 | 说明 |
|------|--------|------|
| **A. 子域名** `school1.your.com` | 2天 | 需 DNS+Nginx，最专业 |
| **B. URL路径** `your.com/?campus_id=1` | 0.5天 | 最简单，URL 带参数 |
| **C. 独立登录入口** `your.com/admin/school1/` | 1天 | Nginx rewrite 实现 |

**推荐组合：** 主方案用 A（子域名），降级方案用 B（URL 参数）。

---

## 二、数据库变更

### 2.1 扩展 `sc_campus` 表

```sql
ALTER TABLE sc_campus ADD COLUMN (
  domain       VARCHAR(100) DEFAULT ''  COMMENT '绑定的域名（不含协议）',
  site_name    VARCHAR(60)  DEFAULT ''  COMMENT '机构站点名称',
  logo         VARCHAR(255) DEFAULT ''  COMMENT '机构LOGO',
  theme_color  VARCHAR(7)   DEFAULT '#4e73df' COMMENT '主题色',
  icp          VARCHAR(60)  DEFAULT ''  COMMENT '备案号',
  contact_name VARCHAR(30)  DEFAULT ''  COMMENT '联系人',
  contact_phone VARCHAR(20) DEFAULT ''  COMMENT '联系电话',
  expire_date  INT(11)      DEFAULT 0   COMMENT '到期时间戳',
  UNIQUE KEY `uk_domain` (`domain`)
);
```

### 2.2 `sc_admin` 表加 `campus_id`

```sql
ALTER TABLE sc_admin ADD COLUMN (
  campus_id   INT(11) UNSIGNED DEFAULT 0  COMMENT '所属校区（0=超管）',
  is_super    TINYINT(1)      DEFAULT 0   COMMENT '是否超管'
);
```

### 2.3 将 `sc_config` 按租户拆分

保持 `sc_config` 存储全局配置，新建 `sc_campus_config` 存储租户级配置：

```sql
CREATE TABLE sc_campus_config (
  id         INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campus_id  INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '校区ID（0=全局）',
  name       VARCHAR(60) NOT NULL COMMENT '配置键名',
  value      TEXT COMMENT '配置值',
  create_time INT(11) UNSIGNED DEFAULT 0,
  update_time INT(11) UNSIGNED DEFAULT 0,
  UNIQUE KEY `uk_campus_name` (`campus_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='租户配置';
```

### 迁移已有管理员

```sql
-- 将现有 admin 设为超管
UPDATE sc_admin SET is_super=1, campus_id=0 WHERE id=1;
```

---

## 三、租户识别层（2天）

### 3.1 Nginx 泛域名配置

```nginx
# /etc/nginx/conf.d/schoolcms-tenant.conf
server {
    listen 80;
    server_name ~^(?<tenant>[^.]+)\.yourdomain\.com$;
    root /var/www/schoolcms;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param TENANT_NAME $tenant;        # ← 传递租户名给 PHP
        include fastcgi_params;
    }
}
```

### 3.2 PHP 租户识别（`tenant.php` 入口或 `CommonController` 中）

```php
// Application/Common/Common/function.php

function GetTenantCampusId() {
    // 优先级：1. URL参数  2. 子域名  3. Session（已登录）
    
    // 1. URL参数方式（降级方案）
    $campus_id = I('campus_id', 0, 'intval');
    if ($campus_id > 0) return $campus_id;
    
    // 2. nginx 传来的子域名
    $tenant_name = $_SERVER['TENANT_NAME'] ?? '';
    if (!empty($tenant_name)) {
        $campus = M('Campus')->where(['domain' => $tenant_name])->find();
        if ($campus && $campus['status'] == 1) {
            session('tenant_campus_id', $campus['id']);
            session('tenant_campus_name', $campus['name']);
            return $campus['id'];
        }
    }
    
    // 3. 已登录管理员所属校区
    $admin_campus_id = session('admin.campus_id');
    if ($admin_campus_id > 0) return $admin_campus_id;
    
    return 0; // 超管/无租户
}
```

### 3.3 Session 存储当前租户

管理员登录成功后，将 `campus_id` 存入 session：

```php
// AdminController::Login() 登录成功后
session('admin.campus_id', $user['campus_id']);
session('admin.is_super', $user['is_super']);
```

---

## 四、数据隔离层（2-3天）

### 4.1 CommonController 自动追加

```php
// CommonController::_initialize()
protected function _initialize() {
    parent::_initialize();
    
    // 自动追踪当前校区
    $campus_id = GetTenantCampusId();
    $this->assign('tenant_campus_id', $campus_id);
    
    // 非超管自动过滤数据
    if (!session('admin.is_super')) {
        $this->tenant_campus_id = $campus_id;
    }
}
```

### 4.2 自动 SQL 过滤助手

```php
/**
 * 带租户隔离的查询 — 自动加 campus_id 条件
 * @param string $model 模型名
 * @param array  $where 已有条件
 * @param bool   $force  强制加条件（即使超管也加）
 */
function M_Tenant($model, $where = [], $force = false) {
    $m = M($model);
    $campus_id = GetTenantCampusId();
    
    // 检查表是否有 campus_id 列
    if ($campus_id > 0 || $force) {
        $where['campus_id'] = $campus_id;
    }
    return $m->where($where);
}

// 使用方式
$students = M_Tenant('Student')->select();  // 自动 WHERE campus_id=当前校区
```

### 4.3 需要审计的 48 个控制器

**高优先级**（已有 campus_id 但可能遗漏）：
- `StudentController`, `TeacherController`, `ClassController`
- `OrderController`, `FinanceController`, `StatsController`
- `AttendanceController`, `ScheduleController`, `CourseController`
- `PackageController`, `StudentCourseController`

**低优先级**（系统级功能，不需隔离）：
- `ConfigController`, `ThemeController`, `PowerController`
- `CacheController`, `LinkController`

### 4.4 超管跨校区查看

```php
// 在查询参数中添加 &campus_id=0 可查看全部校区
// 超管不受 campus_id 过滤限制
```

---

## 五、管理员登录改造（1-2天）

### 5.1 机构管理员注册/创建

后台超管操作：
```
后台 → 校区管理 → 编辑校区 → 「开通管理员」
→ 自动创建 sc_admin 账号（campus_id=该校区, is_super=0）
→ 发送登录地址给机构
```

### 5.2 机构管理员登录流程

```
机构管理员访问：https://school1.yourdomain.com/admin.php
→ 租户识别层自动设置 campus_id
→ 登录页显示该机构名称和LOGO
→ 登录成功后 session 记录 campus_id
→ 所有操作自动带 campus_id 过滤
```

### 5.3 登录页面品牌化

```php
// Admin/Controller/AdminController::LoginInfo()
$campus_id = GetTenantCampusId();
if ($campus_id > 0) {
    $campus = M('Campus')->find($campus_id);
    $this->assign('tenant_name', $campus['site_name'] ?: $campus['name']);
    $this->assign('tenant_logo', $campus['logo']);
    $this->assign('tenant_color', $campus['theme_color']);
}
```

---

## 六、实施步骤

| 步骤 | 内容 | 工时 | 交付物 |
|------|------|------|--------|
| **Day 1** | DB 变更（扩展 campus 表 + admin 表 + campus_config 表） | 0.5天 | SQL 脚本 |
| | Nginx 泛域名配置 + 泛解析 DNS | 0.5天 | Nginx conf + DNS记录 |
| | `GetTenantCampusId()` 函数 | 0.5天 | function.php 新函数 |
| | 管理员登录绑定 campus_id | 0.5天 | 改 AdminController |
| **Day 2** | CommonController 自动过滤 | 0.5天 | _initialize 改造 |
| | `M_Tenant()` 辅助函数 | 0.5天 | function.php |
| | 审计并修复 48 个控制器 | 1天 | 逐个文件改 |
| **Day 3** | 超管后台 → 机构开通管理界面 | 0.5天 | 校区编辑页扩展 |
| | 机构管理员创建/管理 | 0.5天 | Admin→campus 管理 |
| | 登录页品牌化 | 0.5天 | LoginInfo.html |
| | 测试 + 部署 | 0.5天 | 上线 |

---

## 七、注意事项 / 陷阱

### ⚠️ 已知问题

| 问题 | 说明 | 对策 |
|------|------|------|
| 某些表没有 `campus_id` 列 | `sc_user`（家长端）、`sc_config`、`sc_article` | 这些是全局数据，不需要加 |
| 已经使用 `campus_id` 的 48 处引用可能不全 | 旧代码可能用 `where('campus_id='.$id)` 字符串形式 | 用 grep 扫 `campus_id` 全匹配 |
| 非 CommonController 子类 | OrderController 等直接继承 Think\Controller | 需要逐个加过滤逻辑 |
| 分页统计 count 也需过滤 | `->count()` 前也要加 campus_id | M_Tenant() 自动处理 |
| 超管需要"切换租户查看"功能 | 超管登录后可以选校区 | 加一个简单 switch 界面 |
| 机构到期停用 | `expire_date` 判断 | CommonController 校验收费状态 |
