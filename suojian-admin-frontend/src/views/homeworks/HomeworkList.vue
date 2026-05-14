<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>作业管理</h2>
        <p>管理作业发布、提交与批改</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>发布作业
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总作业</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.active }}</span>
          <span class="stat-label">进行中</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.overdue }}</span>
          <span class="stat-label">已截止</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.courses }}</span>
          <span class="stat-label">涉及课程</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="课程">
          <el-select v-model="filters.course_id" clearable style="width: 160px">
            <el-option label="全部" value="" />
            <el-option v-for="c in courses" :key="c.id" :label="c.course_name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="作业标题" clearable style="width: 220px">
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
        <el-table-column prop="title" label="作业标题" min-width="160" />
        <el-table-column prop="course_name" label="课程" width="130" />
        <el-table-column prop="class_name" label="班级" width="120">
          <template #default="{ row }">{{ row.class_name || '-' }}</template>
        </el-table-column>
        <el-table-column label="内容摘要" min-width="200">
          <template #default="{ row }">
            <span class="cell-sub">{{ row.content ? row.content.substring(0, 50) + (row.content.length > 50 ? '...' : '') : '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="截止时间" width="150">
          <template #default="{ row }">{{ row.submit_deadline || '-' }}</template>
        </el-table-column>
        <el-table-column label="创建时间" width="150">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="openEdit(row)">
              <el-icon><Edit /></el-icon>编辑
            </el-button>
            <el-button size="small" text type="success" @click="openPublish(row)">
              <el-icon><Upload /></el-icon>布置
            </el-button>
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

    <!-- Add/Edit Dialog -->
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑作业' : '发布作业'" width="580px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="作业标题" required>
          <el-input v-model="form.title" placeholder="请输入作业标题" />
        </el-form-item>
        <el-form-item label="所属课程" required>
          <el-select v-model="form.course_id" style="width: 100%" placeholder="请选择课程">
            <el-option v-for="c in courses" :key="c.id" :label="c.course_name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="班级">
          <el-input v-model="form.class_name" placeholder="请输入班级名称" />
        </el-form-item>
        <el-form-item label="作业内容">
          <el-input v-model="form.content" type="textarea" :rows="4" placeholder="请输入作业内容" />
        </el-form-item>
        <el-form-item label="截止时间" required>
          <el-date-picker
            v-model="form.submit_deadline"
            type="datetime"
            placeholder="选择截止时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">保存</el-button>
      </template>
    </el-dialog>

    <!-- Publish Dialog (布置作业给学员) -->
    <el-dialog v-model="showPublish" title="布置作业" width="480px" :close-on-click-modal="false">
      <div style="margin-bottom: 16px;">
        <span style="font-weight: 500;">作业：</span>{{ publishHomework?.title }}
      </div>
      <el-form label-width="80px">
        <el-form-item label="选择学员">
          <el-select v-model="publishStudentIds" multiple style="width: 100%" placeholder="请选择学员">
            <el-option v-for="s in students" :key="s.id" :label="s.username" :value="s.id" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showPublish = false">取消</el-button>
        <el-button type="primary" @click="handlePublish" :loading="publishing">确认布置</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Edit, Delete, Upload } from '@element-plus/icons-vue'
import { getHomeworkList, createHomework, updateHomework, deleteHomework } from '@/api/homeworks'
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

// Publish dialog
const showPublish = ref(false)
const publishing = ref(false)
const publishHomework = ref(null)
const publishStudentIds = ref([])
const students = ref([])
const courses = ref([])

const filters = reactive({ course_id: '', keyword: '' })
const stats = reactive({ total: 0, active: 0, overdue: 0, courses: 0 })

const defaultForm = { title: '', course_id: '', class_name: '', content: '', submit_deadline: '' }
const form = reactive({ ...defaultForm })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

async function loadCourses() {
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/homeworkCourses', { params: { page: 1, pageSize: 200 } })
    const data = res.data
    if (data.code === 0) {
      courses.value = Array.isArray(data.data) ? data.data : Array.isArray(data.data?.list) ? data.data.list : []
    } else {
      courses.value = []
    }
  } catch {
    console.error('[homeworks] error:');
    courses.value = []
  }
}

async function loadStudents() {
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/students', { params: { page: 1, pageSize: 200 } })
    const data = res.data
    const arr = data.code === 0 ? (data.data?.list || []) : (Array.isArray(data.list) ? data.list : [])
    students.value = arr
  } catch {
    console.error('[homeworks] error:');
    students.value = []
  }
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.course_id !== '') params.course_id = filters.course_id
    const res = await getHomeworkList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr
    total.value = res.total || arr.length
    const now = Date.now()
    stats.total = total.value
    stats.active = arr.filter(i => !i.submit_deadline || new Date(i.submit_deadline).getTime() > now).length
    stats.overdue = arr.filter(i => i.submit_deadline && new Date(i.submit_deadline).getTime() <= now).length
    const courseSet = new Set(arr.map(i => i.course_id).filter(Boolean))
    stats.courses = courseSet.size
  } catch {
    console.error('[homeworks] error:');
    ElMessage.error('加载作业列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.course_id = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  Object.assign(form, defaultForm)
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.title = row.title || ''
  form.course_id = row.course_id || ''
  form.class_name = row.class_name || ''
  form.content = row.content || ''
  form.submit_deadline = row.submit_deadline || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.title) {
    ElMessage.warning('请填写作业标题')
    return
  }
  if (!form.course_id) {
    ElMessage.warning('请选择所属课程')
    return
  }
  saving.value = true
  try {
    const data = {
      title: form.title,
      course_id: form.course_id,
      class_name: form.class_name,
      content: form.content,
      submit_deadline: form.submit_deadline,
    }
    if (isEdit.value && editId.value) {
      await updateHomework(editId.value, data)
      ElMessage.success('编辑成功')
    } else {
      await createHomework(data)
      ElMessage.success('发布成功')
    }
    showDialog.value = false
    loadData()
  } catch {
    console.error('[homeworks] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除作业「${row.title}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await deleteHomework(row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[homeworks] error:');
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {})
}

function openPublish(row) {
  publishHomework.value = row
  publishStudentIds.value = []
  showPublish.value = true
}

async function handlePublish() {
  if (publishStudentIds.value.length === 0) {
    ElMessage.warning('请选择学员')
    return
  }
  publishing.value = true
  try {
    const params = new URLSearchParams()
    params.append('id', publishHomework.value.id)
    params.append('student_ids', publishStudentIds.value.join(','))
    await axios.post(BASE + '/m/Admin/c/Api/a/homeworkAssign', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    })
    ElMessage.success('布置成功')
    showPublish.value = false
  } catch {
    console.error('[homeworks] error:');
    ElMessage.error('布置失败')
  } finally {
    publishing.value = false
  }
}

watch(page, () => { loadData() })
onMounted(() => {
  loadData()
  loadCourses()
  loadStudents()
})
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
.cell-sub { font-size: 13px; color: #909399; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
