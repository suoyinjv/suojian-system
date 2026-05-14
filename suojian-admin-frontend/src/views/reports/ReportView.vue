<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>报表管理</h2>
        <p>财务数据与学员数据的统计概览</p>
      </div>
    </div>

    <!-- Financial Stats -->
    <div class="section-label">财务统计</div>
    <el-row :gutter="20" class="stats-row">
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#4fc3f7">
          <div class="stat-icon" style="background:#e3f2fd"><el-icon size="22" color="#4fc3f7"><Coin /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">¥{{ formatMoney(finance.total_income) }}</span>
            <span class="stat-label">总收入</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#00c853">
          <div class="stat-icon" style="background:#e8f5e9"><el-icon size="22" color="#00c853"><TrendingUp /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">¥{{ formatMoney(finance.month_income) }}</span>
            <span class="stat-label">本月收入</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#ff6b35">
          <div class="stat-icon" style="background:#fff3e0"><el-icon size="22" color="#ff6b35"><TrendingDown /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">¥{{ formatMoney(finance.total_expense) }}</span>
            <span class="stat-label">总支出</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#f56c6c">
          <div class="stat-icon" style="background:#fbe9e7"><el-icon size="22" color="#f56c6c"><Refresh /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">¥{{ formatMoney(finance.refund_amount) }}</span>
            <span class="stat-label">退费金额</span>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Student Stats -->
    <div class="section-label" style="margin-top:30px;">学员统计</div>
    <el-row :gutter="20" class="stats-row">
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#7c4dff">
          <div class="stat-icon" style="background:#f3e5f5"><el-icon size="22" color="#7c4dff"><User /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ student.total_students ?? '-' }}</span>
            <span class="stat-label">学员总数</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#ff9800">
          <div class="stat-icon" style="background:#fff3e0"><el-icon size="22" color="#ff9800"><Plus /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ student.new_students ?? '-' }}</span>
            <span class="stat-label">本月新增</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#26a69a">
          <div class="stat-icon" style="background:#e0f2f1"><el-icon size="22" color="#26a69a"><DataBoard /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ student.active_students ?? '-' }}</span>
            <span class="stat-label">活跃学员</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#e91e63">
          <div class="stat-icon" style="background:#fce4ec"><el-icon size="22" color="#e91e63"><Male /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ student.gender_ratio || '-' }}</span>
            <span class="stat-label">男女比例</span>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Finance Detail Table -->
    <el-card class="table-card" shadow="never" style="margin-top: 24px;">
      <template #header>
        <div class="card-header">
          <span class="card-title">财务明细</span>
          <el-tag size="small" type="info">共 {{ financeList.length }} 条</el-tag>
        </div>
      </template>
      <el-table :data="financeList" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="date" label="日期" width="120" />
        <el-table-column prop="income" label="收入" width="130" align="right">
          <template #default="{ row }">¥{{ formatMoney(row.income) }}</template>
        </el-table-column>
        <el-table-column prop="expense" label="支出" width="130" align="right">
          <template #default="{ row }">¥{{ formatMoney(row.expense) }}</template>
        </el-table-column>
        <el-table-column prop="-profit-" label="利润" width="130" align="right">
          <template #default="{ row }">
            <span :style="{ color: (row.income - row.expense) >= 0 ? '#00c853' : '#f56c6c', fontWeight: 600 }">
              ¥{{ formatMoney(row.income - row.expense) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="note" label="备注" min-width="180" show-overflow-tooltip />
      </el-table>
    </el-card>

    <!-- Student Detail Table -->
    <el-card class="table-card" shadow="never" style="margin-top: 16px;">
      <template #header>
        <div class="card-header">
          <span class="card-title">学员分布</span>
          <el-tag size="small" type="info">共 {{ studentList.length }} 条</el-tag>
        </div>
      </template>
      <el-table :data="studentList" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="category" label="分类" width="140" />
        <el-table-column prop="count" label="人数" width="100" align="center">
          <template #default="{ row }"><el-tag size="small">{{ row.count ?? '-' }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="ratio" label="占比" width="100" align="center">
          <template #default="{ row }">{{ row.ratio || '-' }}</template>
        </el-table-column>
        <el-table-column prop="note" label="说明" min-width="200" show-overflow-tooltip />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { User, Plus, DataBoard } from '@element-plus/icons-vue'
import http from '../../utils/http'

const BASE = 'http://47.114.125.123'
const loading = ref(false)
const financeList = ref([])
const studentList = ref([])
const finance = reactive({ total_income: 0, month_income: 0, total_expense: 0, refund_amount: 0 })
const student = reactive({ total_students: 0, new_students: 0, active_students: 0, gender_ratio: '-' })

function formatMoney(v) {
  if (v == null || isNaN(v)) return '0.00'
  return Number(v).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function loadFinance() {
  try {
    const res = await http.get(BASE + '/m/Admin/c/Api/a/reportFinance')
    const data = res.data
    if (data && typeof data === 'object') {
      finance.total_income = data.total_income ?? data.totalIncome ?? 0
      finance.month_income = data.month_income ?? data.monthIncome ?? 0
      finance.total_expense = data.total_expense ?? data.totalExpense ?? 0
      finance.refund_amount = data.refund_amount ?? data.refundAmount ?? 0
      financeList.value = Array.isArray(data.list) ? data.list : []
    }
  } catch {
    console.error('[reports] error:');
    // use defaults
  }
}

async function loadStudent() {
  try {
    const res = await http.get(BASE + '/m/Admin/c/Api/a/reportStudent')
    const data = res.data
    if (data && typeof data === 'object') {
      student.total_students = data.total_students ?? data.totalStudents ?? 0
      student.new_students = data.new_students ?? data.newStudents ?? 0
      student.active_students = data.active_students ?? data.activeStudents ?? 0
      student.gender_ratio = data.gender_ratio ?? data.genderRatio ?? '-'
      studentList.value = Array.isArray(data.list) ? data.list : []
    }
  } catch {
    console.error('[reports] error:');
    // use defaults
  }
}

async function loadData() {
  loading.value = true
  await Promise.all([loadFinance(), loadStudent()])
  loading.value = false
}

onMounted(() => { loadData() })
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.section-label { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }
.stats-row { margin-bottom: 0; }
.stat-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; border: 1px solid #ebeef5; transition: all 0.3s; margin-bottom: 20px; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 24px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; }
.card-header { display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
</style>
