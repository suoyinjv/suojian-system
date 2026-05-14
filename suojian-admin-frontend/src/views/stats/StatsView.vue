<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>数据统计</h2>
        <p>总览数据、学员统计与营收趋势</p>
      </div>
    </div>

    <!-- Overview Cards -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6" v-for="card in overviewCards" :key="card.label">
        <div class="stat-card" :style="{ '--accent': card.color }">
          <div class="stat-icon" :style="{ background: `${card.color}18` }">
            <el-icon :size="22" :color="card.color"><component :is="card.icon" /></el-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ card.value }}</span>
            <span class="stat-label">{{ card.label }}</span>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Charts Row -->
    <el-row :gutter="20" class="content-row">
      <!-- Revenue Trend -->
      <el-col :xs="24" :lg="12">
        <el-card class="chart-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">营收趋势</span>
              <el-radio-group v-model="revenueRange" size="small" @change="loadRevenue">
                <el-radio-button value="week">本周</el-radio-button>
                <el-radio-button value="month">本月</el-radio-button>
                <el-radio-button value="year">全年</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div class="chart-area">
            <div class="bar-chart">
              <div
                v-for="(v, i) in revenueData"
                :key="i"
                class="bar-item"
                :style="{ height: barHeight(v, revenueData) + '%', '--delay': i * 0.08 + 's' }"
              >
                <span class="bar-value">{{ v }}</span>
              </div>
            </div>
            <div class="chart-labels">
              <span v-for="l in revenueLabels" :key="l">{{ l }}</span>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Student Stats -->
      <el-col :xs="24" :lg="12">
        <el-card class="chart-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">学员统计</span>
              <el-radio-group v-model="studentRange" size="small" @change="loadStudent">
                <el-radio-button value="week">本周</el-radio-button>
                <el-radio-button value="month">本月</el-radio-button>
                <el-radio-button value="year">全年</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div class="chart-area">
            <div class="bar-chart">
              <div
                v-for="(v, i) in studentData"
                :key="i"
                class="bar-item bar-student"
                :style="{ height: barHeight(v, studentData) + '%', '--delay': i * 0.08 + 's' }"
              >
                <span class="bar-value">{{ v }}</span>
              </div>
            </div>
            <div class="chart-labels">
              <span v-for="l in studentLabels" :key="l">{{ l }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { User, School, TrendCharts, Money } from '@element-plus/icons-vue'
import { getStatsOverview, getStatsRevenue, getStatsStudent } from '@/api/stats'

const overviewCards = ref([
  { icon: 'User', label: '总学员', value: '-', color: '#4fc3f7' },
  { icon: 'School', label: '活跃学员', value: '-', color: '#00c853' },
  { icon: 'TrendCharts', label: '新增学员', value: '-', color: '#7c4dff' },
  { icon: 'Money', label: '总收入', value: '-', color: '#ff6b35' },
])

const revenueRange = ref('month')
const studentRange = ref('month')
const revenueData = ref([])
const revenueLabels = ref([])
const studentData = ref([])
const studentLabels = ref([])

function barHeight(v, arr) {
  const max = Math.max(...arr, 1)
  return Math.max((v / max) * 100, 4)
}

async function loadOverview() {
  try {
    const res = await getStatsOverview()
    overviewCards.value = [
      { icon: 'User', label: '总学员', value: res.total_students ?? '-', color: '#4fc3f7' },
      { icon: 'School', label: '活跃学员', value: res.active_students ?? '-', color: '#00c853' },
      { icon: 'TrendCharts', label: '新增学员', value: res.new_students ?? '-', color: '#7c4dff' },
      { icon: 'Money', label: '总收入', value: res.total_revenue ? '¥' + Number(res.total_revenue).toLocaleString() : '-', color: '#ff6b35' },
    ]
  } catch {
    console.error('[stats] error:');
    // use defaults
  }
}

async function loadRevenue() {
  try {
    const data = await getStatsRevenue({ range: revenueRange.value })
    if (Array.isArray(data) && data.length) {
      revenueData.value = data.map(d => d.value ?? 0)
      revenueLabels.value = data.map(d => d.label ?? '')
    } else {
      revenueData.value = []
      revenueLabels.value = []
    }
  } catch {
    console.error('[stats] error:');
    revenueData.value = []
    revenueLabels.value = []
  }
}

async function loadStudent() {
  try {
    const data = await getStatsStudent({ range: studentRange.value })
    if (Array.isArray(data) && data.length) {
      studentData.value = data.map(d => d.value ?? 0)
      studentLabels.value = data.map(d => d.label ?? '')
    } else {
      studentData.value = []
      studentLabels.value = []
    }
  } catch {
    console.error('[stats] error:');
    studentData.value = []
    studentLabels.value = []
  }
}

onMounted(() => {
  loadOverview()
  loadRevenue()
  loadStudent()
})
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.page-stats { margin-bottom: 20px; }
.stat-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; border: 1px solid #ebeef5; transition: all 0.3s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 24px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; }
.content-row { margin-bottom: 20px; }
.chart-card { border-radius: 12px; border: 1px solid #ebeef5; height: 100%; }
.card-header { display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
.chart-area { padding: 0 4px; }
.bar-chart { display: flex; align-items: flex-end; height: 200px; gap: 8px; padding: 0 10px; }
.bar-item { flex: 1; background: linear-gradient(180deg, #4fc3f7, #2196f3); border-radius: 6px 6px 0 0; position: relative; min-height: 4px; animation: barGrow 0.8s ease-out forwards; animation-delay: var(--delay); opacity: 0; }
.bar-student { background: linear-gradient(180deg, #7c4dff, #651fff); }
@keyframes barGrow { from { opacity: 0; transform: scaleY(0); transform-origin: bottom; } to { opacity: 1; transform: scaleY(1); } }
.bar-value { position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 11px; color: #606266; font-weight: 500; }
.chart-labels { display: flex; gap: 8px; padding: 8px 10px 0; }
.chart-labels span { flex: 1; text-align: center; font-size: 11px; color: #909399; }
</style>
