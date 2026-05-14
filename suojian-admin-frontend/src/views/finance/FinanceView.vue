<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>财务管理</h2>
        <p>查看财务数据看板和最近订单</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="finance-card" style="--accent:#4fc3f7">
          <div class="finance-icon" style="background:#e3f2fd"><el-icon size="22" color="#4fc3f7"><Coin /></el-icon></div>
          <div class="finance-info">
            <span class="finance-value">¥{{ formatMoney(stats.total_income) }}</span>
            <span class="finance-label">总收入</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="finance-card" style="--accent:#00c853">
          <div class="finance-icon" style="background:#e8f5e9"><el-icon size="22" color="#00c853"><TrendingUp /></el-icon></div>
          <div class="finance-info">
            <span class="finance-value">¥{{ formatMoney(stats.month_income) }}</span>
            <span class="finance-label">本月收入</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="finance-card" style="--accent:#ff6b35">
          <div class="finance-icon" style="background:#fff3e0"><el-icon size="22" color="#ff6b35"><TrendingDown /></el-icon></div>
          <div class="finance-info">
            <span class="finance-value">¥{{ formatMoney(stats.total_expense) }}</span>
            <span class="finance-label">总支出</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="finance-card" style="--accent:#f56c6c">
          <div class="finance-icon" style="background:#fbe9e7"><el-icon size="22" color="#f56c6c"><Refresh /></el-icon></div>
          <div class="finance-info">
            <span class="finance-value">¥{{ formatMoney(stats.refund_amount) }}</span>
            <span class="finance-label">退费统计</span>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Recent Orders -->
    <el-card class="table-card" shadow="never">
      <template #header>
        <div class="card-header">
          <span class="card-title">最近订单</span>
          <el-tag size="small" type="info">共 {{ total }} 条</el-tag>
        </div>
      </template>
      <el-table :data="orders" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="order_no" label="订单号" width="180" />
        <el-table-column prop="name" label="学员" width="120" />
        <el-table-column prop="item" label="项目" width="150">
          <template #default="{ row }">{{ row.item || '-' }}</template>
        </el-table-column>
        <el-table-column prop="amount" label="金额" width="120" align="right">
          <template #default="{ row }">
            <span :style="{ color: row.type === 'income' ? '#00c853' : '#f56c6c', fontWeight: 600 }">
              {{ row.type === 'income' ? '+' : '-' }}¥{{ formatMoney(row.amount) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.type === 'income' ? 'success' : 'danger'" size="small" effect="plain">
              {{ row.type === 'income' ? '收入' : '支出' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
        </el-table-column>
      </el-table>
      <div class="table-footer">
        <el-pagination
          v-model:current-page="page"
          :page-size="pageSize"
          :total="total"
          layout="total, prev, pager, next"
          background
          small
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Money, Top, Bottom, RefreshRight } from '@element-plus/icons-vue'
import { getFinanceStats, getOrders } from '@/api/finance'

const orders = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const stats = reactive({ total_income: 0, month_income: 0, total_expense: 0, refund_amount: 0 })

function formatMoney(v) {
  if (v == null || isNaN(v)) return '0.00'
  return Number(v).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

async function loadStats() {
  try {
    const res = await getFinanceStats()
    Object.assign(stats, {
      total_income: res.total_income || 0,
      month_income: res.month_income || 0,
      total_expense: res.total_expense || 0,
      refund_amount: res.refund_amount || 0,
    })
  } catch {
    console.error('[finance] error:');
    // use defaults
  }
}

async function loadOrders() {
  loading.value = true
  try {
    const res = await getOrders({ page: page.value, pageSize })
    orders.value = Array.isArray(res.list) ? res.list : []
    total.value = res.total || orders.value.length
  } catch {
    console.error('[finance] error:');
    ElMessage.error('加载订单列表失败')
  } finally {
    loading.value = false
  }
}

function loadData() {
  loadStats()
  loadOrders()
}

watch(page, () => { loadOrders() })
onMounted(() => { loadData() })
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.page-stats { margin-bottom: 20px; }
.finance-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; border: 1px solid #ebeef5; transition: all 0.3s; }
.finance-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.finance-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.finance-info { display: flex; flex-direction: column; }
.finance-value { font-size: 24px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
.finance-label { font-size: 13px; color: #909399; margin-top: 4px; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; }
.card-header { display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
</style>
