<?php
/**
 * SUOJIAN API Layer
 * 独立 JSON API，直接操作数据库，不修改原系统
 */

// Config
define('API_DB_HOST', '127.0.0.1');
define('API_DB_USER', 'schoolcms');
define('API_DB_PWD',  'SchoolCMS@2024');
define('API_DB_NAME', 'schoolcms');

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allow_origins = ['http://47.114.125.123', 'http://47.114.125.123:5173'];
if (in_array($origin, $allow_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Helpers
function json($data = [], $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . API_DB_HOST . ';dbname=' . API_DB_NAME . ';charset=utf8mb4',
            API_DB_USER, API_DB_PWD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function auth_check() {
    session_name('PHPSESSID');
    session_start();
    if (empty($_SESSION['admin'])) {
        json(['code' => 401, 'msg' => '未登录'], 401);
    }
    return $_SESSION['admin'];
}

function page_total($total, $page, $limit) {
    return [
        'total' => (int)$total,
        'page'  => (int)$page,
        'limit' => (int)$limit,
        'pages' => ceil($total / $limit),
    ];
}

// Routing
$path = trim($_GET['api'] ?? '/', '/');
$method = $_SERVER['REQUEST_METHOD'];

// ---- Auth ----
if ($path === 'auth/login' && $method === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? $_POST['login_pwd'] ?? '';

    if (!$username || !$password) {
        json(['code' => 400, 'msg' => '用户名和密码不能为空']);
    }

    $stmt = db()->prepare("SELECT id, username, login_pwd, login_salt, role_id, campus_id, is_super FROM sc_admin WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if (!$row) {
        json(['code' => -2, 'msg' => '用户名不存在'], 401);
    }

    $hash = md5($row['login_salt'] . $password);
    if ($hash !== $row['login_pwd']) {
        json(['code' => -3, 'msg' => '密码错误'], 401);
    }

    session_name('PHPSESSID');
    session_start();
    unset($row['login_pwd'], $row['login_salt']);
    $_SESSION['admin'] = $row;

    $salt = substr(md5(uniqid(mt_rand(), true)), 0, 6);
    $newPwd = md5($salt . $password);
    $up = db()->prepare("UPDATE sc_admin SET login_salt=?, login_pwd=?, login_total=login_total+1, login_time=? WHERE id=?");
    $up->execute([$salt, $newPwd, time(), $row['id']]);

    json([
        'code' => 0,
        'msg'  => '登录成功',
        'data' => [
            'token' => session_id(),
            'user'  => ['id' => $row['id'], 'username' => $row['username'], 'role_id' => $row['role_id']],
        ]
    ]);
}

if ($path === 'auth/logout' && $method === 'POST') {
    session_name('PHPSESSID');
    session_start();
    session_destroy();
    json(['code' => 0, 'msg' => '已退出']);
}

if ($path === 'auth/me' && $method === 'GET') {
    $admin = auth_check();
    json(['code' => 0, 'data' => ['id' => $admin['id'], 'username' => $admin['username'], 'role_id' => $admin['role_id']]]);
}

// ---- Articles ----
if ($path === 'articles' && $method === 'GET') {
    auth_check();
    $page   = max(1, intval($_GET['page'] ?? 1));
    $limit  = min(100, max(10, intval($_GET['limit'] ?? 15)));
    $offset = ($page - 1) * $limit;

    $where  = 'is_enable IN (0,1)';
    $params = [];

    if (!empty($_GET['keyword'])) {
        $where .= ' AND title LIKE ?';
        $params[] = '%' . $_GET['keyword'] . '%';
    }
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $where .= ' AND is_enable = ?';
        $params[] = intval($_GET['status']);
    }
    if (!empty($_GET['category_id'])) {
        $where .= ' AND article_class_id = ?';
        $params[] = intval($_GET['category_id']);
    }

    $sel = db()->prepare("SELECT id, title, title_color, article_class_id, jump_url, is_enable, access_count, add_time, upd_time FROM sc_article WHERE $where ORDER BY id DESC LIMIT $offset, $limit");
    $sel->execute($params);
    $rows = $sel->fetchAll();

    $classes_st = db()->prepare("SELECT id, name FROM sc_article_class WHERE is_enable=1 ORDER BY sort ASC, id ASC");
    $classes_st->execute();
    $classes = $classes_st->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($rows as &$r) {
        $r['category'] = $classes[$r['article_class_id']] ?? '-';
        $r['status']   = $r['is_enable'] ? 'published' : 'draft';
        $r['views']    = $r['access_count'];
        $r['date']      = date('Y-m-d', $r['add_time']);
    }

    $total_st = db()->prepare("SELECT COUNT(*) FROM sc_article WHERE $where");
    $total_st->execute($params);
    $total = $total_st->fetchColumn();

    json([
        'code'  => 0,
        'data'  => $rows,
        'extra' => page_total($total, $page, $limit),
    ]);
}

if ($path === 'articles' && $method === 'POST') {
    auth_check();
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $id = intval($input['id'] ?? 0);
    $data = [
        'title'             => trim($input['title'] ?? ''),
        'title_color'      => $input['title_color'] ?? '#333333',
        'article_class_id'  => intval($input['category_id'] ?? $input['article_class_id'] ?? 0),
        'is_enable'         => isset($input['is_enable']) ? intval($input['is_enable']) : 1,
        'content'          => $input['content'] ?? '',
        'upd_time'         => time(),
    ];

    if (!$data['title']) {
        json(['code' => 400, 'msg' => '标题不能为空'], 400);
    }

    if ($id > 0) {
        $sets = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = ?";
            $vals[] = $v;
        }
        $vals[] = $id;
        db()->prepare("UPDATE sc_article SET " . implode(',', $sets) . " WHERE id=?")->execute($vals);
        json(['code' => 0, 'msg' => '编辑成功', 'data' => ['id' => $id]]);
    } else {
        $data['add_time'] = time();
        $cols = implode(',', array_keys($data));
        $phs = implode(',', array_fill(0, count($data), '?'));
        db()->prepare("INSERT INTO sc_article ($cols) VALUES ($phs)")->execute(array_values($data));
        json(['code' => 0, 'msg' => '发布成功', 'data' => ['id' => db()->lastInsertId()]]);
    }
}

if ($path === 'articles' && $method === 'DELETE') {
    auth_check();
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) json(['code' => 400, 'msg' => '参数错误'], 400);
    db()->prepare("DELETE FROM sc_article WHERE id = ?")->execute([$id]);
    json(['code' => 0, 'msg' => '删除成功']);
}

