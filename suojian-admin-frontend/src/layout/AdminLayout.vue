<template>
  <el-container class="admin-container">
    <!-- Sidebar -->
    <el-aside :width="sidebarCollapsed ? '64px' : '240px'" class="admin-sidebar">
      <div class="sidebar-header">
        <div class="logo-text" v-show="!sidebarCollapsed">SUOJIAN</div>
        <div class="logo-icon" v-show="sidebarCollapsed">S</div>
      </div>
      <el-menu
        :default-active="currentRoute"
        :collapse="sidebarCollapsed"
        :router="true"
        background-color="#1a1a2e"
        text-color="#a0aec0"
        active-text-color="#4fc3f7"
        class="sidebar-menu"
      >
        <el-menu-item index="/dashboard">
          <el-icon><Odometer /></el-icon>
          <span>仪表盘</span>
        </el-menu-item>
        <el-menu-item index="/articles">
          <el-icon><Document /></el-icon>
          <span>文章管理</span>
        </el-menu-item>
        <!-- 教务管理 -->
        <el-sub-menu index="edu">
          <template #title>
            <el-icon><Reading /></el-icon>
            <span>教务管理</span>
          </template>
          <el-menu-item index="/students">
            <el-icon><UserFilled /></el-icon>
            <span>学员管理</span>
          </el-menu-item>
          <el-menu-item index="/teachers">
            <el-icon><Avatar /></el-icon>
            <span>教师管理</span>
          </el-menu-item>
          <el-menu-item index="/transfers">
            <el-icon><Switch /></el-icon>
            <span>转校管理</span>
          </el-menu-item>
          <el-menu-item index="/schedules">
            <el-icon><Calendar /></el-icon>
            <span>排课管理</span>
          </el-menu-item>
          <el-menu-item index="/attendances">
            <el-icon><Finished /></el-icon>
            <span>考勤管理</span>
          </el-menu-item>
          <el-menu-item index="/student-courses">
            <el-icon><Timer /></el-icon>
            <span>课时管理</span>
          </el-menu-item>
        </el-sub-menu>
        <!-- 业务管理 -->
        <el-sub-menu index="biz">
          <template #title>
            <el-icon><List /></el-icon>
            <span>业务管理</span>
          </template>
          <el-menu-item index="/orders">
            <el-icon><Tickets /></el-icon>
            <span>订单管理</span>
          </el-menu-item>
          <el-menu-item index="/messages">
            <el-icon><Message /></el-icon>
            <span>消息管理</span>
          </el-menu-item>
        </el-sub-menu>
        <!-- 基础数据 -->
        <el-sub-menu index="base">
          <template #title>
            <el-icon><DataBoard /></el-icon>
            <span>基础数据</span>
          </template>
          <el-menu-item index="/subjects">
            <el-icon><School /></el-icon>
            <span>科目管理</span>
          </el-menu-item>
          <el-menu-item index="/classes">
            <el-icon><Notebook /></el-icon>
            <span>班级管理</span>
          </el-menu-item>
          <el-menu-item index="/rooms">
            <el-icon><HomeFilled /></el-icon>
            <span>教室管理</span>
          </el-menu-item>
          <el-menu-item index="/campuses">
            <el-icon><MapLocation /></el-icon>
            <span>校区管理</span>
          </el-menu-item>
          <el-menu-item index="/courses">
            <el-icon><Reading /></el-icon>
            <span>课程管理</span>
          </el-menu-item>
        </el-sub-menu>
        <!-- 教学评价 -->
        <el-sub-menu index="eval">
          <template #title>
            <el-icon><ChatLineSquare /></el-icon>
            <span>教学评价</span>
          </template>
          <el-menu-item index="/reviews">
            <el-icon><Star /></el-icon>
            <span>课后点评</span>
          </el-menu-item>
          <el-menu-item index="/leaves">
            <el-icon><Document /></el-icon>
            <span>请假管理</span>
          </el-menu-item>
          <el-menu-item index="/homeworks">
            <el-icon><EditPen /></el-icon>
            <span>作业管理</span>
          </el-menu-item>
          <el-menu-item index="/fractions">
            <el-icon><List /></el-icon>
            <span>成绩管理</span>
          </el-menu-item>
          <el-menu-item index="/plans">
            <el-icon><Notebook /></el-icon>
            <span>学习计划</span>
          </el-menu-item>
          <el-menu-item index="/growth">
            <el-icon><Opportunity /></el-icon>
            <span>成长档案</span>
          </el-menu-item>
          <el-menu-item index="/points">
            <el-icon><Coin /></el-icon>
            <span>积分管理</span>
          </el-menu-item>
        </el-sub-menu>
        <!-- 营销管理 -->
        <el-sub-menu index="market">
          <template #title>
            <el-icon><TrendCharts /></el-icon>
            <span>营销管理</span>
          </template>
          <el-menu-item index="/leads">
            <el-icon><User /></el-icon>
            <span>线索管理</span>
          </el-menu-item>
          <el-menu-item index="/coupons">
            <el-icon><Ticket /></el-icon>
            <span>优惠券</span>
          </el-menu-item>
          <el-menu-item index="/packages">
            <el-icon><Box /></el-icon>
            <span>套餐管理</span>
          </el-menu-item>
          <el-menu-item index="/enrollments">
            <el-icon><School /></el-icon>
            <span>招生管理</span>
          </el-menu-item>
          <el-menu-item index="/marketings">
            <el-icon><TrendCharts /></el-icon>
            <span>营销活动</span>
          </el-menu-item>
          <el-menu-item index="/feedbacks">
            <el-icon><ChatDotRound /></el-icon>
            <span>反馈管理</span>
          </el-menu-item>
          <el-menu-item index="/checkins">
            <el-icon><Select /></el-icon>
            <span>打卡活动</span>
          </el-menu-item>
          <el-menu-item index="/lives">
            <el-icon><VideoCamera /></el-icon>
            <span>直播管理</span>
          </el-menu-item>
          <el-menu-item index="/posters">
            <el-icon><Picture /></el-icon>
            <span>海报管理</span>
          </el-menu-item>
        </el-sub-menu>
        <el-sub-menu index="biz-menu">
          <template #title>
            <el-icon><Money /></el-icon>
            <span>财务业务</span>
          </template>
          <el-menu-item index="/orders">
            <el-icon><Tickets /></el-icon>
            <span>订单管理</span>
          </el-menu-item>
          <el-menu-item index="/finance">
            <el-icon><DataBoard /></el-icon>
            <span>财务管理</span>
          </el-menu-item>
          <el-menu-item index="/stats">
            <el-icon><Histogram /></el-icon>
            <span>数据统计</span>
          </el-menu-item>
          <el-menu-item index="/messages">
            <el-icon><Message /></el-icon>
            <span>消息管理</span>
          </el-menu-item>
          <el-menu-item index="/sms">
            <el-icon><Iphone /></el-icon>
            <span>短信记录</span>
          </el-menu-item>
          <el-menu-item index="/bubbles">
            <el-icon><ChatLineSquare /></el-icon>
            <span>冒泡广场</span>
          </el-menu-item>
        </el-sub-menu>
        <el-sub-menu index="content">
          <template #title>
            <el-icon><Grid /></el-icon>
            <span>内容管理</span>
          </template>
            <el-menu-item index="/comments">
              <el-icon><ChatDotSquare /></el-icon>
              <span>评论管理</span>
            </el-menu-item>
            <el-menu-item index="/categories">
              <el-icon><Folder /></el-icon>
              <span>分类管理</span>
            </el-menu-item>
        </el-sub-menu>
        <el-sub-menu index="system">
          <template #title>
            <el-icon><Setting /></el-icon>
            <span>系统管理</span>
          </template>
          <el-menu-item index="/users">
            <el-icon><User /></el-icon>
            <span>用户管理</span>
          </el-menu-item>
          <el-menu-item index="/settings">
            <el-icon><Tools /></el-icon>
            <span>系统设置</span>
          </el-menu-item>
          <el-menu-item index="/admin">
            <el-icon><UserFilled /></el-icon>
            <span>管理员管理</span>
          </el-menu-item>
          <el-menu-item index="/roles">
            <el-icon><Key /></el-icon>
            <span>角色管理</span>
          </el-menu-item>
          <el-menu-item index="/powers">
            <el-icon><Lock /></el-icon>
            <span>权限管理</span>
          </el-menu-item>
          <el-menu-item index="/courses">
            <el-icon><Reading /></el-icon>
            <span>课程管理</span>
          </el-menu-item>
          <el-menu-item index="/nav">
            <el-icon><Menu /></el-icon>
            <span>导航管理</span>
          </el-menu-item>
          <el-menu-item index="/configs">
            <el-icon><Setting /></el-icon>
            <span>系统配置</span>
          </el-menu-item>
          <el-menu-item index="/seo">
            <el-icon><Search /></el-icon>
            <span>SEO设置</span>
          </el-menu-item>
          <el-menu-item index="/themes">
            <el-icon><MagicStick /></el-icon>
            <span>主题管理</span>
          </el-menu-item>
          <el-menu-item index="/resources">
            <el-icon><Folder /></el-icon>
            <span>资源库</span>
          </el-menu-item>
          <el-menu-item index="/semesters">
            <el-icon><Calendar /></el-icon>
            <span>学期管理</span>
          </el-menu-item>
          <el-menu-item index="/weeks">
            <el-icon><Date /></el-icon>
            <span>星期设置</span>
          </el-menu-item>
          <el-menu-item index="/intervals">
            <el-icon><Time /></el-icon>
            <span>节次管理</span>
          </el-menu-item>
          <el-menu-item index="/links">
            <el-icon><Link /></el-icon>
            <span>友链管理</span>
          </el-menu-item>
          <el-menu-item index="/custom-views">
            <el-icon><Grid /></el-icon>
            <span>自定义视图</span>
          </el-menu-item>
          <el-menu-item index="/regions">
            <el-icon><MapLocation /></el-icon>
            <span>区域管理</span>
          </el-menu-item>
          <el-menu-item index="/site-config">
            <el-icon><Setting /></el-icon>
            <span>站点配置</span>
          </el-menu-item>
          <el-menu-item index="/email-config">
            <el-icon><Message /></el-icon>
            <span>邮箱配置</span>
          </el-menu-item>
          <el-menu-item index="/caches">
            <el-icon><Refresh /></el-icon>
            <span>缓存管理</span>
          </el-menu-item>
          <el-menu-item index="/reports">
            <el-icon><DataAnalysis /></el-icon>
            <span>报表管理</span>
          </el-menu-item>
        </el-sub-menu>
      </el-menu>
    </el-aside>

    <!-- Main Content -->
    <el-container>
      <!-- Header -->
      <el-header class="admin-header">
        <div class="header-left">
          <el-icon class="collapse-btn" @click="appStore.toggleSidebar" :size="20">
            <Fold v-if="!appStore.sidebarCollapsed" />
            <Expand v-else />
          </el-icon>
          <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/dashboard' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item v-if="currentTitle">{{ currentTitle }}</el-breadcrumb-item>
          </el-breadcrumb>
        </div>
        <div class="header-right">
          <el-tooltip content="消息通知" placement="bottom">
            <el-badge :value="3" class="badge-item">
              <el-icon :size="20"><Bell /></el-icon>
            </el-badge>
          </el-tooltip>
          <el-tooltip content="全屏" placement="bottom">
            <el-icon :size="20" class="header-icon" @click="toggleFullscreen">
              <FullScreen />
            </el-icon>
          </el-tooltip>
          <el-dropdown trigger="click">
            <div class="user-info">
              <el-avatar :size="32" class="user-avatar">A</el-avatar>
              <span class="username">管理员</span>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item>
                  <el-icon><User /></el-icon>个人中心
                </el-dropdown-item>
                <el-dropdown-item divided @click="handleLogout">
                  <el-icon><SwitchButton /></el-icon>退出登录
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <!-- Content -->
      <el-main class="admin-main">
        <router-view />
      </el-main>

      <!-- Footer -->
      <el-footer class="admin-footer">
        <span>SUOJIAN © 2026 — 让管理更简单</span>
      </el-footer>
    </el-container>
  </el-container>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '../stores/app'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()

