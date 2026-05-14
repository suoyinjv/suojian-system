import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/login/LoginView.vue'),
  },
  {
    path: '/',
    component: () => import('../layout/AdminLayout.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/dashboard/DashboardView.vue'),
        meta: { title: '仪表盘' },
      },
      {
        path: 'articles',
        name: 'Articles',
        component: () => import('../views/articles/ArticleList.vue'),
        meta: { title: '文章管理' },
      },
      {
        path: 'categories',
        name: 'Categories',
        component: () => import('../views/categories/CategoryList.vue'),
        meta: { title: '分类管理' },
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/users/UserList.vue'),
        meta: { title: '用户管理' },
      },
      {
        path: 'comments',
        name: 'Comments',
        component: () => import('../views/comments/CommentList.vue'),
        meta: { title: '评论管理' },
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/settings/SettingsView.vue'),
        meta: { title: '系统设置' },
      },
      // ========== 教务管理 ==========
      {
        path: 'students',
        name: 'Students',
        component: () => import('../views/students/StudentList.vue'),
        meta: { title: '学员管理' },
      },
      {
        path: 'teachers',
        name: 'Teachers',
        component: () => import('../views/teachers/TeacherList.vue'),
        meta: { title: '教师管理' },
      },
      {
        path: 'schedules',
        name: 'Schedules',
        component: () => import('../views/schedules/ScheduleList.vue'),
        meta: { title: '排课管理' },
      },
      {
        path: 'attendances',
        name: 'Attendances',
        component: () => import('../views/attendances/AttendanceList.vue'),
        meta: { title: '考勤管理' },
      },
      // ========== 业务管理 ==========
      {
        path: 'orders',
        name: 'Orders',
        component: () => import('../views/orders/OrderList.vue'),
        meta: { title: '订单管理' },
      },
      {
        path: 'student-courses',
        name: 'StudentCourses',
        component: () => import('../views/studentCourses/StudentCourseList.vue'),
        meta: { title: '课时管理' },
      },
      {
        path: 'messages',
        name: 'Messages',
        component: () => import('../views/messages/MessageList.vue'),
        meta: { title: '消息管理' },
      },
      // ========== 基础数据 ==========
      {
        path: 'subjects',
        name: 'Subjects',
        component: () => import('../views/subjects/SubjectList.vue'),
        meta: { title: '科目管理' },
      },
      {
        path: 'campuses',
        name: 'Campuses',
        component: () => import('../views/campuses/CampusList.vue'),
        meta: { title: '校区管理' },
      },
      {
        path: 'classes',
        name: 'Classes',
        component: () => import('../views/classes/ClassList.vue'),
        meta: { title: '班级管理' },
      },
      {
        path: 'rooms',
        name: 'Rooms',
        component: () => import('../views/rooms/RoomList.vue'),
        meta: { title: '教室管理' },
      },
      // ========== 教学评价 ==========
      {
        path: 'reviews',
        name: 'Reviews',
        component: () => import('../views/reviews/ReviewList.vue'),
        meta: { title: '课后点评' },
      },
      {
        path: 'leaves',
        name: 'Leaves',
        component: () => import('../views/leaves/LeaveList.vue'),
        meta: { title: '请假管理' },
      },
      // ========== 营销管理 ==========
      {
        path: 'coupons',
        name: 'Coupons',
        component: () => import('../views/coupons/CouponList.vue'),
        meta: { title: '优惠券管理' },
      },
      {
        path: 'packages',
        name: 'Packages',
        component: () => import('../views/packages/PackageList.vue'),
        meta: { title: '套餐管理' },
      },
      {
        path: 'leads',
        name: 'Leads',
        component: () => import('../views/leads/LeadList.vue'),
        meta: { title: '线索管理' },
      },
      {
        path: 'homeworks',
        name: 'Homeworks',
        component: () => import('../views/homeworks/HomeworkList.vue'),
        meta: { title: '作业管理' },
      },
      // ========== 招生管理 ==========
      {
        path: 'enrollments',
        name: 'Enrollments',
        component: () => import('../views/enrollments/EnrollmentList.vue'),
        meta: { title: '招生管理' },
      },
      // ========== 营销活动 ==========
      {
        path: 'marketings',
        name: 'Marketings',
        component: () => import('../views/marketings/MarketingList.vue'),
        meta: { title: '营销活动' },
      },
      // ========== 反馈管理 ==========
      {
        path: 'feedbacks',
        name: 'Feedbacks',
        component: () => import('../views/feedbacks/FeedbackList.vue'),
        meta: { title: '反馈管理' },
      },
      // ========== 财务管理 ==========
      {
        path: 'finance',
        name: 'Finance',
        component: () => import('../views/finance/FinanceView.vue'),
        meta: { title: '财务管理' },
      },
      // ========== 数据统计 ==========
      {
        path: 'stats',
        name: 'Stats',
        component: () => import('../views/stats/StatsView.vue'),
        meta: { title: '数据统计' },
      },
      // ========== 冒泡广场 ==========
      {
        path: 'bubbles',
        name: 'Bubbles',
        component: () => import('../views/bubbles/BubbleList.vue'),
        meta: { title: '冒泡广场' },
      },
      // ========== 系统扩展 ==========
      {
        path: 'roles',
        name: 'Roles',
        component: () => import('../views/roles/RoleList.vue'),
        meta: { title: '角色管理' },
      },
      {
        path: 'admin',
        name: 'AdminList',
        component: () => import('../views/admin/AdminList.vue'),
        meta: { title: '管理员管理' },
      },
      // ========== 教学业务 ==========
      {
        path: 'fractions',
        name: 'Fractions',
        component: () => import('../views/fractions/FractionList.vue'),
        meta: { title: '成绩管理' },
      },
      {
        path: 'plans',
        name: 'StudyPlans',
        component: () => import('../views/plans/StudyPlanList.vue'),
        meta: { title: '学习计划' },
      },
      {
        path: 'growth',
        name: 'Growth',
        component: () => import('../views/growth/GrowthList.vue'),
        meta: { title: '成长档案' },
      },
      {
        path: 'points',
        name: 'Points',
        component: () => import('../views/points/PointList.vue'),
        meta: { title: '积分管理' },
      },
      // ========== 运营管理 ==========
      {
        path: 'checkins',
        name: 'Checkins',
        component: () => import('../views/checkins/CheckinList.vue'),
        meta: { title: '打卡活动' },
      },
      {
        path: 'lives',
        name: 'Lives',
        component: () => import('../views/lives/LiveList.vue'),
        meta: { title: '直播管理' },
      },
      // ========== 消息配置 ==========
      {
        path: 'sms',
        name: 'Sms',
        component: () => import('../views/sms/SmsList.vue'),
        meta: { title: '短信记录' },
      },
      // ========== 系统配置 ==========
      {
        path: 'nav',
        name: 'Nav',
        component: () => import('../views/nav/NavList.vue'),
        meta: { title: '导航管理' },
      },
      {
        path: 'configs',
        name: 'Configs',
        component: () => import('../views/configs/ConfigList.vue'),
        meta: { title: '系统配置' },
      },
      {
        path: 'seo',
        name: 'Seo',
        component: () => import('../views/seo/SeoView.vue'),
        meta: { title: 'SEO设置' },
      },
      // ========== 课程管理 ==========
      {
        path: 'courses',
        name: 'Courses',
        component: () => import('../views/courses/CourseList.vue'),
        meta: { title: '课程管理' },
      },
      // ========== 学期管理 ==========
      {
        path: 'semesters',
        name: 'Semesters',
        component: () => import('../views/semesters/SemesterList.vue'),
        meta: { title: '学期管理' },
      },
      // ========== 星期设置 ==========
      {
        path: 'weeks',
        name: 'Weeks',
        component: () => import('../views/weeks/WeekList.vue'),
        meta: { title: '星期设置' },
      },
      // ========== 节次管理 ==========
      {
        path: 'intervals',
        name: 'Intervals',
        component: () => import('../views/intervals/IntervalList.vue'),
        meta: { title: '节次管理' },
      },
      // ========== 友链管理 ==========
      {
        path: 'links',
        name: 'Links',
        component: () => import('../views/links/LinkList.vue'),
        meta: { title: '友链管理' },
      },
      // ========== 自定义视图 ==========
      {
        path: 'custom-views',
        name: 'CustomViews',
        component: () => import('../views/customViews/CustomViewList.vue'),
        meta: { title: '自定义视图' },
      },
      // ========== 缓存管理 ==========
      {
        path: 'caches',
        name: 'Caches',
        component: () => import('../views/caches/CacheView.vue'),
        meta: { title: '缓存管理' },
      },
      // ========== 站点配置 ==========
      {
        path: 'site-config',
        name: 'SiteConfig',
        component: () => import('../views/configs/ConfigView.vue'),
        meta: { title: '站点配置' },
      },
      // ========== 邮箱配置 ==========
      {
        path: 'email-config',
        name: 'EmailConfig',
        component: () => import('../views/emails/EmailView.vue'),
        meta: { title: '邮箱配置' },
      },
      // ========== 区域管理 ==========
      {
        path: 'regions',
        name: 'Regions',
        component: () => import('../views/regions/RegionList.vue'),
        meta: { title: '区域管理' },
      },
      // ========== 报表管理 ==========
      {
        path: 'reports',
        name: 'Reports',
        component: () => import('../views/reports/ReportView.vue'),
        meta: { title: '报表管理' },
      },
      // ========== 权限管理 ==========
      {
        path: 'powers',
        name: 'Powers',
        component: () => import('../views/powers/PowerList.vue'),
        meta: { title: '权限管理' },
      },
      // ========== 转校管理 ==========
      {
        path: 'transfers',
        name: 'Transfers',
        component: () => import('../views/transfers/TransferList.vue'),
        meta: { title: '转校管理' },
      },
      // ========== 海报管理 ==========
      {
        path: 'posters',
        name: 'Posters',
        component: () => import('../views/posters/PosterList.vue'),
        meta: { title: '海报管理' },
      },
      // ========== 主题管理 ==========
      {
        path: 'themes',
        name: 'Themes',
        component: () => import('../views/themes/ThemeView.vue'),
        meta: { title: '主题管理' },
      },
      // ========== 资源库 ==========
      {
        path: 'resources',
        name: 'Resources',
        component: () => import('../views/resources/ResourceList.vue'),
        meta: { title: '资源库' },
      },
      // ========== 排课中心 ==========
      {
        path: 'schedules-center',
        name: 'ScheduleCenter',
        component: () => import('../views/schedules/ScheduleCenter.vue'),
        meta: { title: '排课中心' },
      },
      // ========== 1v1预约 ==========
      {
        path: 'reservations',
        name: 'Reservations',
        component: () => import('../views/reservations/ReservationList.vue'),
        meta: { title: '1v1预约' },
      },
      // ========== 课时包 ==========
      {
        path: 'student-packages',
        name: 'StudentPackages',
        component: () => import('../views/studentPackages/StudentPackageList.vue'),
        meta: { title: '课时包管理' },
      },
      // ========== 托管签到 ==========
      {
        path: 'aftercare',
        name: 'Aftercare',
        component: () => import('../views/aftercare/AftercareCheckin.vue'),
        meta: { title: '托管签到' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/error/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory('/suojian-admin'),
  routes,
})

// Auth guard
router.beforeEach((to, from, next) => {
  const token = sessionStorage.getItem('token')
  if (to.name !== 'Login' && !token) {
    next({ name: 'Login' })
  } else {
    next()
  }
})

export default router
