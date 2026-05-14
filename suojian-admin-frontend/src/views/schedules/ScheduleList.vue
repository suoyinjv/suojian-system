<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>排课管理</h2>
        <p>管理课程时间安排与教室分配</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增排课
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
          <span class="stat-num">{{ stats.weekdays }}</span>
          <span class="stat-label">工作日</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.weekend }}</span>
          <span class="stat-label">周末</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.teachers }}</span>
          <span class="stat-label">授课教师</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="星期">
          <el-select v-model="filters.weekday" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option v-for="(n, i) in weekNames" :key="i" :label="n" :value="i + 1" />
          </el-select>
        </el-form-item>
        <el-form-item label="教室">
          <el-input v-model="filters.classroom" placeholder="教室名称" clearable style="width: 140px" />
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学员/教师" clearable style="width: 200px">
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
        <el-table-column label="教师" width="130" prop="teacher_name" />
        <el-table-column label="星期" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="weekType(row.weekday)" size="small">{{ weekNames[(row.weekday || 1) - 1] }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="180">
          <template #default="{ row }">
            {{ row.start_time || '-' }} ~ {{ row.end_time || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="教室" width="130" prop="classroom" />
        <el-table-column label="操作" width="120" fixed="right" align="center">
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

    <!-- Add Dialog -->
    <el-dialog v-model="showDialog" title="新增排课" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="学员" required>
          <el-select v-model="form.student_id" filterable style="width: 100%" placeholder="请选择学员">
            <el-option v-for="s in students" :key="s.id" :label="s.username" :value="s.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="教师" required>
          <el-select v-model="form.teacher_id" filterable style="width: 100%" placeholder="请选择教师">
            <el-option v-for="t in teachers" :key="t.id" :label="t.username" :value="t.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="星期" required>
          <el-select v-model="form.weekday" style="width: 100%">
            <el-option v-for="(n, i) in weekNames" :key="i" :label="n" :value="i + 1" />
          </el-select>
        </el-form-item>
        <el-form-item label="开始时间" required>
          <el-time-picker v-model="form.start_time" format="HH:mm" value-format="HH:mm" style="width: 100%" placeholder="选择时间" />
        </el-form-item>
        <el-form-item label="结束时间" required>
          <el-time-picker v-model="form.end_time" format="HH:mm" value-format="HH:mm" style="width: 100%" placeholder="选择时间" />
        </el-form-item>
        <el-form-item label="教室">
          <el-input v-model="form.classroom" placeholder="请输入教室名称" />
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
import { Plus, Search, Delete } from '@element-plus/icons-vue'
import axios from '../../utils/http'

const BASE = 'http://47.114.125.123'
const weekNames = ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
const list = ref([])
const students = ref([])
const teachers = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)

const filters = reactive({ weekday: '', classroom: '', keyword: '' })
const form = reactive({ student_id: '', teacher_id: '', weekday: 1, start_time: '', end_time: '', classroom: '' })
const stats = reactive({ total: 0, weekdays: 0, weekend: 0, teachers: 0 })

function weekType(w) {
  if (!w) return 'info'
  const n = Number(w)
  if (n >= 6) return 'danger'
  return 'primary'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.weekday !== '') params.weekday = filters.weekday
    if (filters.classroom) params.classroom = filters.classroom
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/schedules', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
    stats.total = total.value
    stats.weekdays = arr.filter(i => i.weekday && i.weekday <= 5).length
    stats.weekend = arr.filter(i => i.weekday && i.weekday >= 6).length
    const tset = new Set(arr.map(i => i.teacher_name).filter(Boolean))
    stats.teachers = tset.size
  } catch {
    console.error('[schedules] error:');
    ElMessage.error('加载排课列表失败')
  } finally {
    loading.value = false
  }
}

async function loadOptions() {
  try {
    const [sRes, tRes] = await Promise.all([
      axios.get(BASE + '/m/Admin/c/Api/a/students', { params: { page: 1, pageSize: 999 } }),
      axios.get(BASE + '/m/Admin/c/Api/a/teachers', { params: { page: 1, pageSize: 999 } })
    ])
    students.value = Array.isArray(sRes.data.list) ? sRes.data.list : []
    teachers.value = Array.isArray(tRes.data.list) ? tRes.data.list : []
  } catch {
    console.error('[schedules] error:');
    // silent
  }
}

function resetFilters() {
  filters.weekday = ''
  filters.classroom = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  form.student_id = ''
  form.teacher_id = ''
  form.weekday = 1
  form.start_time = ''
  form.end_time = ''
  form.classroom = ''
  loadOptions()
  showDialog.value = true
}

async function handleSave() {
  if (!form.student_id || !form.teacher_id || !form.start_time || !form.end_time) {
    ElMessage.warning('请填写完整信息')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('student_id', form.student_id)
    params.append('teacher_id', form.teacher_id)
    params.append('weekday', form.weekday)
    params.append('start_time', form.start_time)
    params.append('end_time', form.end_time)
    if (form.classroom) params.append('classroom', form.classroom)
    await axios.post(BASE + '/m/Admin/c/Api/a/scheduleCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('排课成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[schedules] error:');
    ElMessage.error('排课失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除该排课记录吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/scheduleDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[schedules] error:');
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
</style>
