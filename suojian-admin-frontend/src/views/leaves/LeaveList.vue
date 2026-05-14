<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>请假管理</h2>
        <p>审核与管理学员请假申请</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增请假
      </el-button>
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
          <span class="stat-num" style="background:linear-gradient(135deg,#ff6b35,#ffb300);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.pending }}</span>
          <span class="stat-label">待审批</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#00c853,#4fc3f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.approved }}</span>
          <span class="stat-label">已通过</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#e91e63,#7c4dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.rejected }}</span>
          <span class="stat-label">已拒绝</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 130px">
            <el-option label="全部" value="" />
            <el-option label="待审批" value="0" />
            <el-option label="已通过" value="1" />
            <el-option label="已拒绝" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="日期">
          <el-date-picker v-model="filters.date" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" clearable style="width: 160px" />
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学员/班级" clearable style="width: 220px">
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
        <el-table-column label="学员" width="130" prop="student_name" />
        <el-table-column label="班级" width="130" prop="class_name" />
        <el-table-column label="开始日期" width="120">
          <template #default="{ row }">{{ row.start_date || '-' }}</template>
        </el-table-column>
        <el-table-column label="结束日期" width="120">
          <template #default="{ row }">{{ row.end_date || '-' }}</template>
        </el-table-column>
        <el-table-column label="原因" min-width="150">
          <template #default="{ row }">
            <el-tooltip :content="row.reason" placement="top" :show-after="300" :disabled="!row.reason || row.reason.length < 20">
              <span class="cell-ellipsis">{{ row.reason || '-' }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="default" effect="dark">
              {{ statusName(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="审批备注" min-width="140">
          <template #default="{ row }">{{ row.approve_remark || '-' }}</template>
        </el-table-column>
        <el-table-column label="申请时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="220" fixed="right" align="center">
          <template #default="{ row }">
            <template v-if="row.status === 0 || row.status === '0'">
              <el-button size="small" type="success" @click="handleApprove(row)">
                <el-icon><Select /></el-icon>通过
              </el-button>
              <el-button size="small" type="danger" plain @click="handleReject(row)">
                <el-icon><Close /></el-icon>拒绝
              </el-button>
            </template>
            <template v-else>
              <el-button size="small" text type="primary" @click="openEdit(row)">
                <el-icon><Edit /></el-icon>编辑
              </el-button>
              <el-button size="small" text type="danger" @click="handleDelete(row)">
                <el-icon><Delete /></el-icon>删除
              </el-button>
            </template>
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

    <!-- Add/Edit Dialog -->
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑请假' : '新增请假'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="学员" required>
          <el-input v-model="form.student_name" placeholder="请输入学员姓名" />
        </el-form-item>
        <el-form-item label="班级">
          <el-input v-model="form.class_name" placeholder="请输入班级" />
        </el-form-item>
        <el-form-item label="开始日期">
          <el-date-picker v-model="form.start_date" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" style="width: 100%" />
        </el-form-item>
        <el-form-item label="结束日期">
          <el-date-picker v-model="form.end_date" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" style="width: 100%" />
        </el-form-item>
        <el-form-item label="原因" required>
          <el-input v-model="form.reason" type="textarea" :rows="3" placeholder="请输入请假原因" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">保存</el-button>
      </template>
    </el-dialog>

    <!-- Approve/Reject Dialog -->
    <el-dialog v-model="showApproveDialog" :title="approveAction === 'approve' ? '通过审批' : '拒绝申请'" width="420px" :close-on-click-modal="false">
      <el-form :model="approveForm" label-width="80px" class="dialog-form">
        <el-form-item label="审批备注">
          <el-input v-model="approveForm.remark" type="textarea" :rows="3" placeholder="请输入审批备注（可选）" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showApproveDialog = false">取消</el-button>
        <el-button :type="approveAction === 'approve' ? 'success' : 'danger'" @click="confirmApprove" :loading="saving">
          {{ approveAction === 'approve' ? '确认通过' : '确认拒绝' }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Edit, Delete, Select, Close } from '@element-plus/icons-vue'
import axios from '../../utils/http'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const isEdit = ref(false)
const editId = ref(null)
const showApproveDialog = ref(false)
const approveAction = ref('approve')
const approveRow = ref(null)

const filters = reactive({ status: '', date: '', keyword: '' })
const form = reactive({ student_name: '', class_name: '', start_date: '', end_date: '', reason: '' })
const approveForm = reactive({ remark: '' })
const stats = reactive({ total: 0, pending: 0, approved: 0, rejected: 0 })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

function statusType(s) {
  s = Number(s)
  if (s === 0) return 'warning'
  if (s === 1) return 'success'
  if (s === 2) return 'danger'
  return 'info'
}

function statusName(s) {
  s = Number(s)
  if (s === 0) return '待审批'
  if (s === 1) return '已通过'
  if (s === 2) return '已拒绝'
  return '未知'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.status !== '') params.status = filters.status
    if (filters.date) params.date = filters.date
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/leaveList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, status: item.status ?? 0 }))
    total.value = data.total || arr.length
    stats.total = total.value
    stats.pending = arr.filter(i => Number(i.status) === 0).length
    stats.approved = arr.filter(i => Number(i.status) === 1).length
    stats.rejected = arr.filter(i => Number(i.status) === 2).length
  } catch {
    console.error('[leaves] error:');
    ElMessage.error('加载请假列表失败')
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

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.student_name = ''
  form.class_name = ''
  form.start_date = ''
  form.end_date = ''
  form.reason = ''
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.student_name = row.student_name || ''
  form.class_name = row.class_name || ''
  form.start_date = row.start_date || ''
  form.end_date = row.end_date || ''
  form.reason = row.reason || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.student_name || !form.reason) {
    ElMessage.warning('请填写学员和请假原因')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('student_name', form.student_name)
    if (form.class_name) params.append('class_name', form.class_name)
    if (form.start_date) params.append('start_date', form.start_date)
    if (form.end_date) params.append('end_date', form.end_date)
    params.append('reason', form.reason)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/leaveUpdate'
      : BASE + '/m/Admin/c/Api/a/leaveCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '新增成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[leaves] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function openApprove(row, action) {
  approveRow.value = row
  approveAction.value = action
  approveForm.remark = ''
  showApproveDialog.value = true
}

function handleApprove(row) {
  openApprove(row, 'approve')
}

function handleReject(row) {
  openApprove(row, 'reject')
}

async function confirmApprove() {
  if (!approveRow.value) return
  saving.value = true
  const newStatus = approveAction.value === 'approve' ? 1 : 2
  try {
    const params = new URLSearchParams()
    params.append('id', approveRow.value.id)
    params.append('status', newStatus)
    if (approveForm.remark) params.append('approve_remark', approveForm.remark)
    await axios.post(BASE + '/m/Admin/c/Api/a/leaveUpdate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(approveAction.value === 'approve' ? '已通过审批' : '已拒绝申请')
    showApproveDialog.value = false
    loadData()
  } catch {
    console.error('[leaves] error:');
    ElMessage.error('审批操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除此请假记录吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/leaveDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[leaves] error:');
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {})
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
.cell-ellipsis { display: inline-block; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
