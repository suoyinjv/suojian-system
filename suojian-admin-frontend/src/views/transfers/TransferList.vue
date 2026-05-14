<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>转校管理</h2>
        <p>管理学员转校记录</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增转校
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学员姓名" clearable style="width: 240px">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
        </el-form-item>
        <el-form-item label="目标校区">
          <el-select v-model="filters.campus_id" clearable style="width: 160px" placeholder="选择校区">
            <el-option v-for="c in campusList" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
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
        <el-table-column label="学员姓名" width="140" prop="student_name" />
        <el-table-column label="原校区" width="160">
          <template #default="{ row }">{{ row.from_campus_name || row.from_campus || '-' }}</template>
        </el-table-column>
        <el-table-column label="目标校区" width="160">
          <template #default="{ row }">{{ row.to_campus_name || row.to_campus || '-' }}</template>
        </el-table-column>
        <el-table-column label="转校原因" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">{{ row.reason || '-' }}</template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time || row.add_time) }}</template>
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
    <el-dialog v-model="showDialog" title="新增转校记录" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="选择学员" required>
          <el-select
            v-model="form.student_id"
            filterable
            remote
            :remote-method="searchStudents"
            :loading="searchingStudent"
            placeholder="输入学员姓名搜索"
            style="width: 100%"
            @change="onStudentChange"
          >
            <el-option
              v-for="s in studentOptions"
              :key="s.id"
              :label="s.name || s.real_name"
              :value="s.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="学员姓名">
          <el-input v-model="form.student_name" disabled placeholder="选择学员后自动填充" />
        </el-form-item>
        <el-form-item label="目标校区" required>
          <el-select v-model="form.to_campus_id" filterable placeholder="选择目标校区" style="width: 100%">
            <el-option v-for="c in campusList" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="转校原因">
          <el-input v-model="form.reason" type="textarea" :rows="3" placeholder="请输入转校原因" />
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
import { ElMessage } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import http from '../../utils/http'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)

const filters = reactive({ keyword: '', campus_id: '' })
const form = reactive({ student_id: '', student_name: '', to_campus_id: '', reason: '' })

const studentOptions = ref([])
const searchingStudent = ref(false)
const campusList = ref([])

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
    if (filters.campus_id) params.campus_id = filters.campus_id
    const res = await http.get(BASE + '/m/Admin/c/Api/a/transferList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[transfers] error:');
    ElMessage.error('加载转校列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.keyword = ''
  filters.campus_id = ''
  page.value = 1
  loadData()
}

async function searchStudents(query) {
  if (!query) { studentOptions.value = []; return }
  searchingStudent.value = true
  try {
    const res = await http.get(BASE + '/m/Admin/c/Api/a/studentList', { params: { keyword: query, pageSize: 20 } })
    const data = res.data
    studentOptions.value = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
  } catch {
    console.error('[transfers] error:');
    studentOptions.value = []
  } finally {
    searchingStudent.value = false
  }
}

function onStudentChange(val) {
  const s = studentOptions.value.find(i => i.id === val)
  form.student_name = s ? (s.name || s.real_name) : ''
}

async function loadCampuses() {
  try {
    const res = await http.get(BASE + '/m/Admin/c/Api/a/campusList', { params: { pageSize: 200 } })
    const data = res.data
    campusList.value = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
  } catch {
    console.error('[transfers] error:');
    campusList.value = []
  }
}

function openAdd() {
  form.student_id = ''
  form.student_name = ''
  form.to_campus_id = ''
  form.reason = ''
  studentOptions.value = []
  showDialog.value = true
}

async function handleSave() {
  if (!form.student_id) {
    ElMessage.warning('请选择学员')
    return
  }
  if (!form.to_campus_id) {
    ElMessage.warning('请选择目标校区')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('student_id', form.student_id)
    if (form.student_name) params.append('student_name', form.student_name)
    params.append('to_campus_id', form.to_campus_id)
    if (form.reason) params.append('reason', form.reason)
    await http.post(BASE + '/m/Admin/c/Api/a/transferCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('转校记录添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[transfers] error:');
    ElMessage.error('添加失败')
  } finally {
    saving.value = false
  }
}

watch(page, () => { loadData() })
onMounted(() => { loadData(); loadCampuses() })
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
