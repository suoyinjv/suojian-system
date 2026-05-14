<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>导航管理</h2>
        <p>管理顶部导航与底部导航菜单</p>
      </div>
    </div>

    <!-- Tabs -->
    <el-card class="table-card" shadow="never">
      <el-tabs v-model="activeTab" @tab-change="loadData">
        <el-tab-pane label="顶部导航" name="header">
          <template #label>
            <span><el-icon style="vertical-align: middle; margin-right: 4px;"><Top /></el-icon> 顶部导航</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="底部导航" name="footer">
          <template #label>
            <span><el-icon style="vertical-align: middle; margin-right: 4px;"><Bottom /></el-icon> 底部导航</span>
          </template>
        </el-tab-pane>
      </el-tabs>

      <div style="margin-bottom: 16px;">
        <el-button type="primary" @click="openAdd">
          <el-icon><Plus /></el-icon>新增导航
        </el-button>
      </div>

      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="title" label="导航名称" min-width="160" />
        <el-table-column prop="url" label="链接地址" min-width="220" show-overflow-tooltip />
        <el-table-column label="排序" width="80" align="center">
          <template #default="{ row }">{{ row.sort_order ?? row.sort ?? 0 }}</template>
        </el-table-column>
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? 'success' : 'info'" size="small" effect="dark">
              {{ row.status == 1 ? '启用' : '禁用' }}
            </el-tag>
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑导航' : '新增导航'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="导航名称" required>
          <el-input v-model="form.title" placeholder="请输入导航名称" />
        </el-form-item>
        <el-form-item label="链接地址" required>
          <el-input v-model="form.url" placeholder="请输入链接地址" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort_order" :min="0" :max="999" style="width: 180px" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            v-model="form.status"
            :active-value="1"
            :inactive-value="0"
            active-text="启用"
            inactive-text="禁用"
          />
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
import { Plus, Edit, Delete, Top, Bottom } from '@element-plus/icons-vue'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const activeTab = ref('header')
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const isEdit = ref(false)
const editId = ref(null)

const defaultForm = { title: '', url: '', sort_order: 0, status: 1 }
const form = reactive({ ...defaultForm })

function getApiEndpoint() {
  return activeTab.value === 'header' ? 'navHeaderList' : 'navFooterList'
}

function getCreateEndpoint() {
  return activeTab.value === 'header' ? 'navHeaderCreate' : 'navFooterCreate'
}

function getUpdateEndpoint() {
  return activeTab.value === 'header' ? 'navHeaderUpdate' : 'navFooterUpdate'
}

function getDeleteEndpoint() {
  return activeTab.value === 'header' ? 'navHeaderDelete' : 'navFooterDelete'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/' + getApiEndpoint(), { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[nav] error:');
    ElMessage.error('加载导航列表失败')
  } finally {
    loading.value = false
  }
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  Object.assign(form, defaultForm)
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.title = row.title || ''
  form.url = row.url || ''
  form.sort_order = row.sort_order ?? row.sort ?? 0
  form.status = row.status ?? 1
  showDialog.value = true
}

async function handleSave() {
  if (!form.title) {
    ElMessage.warning('请填写导航名称')
    return
  }
  if (!form.url) {
    ElMessage.warning('请填写链接地址')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('title', form.title)
    params.append('url', form.url)
    params.append('sort_order', form.sort_order)
    params.append('status', form.status)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const endpoint = isEdit.value ? getUpdateEndpoint() : getCreateEndpoint()
    const url = BASE + '/m/Admin/c/Api/a/' + endpoint
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '新增成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[nav] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除导航「${row.title}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/' + getDeleteEndpoint() + '?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[nav] error:');
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
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
