<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>招生管理</h2>
        <p>管理学员招生信息，跟踪报名状态</p>
      </div>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总报名</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.pending }}</span>
          <span class="stat-label">待确认</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.confirmed }}</span>
          <span class="stat-label">已确认</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.cancelled }}</span>
          <span class="stat-label">已取消</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="待确认" value="1" />
            <el-option label="已确认" value="2" />
            <el-option label="已取消" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="姓名/手机" clearable style="width: 240px">
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
        <el-table-column label="姓名" width="120" prop="name" />
        <el-table-column label="手机" width="140" prop="phone" />
        <el-table-column label="来源" width="120">
          <template #default="{ row }">{{ row.source || '-' }}</template>
        </el-table-column>
        <el-table-column label="状态" width="110" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="small" effect="dark">
              {{ statusName(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="openEdit(row)">
              <el-icon><Edit /></el-icon>编辑
            </el-button>
            <el-button
              size="small"
              text
              :type="row.status == 2 ? 'warning' : 'success'"
              @click="toggleStatus(row)"
            >
              <el-icon><Switch /></el-icon>{{ row.status == 2 ? '取消' : '确认' }}
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

    <!-- Edit Dialog -->
    <el-dialog v-model="showDialog" title="编辑报名信息" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="姓名" required>
          <el-input v-model="form.name" placeholder="请输入姓名" />
        </el-form-item>
        <el-form-item label="手机" required>
          <el-input v-model="form.phone" placeholder="请输入手机号" />
        </el-form-item>
        <el-form-item label="来源">
          <el-input v-model="form.source" placeholder="如：转介绍、线下活动" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="待确认" :value="1" />
            <el-option label="已确认" :value="2" />
            <el-option label="已取消" :value="3" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Edit, Switch } from '@element-plus/icons-vue'
import { getEnrollmentList, updateEnrollment } from '@/api/enrollments'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const editId = ref(null)

const filters = reactive({ status: '', keyword: '' })
const stats = reactive({ total: 0, pending: 0, confirmed: 0, cancelled: 0 })
const form = reactive({ name: '', phone: '', source: '', status: 1 })

function statusName(v) {
  return { 1: '待确认', 2: '已确认', 3: '已取消' }[v] || '未知'
}
function statusType(v) {
  return { 1: 'warning', 2: 'success', 3: 'danger' }[v] || 'info'
}

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.status !== '') params.status = filters.status
    const res = await getEnrollmentList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr
    total.value = res.total || arr.length
    stats.total = total.value
    stats.pending = arr.filter(i => i.status == 1).length
    stats.confirmed = arr.filter(i => i.status == 2).length
    stats.cancelled = arr.filter(i => i.status == 3).length
  } catch {
    console.error('[enrollments] error:');
    ElMessage.error('加载招生列表失败')
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

function openEdit(row) {
  editId.value = row.id
  form.name = row.name || ''
  form.phone = row.phone || ''
  form.source = row.source || ''
  form.status = row.status ?? 1
  showDialog.value = true
}

async function handleSave() {
  if (!form.name || !form.phone) {
    ElMessage.warning('请填写姓名和手机号')
    return
  }
  saving.value = true
  try {
    await updateEnrollment(editId.value, {
      name: form.name,
      phone: form.phone,
      source: form.source,
      status: form.status,
    })
    ElMessage.success('编辑成功')
    showDialog.value = false
    editId.value = null
    loadData()
  } catch {
    console.error('[enrollments] error:');
    ElMessage.error('编辑失败')
  } finally {
    saving.value = false
  }
}

async function toggleStatus(row) {
  const newStatus = row.status == 2 ? 3 : 2
  const label = newStatus == 2 ? '确认' : '取消'
  try {
    await ElMessageBox.confirm(`确定${label}「${row.name}」的报名吗？`, '提示', { type: 'warning' })
    await updateEnrollment(row.id, { status: newStatus })
    ElMessage.success(`${label}成功`)
    loadData()
  } catch {
    console.error('[enrollments] error:');
    // cancelled
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
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
