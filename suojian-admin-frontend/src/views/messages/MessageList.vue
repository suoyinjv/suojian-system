<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>消息管理</h2>
        <p>发送与管理系统内通知消息</p>
      </div>
      <el-button type="primary" @click="openSend">
        <el-icon><Plus /></el-icon>发送消息
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总消息</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.sent }}</span>
          <span class="stat-label">已发送</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.draft }}</span>
          <span class="stat-label">草稿</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.allStudents }}</span>
          <span class="stat-label">通知学员</span>
        </div>
      </el-col>
    </el-row>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column label="标题" min-width="160">
          <template #default="{ row }">
            <div class="cell-name">{{ row.title || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="内容" min-width="220">
          <template #default="{ row }">
            <div class="cell-sub text-ellipsis" style="max-width: 300px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">{{ row.content || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="发送对象" width="110" align="center">
          <template #default="{ row }">
            <el-tag :type="toTypeTag(row.to_type)" size="small">{{ toTypeName(row.to_type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="发送时间" width="160">
          <template #default="{ row }">{{ row.create_time || '-' }}</template>
        </el-table-column>
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'sent' ? 'success' : 'info'" size="small">{{ row.status === 'sent' ? '已发送' : '草稿' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="110" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="danger" @click="handleDelete(row)">
              <el-icon><Delete /></el-icon>删除
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

    <!-- Send Dialog -->
    <el-dialog v-model="showDialog" title="发送消息" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="消息标题" required>
          <el-input v-model="form.title" placeholder="请输入消息标题" />
        </el-form-item>
        <el-form-item label="消息内容" required>
          <el-input v-model="form.content" type="textarea" :rows="4" placeholder="请输入消息内容" />
        </el-form-item>
        <el-form-item label="发送对象" required>
          <el-select v-model="form.to_type" style="width: 100%">
            <el-option label="全部学员" value="all_students" />
            <el-option label="全部教师" value="all_teachers" />
            <el-option label="指定学员" value="specific_student" />
          </el-select>
        </el-form-item>
        <el-form-item label="指定学员" v-if="form.to_type === 'specific_student'">
          <el-select v-model="form.student_id" filterable style="width: 100%" placeholder="请选择学员">
            <el-option v-for="s in students" :key="s.id" :label="s.username" :value="s.id" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="handleSend" :loading="sending">发送</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Delete } from '@element-plus/icons-vue'
import axios from '../../utils/http'

const BASE = 'http://47.114.125.123'
const list = ref([])
const students = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const sending = ref(false)
const showDialog = ref(false)

const form = reactive({ title: '', content: '', to_type: 'all_students', student_id: '' })
const stats = reactive({ total: 0, sent: 0, draft: 0, allStudents: 0 })

function toTypeTag(t) {
  const map = { all_students: 'success', all_teachers: 'warning', specific_student: 'primary' }
  return map[t] || 'info'
}
function toTypeName(t) {
  const map = { all_students: '全部学员', all_teachers: '全部教师', specific_student: '指定学员' }
  return map[t] || t || '未知'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/messages', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
    stats.total = total.value
    stats.sent = arr.filter(i => i.status === 'sent').length
    stats.draft = arr.filter(i => i.status !== 'sent').length
    stats.allStudents = arr.filter(i => i.to_type === 'all_students').length
  } catch {
    console.error('[messages] error:');
    ElMessage.error('加载消息列表失败')
  } finally {
    loading.value = false
  }
}

async function loadStudents() {
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/students', { params: { page: 1, pageSize: 999 } })
    students.value = Array.isArray(res.data.list) ? res.data.list : []
  } catch {
    console.error('[messages] error:');
    // silent
  }
}

function openSend() {
  form.title = ''
  form.content = ''
  form.to_type = 'all_students'
  form.student_id = ''
  loadStudents()
  showDialog.value = true
}

async function handleSend() {
  if (!form.title || !form.content) {
    ElMessage.warning('请填写标题和内容')
    return
  }
  sending.value = true
  try {
    const params = new URLSearchParams()
    params.append('title', form.title)
    params.append('content', form.content)
    params.append('to_type', form.to_type)
    if (form.to_type === 'specific_student' && form.student_id) {
      params.append('student_id', form.student_id)
    }
    await axios.post(BASE + '/m/Admin/c/Api/a/messageCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('消息发送成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[messages] error:');
    ElMessage.error('发送失败')
  } finally {
    sending.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除消息「${row.title || row.id}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/messageDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[messages] error:');
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
.cell-row { display: flex; align-items: center; gap: 10px; }
.cell-name { font-size: 14px; color: #303133; font-weight: 500; }
.cell-sub { font-size: 12px; color: #909399; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
.text-ellipsis { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
</style>
