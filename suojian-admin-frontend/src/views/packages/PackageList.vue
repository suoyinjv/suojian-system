<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>套餐管理</h2>
        <p>管理课程套餐信息，含价格与课时设置</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加套餐
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总套餐</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.enabled }}</span>
          <span class="stat-label">启用中</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.maxPrice }}元</span>
          <span class="stat-label">最高价格</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.totalHours }}</span>
          <span class="stat-label">总课时</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="类型">
          <el-select v-model="filters.type" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="课时包" value="1" />
            <el-option label="学期包" value="2" />
            <el-option label="年卡" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="套餐名称" clearable style="width: 220px">
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
        <el-table-column prop="name" label="套餐名称" min-width="140" />
        <el-table-column label="类型" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="typeTag(row.type)" size="small">{{ typeName(row.type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="总课时" width="90" align="center">
          <template #default="{ row }">
            <span style="font-weight: 600;">{{ row.total_hours || 0 }}</span>
            <span style="color: #909399; font-size: 12px;"> 课时</span>
          </template>
        </el-table-column>
        <el-table-column label="赠送课时" width="90" align="center">
          <template #default="{ row }">{{ row.gift_hours || 0 }}</template>
        </el-table-column>
        <el-table-column label="价格" width="110" align="center">
          <template #default="{ row }">
            <span style="font-weight: 600; color: #ff6b35;">¥{{ row.price || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="有效期" width="90" align="center">
          <template #default="{ row }">{{ row.valid_days || '-' }}天</template>
        </el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? 'success' : 'info'" size="small" effect="dark">
              {{ row.status == 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="160" fixed="right" align="center">
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑套餐' : '添加套餐'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="套餐名称" required>
          <el-input v-model="form.name" placeholder="请输入套餐名称" />
        </el-form-item>
        <el-form-item label="套餐类型" required>
          <el-select v-model="form.type" style="width: 100%">
            <el-option label="课时包" :value="1" />
            <el-option label="学期包" :value="2" />
            <el-option label="年卡" :value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="总课时" required>
          <el-input-number v-model="form.total_hours" :min="0" :precision="1" :step="1" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">课时</span>
        </el-form-item>
        <el-form-item label="赠送课时">
          <el-input-number v-model="form.gift_hours" :min="0" :precision="1" :step="1" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">课时</span>
        </el-form-item>
        <el-form-item label="价格" required>
          <el-input-number v-model="form.price" :min="0" :precision="2" :step="100" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">元</span>
        </el-form-item>
        <el-form-item label="有效期" required>
          <el-input-number v-model="form.valid_days" :min="1" :step="30" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">天</span>
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
import { Plus, Search, Edit, Delete } from '@element-plus/icons-vue'
import { getPackagesList, createPackage, updatePackage, deletePackage } from '@/api/packages'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const isEdit = ref(false)
const editId = ref(null)

const filters = reactive({ type: '', keyword: '' })
const stats = reactive({ total: 0, enabled: 0, maxPrice: 0, totalHours: 0 })

const defaultForm = { name: '', type: 1, total_hours: 0, gift_hours: 0, price: 0, valid_days: 365, status: 1 }
const form = reactive({ ...defaultForm })

function typeName(t) {
  return { 1: '课时包', 2: '学期包', 3: '年卡' }[t] || '未知'
}
function typeTag(t) {
  return { 1: 'primary', 2: 'success', 3: 'warning' }[t] || 'info'
}

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.type !== '') params.type = filters.type
    const res = await getPackagesList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr
    total.value = res.total || arr.length
    stats.total = total.value
    stats.enabled = arr.filter(i => i.status == 1).length
    stats.maxPrice = arr.reduce((max, i) => Math.max(max, parseFloat(i.price) || 0), 0)
    stats.totalHours = arr.reduce((sum, i) => sum + (parseFloat(i.total_hours) || 0), 0)
  } catch {
    console.error('[packages] error:');
    ElMessage.error('加载套餐列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.type = ''
  filters.keyword = ''
  page.value = 1
  loadData()
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
  form.type = row.type ?? 1
  form.total_hours = row.total_hours ?? 0
  form.gift_hours = row.gift_hours ?? 0
  form.price = row.price ?? 0
  form.valid_days = row.valid_days ?? 365
  form.status = row.status ?? 1
  showDialog.value = true
}

async function handleSave() {
  if (!form.name) {
    ElMessage.warning('请填写套餐名称')
    return
  }
  saving.value = true
  try {
    const data = {
      name: form.name,
      type: form.type,
      total_hours: form.total_hours,
      gift_hours: form.gift_hours,
      price: form.price,
      valid_days: form.valid_days,
      status: form.status,
    }
    if (isEdit.value && editId.value) {
      await updatePackage(editId.value, data)
      ElMessage.success('编辑成功')
    } else {
      await createPackage(data)
      ElMessage.success('添加成功')
    }
    showDialog.value = false
    loadData()
  } catch {
    console.error('[packages] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除套餐「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await deletePackage(row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[packages] error:');
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
.page-stats { margin-bottom: 20px; }
.stat-item { background: #fff; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #ebeef5; transition: transform 0.2s; }
.stat-item:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(79,195,247,0.15); }
.stat-num { display: block; font-size: 28px; font-weight: 700; background: linear-gradient(135deg,#4fc3f7,#7c4dff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; display: block; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
