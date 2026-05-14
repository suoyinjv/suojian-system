<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>友链管理</h2>
        <p>管理友情链接，支持自定义排序与打开方式</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加友链
      </el-button>
    </div>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="name" label="链接名称" min-width="140" />
        <el-table-column prop="url" label="链接地址" min-width="260" show-overflow-tooltip>
          <template #default="{ row }">
            <a :href="row.url" target="_blank" class="link-url">{{ row.url }}</a>
          </template>
        </el-table-column>
        <el-table-column label="新窗口打开" width="110" align="center">
          <template #default="{ row }">
            <el-tag :type="row.is_new_window_open == 1 ? 'primary' : 'info'" size="small" effect="dark">
              {{ row.is_new_window_open == 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="sort_order" label="排序" width="70" align="center" />
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑友链' : '添加友链'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="链接名称" required>
          <el-input v-model="form.name" placeholder="请输入友链名称" />
        </el-form-item>
        <el-form-item label="链接地址" required>
          <el-input v-model="form.url" placeholder="请输入完整链接地址，如：https://example.com" />
        </el-form-item>
        <el-form-item label="新窗口打开">
          <el-switch
            v-model="form.is_new_window_open"
            :active-value="1"
            :inactive-value="0"
            active-text="是"
            inactive-text="否"
          />
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
            inactive-text="停用"
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
import { Plus, Edit, Delete } from '@element-plus/icons-vue'
import http from '../../utils/http'

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

const defaultForm = { name: '', url: '', is_new_window_open: 1, sort_order: 0, status: 1 }
const form = reactive({ ...defaultForm })

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    const res = await http.get(BASE + '/m/Admin/c/Api/a/linkList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, status: item.status ?? 1, is_new_window_open: item.is_new_window_open ?? 1 }))
    total.value = data.total || arr.length
  } catch {
    console.error('[links] error:');
    ElMessage.error('加载友链列表失败')
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
  form.name = row.name || ''
  form.url = row.url || ''
  form.is_new_window_open = row.is_new_window_open ?? 1
  form.sort_order = row.sort_order ?? 0
  form.status = row.status ?? 1
  showDialog.value = true
}

async function handleSave() {
  if (!form.name) {
    ElMessage.warning('请填写链接名称')
    return
  }
  if (!form.url) {
    ElMessage.warning('请填写链接地址')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('name', form.name)
    params.append('url', form.url)
    params.append('is_new_window_open', form.is_new_window_open)
    params.append('sort_order', form.sort_order)
    params.append('status', form.status)
    if (isEdit.value && editId.value) params.append('id', editId.value)

    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/linkUpdate'
      : BASE + '/m/Admin/c/Api/a/linkCreate'
    await http.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[links] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除友链「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await http.get(BASE + '/m/Admin/c/Api/a/linkDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[links] error:');
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
    await http.post(BASE + '/m/Admin/c/Api/a/linkUpdate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(row.status === 1 ? '已启用' : '已停用')
  } catch {
    console.error('[links] error:');
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
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.link-url { color: #409eff; text-decoration: none; font-size: 13px; }
.link-url:hover { text-decoration: underline; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
