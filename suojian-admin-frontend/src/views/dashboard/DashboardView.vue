<template>
  <div class="dashboard">
    <!-- Stats Cards -->
    <el-row :gutter="20" class="stats-row">
      <el-col :xs="12" :sm="6" v-for="card in statsCards" :key="card.label">
        <div class="stat-card" :style="{ '--accent': card.color }">
          <div class="stat-icon" :style="{ background: `${card.color}18` }">
            <el-icon :size="24" :color="card.color"><component :is="card.icon" /></el-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ card.value }}</span>
            <span class="stat-label">{{ card.label }}</span>
          </div>
          <div class="stat-trend" :class="card.trend >= 0 ? 'up' : 'down'">
            <el-icon><Top v-if="card.trend >= 0" /><Bottom v-else /></el-icon>
            {{ Math.abs(card.trend) }}%
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Charts & Quick Actions -->
    <el-row :gutter="20" class="content-row">
      <el-col :xs="24" :lg="16">
        <el-card class="content-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">访问趋势</span>
              <el-radio-group v-model="chartRange" size="small">
                <el-radio-button value="week">本周</el-radio-button>
                <el-radio-button value="month">本月</el-radio-button>
                <el-radio-button value="year">全年</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div class="chart-area">
            <div class="bar-chart">
              <div
                v-for="(v, i) in chartData"
                :key="i"
                class="bar-item"
                :style="{ height: v + '%', '--delay': i * 0.08 + 's' }"
              >
                <span class="bar-value">{{ v }}</span>
              </div>
            </div>
            <div class="chart-labels">
              <span v-for="l in chartLabels" :key="l">{{ l }}</span>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :xs="24" :lg="8">
        <el-card class="content-card" shadow="never">
          <template #header><span class="card-title">快捷操作</span></template>
          <div class="quick-actions">
            <div class="action-item" @click="$router.push('/articles')">
              <div class="action-icon" style="background:#e3f2fd"><el-icon size="20" color="#4fc3f7"><EditPen /></el-icon></div>
              <span>发布文章</span>
            </div>
            <div class="action-item">
              <div class="action-icon" style="background:#f3e5f5"><el-icon size="20" color="#7c4dff"><Upload /></el-icon></div>
              <span>上传文件</span>
            </div>
            <div class="action-item">
              <div class="action-icon" style="background:#fff3e0"><el-icon size="20" color="#ff6b35"><Message /></el-icon></div>
              <span>系统消息</span>
            </div>
            <div class="action-item">
              <div class="action-icon" style="background:#e8f5e9"><el-icon size="20" color="#00c853"><DataAnalysis /></el-icon></div>
              <span>数据备份</span>
            </div>
          </div>
        </el-card>

        <el-card class="content-card" shadow="never" style="margin-top:16px">
          <template #header><span class="card-title">系统信息</span></template>
          <div class="system-info">
            <div class="info-row"><span>系统版本</span><span class="info-val">SUOJIAN v1.0</span></div>
            <div class="info-row"><span>前端框架</span><span class="info-val">Vue 3 + Vite</span></div>
            <div class="info-row"><span>UI 组件库</span><span class="info-val">Element Plus</span></div>
            <div class="info-row"><span>后端框架</span><span class="info-val">ThinkPHP 3.2</span></div>
            <div class="info-row"><span>数据库</span><span class="info-val">MySQL 8.0</span></div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Recent Articles -->
    <el-row :gutter="20" class="content-row">
      <el-col :span="24">
        <el-card class="content-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">最近文章</span>
              <el-link type="primary" :underline="false" @click="$router.push('/articles')">
                查看全部 <el-icon><ArrowRight /></el-icon>
              </el-link>
            </div>
          </template>
          <el-table :data="recentArticles" stripe style="width:100%">
            <el-table-column prop="id" label="ID" width="60" align="center" />
            <el-table-column prop="title" label="标题" min-width="220" />
            <el-table-column prop="category" label="分类" width="110" align="center">
              <template #default="{ row }">
                <el-tag size="small" effect="plain">{{ row.category }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="views" label="浏览" width="90" align="center">
              <template #default="{ row }">
                <span style="color:#909399;font-size:13px">{{ row.views }}</span>
              </template>
            </el-table-column>
            <el-table-column label="状态" width="100" align="center">
              <template #default="{ row }">
                <el-tag :type="row.status === 'published' ? 'success' : 'info'" size="small">{{ row.status === 'published' ? '已发布' : '草稿' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="date" label="日期" width="120" align="center" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { View, User, EditPen, Document, Top, Bottom, ArrowRight, Upload, Message, DataAnalysis } from '@element-plus/icons-vue'
import { getDashboardStats, getRecentArticles } from '../../api/dashboard'

const chartRange = ref('year')
const chartData = ref([45, 62, 38, 78, 55, 88, 72, 60, 90, 42, 68, 50])
const chartLabels = ref(['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'])
const recentArticles = ref([])
const statsCards = ref([
  { icon: 'View', label: '今日访问', value: '1,284', color: '#4fc3f7', trend: 12 },
  { icon: 'User', label: '用户总数', value: '3,621', color: '#7c4dff', trend: 8 },
  { icon: 'EditPen', label: '文章总数', value: '256', color: '#00c853', trend: -3 },
  { icon: 'Document', label: '待审核', value: '12', color: '#ff6b35', trend: 5 },
])

onMounted(async () => {
  try {
    const [stats, articles] = await Promise.all([getDashboardStats(), getRecentArticles()])
    statsCards.value = [
      { icon: 'View', label: '今日访问', value: stats.todayViews.toLocaleString(), color: '#4fc3f7', trend: stats.viewTrend },
      { icon: 'User', label: '用户总数', value: stats.totalUsers.toLocaleString(), color: '#7c4dff', trend: stats.userTrend },
      { icon: 'EditPen', label: '文章总数', value: stats.totalArticles.toLocaleString(), color: '#00c853', trend: stats.articleTrend },
      { icon: 'Document', label: '待审核', value: stats.pendingComments.toString(), color: '#ff6b35', trend: stats.commentTrend },
    ]
    chartData.value = stats.visitData
    chartLabels.value = stats.visitLabels
    recentArticles.value = articles
  } catch (e) {
    console.error('[dashboard] error:');
    // use mock data already set
  }
})
</script>

<style scoped>
.dashboard { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.stats-row { margin-bottom: 20px; }
.stat-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; position: relative; border: 1px solid #ebeef5; transition: all 0.3s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-info { display: flex; flex-direction: column; flex: 1; }
.stat-value { font-size: 26px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; }
.stat-trend { position: absolute; top: 12px; right: 12px; font-size: 12px; display: flex; align-items: center; gap: 2px; padding: 2px 8px; border-radius: 10px; }
.stat-trend.up { color: #00c853; background: #e8f5e9; }
.stat-trend.down { color: #f56c6c; background: #fbe9e7; }
.content-row { margin-bottom: 20px; }
.content-card { border-radius: 12px; border: 1px solid #ebeef5; }
.card-header { display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
.bar-chart { display: flex; align-items: flex-end; height: 180px; gap: 8px; padding: 0 10px; }
.bar-item { flex: 1; background: linear-gradient(180deg, #4fc3f7, #2196f3); border-radius: 6px 6px 0 0; position: relative; min-height: 8px; animation: barGrow 0.8s ease-out forwards; animation-delay: var(--delay); opacity: 0; }
@keyframes barGrow { from { opacity: 0; transform: scaleY(0); transform-origin: bottom; } to { opacity: 1; transform: scaleY(1); } }
.bar-value { position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 11px; color: #606266; font-weight: 500; }
.chart-labels { display: flex; gap: 8px; padding: 8px 10px 0; }
.chart-labels span { flex: 1; text-align: center; font-size: 11px; color: #909399; }
.quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.action-item { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 12px; background: #f5f7fa; border-radius: 10px; cursor: pointer; transition: all 0.3s; }
.action-item:hover { background: #e8f4fd; transform: translateY(-2px); }
.action-item span { font-size: 13px; color: #606266; }
.action-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.system-info { display: flex; flex-direction: column; gap: 0; }
.info-row { display: flex; justify-content: space-between; font-size: 13px; color: #606266; padding: 8px 0; border-bottom: 1px dashed #f0f0f0; }
.info-row:last-child { border-bottom: none; }
.info-val { color: #303133; font-weight: 500; }
</style>
