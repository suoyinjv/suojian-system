<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>学习计划</h2>
        <p>管理系统内所有学习计划安排</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增计划
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="计划标题/学生" clearable style="width: 240px">
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
        <el-table-column label="计划标题" min-width="180" prop="title" />
        <el-table-column label="学生" min-width="140" prop="student_name" />
        <el-table-column label="计划内容" min-width="200" prop="content" show-overflow-tooltip />
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
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
    <el-dialog v-model="showDialog" title="新增学习计划" width="560px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="计划标题" required>
          <el-input v-model="form.title" placeholder="请输入计划标题" />
        </el-form-item>
        <el-form-item label="学生姓名" required>
          <el-input v-model="form.student_name" placeholder="请输入学生姓名" />
        </el-form-item>
        <el-form-item label="计划内容" required>
          <el-input v-model="form.content" type="textarea" :rows="4" placeholder="请输入计划内容" />
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
const form = reactive({ title: '', student_name: '', content: '' })

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
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/studyPlanList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[plans] error:');
    ElMessage.error('加载学习计划列表失败')
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
  form.title = ''
  form.student_name = ''
  form.content = ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.title || !form.student_name || !form.content) {
    ElMessage.warning('请填写完整信息')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('title', form.title)
    params.append('student_name', form.student_name)
    params.append('content', form.content)
    await axios.post(BASE + '/m/Admin/c/Api/a/studyPlanCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('新增成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[plans] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
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
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
