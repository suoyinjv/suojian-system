<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>学员管理</h2>
        <p>管理系统内所有学员信息与状态</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加学员
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总学员</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.active }}</span>
          <span class="stat-label">正常</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.frozen }}</span>
          <span class="stat-label">冻结</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.new }}</span>
          <span class="stat-label">本月新增</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="性别">
          <el-select v-model="filters.sex" clearable style="width: 100px">
            <el-option label="全部" value="" />
            <el-option label="男" value="1" />
            <el-option label="女" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="正常" value="1" />
            <el-option label="冻结" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="姓名/手机/家长" clearable style="width: 220px">
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
        <el-table-column label="学员" min-width="160">
          <template #default="{ row }">
            <div class="cell-row">
              <el-avatar :size="36" :style="{ background: row.color }">{{ row.username?.[0] || '?' }}</el-avatar>
              <div>
                <div class="cell-name">{{ row.username }}</div>
                <div class="cell-sub">{{ row.my_mobile }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="性别" width="60" align="center">
          <template #default="{ row }">
            <el-tag :type="row.sex == 1 ? 'primary' : 'success'" size="small">{{ row.sex == 1 ? '男' : '女' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="家长" min-width="130">
          <template #default="{ row }">
            <div class="cell-name">{{ row.parent_name || '-' }}</div>
            <div class="cell-sub">{{ row.parent_phone || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.status"
              :active-value="1"
              :inactive-value="0"
              @change="toggleStatus(row)"
            />
          </template>
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑学员' : '添加学员'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="姓名" required>
          <el-input v-model="form.username" placeholder="请输入姓名" />
        </el-form-item>
        <el-form-item label="手机号" required>
          <el-input v-model="form.my_mobile" placeholder="请输入手机号" />
        </el-form-item>
        <el-form-item label="性别">
          <el-select v-model="form.sex" style="width: 100%">
            <el-option label="男" :value="1" />
            <el-option label="女" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="家长姓名">
          <el-input v-model="form.parent_name" placeholder="请输入家长姓名" />
        </el-form-item>
        <el-form-item label="家长电话">
          <el-input v-model="form.parent_phone" placeholder="请输入家长电话" />
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

const filters = reactive({ sex: '', status: '', keyword: '' })
const form = reactive({ username: '', my_mobile: '', sex: 1, parent_name: '', parent_phone: '' })
const stats = reactive({ total: 0, active: 0, frozen: 0, new: 0 })
const colors = ['#4fc3f7', '#7c4dff', '#00c853', '#ff6b35', '#ffb300', '#e91e63']

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
    if (filters.sex !== '') params.sex = filters.sex
    if (filters.status !== '') params.status = filters.status
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/students', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({
      ...item,
      color: colors[Math.floor(Math.random() * colors.length)],
      status: item.status ?? 1
    }))
    total.value = data.total || arr.length
    stats.total = total.value
    stats.active = arr.filter(i => i.status !== 0).length
    stats.frozen = arr.filter(i => i.status === 0).length
  } catch {
    console.error('[students] error:');
    ElMessage.error('加载学员列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.sex = ''
  filters.status = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.username = ''
  form.my_mobile = ''
  form.sex = 1
  form.parent_name = ''
  form.parent_phone = ''
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.username = row.username || ''
  form.my_mobile = row.my_mobile || ''
  form.sex = row.sex ?? 1
  form.parent_name = row.parent_name || ''
  form.parent_phone = row.parent_phone || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.username || !form.my_mobile) {
    ElMessage.warning('请填写姓名和手机号')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('username', form.username)
    params.append('my_mobile', form.my_mobile)
    if (form.sex !== null) params.append('sex', form.sex)
    if (form.parent_name) params.append('parent_name', form.parent_name)
    if (form.parent_phone) params.append('parent_phone', form.parent_phone)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/studentUpdate'
      : BASE + '/m/Admin/c/Api/a/studentCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[students] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除学员「${row.username}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/studentDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[students] error:');
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {})
}

async function toggleStatus(row) {
  try {
    const params = new URLSearchParams()
    params.append('id', row.id)
    params.append('status', row.status)
    await axios.post(BASE + '/m/Admin/c/Api/a/studentUpdate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(row.status === 1 ? '已启用' : '已冻结')
  } catch {
    console.error('[students] error:');
    ElMessage.error('操作失败')
    row.status = row.status === 1 ? 0 : 1
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
.cell-row { display: flex; align-items: center; gap: 10px; }
.cell-name { font-size: 14px; color: #303133; font-weight: 500; }
.cell-sub { font-size: 12px; color: #909399; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