const currentRoute = computed(() => route.path)
const currentTitle = computed(() => route.meta?.title)

function handleLogout() {
  appStore.logout()
  router.push('/login')
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}
</script>

<style scoped>
.admin-container {
  height: 100vh;
  background: #f0f2f5;
}

.admin-sidebar {
  background: #1a1a2e;
  transition: width 0.3s ease;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.06);
}

.sidebar-header {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.logo-text {
  font-size: 22px;
  font-weight: 700;
  background: linear-gradient(135deg, #4fc3f7, #7c4dff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: 3px;
}

.logo-icon {
  font-size: 22px;
  font-weight: 700;
  color: #4fc3f7;
}

.sidebar-menu {
  border-right: none;
  flex: 1;
}

.sidebar-menu:not(.el-menu--collapse) {
  width: 240px;
}

.admin-header {
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 60px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  z-index: 10;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.collapse-btn {
  cursor: pointer;
  color: #606266;
  transition: color 0.2s;
}
.collapse-btn:hover {
  color: #4fc3f7;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.header-icon {
  cursor: pointer;
  color: #606266;
  transition: color 0.2s;
}
.header-icon:hover {
  color: #4fc3f7;
}

.badge-item :deep(.el-badge__content) {
  background-color: #f56c6c;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 8px;
  transition: background 0.2s;
}
.user-info:hover {
  background: #f5f7fa;
}

.user-avatar {
  background: linear-gradient(135deg, #4fc3f7, #7c4dff);
}

.username {
  font-size: 14px;
  color: #303133;
}

.admin-main {
  padding: 20px;
  background: #f0f2f5;
  overflow-y: auto;
}

.admin-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 48px;
  color: #909399;
  font-size: 12px;
  background: #fff;
  border-top: 1px solid #ebeef5;
}
</style>
