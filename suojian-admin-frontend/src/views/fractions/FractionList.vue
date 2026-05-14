<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>成绩管理</h2>
        <p>管理系统内所有考试成绩记录</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增成绩
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
          <span class="stat-num" style="background:linear-gradient(135deg,#00c853,#4fc3f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.avg }}</span>
          <span class="stat-label">平均分</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#ff6b35,#ffb300);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.high }}</span>
          <span class="stat-label">最高分</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#7c4dff,#e91e63);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.low }}</span>
          <span class="stat-label">最低分</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学生/课程" clearable style="width: 240px">
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
        <el-table-column label="学生" min-width="140" prop="student_name" />
        <el-table-column label="课程" min-width="140" prop="course_name" />
        <el-table-column label="分数" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="scoreType(row.score)" size="small" effect="dark">{{ row.score }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="考试日期" width="140" prop="exam_date" />
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
        </el-table-column>
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
    <el-dialog v-model="showDialog" title="新增成绩" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="学生姓名" required>
          <el-input v-model="form.student_name" placeholder="请输入学生姓名" />
        </el-form-item>
        <el-form-item label="课程名称" required>
          <el-input v-model="form.course_name" placeholder="请输入课程名称" />
        </el-form-item>
        <el-form-item label="分数" required>
          <el-input-number v-model="form.score" :min="0" :max="100" style="width: 100%" />
        </el-form-item>
        <el-form-item label="考试日期" required>
          <el-date-picker v-model="form.exam_date" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
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
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)

const filters = reactive({ keyword: '' })
const form = reactive({ student_name: '', course_name: '', score: 0, exam_date: '' })
const stats = reactive({ total: 0, avg: 0, high: 0, low: 0 })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

function scoreType(s) {
  const n = Number(s)
  if (n >= 90) return 'success'
  if (n >= 60) return 'warning'
  return 'danger'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/fractionList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
    const scores = arr.map(i => Number(i.score)).filter(s => !isNaN(s))
    stats.total = total.value
    stats.avg = scores.length ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : 0
    stats.high = scores.length ? Math.max(...scores) : 0
    stats.low = scores.length ? Math.min(...scores) : 0
  } catch {
    console.error('[fractions] error:');
    ElMessage.error('加载成绩列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  form.student_name = ''
  form.course_name = ''
  form.score = 0
  form.exam_date = ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.student_name || !form.course_name || !form.exam_date) {
    ElMessage.warning('请填写完整信息')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('student_name', form.student_name)
    params.append('course_name', form.course_name)
    params.append('score', form.score)
    params.append('exam_date', form.exam_date)
    await axios.post(BASE + '/m/Admin/c/Api/a/fractionCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('新增成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[fractions] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除「${row.student_name}」的成绩记录吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/fractionDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[fractions] error:');
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
