<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>订单管理</h2>
        <p>查看与管理系统内所有交易订单</p>
      </div>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总订单</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.paid }}</span>
          <span class="stat-label">已支付</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.pending }}</span>
          <span class="stat-label">待支付</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">¥{{ stats.amount }}</span>
          <span class="stat-label">总金额</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="已支付" value="paid" />
            <el-option label="待支付" value="pending" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="订单号/学员" clearable style="width: 220px">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadData">查询</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column label="订单号" width="180" prop="order_sn" />
        <el-table-column label="学员" width="120" prop="student_name" />
        <el-table-column label="金额" width="100" align="center">
          <template #default="{ row }">
            <span style="font-weight: 600; color: #ff6b35;">¥{{ row.pay_amount || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付时间" width="160">
          <template #default="{ row }">{{ row.pay_time || '-' }}</template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ row.create_time || '-' }}</template>
        </el-table-column>
        <el-table-column label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="orderStatusType(row.status)" size="small" effect="dark">
              {{ orderStatusName(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="110" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="viewDetail(row)">
              <el-icon><View /></el-icon>详情
            </el-button>
          </template>
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

    <!-- Detail Dialog -->
    <el-dialog v-model="showDetail" title="订单详情" width="520px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="订单号" span="2">{{ detail.order_sn }}</el-descriptions-item>
        <el-descriptions-item label="学员">{{ detail.student_name }}</el-descriptions-item>
        <el-descriptions-item label="金额">¥{{ detail.pay_amount }}</el-descriptions-item>
        <el-descriptions-item label="支付时间">{{ detail.pay_time || '-' }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail.create_time || '-' }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="orderStatusType(detail.status)" size="small">{{ orderStatusName(detail.status) }}</el-tag>
        </el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="showDetail = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, View } from '@element-plus/icons-vue'
import axios from '../../utils/http'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const showDetail = ref(false)
const detail = ref({})

const filters = reactive({ status: '', keyword: '' })
const stats = reactive({ total: 0, paid: 0, pending: 0, amount: 0 })

function orderStatusType(s) {
  const map = { paid: 'success', pending: 'warning', cancelled: 'info' }
  return map[s] || 'info'
}
function orderStatusName(s) {
  const map = { paid: '已支付', pending: '待支付', cancelled: '已取消' }
  return map[s] || s || '未知'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.status) params.status = filters.status
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/orders', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
    stats.total = total.value
    stats.paid = arr.filter(i => i.status === 'paid').length
    stats.pending = arr.filter(i => i.status === 'pending').length
    stats.amount = arr.reduce((sum, i) => sum + (parseFloat(i.pay_amount) || 0), 0)
  } catch {
    console.error('[orders] error:');
    ElMessage.error('加载订单列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.status = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function viewDetail(row) {
  detail.value = { ...row }
  showDetail.value = true
}

watch(page, () => { loadData() })
onMounted(() => { loadData() })
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.page-stats { margin-bottom: 20px; }
.stat-item { background: #fff; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #ebeef5; transition: transform 0.2s; }
.stat-item:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(79,195,247,0.15); }
.stat-num { display: block; font-size: 28px; font-weight: 700; background: linear-gradient(135deg,#4fc3f7,#7c4dff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; display: block; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.cell-row { display: flex; align-items: center; gap: 10px; }
.cell-name { font-size: 14px; color: #303133; font-weight: 500; }
.cell-sub { font-size: 12px; color: #909399; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
