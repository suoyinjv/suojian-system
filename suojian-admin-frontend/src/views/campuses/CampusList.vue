<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>校区管理</h2>
        <p>管理所有校区信息与配置</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加校区
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="启用" value="1" />
            <el-option label="停用" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="校区名称/编码/地址" clearable style="width: 240px">
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
        <el-table-column label="校区名称" min-width="140">
          <template #default="{ row }">
            <div class="cell-name">{{ row.name }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="site_name" label="站点名称" min-width="120" />
        <el-table-column prop="code" label="编码" width="100" />
        <el-table-column prop="domain" label="域名" min-width="140" show-overflow-tooltip />
        <el-table-column prop="address" label="地址" min-width="160" show-overflow-tooltip />
        <el-table-column prop="phone" label="电话" width="130" />
        <el-table-column prop="principal" label="负责人" width="100" />
        <el-table-column label="有效期" width="110">
          <template #default="{ row }">{{ row.expire_date || '-' }}</template>
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑校区' : '添加校区'" width="560px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="校区名称" required>
              <el-input v-model="form.name" placeholder="请输入校区名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="站点名称" required>
              <el-input v-model="form.site_name" placeholder="请输入站点名称" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="编码" required>
              <el-input v-model="form.code" placeholder="请输入校区编码" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="域名">
              <el-input v-model="form.domain" placeholder="请输入域名" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="地址">
          <el-input v-model="form.address" placeholder="请输入校区地址" />
        </el-form-item>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="电话">
              <el-input v-model="form.phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="负责人">
              <el-input v-model="form.principal" placeholder="请输入负责人姓名" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="有效期">
              <el-date-picker v-model="form.expire_date" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="状态">
              <el-select v-model="form.status" style="width: 100%">
                <el-option label="启用" :value="1" />
                <el-option label="停用" :value="0" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
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

const filters = reactive({ status: '', keyword: '' })
const form = reactive({
  name: '', site_name: '', code: '', domain: '',
  address: '', phone: '', principal: '', status: 1, expire_date: ''
})

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.status !== '') params.status = filters.status
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/campusList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, status: item.status ?? 1 }))
    total.value = data.total || arr.length
  } catch {
    console.error('[campuses] error:');
    ElMessage.error('加载校区列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.status = ''
  filters.keyword = ''
  page.value = 1
  loadData()
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  form.name = ''
  form.site_name = ''
  form.code = ''
  form.domain = ''
  form.address = ''
  form.phone = ''
  form.principal = ''
  form.status = 1
  form.expire_date = ''
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.name = row.name || ''
  form.site_name = row.site_name || ''
  form.code = row.code || ''
  form.domain = row.domain || ''
  form.address = row.address || ''
  form.phone = row.phone || ''
  form.principal = row.principal || ''
  form.status = row.status ?? 1
  form.expire_date = row.expire_date || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.name || !form.site_name || !form.code) {
    ElMessage.warning('请填写校区名称、站点名称和编码')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('name', form.name)
    params.append('site_name', form.site_name)
    params.append('code', form.code)
    if (form.domain) params.append('domain', form.domain)
    if (form.address) params.append('address', form.address)
    if (form.phone) params.append('phone', form.phone)
    if (form.principal) params.append('principal', form.principal)
    params.append('status', form.status)
    if (form.expire_date) params.append('expire_date', form.expire_date)
    if (isEdit.value && editId.value) params.append('id', editId.value)
    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/campusUpdate'
      : BASE + '/m/Admin/c/Api/a/campusCreate'
    await axios.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[campuses] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除校区「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/campusDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[campuses] error:');
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
    await axios.post(BASE + '/m/Admin/c/Api/a/campusUpdate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(row.status === 1 ? '已启用' : '已停用')
  } catch {
    console.error('[campuses] error:');
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
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.cell-name { font-size: 14px; color: #303133; font-weight: 500; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
