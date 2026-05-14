<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>管理员管理</h2>
        <p>管理系统内所有管理员账号与权限</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加管理员
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="用户名/手机号" clearable style="width: 240px">
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
        <el-table-column label="用户名" min-width="140" prop="username" />
        <el-table-column label="手机号" width="130" prop="mobile" />
        <el-table-column label="角色" width="120">
          <template #default="{ row }">
            <el-tag size="small">{{ row.role_name || '-' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="超级管理员" width="110" align="center">
          <template #default="{ row }">
            <el-tag :type="row.is_super == 1 ? 'danger' : 'info'" size="small">
              {{ row.is_super == 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="最近登录" width="160">
          <template #default="{ row }">{{ formatTime(row.login_time) }}</template>
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑管理员' : '添加管理员'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="用户名" required>
          <el-input v-model="form.username" placeholder="请输入用户名" />
        </el-form-item>
        <el-form-item v-if="!isEdit" label="密码" required>
          <el-input v-model="form.password" type="password" placeholder="请输入密码" show-password />
        </el-form-item>
        <el-form-item label="手机号" required>
          <el-input v-model="form.mobile" placeholder="请输入手机号" />
        </el-form-item>
        <el-form-item label="角色">
          <el-input v-model="form.role_id" placeholder="角色ID" />
        </el-form-item>
        <el-form-item label="超级管理员">
          <el-switch v-model="form.is_super" :active-value="1" :inactive-value="0" />
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

const filters = reactive({ keyword: '' })
const form = reactive({ username: '', password: '', mobile: '', role_id: '', is_super: 0 })

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
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/adminList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[admin] error:');
    ElMessage.error('加载管理员列表失败')
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
  isEdit.value = false
  editId.value = null
  form.username = ''
  form.password = ''
  form.mobile = ''
  form.role_id = ''
  form.is_super = 0
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.username = row.username || ''
  form.password = ''
  form.mobile = row.mobile || ''
  form.role_id = row.role_id ?? ''
  form.is_super = row.is_super ?? 0
  showDialog.value = true
}

async function handleSave() {
  if (!form.username || (!isEdit.value && !form.password) || !form.mobile) {
    ElMessage.warning('请填写用户名、密码和手机号')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('username', form.username)
    params.append('mobile', form.mobile)
    if (form.password) params.append('password', form.password)
    if (form.role_id) params.append('role_id', form.role_id)
    params.append('is_super', form.is_super)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/adminUpdate'
      : BASE + '/m/Admin/c/Api/a/adminCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[admin] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除管理员「${row.username}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/adminDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[admin] error:');
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
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
