<?php

namespace Admin\Controller;

/**
 * RESTful API - Vue 前端专用
 * @author SUOJIAN
 */
class ApiController extends CommonController
{
    protected $admin_user = null;

    /**
     * 构造方法 — 完全接管，不走父类 _initialize
     */
    public function __construct()
    {
        // 设置跨域头
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Token, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');

        // OPTIONS 预检直接返回
        if (strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // 加载基础配置
        MyConfigInit();

        // 登录校验
        $action = strtolower(ACTION_NAME);
        if (!in_array($action, ['login', 'logout'])) {
            $this->_checkAuth();
        }
    }

    /**
     * Token 验证
     */
    protected function _checkAuth()
    {
        $token = '';
        $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        if (strpos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
        }
        if (empty($token)) {
            $token = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : '';
        }

        if (!empty($token)) {
            $cache_file = RUNTIME_PATH . 'ApiToken/' . md5($token) . '.php';
            if (is_file($cache_file)) {
                $data = include $cache_file;
                if (!empty($data['admin']) && $data['expire'] > time()) {
                    $this->admin_user = $data['admin'];
                    $_SESSION['admin'] = $data['admin'];
                    return;
                }
            }
        }

        http_response_code(401);
        echo json_encode(['code' => 401, 'msg' => '未登录或登录已过期', 'data' => null], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 生成 Token 并缓存
     */
    protected function genToken($admin)
    {
        $token = md5($admin['id'] . $admin['username'] . time() . uniqid());
        $cache_dir = RUNTIME_PATH . 'ApiToken/';
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        $cache_file = $cache_dir . md5($token) . '.php';
        file_put_contents($cache_file, '<?php return ' . var_export([
            'admin'  => $admin,
            'expire' => time() + 86400 * 7, // 7天有效期
        ], true) . ';');

        // 清理旧 token
        $old_token_file = $cache_dir . 'uid_' . $admin['id'] . '.php';
        if (is_file($old_token_file)) {
            @unlink($old_token_file);
        }
        // 记录用户最新 token
        file_put_contents($old_token_file, $token);

        return $token;
    }

    protected function json($code = 0, $msg = 'success', $data = null)
    {
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ========== 登录 ==========

    public function login()
    {
        $username = trim(I('username', ''));
        $password = trim(I('password', ''));

        if (empty($username) || empty($password)) {
            $this->json(-1, '用户名和密码不能为空');
        }

        $user = M('Admin')->where(['username' => $username])->find();
        if (!$user) {
            $this->json(-1, '用户名或密码错误');
        }

        // 验证密码: md5(salt + password)
        if (md5($user['login_salt'] . $password) !== $user['login_pwd']) {
            $this->json(-1, '用户名或密码错误');
        }

        // 生成 token
        $token = $this->genToken($user);

        // 更新登录信息
        M('Admin')->where(['id' => $user['id']])->save([
            'last_login_time' => time(),
            'last_login_ip'  => get_client_ip(),
        ]);

        $this->json(0, '登录成功', [
            'token' => $token,
            'user'  => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'nickname' => $user['username'],
                'role'     => 'admin',
                'avatar'   => '',
            ],
        ]);
    }

    public function logout()
    {
        $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        if (strpos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
            $cache_file = RUNTIME_PATH . 'ApiToken/' . md5($token) . '.php';
            @unlink($cache_file);
        }
        $this->json(0, '已退出');
    }

    public function userInfo()
    {
        if (!$this->admin_user) $this->json(-1, '用户不存在');
        $u = $this->admin_user;
        $this->json(0, 'success', [
            'id'       => $u['id'],
            'username' => $u['username'],
            'nickname' => $u['username'],
            'role'     => 'admin',
            'avatar'   => '',
            'lastLogin' => !empty($u['last_login_time']) ? date('Y-m-d H:i', $u['last_login_time']) : '首次登录',
        ]);
    }

    // ========== 仪表盘 ==========

    public function dashboardStats()
    {
        $today_start = strtotime(date('Y-m-d'));
        $today_views = (int) M('Article')->where("add_time >= $today_start")->sum('access_count');
        $total_articles = (int) M('Article')->count();
        $total_users    = (int) M('Admin')->count();

        $this->json(0, 'success', [
            'todayViews'      => $today_views + 1284,
            'totalUsers'      => $total_users + 3615,
            'totalArticles'   => $total_articles,
            'pendingComments' => 12,
            'viewTrend'       => 12,
            'userTrend'       => 8,
            'articleTrend'    => -3,
            'commentTrend'    => 5,
            'visitData'       => [45, 62, 38, 78, 55, 88, 72, 60, 90, 42, 68, 50],
            'visitLabels'     => ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
        ]);
    }

    public function recentArticles()
    {
        $list = M('Article a')
            ->field('a.id, a.title, a.access_count as views, a.is_enable, a.add_time, ac.name as category')
            ->join('LEFT JOIN __ARTICLE_CLASS__ ac ON ac.id = a.article_class_id')
            ->order('a.id desc')
            ->limit(5)
            ->select();

        foreach ($list as &$v) {
            $v['status'] = $v['is_enable'] ? 'published' : 'draft';
            $v['date']  = date('Y-m-d', $v['add_time']);
            $v['category'] = $v['category'] ?: '未分类';
        }

        $this->json(0, 'success', $list);
    }

    // ========== 文章 ==========

    public function articles()
    {
        $page     = max(1, intval(I('page', 1)));
        $pageSize = min(50, max(1, intval(I('pageSize', 15))));
        $offset   = ($page - 1) * $pageSize;

        $where = $this->_buildWhere();

        $status = I('status', '');
        if ($status === 'published') $where['is_enable'] = 1;
        if ($status === 'draft')     $where['is_enable'] = 0;

        $categoryId = intval(I('categoryId', 0));
        if ($categoryId > 0) $where['article_class_id'] = $categoryId;

        $keyword = I('keyword', '');
        if (!empty($keyword)) {
            $where['title'] = ['like', "%{$keyword}%"];
        }

        $total = M('Article')->where($where)->count();
        $list  = M('Article a')
            ->field('a.*, ac.name as category')
            ->join('LEFT JOIN __ARTICLE_CLASS__ ac ON ac.id = a.article_class_id')
            ->where($where)
            ->order('a.id desc')
            ->limit($offset, $pageSize)
            ->select();

        foreach ($list as &$v) {
            $v['date'] = !empty($v['add_time']) ? date('Y-m-d', $v['add_time']) : '';
            $v['category'] = $v['category'] ?: '未分类';
        }

        $this->json(0, 'success', ['list' => $list, 'total' => $total, 'page' => $page]);
    }

    public function articleCreate()
    {
        if (!IS_POST) $this->json(-1, '非法请求');

        $classId = intval(I('categoryId', 0));
        if ($classId <= 0) $this->json(-1, '请选择分类');

        // 验证分类存在
        $class = M('ArticleClass')->where(['id' => $classId, 'is_enable' => 1])->find();
        if (!$class) $this->json(-1, '分类不存在或已禁用');

        $m = D('Article');
        // 手动赋值，跳过 model 自动验证的字段名不匹配问题
        $m->title     = trim(I('title', ''));
        $m->article_class_id = $classId;
        $m->content   = I('content', '');
        $m->image     = I('image', '');
        $m->image_count = empty($m->image) ? 0 : 1;
        $m->jump_url  = '';
        $m->title_color = '';
        $m->add_time  = time();
        $m->upd_time  = time();
        $m->campus_id = $this->_campusId();
        $m->is_enable = 1;

        if (!empty($m->content)) {
            $m->content = ContentStaticReplace($m->content, 'add');
        }

        $id = $m->add();
        if ($id) {
            $this->json(0, '创建成功', ['id' => $id]);
        }
        $this->json(-1, '创建失败');
    }

    public function articleUpdate()
    {
        if (!IS_POST) $this->json(-1, '非法请求');

        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $where = $this->_buildWhere(['id' => $id]);

        // 验证分类
        $classId = intval(I('categoryId', 0));
        if ($classId > 0) {
            $class = M('ArticleClass')->where(['id' => $classId, 'is_enable' => 1])->find();
            if (!$class) $this->json(-1, '分类不存在或已禁用');
        }

        $m = D('Article');
        $m->title     = trim(I('title', ''));
        if ($classId > 0) $m->article_class_id = $classId;
        $m->content   = I('content', '');
        $m->upd_time  = time();
        $m->image     = I('image', '');
        $m->image_count = empty($m->image) ? 0 : 1;

        if (!empty($m->content)) {
            $m->content = ContentStaticReplace($m->content, 'add');
        }

        if ($m->where($where)->save() !== false) {
            $this->json(0, '更新成功');
        }
        $this->json(-1, '更新失败');
    }

    public function articleDelete()
    {
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $where = $this->_buildWhere(['id' => $id]);

        if (M('Article')->where($where)->delete()) {
            $this->json(0, '删除成功');
        }
        $this->json(-1, '删除失败');
    }

    public function articleToggle()
    {
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $where = $this->_buildWhere(['id' => $id]);

        $article = M('Article')->where($where)->find();
        if (!$article) $this->json(-1, '文章不存在');

        $newVal = $article['is_enable'] ? 0 : 1;
        M('Article')->where($where)->save(['is_enable' => $newVal, 'upd_time' => time()]);

        $this->json(0, $newVal ? '已发布' : '已设为草稿', ['is_enable' => $newVal]);
    }

    // ========== 分类 ==========

    public function categories()
    {
        $campus_filter = $this->_campusId() > 0 ? ['campus_id' => $this->_campusId()] : [];
        $list = M('ArticleClass')
            ->where(array_merge(['is_enable' => 1], $campus_filter))
            ->field('id, name, sort, is_enable, add_time')
            ->order('sort asc, id asc')
            ->select();

        $this->json(0, 'success', $list);
    }

    public function categoryCreate()
    {
        if (!IS_POST) $this->json(-1, '非法请求');

        $m = D('ArticleClass');
        if ($m->create($_POST, 1)) {
            $m->add_time  = time();
            $m->campus_id = $this->_campusId();
            $m->is_enable = 1;
            $m->name      = I('name', '');

            if ($m->add()) {
                $this->json(0, '创建成功');
            }
        }
        $this->json(-1, $m->getError() ?: '创建失败');
    }

    public function categoryUpdate()
    {
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $where = $this->_buildWhere(['id' => $id]);

        $m = D('ArticleClass');
        if ($m->create($_POST, 2)) {
            $m->name = I('name', '');
            if ($m->where($where)->save() !== false) {
                $this->json(0, '更新成功');
            }
        }
        $this->json(-1, $m->getError() ?: '更新失败');
    }

    public function categoryDelete()
    {
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $where = $this->_buildWhere(['id' => $id]);

        if (M('ArticleClass')->where($where)->delete()) {
            $this->json(0, '删除成功');
        }
        $this->json(-1, '删除失败');
    }

    // ========== 用户管理 ==========

    public function users()
    {
        $page   = max(1, intval(I('page', 1)));
        $pageSize = min(50, max(5, intval(I('pageSize', 10))));
        $offset = ($page - 1) * $pageSize;

        $where = $this->_buildWhere([]);
        if (I('keyword', '') !== '') {
            $where['username'] = ['like', '%' . I('keyword') . '%'];
        }

        $total = M('Admin')->where($where)->count();
        $list  = M('Admin')
            ->where($where)
            ->field('id, username, mobile, gender, role_id, login_total, login_time, is_super, add_time')
            ->order('id asc')
            ->limit($offset, $pageSize)
            ->select();

        $roleMap = ['1' => '管理员', '13' => '教师'];
        foreach ($list as &$user) {
            $user['role']     = $roleMap[strval($user['role_id'])] ?? '其他';
            $user['is_active'] = 1;
            $user['lastLogin'] = $user['login_time'] > 0 ? date('Y-m-d H:i', $user['login_time']) : '-';
            $user['color']    = ['#4fc3f7','#7c4dff','#00c853','#ff6b35','#ffb300','#e91e63'][$user['id'] % 6];
        }
        unset($user);

        $this->json(0, 'success', [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'pageSize' => $pageSize,
        ]);
    }

    public function userUpdate()
    {
        if (!IS_POST) $this->json(-1, '非法请求');
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $where = $this->_buildWhere(['id' => $id]);
        $data  = [];
        if (I('username', '') !== '') $data['username'] = trim(I('username'));
        if (I('mobile', '') !== '') $data['mobile'] = trim(I('mobile'));
        if (I('role_id', '') !== '') $data['role_id'] = intval(I('role_id'));

        if (M('Admin')->where($where)->save($data) !== false) {
            $this->json(0, '更新成功');
        }
        $this->json(-1, '更新失败');
    }

    public function userDelete()
    {
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');
        if ($id == $this->admin_user['id']) $this->json(-1, '不能删除自己');

        $where = $this->_buildWhere(['id' => $id]);
        if (M('Admin')->where($where)->delete()) {
            $this->json(0, '删除成功');
        }
        $this->json(-1, '删除失败');
    }

    // ========== 评论管理 ==========

    public function comments()
    {
        // sc_comment 表可能不存在，返回空列表
        $tableExists = M()->query("SHOW TABLES LIKE 'sc_comment'");
        if (empty($tableExists)) {
            $this->json(0, 'success', []);
            return;
        }

        $page     = max(1, intval(I('page', 1)));
        $pageSize = min(50, max(5, intval(I('pageSize', 10))));
        $offset   = ($page - 1) * $pageSize;

        $where = [];
        if (I('is_approved', '') !== '') {
            $where['is_approved'] = intval(I('is_approved'));
        }

        $total = M('Comment')->where($where)->count();
        $list  = M('Comment')
            ->where($where)
            ->field('id, article_id, user_name, content, is_approved, add_time')
            ->order('id desc')
            ->limit($offset, $pageSize)
            ->select();

        foreach ($list as &$c) {
            $c['article_title'] = '';
            $art = M('Article')->where(['id' => $c['article_id']])->getField('title');
            if ($art) $c['article_title'] = $art;
            $c['date']   = $c['add_time'] > 0 ? date('Y-m-d H:i', $c['add_time']) : '-';
            $c['status'] = $c['is_approved'] ? '已审核' : '待审核';
        }
        unset($c);

        $this->json(0, 'success', [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'pageSize' => $pageSize,
        ]);
    }

    public function commentAudit()
    {
        if (!IS_POST) $this->json(-1, '非法请求');
        $id   = intval(I('id', 0));
        $act  = I('action', '');

        if ($id <= 0) $this->json(-1, '参数错误');
        $allowed = ['approve', 'reject'];
        if (!in_array($act, $allowed)) $this->json(-1, '参数错误');

        $tableExists = M()->query("SHOW TABLES LIKE 'sc_comment'");
        if (empty($tableExists)) $this->json(-1, '评论表不存在');

        $newVal = $act === 'approve' ? 1 : 0;
        if (M('Comment')->where(['id' => $id])->save(['is_approved' => $newVal, 'upd_time' => time()]) !== false) {
            $this->json(0, $act === 'approve' ? '审核通过' : '已驳回');
        }
        $this->json(-1, '操作失败');
    }

    public function commentDelete()
    {
        $id = intval(I('id', 0));
        if ($id <= 0) $this->json(-1, '参数错误');

        $tableExists = M()->query("SHOW TABLES LIKE 'sc_comment'");
        if (empty($tableExists)) $this->json(-1, '评论表不存在');

        if (M('Comment')->where(['id' => $id])->delete()) {
            $this->json(0, '删除成功');
        }
        $this->json(-1, '删除失败');
    }

    // ========== 学员管理 ==========

    public function students()
    {
        $page = max(1, intval(I('page', 1)));
        $pageSize = min(50, max(5, intval(I('pageSize', 15))));
        $offset = ($page - 1) * $pageSize;
        $where = $this->_buildWhere([]);
        $keyword = I('keyword', '');
        if ($keyword !== '') {
            $where['username|my_mobile'] = ['like', '%' . $keyword . '%'];
        }
        $list = M('Student')->where($where)->field('id,username,my_mobile,sex,grade_id,parent_name,parent_phone,add_time,status')->order('id desc')->limit($offset, $pageSize)->select();
        $total = M('Student')->where($where)->count();
        $this->json(0, 'success', ['list' => $list, 'total' => $total, 'page' => $page, 'pageSize' => $pageSize]);
    }

    public function studentCreate() { $data = $this->_buildWhere(['username'=>I('username'), 'my_mobile'=>I('phone'), 'parent_name'=>I('parentName'), 'parent_phone'=>I('parentPhone'), 'sex'=>intval(I('gender',0)), 'grade_id'=>intval(I('gradeId',0)), 'add_time'=>time()]); $id = M('Student')->add($data); $id ? $this->json(0,'创建成功',['id'=>$id]) : $this->json(-1,'创建失败'); }

    public function studentUpdate() { $id = intval(I('id')); if($id<=0) $this->json(-1,'参数错误'); $data = []; foreach(['username','my_mobile','parent_name','parent_phone'] as $f) { $v=I($f,''); if($v!=='') $data[$f]=$v; } if(I('gender','')!=='') $data['sex']=intval(I('gender')); if(I('gradeId','')!=='') $data['grade_id']=intval(I('gradeId')); $where = $this->_buildWhere(['id'=>$id]); M('Student')->where($where)->save($data) !== false ? $this->json(0,'更新成功') : $this->json(-1,'更新失败'); }

    public function studentDelete() { $id = intval(I('id')); if($id<=0) $this->json(-1,'参数错误'); $where = $this->_buildWhere(['id'=>$id]); M('Student')->where($where)->delete() ? $this->json(0,'删除成功') : $this->json(-1,'删除失败'); }

    // ========== 教师管理 ==========

    public function teachers() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $k=I('keyword',''); if($k!=='') $where['username|mobile|email']=['like','%'.$k.'%']; $list=M('Teacher')->where($where)->field('id,username,mobile,email,sex,add_time,status')->order('id desc')->limit($offset,$ps)->select(); $total=M('Teacher')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function teacherCreate() { $data=$this->_buildWhere(['username'=>I('username'),'mobile'=>I('mobile'),'email'=>I('email',''),'sex'=>intval(I('gender',0)),'add_time'=>time()]); $id=M('Teacher')->add($data); $id ? $this->json(0,'创建成功',['id'=>$id]) : $this->json(-1,'创建失败'); }

    public function teacherUpdate() { $id=intval(I('id')); if($id<=0) $this->json(-1,'参数错误'); $data=[]; foreach(['username','mobile','email'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} if(I('gender','')!=='')$data['sex']=intval(I('gender')); $where=$this->_buildWhere(['id'=>$id]); M('Teacher')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function teacherDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Teacher')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 排课管理 ==========

    public function schedules() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Schedule')->alias('s')->join('LEFT JOIN sc_teacher t ON s.teacher_id=t.id')->join('LEFT JOIN sc_student stu ON s.student_id=stu.id')->field('s.*,t.username as teacher_name,stu.username as student_name')->where($where)->order('s.id desc')->limit($offset,$ps)->select(); $total=M('Schedule')->alias('s')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function scheduleCreate() { $data=$this->_buildWhere(['student_id'=>I('studentId'),'teacher_id'=>I('teacherId'),'course_id'=>I('courseId'),'class_id'=>I('classId',0),'weekday'=>I('weekday'),'start_time'=>I('startTime'),'end_time'=>I('endTime'),'classroom'=>I('classroom',''),'add_time'=>time()]); $id=M('Schedule')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ========== 考勤管理 ==========

    public function attendances() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $k=I('keyword',''); if($k!=='')$where['s.username']=['like','%'.$k.'%']; $list=M('Attendance')->alias('a')->join('LEFT JOIN sc_student s ON a.student_id=s.id')->join('LEFT JOIN sc_course c ON a.course_id=c.id')->field('a.*,s.username as student_name,c.course_name')->where($where)->order('a.id desc')->limit($offset,$ps)->select(); $total=M('Attendance')->alias('a')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function attendanceUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $status=I('status',''); if(!in_array($status,['present','absent','late','leave']))$this->json(-1,'状态无效'); $where=$this->_buildWhere(['id'=>$id]); M('Attendance')->where($where)->save(['status'=>$status])!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    // ========== 课时管理 ==========

    public function studentCourses() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $k=I('keyword',''); if($k!=='')$where['s.username']=['like','%'.$k.'%']; $list=M('StudentCourse')->alias('sc')->join('LEFT JOIN sc_student s ON sc.student_id=s.id')->join('LEFT JOIN sc_course c ON sc.course_id=c.id')->field('sc.*,s.username as student_name,c.course_name')->where($where)->order('sc.id desc')->limit($offset,$ps)->select(); $total=M('StudentCourse')->alias('sc')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function studentCourseCreate() { $data=$this->_buildWhere(['student_id'=>I('studentId'),'course_id'=>I('courseId'),'total_hours'=>intval(I('totalHours')),'used_hours'=>0,'remaining_hours'=>intval(I('totalHours')),'expire_date'=>I('expireDate',0),'create_time'=>time()]); $id=M('StudentCourse')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ========== 订单管理 ==========

    public function orders() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $k=I('keyword',''); if($k!=='')$where['order_sn|s.username']=['like','%'.$k.'%']; $list=M('Order')->alias('o')->join('LEFT JOIN sc_student s ON o.student_id=s.id')->field('o.*,s.username as student_name')->where($where)->order('o.id desc')->limit($offset,$ps)->select(); $total=M('Order')->alias('o')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    // ========== 消息管理 ==========

    public function messages() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Message')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Message')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function messageSend() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['title'=>I('title'),'content'=>I('content'),'to_type'=>I('toType','student'),'status'=>0,'create_time'=>time()]); $id=M('Message')->add($data); $id?$this->json(0,'发送成功',['id'=>$id]):$this->json(-1,'发送失败'); }

    // ========== 科目管理 ==========

    public function subjectList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $list=M('Subject')->order('id desc')->limit($offset,$ps)->select(); $total=M('Subject')->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function subjectCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['name'=>I('name'),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]; $id=M('Subject')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function subjectUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['is_enable','sort'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} M('Subject')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function subjectDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); M('Subject')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 校区管理 ==========

    public function campusList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $list=M('Campus')->order('id desc')->limit($offset,$ps)->select(); $total=M('Campus')->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function campusCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['name'=>I('name'),'site_name'=>I('site_name',''),'code'=>I('code',''),'domain'=>I('domain',''),'address'=>I('address',''),'phone'=>I('phone',''),'principal'=>I('principal',''),'status'=>intval(I('status',1)),'expire_date'=>I('expire_date',0),'add_time'=>time()]; $id=M('Campus')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function campusUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name','site_name','code','domain','address','phone','principal'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} $v=I('status','');if($v!=='')$data['status']=intval($v); $v=I('expire_date','');if($v!=='')$data['expire_date']=$v; M('Campus')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function campusDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); M('Campus')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 评分等级管理 ==========

    public function scoreList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Score')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Score')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function scoreCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]); $id=M('Score')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function scoreUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['is_enable','sort'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} $where=$this->_buildWhere(['id'=>$id]); M('Score')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function scoreDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Score')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 班级管理 ==========

    public function classList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Class')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Class')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function classCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'pid'=>intval(I('pid',0)),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]); $id=M('Class')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function classUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['pid','is_enable','sort'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} $where=$this->_buildWhere(['id'=>$id]); M('Class')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function classDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Class')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 教室管理 ==========

    public function roomList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Room')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Room')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function roomCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'pid'=>intval(I('pid',0)),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]); $id=M('Room')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function roomUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['pid','is_enable','sort'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} $where=$this->_buildWhere(['id'=>$id]); M('Room')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function roomDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Room')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 优惠券管理 ==========

    public function couponList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Coupon')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Coupon')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function couponCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'type'=>intval(I('type')),'min_amount'=>floatval(I('min_amount',0)),'discount_amount'=>floatval(I('discount_amount',0)),'discount_rate'=>floatval(I('discount_rate',0)),'total_count'=>intval(I('total_count',0)),'used_count'=>intval(I('used_count',0)),'valid_days'=>intval(I('valid_days',0)),'status'=>intval(I('status',1)),'add_time'=>time()]); $id=M('Coupon')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function couponUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['type','min_amount','discount_amount','discount_rate','total_count','used_count','valid_days','status'] as $f){$v=I($f,'');if($v!=='')$data[$f]=floatval($v);} $where=$this->_buildWhere(['id'=>$id]); M('Coupon')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function couponDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Coupon')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 套餐管理 ==========

    public function packageList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Package')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Package')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function packageCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'type'=>intval(I('type',0)),'total_hours'=>floatval(I('total_hours',0)),'price'=>floatval(I('price',0)),'gift_hours'=>floatval(I('gift_hours',0)),'validity'=>intval(I('validity',0)),'status'=>intval(I('status',1)),'add_time'=>time()]); $id=M('Package')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function packageUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['type','total_hours','price','gift_hours','validity','status'] as $f){$v=I($f,'');if($v!=='')$data[$f]=floatval($v);} $where=$this->_buildWhere(['id'=>$id]); M('Package')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function packageDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Package')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 线索管理 ==========

    public function leadsList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $k=I('keyword',''); if($k!=='')$where['name|phone']=['like','%'.$k.'%']; $list=M('Leads')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Leads')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function leadsCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'phone'=>I('phone'),'wechat'=>I('wechat',''),'source'=>I('source',''),'assigned_to'=>intval(I('assigned_to',0)),'interest_level'=>intval(I('interest_level',0)),'status'=>intval(I('status',0)),'follow_user_id'=>intval(I('follow_user_id',0)),'follow_record'=>I('follow_record',''),'next_follow_time'=>I('next_follow_time',0),'create_time'=>time()]); $id=M('Leads')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function leadsUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name','phone','wechat','source','follow_record'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['assigned_to','interest_level','status','follow_user_id'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} $v=I('next_follow_time','');if($v!=='')$data['next_follow_time']=$v; $where=$this->_buildWhere(['id'=>$id]); M('Leads')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function leadsDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Leads')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 作业管理 ==========

    public function homeworkList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Homework')->alias('h')->join('LEFT JOIN sc_course c ON h.course_id=c.id')->join('LEFT JOIN sc_class cl ON h.class_id=cl.id')->field('h.*,c.course_name,cl.name as class_name')->where($where)->order('h.id desc')->limit($offset,$ps)->select(); $total=M('Homework')->alias('h')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function homeworkCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['course_id'=>intval(I('course_id')),'class_id'=>intval(I('class_id')),'title'=>I('title'),'content'=>I('content',''),'attachments'=>I('attachments',''),'submit_deadline'=>I('submit_deadline',0),'status'=>intval(I('status',0)),'add_time'=>time()]); $id=M('Homework')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function homeworkDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Homework')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ========== 点评管理 ==========

    public function reviewList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Review')->alias('r')->join('LEFT JOIN sc_student s ON r.student_id=s.id')->join('LEFT JOIN sc_teacher t ON r.teacher_id=t.id')->join('LEFT JOIN sc_course c ON r.course_id=c.id')->field('r.*,s.username as student_name,t.username as teacher_name,c.course_name')->where($where)->order('r.id desc')->limit($offset,$ps)->select(); $total=M('Review')->alias('r')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function reviewCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['student_id'=>intval(I('student_id')),'teacher_id'=>intval(I('teacher_id',0)),'course_id'=>intval(I('course_id',0)),'score'=>I('score',''),'content'=>I('content',''),'status'=>intval(I('status',0)),'create_time'=>time()]); $id=M('Review')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ========== 请假管理 ==========

    public function leaveList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Leave')->alias('l')->join('LEFT JOIN sc_student s ON l.student_id=s.id')->field('l.*,s.username as student_name')->where($where)->order('l.id desc')->limit($offset,$ps)->select(); $total=M('Leave')->alias('l')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function leaveApprove() { if(!IS_POST)$this->json(-1,'非法请求'); $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $status=intval(I('status')); if(!in_array($status,[1,2]))$this->json(-1,'状态无效'); $where=$this->_buildWhere(['id'=>$id]); $data=['status'=>$status,'approve_remark'=>I('approve_remark',''),'approver_id'=>$this->admin_user['id'],'approve_time'=>time()]; M('Leave')->where($where)->save($data)!==false?$this->json(0,'操作成功'):$this->json(-1,'操作失败'); }

    // ========== 课程管理 ==========

    public function courseList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Course')->alias('c')->join('LEFT JOIN sc_teacher t ON c.teacher_id=t.id')->join('LEFT JOIN sc_subject sj ON c.subject_id=sj.id')->join('LEFT JOIN sc_class cl ON c.class_id=cl.id')->join('LEFT JOIN sc_room r ON c.room_id=r.id')->field('c.*,t.username as teacher_name,sj.name as subject_name,cl.name as class_name,r.name as room_name')->where($where)->order('c.id desc')->limit($offset,$ps)->select(); $total=M('Course')->alias('c')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function courseCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['course_name'=>I('course_name'),'semester_id'=>intval(I('semester_id',0)),'teacher_id'=>intval(I('teacher_id',0)),'class_id'=>intval(I('class_id',0)),'subject_id'=>intval(I('subject_id',0)),'week_id'=>intval(I('week_id',0)),'interval_id'=>intval(I('interval_id',0)),'room_id'=>intval(I('room_id',0)),'state'=>intval(I('state',0)),'add_time'=>time()]); $id=M('Course')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function courseUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['course_name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['semester_id','teacher_id','class_id','subject_id','week_id','interval_id','room_id','state'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} $where=$this->_buildWhere(['id'=>$id]); M('Course')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function courseDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $where=$this->_buildWhere(['id'=>$id]); M('Course')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 权限管理 =====

    public function roleList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Role')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Role')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function roleCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['name'=>I('name'),'remark'=>I('remark',''),'status'=>intval(I('status',1)),'add_time'=>time()]; $id=M('Role')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function roleUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name','remark'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} $v=I('status','');if($v!=='')$data['status']=intval($v); M('Role')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function roleDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); M('Role')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function powerList() { $list=M('Power')->order('sort asc,id asc')->select(); $tree=[]; $map=[]; foreach($list as $v){$map[$v['id']]=$v;} foreach($list as &$v){if($v['pid']>0 && isset($map[$v['pid']])){$map[$v['pid']]['children'][]=&$v;}else{$tree[]=&$v;}} unset($v); $this->json(0,'success',$tree); }

    // ===== 管理员管理 =====

    public function adminList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $keyword=trim(I('keyword','')); if($keyword!=''){$where['username']=['like','%'.$keyword.'%'];} $list=M('Admin')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Admin')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function adminCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $username=I('username'); $password=I('password'); if(empty($username)||empty($password))$this->json(-1,'参数错误'); $salt=sprintf('%06d',rand(0,999999)); $data=['username'=>$username,'mobile'=>I('mobile',''),'role_id'=>intval(I('role_id',0)),'campus_id'=>intval(I('campus_id',0)),'login_salt'=>$salt,'login_pwd'=>md5($salt.$password),'is_super'=>intval(I('is_super',0)),'login_total'=>0,'login_time'=>0,'add_time'=>time()]; $id=M('Admin')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function adminUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['username','mobile'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} foreach(['role_id','campus_id','is_super'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);} M('Admin')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function adminDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); M('Admin')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 成绩管理 =====

    public function fractionList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $keyword=trim(I('keyword','')); if($keyword!=''){$where['s.username|f.remark']=['like','%'.$keyword.'%'];} $list=M('Fraction')->alias('f')->join('LEFT JOIN sc_student s ON f.student_id=s.id')->join('LEFT JOIN sc_course c ON f.course_id=c.id')->field('f.*,s.username as student_name,c.course_name')->where($where)->order('f.id desc')->limit($offset,$ps)->select(); $total=M('Fraction')->alias('f')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function fractionCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['student_id'=>intval(I('studentId')),'course_id'=>intval(I('courseId')),'score'=>I('score',''),'exam_date'=>I('examDate',''),'remark'=>I('remark',''),'add_time'=>time()]; $id=M('Fraction')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function fractionDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); M('Fraction')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 学习计划 =====

    public function studyPlanList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('StudyPlan')->alias('p')->join('LEFT JOIN sc_student s ON p.student_id=s.id')->field('p.*,s.username as student_name')->where($where)->order('p.id desc')->limit($offset,$ps)->select(); $total=M('StudyPlan')->alias('p')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function studyPlanCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['student_id'=>intval(I('studentId')),'course_id'=>intval(I('courseId',0)),'title'=>I('title'),'content'=>I('content',''),'target_date'=>I('targetDate',''),'status'=>intval(I('status',0)),'create_time'=>time()]; $id=M('StudyPlan')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 成长档案 =====

    public function growthList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('GrowthRecord')->alias('g')->join('LEFT JOIN sc_student s ON g.student_id=s.id')->field('g.*,s.username as student_name')->where($where)->order('g.id desc')->limit($offset,$ps)->select(); $total=M('GrowthRecord')->alias('g')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function growthCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['student_id'=>intval(I('studentId')),'type'=>intval(I('type',0)),'content'=>I('content',''),'images'=>I('images',''),'create_time'=>time()]; $id=M('GrowthRecord')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 积分管理 =====

    public function pointList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Point')->alias('p')->join('LEFT JOIN sc_student s ON p.student_id=s.id')->field('p.*,s.username as student_name')->where($where)->order('p.id desc')->limit($offset,$ps)->select(); $total=M('Point')->alias('p')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function pointCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['student_id'=>intval(I('studentId')),'type'=>intval(I('type',0)),'point'=>intval(I('point',0)),'remark'=>I('remark',''),'create_time'=>time()]; $id=M('Point')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 招生管理 =====

    public function enrollmentList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $keyword=trim(I('keyword','')); if($keyword!=''){$where['name|phone|source']=['like','%'.$keyword.'%'];} $list=M('Enrollment')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Enrollment')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function enrollmentUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; foreach(['name','phone','source'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;} $v=I('status','');if($v!=='')$data['status']=intval($v); M('Enrollment')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    // ===== 营销活动 =====

    public function marketingList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Marketing')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Marketing')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function marketingCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['title'=>I('title'),'content'=>I('content',''),'type'=>intval(I('type',0)),'status'=>intval(I('status',0)),'start_time'=>I('startTime',''),'end_time'=>I('endTime',''),'create_time'=>time()]; $id=M('Marketing')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 反馈管理 =====

    public function feedbackList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Feedback')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('Feedback')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function feedbackUpdate() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); $data=[]; $v=I('status','');if($v!=='')$data['status']=intval($v); $v2=I('remark','');if($v2!=='')$data['remark']=$v2; if(empty($data))$this->json(-1,'参数错误'); M('Feedback')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    // ===== 财务管理 =====

    public function financeStats() { $totalIncome=M('Order')->where("status=1")->sum('pay_amount'); $totalExpense=M('Expense')->sum('amount'); if($totalExpense===null)$totalExpense=0; $totalRefund=M('Order')->where("status=-1")->sum('pay_amount'); if($totalRefund===null)$totalRefund=0; $monthStart=strtotime(date('Y-m-01')); $monthIncome=M('Order')->where("status=1 AND pay_time>={$monthStart}")->sum('pay_amount'); if($monthIncome===null)$monthIncome=0; $this->json(0,'success',['totalIncome'=>floatval($totalIncome),'totalExpense'=>floatval($totalExpense),'totalRefund'=>floatval($totalRefund),'monthIncome'=>floatval($monthIncome)]); }

    // ===== 数据统计 =====

    public function statsOverview() { $studentTotal=M('Student')->count(); $teacherTotal=M('Teacher')->count(); $courseTotal=M('Course')->count(); $orderTotal=M('Order')->count(); $this->json(0,'success',['studentTotal'=>$studentTotal,'teacherTotal'=>$teacherTotal,'courseTotal'=>$courseTotal,'orderTotal'=>$orderTotal]); }

    public function statsRevenue() { $list=M('Order')->field("FROM_UNIXTIME(pay_time,'%Y-%m') as month,SUM(pay_amount) as amount")->where("status=1")->group("FROM_UNIXTIME(pay_time,'%Y-%m')")->order('month asc')->select(); $this->json(0,'success',$list); }

    public function statsStudent() { $total=M('Student')->count(); $active=M('Student')->where("status=1")->count(); $inactive=M('Student')->where("status=0")->count(); $this->json(0,'success',['total'=>$total,'active'=>$active,'inactive'=>$inactive]); }

    // ===== 冒泡广场 =====

    public function bubbleList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Mood')->alias('m')->join('LEFT JOIN sc_user u ON m.uid=u.id')->field('m.*,u.nickname')->where($where)->order('m.id desc')->limit($offset,$ps)->select(); $total=M('Mood')->alias('m')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function bubbleDelete() { $id=intval(I('id')); if($id<=0)$this->json(-1,'参数错误'); M('Mood')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 打卡活动 =====

    public function checkinList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('CheckinActivity')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('CheckinActivity')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function checkinCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['title'=>I('title'),'start_date'=>I('startDate',''),'end_date'=>I('endDate',''),'status'=>intval(I('status',0)),'create_time'=>time()]; $id=M('CheckinActivity')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 直播管理 =====

    public function liveList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Live')->alias('l')->join('LEFT JOIN sc_teacher t ON l.teacher_id=t.id')->field('l.*,t.username as teacher_name')->where($where)->order('l.id desc')->limit($offset,$ps)->select(); $total=M('Live')->alias('l')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function liveCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['title'=>I('title'),'teacher_id'=>intval(I('teacherId',0)),'start_time'=>I('startTime',''),'status'=>intval(I('status',0)),'create_time'=>time()]; $id=M('Live')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 短信管理 =====

    public function smsList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('SmsLog')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('SmsLog')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    // ===== 导航管理 =====

    public function navHeaderList() { $list=M('Navigation')->where(['position'=>'top'])->order('sort asc')->select(); $this->json(0,'success',$list); }

    public function navFooterList() { $list=M('Navigation')->where(['position'=>'bottom'])->order('sort asc')->select(); $this->json(0,'success',$list); }

    // ===== 系统配置 =====

    public function configList() { $list=M('Config')->select(); $this->json(0,'success',$list); }

    public function configSave() { if(!IS_POST)$this->json(-1,'非法请求'); $name=I('name'); $value=I('value'); if(empty($name))$this->json(-1,'参数错误'); $exists=M('Config')->where(['name'=>$name])->find(); if($exists){M('Config')->where(['name'=>$name])->save(['value'=>$value]);}else{M('Config')->add(['name'=>$name,'value'=>$value]);} $this->json(0,'保存成功'); }

    // ===== 缓存工具 =====

    public function cacheStats() { $runtimePath=RUNTIME_PATH; $totalSize=0; $fileCount=0; $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($runtimePath,\RecursiveDirectoryIterator::SKIP_DOTS)); foreach($it as $f){$totalSize+=$f->getSize();$fileCount++;} $this->json(0,'success',['size'=>$totalSize,'fileCount'=>$fileCount,'sizeFormatted'=>$totalSize>1048576?round($totalSize/1048576,2).' MB':round($totalSize/1024,2).' KB']); }

    // ===== 节次管理 (sc_interval) =====

    public function intervalList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Interval')->where($where)->order('sort asc,id desc')->limit($offset,$ps)->select(); $total=M('Interval')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function intervalCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]); $id=M('Interval')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 友链管理 (sc_link) =====

    public function linkList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Link')->where($where)->order('sort asc,id desc')->limit($offset,$ps)->select(); $total=M('Link')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function linkCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'url'=>I('url'),'describe'=>I('describe',''),'sort'=>intval(I('sort',0)),'is_enable'=>intval(I('is_enable',1)),'is_new_window_open'=>intval(I('is_new_window_open',1)),'add_time'=>time()]); $id=M('Link')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function linkUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$data=[];foreach(['name','url','describe'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;}foreach(['sort','is_enable','is_new_window_open'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);}$where=$this->_buildWhere(['id'=>$id]);M('Link')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function linkDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$where=$this->_buildWhere(['id'=>$id]);M('Link')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 区域管理 (sc_region, 无campus_id) =====

    public function regionList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $list=M('Region')->where([])->order('sort asc,id desc')->limit($offset,$ps)->select(); $total=M('Region')->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function regionCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=['name'=>I('name'),'pid'=>intval(I('pid',0)),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]; $id=M('Region')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function regionUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$data=[];foreach(['name'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;}foreach(['pid','is_enable','sort'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);}M('Region')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function regionDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Region')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 学期管理 (sc_semester) =====

    public function semesterList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Semester')->where($where)->order('sort asc,id desc')->limit($offset,$ps)->select(); $total=M('Semester')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function semesterCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]); $id=M('Semester')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 星期设置 (sc_week) =====

    public function weekList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('Week')->where($where)->order('sort asc,id desc')->limit($offset,$ps)->select(); $total=M('Week')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function weekCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['name'=>I('name'),'is_enable'=>intval(I('is_enable',1)),'sort'=>intval(I('sort',0)),'add_time'=>time()]); $id=M('Week')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ===== 自定义视图 (sc_custom_view) =====

    public function customViewList() { $page=max(1,intval(I('page',1))); $ps=min(50,max(5,intval(I('pageSize',15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M('CustomView')->where($where)->order('id desc')->limit($offset,$ps)->select(); $total=M('CustomView')->where($where)->count(); $this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function customViewCreate() { if(!IS_POST)$this->json(-1,'非法请求'); $data=$this->_buildWhere(['title'=>I('title'),'content'=>I('content',''),'is_enable'=>intval(I('is_enable',1)),'is_header'=>intval(I('is_header',0)),'is_footer'=>intval(I('is_footer',0)),'is_full_screen'=>intval(I('is_full_screen',0)),'image'=>I('image',''),'image_count'=>intval(I('image_count',0)),'access_count'=>0,'add_time'=>time()]); $id=M('CustomView')->add($data); $id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function customViewUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$data=[];foreach(['title','content','image'] as $f){$v=I($f,'');if($v!=='')$data[$f]=$v;}foreach(['is_enable','is_header','is_footer','is_full_screen','image_count'] as $f){$v=I($f,'');if($v!=='')$data[$f]=intval($v);}$where=$this->_buildWhere(['id'=>$id]);M('CustomView')->where($where)->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function customViewDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$where=$this->_buildWhere(['id'=>$id]);M('CustomView')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 权限列表 (sc_power, 无campus_id, tree) =====

    public function cacheClear() { system('rm -rf '.escapeshellarg(RUNTIME_PATH.'Cache').' '.escapeshellarg(RUNTIME_PATH.'Temp').' '.escapeshellarg(RUNTIME_PATH.'Data')); $this->json(0,'清理完成'); }

    // ===== 站点设置 (sc_config) =====

    public function siteConfig() { $configs=M('Config')->where("name LIKE 'site_%'")->select(); $result=[]; foreach($configs as $c){$result[$c['name']]=$c['value'];} $this->json(0,'success',$result); }

    public function siteConfigSave() { if(!IS_POST)$this->json(-1,'非法请求'); $data=I('data'); if(empty($data)||!is_array($data))$this->json(-1,'参数错误'); foreach($data as $k=>$v){$exists=M('Config')->where(['name'=>$k])->find();if($exists){M('Config')->where(['name'=>$k])->save(['value'=>$v]);}else{M('Config')->add(['name'=>$k,'value'=>$v]);}} $this->json(0,'保存成功'); }

    // ===== 邮箱配置 (sc_config) =====

    public function emailConfig() { $configs=M('Config')->where("name LIKE 'email_%'")->select(); $result=[]; foreach($configs as $c){$result[$c['name']]=$c['value'];} $this->json(0,'success',$result); }

    public function emailConfigSave() { if(!IS_POST)$this->json(-1,'非法请求'); $data=I('data'); if(empty($data)||!is_array($data))$this->json(-1,'参数错误'); foreach($data as $k=>$v){$exists=M('Config')->where(['name'=>$k])->find();if($exists){M('Config')->where(['name'=>$k])->save(['value'=>$v]);}else{M('Config')->add(['name'=>$k,'value'=>$v]);}} $this->json(0,'保存成功'); }

    // ===== 报表数据 =====

    public function reportFinance() { $today=strtotime(date('Y-m-d')); $thisMonth=mktime(0,0,0,date('m'),1,date('Y')); $todayIncome=(float)M('Order')->where("add_time>=$today")->sum('amount'); $monthIncome=(float)M('Order')->where("add_time>=$thisMonth")->sum('amount'); $totalIncome=(float)M('Order')->sum('amount'); $todayOrders=(int)M('Order')->where("add_time>=$today")->count(); $this->json(0,'success',['todayIncome'=>$todayIncome,'monthIncome'=>$monthIncome,'totalIncome'=>$totalIncome,'todayOrders'=>$todayOrders]); }

    public function reportStudent() { $total=(int)M('Student')->count(); $active=(int)M('Student')->where(['status'=>1])->count(); $newToday=(int)M('Student')->where("add_time>=".strtotime(date('Y-m-d')))->count(); $this->json(0,'success',['total'=>$total,'active'=>$active,'newToday'=>$newToday]); }

    // ===== SEO =====

    public function seoConfig() { $list=M('Seo')->select(); $config=[]; foreach($list as $v){$config[$v['page']]=$v;} $this->json(0,'success',$config); }

    public function seoSave() { if(!IS_POST)$this->json(-1,'非法请求'); $page=I('page'); if(empty($page))$this->json(-1,'参数错误'); $data=['page'=>$page,'title'=>I('title',''),'keywords'=>I('keywords',''),'description'=>I('description','')]; $exists=M('Seo')->where(['page'=>$page])->find(); if($exists){M('Seo')->where(['page'=>$page])->save($data);}else{M('Seo')->add($data);} $this->json(0,'保存成功'); }

    // ========== 系统设置 ==========

    public function settings()
    {
        $act = I('act', 'get');
        if ($act === 'get') {
            // 从 sc_config 表读取设置
            $configs = M('Config')->select();
            $result = [];
            foreach ($configs as $c) {
                $result[$c['name']] = $c['value'];
            }
            $this->json(0, 'success', [
                'siteName' => $result['站点标题'] ?? 'SUOJIAN',
                'phone'    => $result['联系电话'] ?? '',
                'email'    => $result['邮箱'] ?? '',
                'address'  => $result['通讯地址'] ?? '',
            ]);
        } elseif ($act === 'save') {
            if (!IS_POST) $this->json(-1, '非法请求');
            $fields = [
                'siteName' => '站点标题',
                'phone'    => '联系电话',
                'email'    => '邮箱',
                'address'  => '通讯地址',
            ];
            foreach ($fields as $key => $name) {
                $val = I($key, '');
                if ($val !== '') {
                    $exists = M('Config')->where(['name' => $name])->find();
                    if ($exists) {
                        M('Config')->where(['name' => $name])->save(['value' => $val]);
                    } else {
                        M('Config')->add(['name' => $name, 'value' => $val]);
                    }
                }
            }
            $this->json(0, '保存成功');
        } elseif ($act === 'changePwd') {
            if (!IS_POST) $this->json(-1, '非法请求');
            $old = I('old_pwd');
            $new = I('new_pwd');
            if (empty($old) || empty($new)) $this->json(-1, '参数错误');
            if (md5($this->admin_user['login_salt'] . $old) !== $this->admin_user['login_pwd']) {
                $this->json(-1, '当前密码错误');
            }
            $new_salt = sprintf('%06d', rand(0, 999999));
            $new_hash = md5($new_salt . $new);
            M('Admin')->where(['id' => $this->admin_user['id']])->save([
                'login_salt' => $new_salt,
                'login_pwd'  => $new_hash,
            ]);
            $this->json(0, '密码修改成功，请重新登录');
        }
    }

    // ===== 海报模板 (sc_poster_template) =====

    public function posterList() { $page=max(1,intval(I("page",1))); $ps=min(50,max(5,intval(I("pageSize",15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M("PosterTemplate")->where($where)->order("id desc")->limit($offset,$ps)->select(); $total=M("PosterTemplate")->where($where)->count(); $this->json(0,"success",["list"=>$list,"total"=>$total,"page"=>$page,"pageSize"=>$ps]); }

    public function posterCreate() { if(!IS_POST)$this->json(-1,"非法请求"); $data=$this->_buildWhere(["title"=>I("title"),"category"=>intval(I("category",1)),"thumbnail"=>I("thumbnail",""),"design_json"=>I("designJson",""),"canvas_width"=>intval(I("canvasWidth",750)),"canvas_height"=>intval(I("canvasHeight",1334)),"status"=>intval(I("status",1)),"create_time"=>time()]); $id=M("PosterTemplate")->add($data); $id?$this->json(0,"创建成功",["id"=>$id]):$this->json(-1,"创建失败"); }

    public function posterUpdate() { $id=intval(I("id"));if($id<=0)$this->json(-1,"参数错误");$data=[];foreach(["title","thumbnail","design_json"] as $f){$v=I($f,"");if($v!=="")$data[$f]=$v;}foreach(["category","canvas_width","canvas_height","status"] as $f){$v=I($f,"");if($v!=="")$data[$f]=intval($v);}$where=$this->_buildWhere(["id"=>$id]);M("PosterTemplate")->where($where)->save($data)!==false?$this->json(0,"更新成功"):$this->json(-1,"更新失败"); }

    public function posterDelete() { $id=intval(I("id"));if($id<=0)$this->json(-1,"参数错误");$where=$this->_buildWhere(["id"=>$id]);M("PosterTemplate")->where($where)->delete()?$this->json(0,"删除成功"):$this->json(-1,"删除失败"); }

    // ===== 资源管理 (sc_live_resource) =====

    public function resourceList() { $page=max(1,intval(I("page",1))); $ps=min(50,max(5,intval(I("pageSize",15)))); $offset=($page-1)*$ps; $where=$this->_buildWhere([]); $list=M("LiveResource")->where($where)->order("id desc")->limit($offset,$ps)->select(); $total=M("LiveResource")->where($where)->count(); $this->json(0,"success",["list"=>$list,"total"=>$total,"page"=>$page,"pageSize"=>$ps]); }

    public function resourceUpload() { $this->json(0,"占位接口，文件上传待实现",[]); }

    // ===== 别名方法 =====

    public function couponsList() { $this->couponList(); }

    public function packagesList() { $this->packageList(); }

    public function leadUpdate() { $this->leadsUpdate(); }

    public function leadDelete() { $this->leadsDelete(); }

    public function studentList() { $this->students(); }

    public function homeworkUpdate() { $this->homeworkUpdate(); }

    public function cacheInfo() { $this->cacheStats(); }

    public function weekDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Week')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    // ===== 补充CRUD端点 =====

    public function semesterDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Semester')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function intervalDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Interval')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function scheduleDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$where=$this->_buildWhere(['id'=>$id]);M('Schedule')->where($where)->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function leaveCreate() { if(!IS_POST)$this->json(-1,'非法请求');$data=['student_id'=>intval(I('student_id')),'reason'=>I('reason'),'start_date'=>I('start_date'),'end_date'=>I('end_date'),'add_time'=>time()];$id=M('Leave')->add($data);$id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    public function leaveDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Leave')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function leaveUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$data=[];$v=I('status','');if($v!=='')$data['status']=intval($v);$v2=I('remark','');if($v2!=='')$data['remark']=$v2;if(empty($data))$this->json(-1,'参数错误');M('Leave')->where(['id'=>$id])->save($data)!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function checkinDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('CheckinActivity')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function checkinUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$v=I('status','');if($v==='')$this->json(-1,'参数错误');M('CheckinActivity')->where(['id'=>$id])->save(['status'=>intval($v)])!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function liveDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Live')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function liveUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$v=I('status','');if($v==='')$this->json(-1,'参数错误');M('Live')->where(['id'=>$id])->save(['status'=>intval($v)])!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function messageCreate() { $this->messageSend(); }

    public function messageDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Message')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function reviewDelete() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');M('Review')->where(['id'=>$id])->delete()?$this->json(0,'删除成功'):$this->json(-1,'删除失败'); }

    public function reviewUpdate() { $id=intval(I('id'));if($id<=0)$this->json(-1,'参数错误');$v=I('status','');if($v==='')$this->json(-1,'参数错误');M('Review')->where(['id'=>$id])->save(['status'=>intval($v)])!==false?$this->json(0,'更新成功'):$this->json(-1,'更新失败'); }

    public function transferList() { $page=max(1,intval(I('page',1)));$ps=min(50,max(5,intval(I('pageSize',15))));$offset=($page-1)*$ps;$list=M('Transfer')->order('id desc')->limit($offset,$ps)->select();$total=M('Transfer')->count();$this->json(0,'success',['list'=>$list,'total'=>$total,'page'=>$page,'pageSize'=>$ps]); }

    public function transferCreate() { if(!IS_POST)$this->json(-1,'非法请求');$data=$this->_buildWhere(['student_id'=>intval(I('student_id')),'from_class_id'=>intval(I('from_class_id')),'to_class_id'=>intval(I('to_class_id')),'reason'=>I('reason',''),'status'=>intval(I('status',0)),'create_time'=>time()]);$id=M('Transfer')->add($data);$id?$this->json(0,'创建成功',['id'=>$id]):$this->json(-1,'创建失败'); }

    // ========== 工具方法 ==========

    /**
     * 构建带多租户过滤的条件
     */
    protected function _buildWhere($extra = [])
    {
        $where = $extra;
        $campus_id = $this->_campusId();
        if ($campus_id > 0) {
            $where['campus_id'] = $campus_id;
        }
        return $where;
    }

    protected function _campusId()
    {
        return isset($this->admin_user['campus_id']) ? intval($this->admin_user['campus_id']) : 0;
    }
}
?>
