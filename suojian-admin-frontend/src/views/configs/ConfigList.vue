<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>系统配置</h2>
        <p>管理系统的键值对配置项</p>
      </div>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="键名">
          <el-input v-model="filters.key" placeholder="配置键名" clearable style="width: 220px">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
        </el-form-item>
        <el-form-item label="分组">
          <el-select v-model="filters.group" clearable style="width: 130px">
            <el-option label="全部" value="" />
            <el-option label="基础" value="basic" />
            <el-option label="系统" value="system" />
            <el-option label="业务" value="biz" />
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
        <el-table-column prop="config_key" label="配置键" width="200">
          <template #default="{ row }">
            <code style="font-size: 13px; color: #409eff;">{{ row.config_key || row.key || '-' }}</code>
          </template>
        </el-table-column>
        <el-table-column prop="config_value" label="配置值" min-width="260" show-overflow-tooltip>
          <template #default="{ row }">
            <span class="value-text">{{ row.config_value || row.value || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="分组" width="100" align="center">
          <template #default="{ row }">
            <el-tag size="small">{{ row.group || 'basic' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="描述" min-width="160" show-overflow-tooltip>
          <template #default="{ row }">{{ row.description || '-' }}</template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="openEdit(row)">
              <el-icon><Edit /></el-icon>编辑
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

    <!-- Edit Dialog -->
    <el-dialog v-model="showDialog" title="编辑配置" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="配置键">
          <el-input v-model="form.config_key" :disabled="true" />
        </el-form-item>
        <el-form-item label="配置值" required>
          <el-input
            v-model="form.config_value"
            type="textarea"
            :rows="4"
            placeholder="请输入配置值"
          />
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="form.description" :disabled="true" />
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
import { Search, Edit } from '@element-plus/icons-vue'
import http from '../../utils/http'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const editRow = ref(null)

const filters = reactive({ key: '', group: '' })
const form = reactive({ config_key: '', config_value: '', description: '' })

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.key) params.key = filters.key
    if (filters.group) params.group = filters.group
    const res = await http.get(BASE + '/m/Admin/c/Api/a/configList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[configs] error:');
    ElMessage.error('加载配置列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.key = ''
  filters.group = ''
  page.value = 1
  loadData()
}

function openEdit(row) {
  editRow.value = row
  form.config_key = row.config_key || row.key || ''
  form.config_value = row.config_value || row.value || ''
  form.description = row.description || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.config_value) {
    ElMessage.warning('请填写配置值')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('config_key', form.config_key)
    params.append('config_value', form.config_value)
    await http.post(BASE + '/m/Admin/c/Api/a/configSave', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('保存成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[configs] error:');
    ElMessage.error('保存失败')
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
.value-text { color: #1a1a2e; font-weight: 500; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
