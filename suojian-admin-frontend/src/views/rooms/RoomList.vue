<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>教室管理</h2>
        <p>管理所有教室信息与使用状态</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加教室
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.is_enable" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="启用" value="1" />
            <el-option label="禁用" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="教室名称" clearable style="width: 220px">
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
        <el-table-column prop="name" label="教室名称" min-width="160" />
        <el-table-column label="所属教室" min-width="130">
          <template #default="{ row }">{{ getParentName(row.pid) }}</template>
        </el-table-column>
        <el-table-column label="排序" width="80" align="center" prop="sort" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.is_enable"
              :active-value="1"
              :inactive-value="0"
              @change="toggleStatus(row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑教室' : '添加教室'" width="440px" :close-on-click-modal="false">
      <el-form :model="form" label-width="80px" class="dialog-form">
        <el-form-item label="教室名称" required>
          <el-input v-model="form.name" placeholder="请输入教室名称" />
        </el-form-item>
        <el-form-item label="上级教室">
          <el-select v-model="form.pid" clearable style="width: 100%">
            <el-option label="无（顶级教室）" :value="0" />
            <el-option v-for="item in parentOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" :max="999" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="form.is_enable" style="width: 100%">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
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
const parentOptions = ref([])

const filters = reactive({ is_enable: '', keyword: '' })
const form = reactive({ name: '', pid: 0, sort: 0, is_enable: 1 })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

function getParentName(pid) {
  if (!pid || pid === 0) return '-'
  const parent = parentOptions.value.find(p => p.id === pid)
  return parent ? parent.name : '-'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.is_enable !== '') params.is_enable = filters.is_enable
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/roomList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, is_enable: item.is_enable ?? 1 }))
    total.value = data.total || arr.length
    if (arr.length) {
      parentOptions.value = arr.filter(i => i.id !== editId.value).map(i => ({ id: i.id, name: i.name }))
    }
  } catch {
    console.error('[rooms] error:');
    ElMessage.error('加载教室列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.is_enable = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.name = ''
  form.pid = 0
  form.sort = 0
  form.is_enable = 1
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.name = row.name || ''
  form.pid = row.pid || 0
  form.sort = row.sort ?? 0
  form.is_enable = row.is_enable ?? 1
  parentOptions.value = list.value.filter(i => i.id !== row.id).map(i => ({ id: i.id, name: i.name }))
  showDialog.value = true
}

async function handleSave() {
  if (!form.name) {
    ElMessage.warning('请输入教室名称')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('name', form.name)
    params.append('pid', form.pid || 0)
    params.append('sort', form.sort)
    params.append('is_enable', form.is_enable)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/roomUpdate'
      : BASE + '/m/Admin/c/Api/a/roomCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[rooms] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除教室「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/roomDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[rooms] error:');
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {})
}

async function toggleStatus(row) {
  try {
    const params = new URLSearchParams()
    params.append('id', row.id)
    params.append('is_enable', row.is_enable)
    await axios.post(BASE + '/m/Admin/c/Api/a/roomUpdate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(row.is_enable === 1 ? '已启用' : '已禁用')
  } catch {
    console.error('[rooms] error:');
    ElMessage.error('操作失败')
    row.is_enable = row.is_enable === 1 ? 0 : 1
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
