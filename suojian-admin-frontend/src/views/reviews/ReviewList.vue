<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>课后点评</h2>
        <p>查看与管理课后点评记录</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增点评
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总点评</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#00c853,#4fc3f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.high }}</span>
          <span class="stat-label">高分(≥8)</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#ff6b35,#ffb300);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.mid }}</span>
          <span class="stat-label">中分(4-7)</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#e91e63,#7c4dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.low }}</span>
          <span class="stat-label">低分(≤3)</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="评分">
          <el-select v-model="filters.score" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="高分(8-10)" value="high" />
            <el-option label="中分(4-7)" value="mid" />
            <el-option label="低分(1-3)" value="low" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学员/教师/课程" clearable style="width: 240px">
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
        <el-table-column label="教师" width="140" prop="teacher_name" />
        <el-table-column label="课程" min-width="140" prop="course_name" />
        <el-table-column label="评分" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="scoreType(row.score)" size="small" effect="dark">
              {{ row.score }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="点评内容" min-width="200">
          <template #default="{ row }">
            <el-tooltip :content="row.content" placement="top" :show-after="300" :disabled="!row.content || row.content.length < 30">
              <span class="cell-ellipsis">{{ row.content || '-' }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="140" fixed="right" align="center">
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑点评' : '新增点评'" width="560px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="学员" required>
          <el-input v-model="form.student_name" placeholder="请输入学员姓名" />
        </el-form-item>
        <el-form-item label="教师" required>
          <el-input v-model="form.teacher_name" placeholder="请输入教师姓名" />
        </el-form-item>
        <el-form-item label="课程" required>
          <el-input v-model="form.course_name" placeholder="请输入课程名称" />
        </el-form-item>
        <el-form-item label="评分(1-10)" required>
          <el-input-number v-model="form.score" :min="1" :max="10" style="width: 100%" />
        </el-form-item>
        <el-form-item label="点评内容">
          <el-input v-model="form.content" type="textarea" :rows="4" placeholder="请输入点评内容" maxlength="500" show-word-limit />
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

const filters = reactive({ score: '', keyword: '' })
const form = reactive({ student_name: '', teacher_name: '', course_name: '', score: 5, content: '' })
const stats = reactive({ total: 0, high: 0, mid: 0, low: 0 })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

function scoreType(s) {
  s = Number(s)
  if (s >= 8) return 'success'
  if (s >= 4) return 'warning'
  return 'danger'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.score) params.score = filters.score
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/reviewList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, score: Number(item.score) || 0 }))
    total.value = data.total || arr.length
    stats.total = total.value
    stats.high = arr.filter(i => Number(i.score) >= 8).length
    stats.mid = arr.filter(i => Number(i.score) >= 4 && Number(i.score) < 8).length
    stats.low = arr.filter(i => Number(i.score) < 4).length
  } catch {
    console.error('[reviews] error:');
    ElMessage.error('加载点评列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.score = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.student_name = ''
  form.teacher_name = ''
  form.course_name = ''
  form.score = 5
  form.content = ''
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.student_name = row.student_name || ''
  form.teacher_name = row.teacher_name || ''
  form.course_name = row.course_name || ''
  form.score = Number(row.score ?? 5)
  form.content = row.content || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.student_name || !form.teacher_name || !form.course_name) {
    ElMessage.warning('请填写学员、教师和课程')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('student_name', form.student_name)
    params.append('teacher_name', form.teacher_name)
    params.append('course_name', form.course_name)
    params.append('score', form.score)
    if (form.content) params.append('content', form.content)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/reviewUpdate'
      : BASE + '/m/Admin/c/Api/a/reviewCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '新增成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[reviews] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除此点评记录吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/reviewDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[reviews] error:');
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
.cell-ellipsis { display: inline-block; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cell-row { display: flex; align-items: center; gap: 10px; }
.cell-name { font-size: 14px; color: #303133; font-weight: 500; }
.cell-sub { font-size: 12px; color: #909399; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
