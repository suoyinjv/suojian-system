<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>积分管理</h2>
        <p>管理系统内所有学员积分变动记录</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增积分
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
          <span class="stat-num" style="background:linear-gradient(135deg,#00c853,#4fc3f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.add }}</span>
          <span class="stat-label">总加分</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#ff6b35,#ffb300);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.sub }}</span>
          <span class="stat-label">总扣分</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num" style="background:linear-gradient(135deg,#7c4dff,#e91e63);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ stats.net }}</span>
          <span class="stat-label">净积分</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学生姓名" clearable style="width: 240px">
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
        <el-table-column label="学生姓名" min-width="140" prop="student_name" />
        <el-table-column label="积分变动" width="120" align="center">
          <template #default="{ row }">
            <el-tag :type="row.points >= 0 ? 'success' : 'danger'" size="small" effect="dark">
              {{ row.points >= 0 ? '+' : '' }}{{ row.points }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="说明" min-width="220" prop="remark" show-overflow-tooltip />
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
    <el-dialog v-model="showDialog" :title="isDeduct ? '扣分' : '新增积分'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="学生姓名" required>
          <el-input v-model="form.student_name" placeholder="请输入学生姓名" />
        </el-form-item>
        <el-form-item :label="isDeduct ? '扣分数' : '加分数'" required>
          <el-input-number v-model="form.points" :min="1" :max="999" style="width: 100%" />
        </el-form-item>
        <el-form-item label="说明">
          <el-input v-model="form.remark" type="textarea" :rows="3" placeholder="请输入积分变动说明" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">{{ isDeduct ? '确认扣分' : '确认加分' }}</el-button>
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
const isDeduct = ref(false)

const filters = reactive({ keyword: '' })
const form = reactive({ student_name: '', points: 1, remark: '' })
const stats = reactive({ total: 0, add: 0, sub: 0, net: 0 })

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
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/pointList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
    const vals = arr.map(i => Number(i.points)).filter(s => !isNaN(s))
    stats.total = total.value
    stats.add = vals.filter(v => v > 0).reduce((a, b) => a + b, 0)
    stats.sub = Math.abs(vals.filter(v => v < 0).reduce((a, b) => a + b, 0))
    stats.net = stats.add - stats.sub
  } catch {
    console.error('[points] error:');
    ElMessage.error('加载积分列表失败')
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
  isDeduct.value = false
  form.student_name = ''
  form.points = 1
  form.remark = ''
  showDialog.value = true
}

function openDeduct() {
  isDeduct.value = true
  form.student_name = ''
  form.points = 1
  form.remark = ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.student_name || !form.points) {
    ElMessage.warning('请填写学生姓名和分数')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('student_name', form.student_name)
    params.append('points', isDeduct.value ? -form.points : form.points)
    if (form.remark) params.append('remark', form.remark)
    await axios.post(BASE + '/m/Admin/c/Api/a/pointCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isDeduct.value ? '扣分成功' : '加分成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[points] error:');
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
.page-stats { margin-bottom: 20px; }
.stat-item { background: #fff; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #ebeef5; transition: transform 0.2s; }
.stat-item:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(79,195,247,0.15); }
.stat-num { display: block; font-size: 28px; font-weight: 700; background: linear-gradient(135deg,#4fc3f7,#7c4dff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; display: block; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