// ---- Categories ----
if ($path === 'categories' && $method === 'GET') {
    auth_check();
    $rows = db()->query("SELECT id, name, pid, sort FROM sc_article_class WHERE is_enable=1 ORDER BY sort ASC, id ASC")->fetchAll();
    json(['code' => 0, 'data' => $rows]);
}

// ---- Users ----
if ($path === 'users' && $method === 'GET') {
    auth_check();
    $page   = max(1, intval($_GET['page'] ?? 1));
    $limit  = min(100, max(10, intval($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $total = db()->query("SELECT COUNT(*) FROM sc_admin")->fetchColumn();
    $rows  = db()->query("SELECT id, username, mobile, role_id, campus_id, is_super, login_total, login_time, add_time FROM sc_admin ORDER BY id DESC LIMIT $offset, $limit")->fetchAll();

    $roles = db()->query("SELECT id, name FROM sc_role WHERE is_enable=1")->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($rows as &$r) {
        $r['role']      = $roles[$r['role_id']] ?? '未知';
        $r['lastLogin'] = $r['login_time'] ? date('Y-m-d H:i', $r['login_time']) : '-';
    }

    json([
        'code'  => 0,
        'data'  => $rows,
        'extra' => page_total($total, $page, $limit),
    ]);
}

// ---- Dashboard Stats ----
if ($path === 'dashboard/stats' && $method === 'GET') {
    auth_check();
    $today_start = strtotime('today');
    $article_total = db()->query("SELECT COUNT(*) FROM sc_article")->fetchColumn();
    $article_today_st = db()->prepare("SELECT COUNT(*) FROM sc_article WHERE add_time >= ?");
    $article_today_st->execute([$today_start]);
    $article_today = $article_today_st->fetchColumn();
    $user_total = db()->query("SELECT COUNT(*) FROM sc_admin")->fetchColumn();
    $visit_st = db()->prepare("SELECT SUM(access_count) FROM sc_article WHERE upd_time >= ?");
    $visit_st->execute([$today_start]);
    $visit_today = $visit_st->fetchColumn() ?: 0;

    json([
        'code' => 0,
        'data' => [
            ['label' => '今日访问',  'value' => $visit_today,  'trend' => 12],
            ['label' => '用户总数',  'value' => $user_total,   'trend' => 8],
            ['label' => '文章总数',  'value' => $article_total, 'trend' => -3],
            ['label' => '今日新增',  'value' => $article_today, 'trend' => 5],
        ]
    ]);
}

// 404
json(['code' => 404, 'msg' => 'API不存在'], 404);
