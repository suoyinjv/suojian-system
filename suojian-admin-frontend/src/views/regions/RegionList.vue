<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>区域管理</h2>
        <p>管理学校所属区域/地区信息</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加区域
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="区域名称">
          <el-input v-model="filters.name" placeholder="请输入区域名称" clearable style="width: 220px">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="启用" value="1" />
            <el-option label="停用" value="0" />
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
        <el-table-column label="区域名称" min-width="160" prop="name" />
        <el-table-column label="编码" width="140" prop="code">
          <template #default="{ row }">
            <code style="font-size:13px;color:#409eff;">{{ row.code || '-' }}</code>
          </template>
        </el-table-column>
        <el-table-column label="排序" width="80" align="center" prop="sort_order" />
        <el-table-column label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? 'success' : 'info'" size="small">
              {{ row.status == 1 ? '启用' : '停用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="200" show-overflow-tooltip prop="remark" />
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑区域' : '添加区域'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="区域名称" required>
          <el-input v-model="form.name" placeholder="请输入区域名称" maxlength="50" show-word-limit />
        </el-form-item>
        <el-form-item label="区域编码">
          <el-input v-model="form.code" placeholder="如：EAST、WEST" maxlength="30" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort_order" :min="0" :max="9999" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.status" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="form.remark" type="textarea" :rows="3" placeholder="备注信息" />
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
import axios from 'axios'

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

const filters = reactive({ name: '', status: '' })
const form = reactive({ name: '', code: '', sort_order: 0, status: 1, remark: '' })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.name) params.name = filters.name
    if (filters.status !== '') params.status = filters.status
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/regionList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[regions] error:');
    ElMessage.error('加载区域列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.name = ''
  filters.status = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.name = ''
  form.code = ''
  form.sort_order = 0
  form.status = 1
  form.remark = ''
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.name = row.name || ''
  form.code = row.code || ''
  form.sort_order = row.sort_order ?? 0
  form.status = row.status ?? 1
  form.remark = row.remark || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.name) {
    ElMessage.warning('请填写区域名称')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('name', form.name)
    if (form.code) params.append('code', form.code)
    params.append('sort_order', form.sort_order)
    params.append('status', form.status)
    if (form.remark) params.append('remark', form.remark)
    if (isEdit.value && editId.value) params.append('id', editId.value)

    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/regionUpdate'
      : BASE + '/m/Admin/c/Api/a/regionCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[regions] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除区域「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.post(BASE + '/m/Admin/c/Api/a/regionDelete', new URLSearchParams({ id: row.id }).toString(), {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[regions] error:');
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
