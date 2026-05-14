<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>课程管理</h2>
        <p>管理系统内所有课程信息与排课数据</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加课程
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总课程</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#00c853,#4fc3f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.active }}</span>
          <span class="stat-label">进行中</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#ff6b35,#ffb300);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.finished }}</span>
          <span class="stat-label">已结束</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#7c4dff,#e91e63);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.frozen }}</span>
          <span class="stat-label">已暂停</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.state" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="进行中" value="1" />
            <el-option label="已结束" value="0" />
            <el-option label="已暂停" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="科目">
          <el-input v-model="filters.subject" placeholder="科目名称" clearable style="width: 140px" />
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="课程名/教师/班级" clearable style="width: 240px">
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
        <el-table-column label="课程名称" min-width="160" prop="course_name" />
        <el-table-column label="教师" width="130" prop="teacher_name" />
        <el-table-column label="班级" width="130" prop="class_name" />
        <el-table-column label="科目" width="120" prop="subject_name" />
        <el-table-column label="教室" width="120" prop="room_name" />
        <el-table-column label="学期" width="80" align="center" prop="semester_id" />
        <el-table-column label="星期" width="70" align="center" prop="week_id" />
        <el-table-column label="节次" width="70" align="center" prop="interval_id" />
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="stateType(row.state)" size="small" effect="dark">
              {{ stateName(row.state) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" width="160">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="openEdit(row)">
              <el-icon><Edit /></el-icon>编辑
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑课程' : '添加课程'" width="620px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="课程名称" required>
              <el-input v-model="form.course_name" placeholder="请输入课程名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="教师" required>
              <el-input v-model="form.teacher_name" placeholder="请输入教师姓名" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="班级" required>
              <el-input v-model="form.class_name" placeholder="请输入班级名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="科目" required>
              <el-input v-model="form.subject_name" placeholder="请输入科目名称" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="教室" required>
              <el-input v-model="form.room_name" placeholder="请输入教室" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="学期">
              <el-input-number v-model="form.semester_id" :min="1" :max="99" style="width: 100%" placeholder="学期编号" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="星期">
              <el-select v-model="form.week_id" placeholder="选择星期" style="width: 100%">
                <el-option label="周一" :value="1" />
                <el-option label="周二" :value="2" />
                <el-option label="周三" :value="3" />
                <el-option label="周四" :value="4" />
                <el-option label="周五" :value="5" />
                <el-option label="周六" :value="6" />
                <el-option label="周日" :value="7" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="节次">
              <el-input-number v-model="form.interval_id" :min="1" :max="12" style="width: 100%" placeholder="节次编号" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="教师ID">
              <el-input-number v-model="form.teacher_id" :min="0" style="width: 100%" placeholder="教师ID" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="班级ID">
              <el-input-number v-model="form.class_id" :min="0" style="width: 100%" placeholder="班级ID" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="科目ID">
              <el-input-number v-model="form.subject_id" :min="0" style="width: 100%" placeholder="科目ID" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="教室ID">
              <el-input-number v-model="form.room_id" :min="0" style="width: 100%" placeholder="教室ID" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="状态">
          <el-select v-model="form.state" style="width: 100%">
            <el-option label="进行中" :value="1" />
            <el-option label="已结束" :value="0" />
            <el-option label="已暂停" :value="2" />
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
import { Plus, Search, Edit, Delete } from '@element-plus/icons-vue'
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

const filters = reactive({ state: '', subject: '', keyword: '' })
const form = reactive({
  course_name: '', teacher_name: '', class_name: '', subject_name: '', room_name: '',
  teacher_id: 0, class_id: 0, subject_id: 0, room_id: 0,
  semester_id: 1, week_id: 1, interval_id: 1, state: 1
})
const stats = reactive({ total: 0, active: 0, finished: 0, frozen: 0 })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

function stateType(s) {
  s = Number(s)
  if (s === 1) return 'success'
  if (s === 0) return 'info'
  if (s === 2) return 'warning'
  return 'info'
}

function stateName(s) {
  s = Number(s)
  if (s === 1) return '进行中'
  if (s === 0) return '已结束'
  if (s === 2) return '已暂停'
  return '未知'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.state !== '') params.state = filters.state
    if (filters.subject) params.subject = filters.subject
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/courseList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({
      ...item,
      state: item.state ?? 1,
      semester_id: Number(item.semester_id ?? 0),
      week_id: Number(item.week_id ?? 0),
      interval_id: Number(item.interval_id ?? 0),
    }))
    total.value = data.total || arr.length
    stats.total = total.value
    stats.active = arr.filter(i => Number(i.state) === 1).length
    stats.finished = arr.filter(i => Number(i.state) === 0).length
    stats.frozen = arr.filter(i => Number(i.state) === 2).length
  } catch {
    console.error('[courses] error:');
    ElMessage.error('加载课程列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.state = ''
  filters.subject = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.course_name = ''
  form.teacher_name = ''
  form.class_name = ''
  form.subject_name = ''
  form.room_name = ''
  form.teacher_id = 0
  form.class_id = 0
  form.subject_id = 0
  form.room_id = 0
  form.semester_id = 1
  form.week_id = 1
  form.interval_id = 1
  form.state = 1
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.course_name = row.course_name || ''
  form.teacher_name = row.teacher_name || ''
  form.class_name = row.class_name || ''
  form.subject_name = row.subject_name || ''
  form.room_name = row.room_name || ''
  form.teacher_id = Number(row.teacher_id ?? 0)
  form.class_id = Number(row.class_id ?? 0)
  form.subject_id = Number(row.subject_id ?? 0)
  form.room_id = Number(row.room_id ?? 0)
  form.semester_id = Number(row.semester_id ?? 1)
  form.week_id = Number(row.week_id ?? 1)
  form.interval_id = Number(row.interval_id ?? 1)
  form.state = Number(row.state ?? 1)
  showDialog.value = true
}

async function handleSave() {
  if (!form.course_name || !form.teacher_name || !form.class_name || !form.subject_name || !form.room_name) {
    ElMessage.warning('请填写课程名称、教师、班级、科目和教室')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('course_name', form.course_name)
    params.append('teacher_name', form.teacher_name)
    params.append('class_name', form.class_name)
    params.append('subject_name', form.subject_name)
    params.append('room_name', form.room_name)
    params.append('teacher_id', form.teacher_id)
    params.append('class_id', form.class_id)
    params.append('subject_id', form.subject_id)
    params.append('room_id', form.room_id)
    params.append('semester_id', form.semester_id)
    params.append('week_id', form.week_id)
    params.append('interval_id', form.interval_id)
    params.append('state', form.state)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/courseUpdate'
      : BASE + '/m/Admin/c/Api/a/courseCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[courses] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除课程「${row.course_name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/courseDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[courses] error:');
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
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
