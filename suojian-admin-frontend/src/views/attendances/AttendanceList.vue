<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>考勤管理</h2>
        <p>记录与管理学员上课出勤情况</p>
      </div>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总记录</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background: linear-gradient(135deg,#00c853,#4fc3f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ stats.present }}</span>
          <span class="stat-label">已到</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background: linear-gradient(135deg,#ff6b35,#ffb300); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ stats.absent }}</span>
          <span class="stat-label">缺勤</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background: linear-gradient(135deg,#7c4dff,#e91e63); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ stats.late }}</span>
          <span class="stat-label">迟到/请假</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 130px">
            <el-option label="全部" value="" />
            <el-option label="已到" value="present" />
            <el-option label="缺勤" value="absent" />
            <el-option label="迟到" value="late" />
            <el-option label="请假" value="leave" />
          </el-select>
        </el-form-item>
        <el-form-item label="日期">
          <el-date-picker v-model="filters.date" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" clearable style="width: 160px" />
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学员/课程" clearable style="width: 200px">
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
        <el-table-column label="学员" width="140" prop="student_name" />
        <el-table-column label="课程" min-width="140" prop="course_name" />
        <el-table-column label="日期" width="120">
          <template #default="{ row }">{{ row.date || '-' }}</template>
        </el-table-column>
        <el-table-column label="状态" width="120" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="default" effect="dark" style="cursor: pointer" @click="changeStatus(row)">
              {{ statusName(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right" align="center">
          <template #default="{ row }">
            <el-select v-model="row.status" @change="val => updateStatus(row, val)" placeholder="切换状态" size="small" style="width: 100px">
              <el-option label="已到" value="present" />
              <el-option label="缺勤" value="absent" />
              <el-option label="迟到" value="late" />
              <el-option label="请假" value="leave" />
            </el-select>
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
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import axios from '../../utils/http'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)

const filters = reactive({ status: '', date: '', keyword: '' })
const stats = reactive({ total: 0, present: 0, absent: 0, late: 0 })

function statusType(s) {
  const map = { present: 'success', absent: 'danger', late: 'warning', leave: 'info' }
  return map[s] || 'info'
}
function statusName(s) {
  const map = { present: '已到', absent: '缺勤', late: '迟到', leave: '请假' }
  return map[s] || s || '未知'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.status) params.status = filters.status
    if (filters.date) params.date = filters.date
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/attendances', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, status: item.status || 'present' }))
    total.value = data.total || arr.length
    stats.total = total.value
    stats.present = arr.filter(i => i.status === 'present').length
    stats.absent = arr.filter(i => i.status === 'absent').length
    stats.late = arr.filter(i => i.status === 'late' || i.status === 'leave').length
  } catch {
    console.error('[attendances] error:');
    ElMessage.error('加载考勤列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.status = ''
  filters.date = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function changeStatus(row) {
  const next = { present: 'absent', absent: 'late', late: 'leave', leave: 'present' }
  const newStatus = next[row.status] || 'present'
  updateStatus(row, newStatus)
}

async function updateStatus(row, newStatus) {
  const oldStatus = row.status
  row.status = newStatus
  try {
    const params = new URLSearchParams()
    params.append('id', row.id)
    params.append('status', newStatus)
    await axios.post(BASE + '/m/Admin/c/Api/a/attendanceUpdate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(`状态已切换为「${statusName(newStatus)}」`)
    loadData()
  } catch {
    console.error('[attendances] error:');
    ElMessage.error('状态更新失败')
    row.status = oldStatus
  }
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
