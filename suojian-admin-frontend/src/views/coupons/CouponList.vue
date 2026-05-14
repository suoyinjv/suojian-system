<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>优惠券管理</h2>
        <p>管理优惠券的创建、发放与使用情况</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加优惠券
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总优惠券</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.active }}</span>
          <span class="stat-label">启用中</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.discount }}</span>
          <span class="stat-label">折扣券</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.deduct }}</span>
          <span class="stat-label">抵扣券</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="类型">
          <el-select v-model="filters.type" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="折扣券" value="1" />
            <el-option label="抵扣券" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 110px">
            <el-option label="全部" value="" />
            <el-option label="启用" value="1" />
            <el-option label="禁用" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="优惠券名称" clearable style="width: 220px">
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
        <el-table-column prop="name" label="名称" min-width="140" />
        <el-table-column label="类型" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.type == 1 ? 'primary' : 'success'" size="small">
              {{ row.type == 1 ? '折扣' : '抵扣' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="优惠内容" width="120" align="center">
          <template #default="{ row }">
            <span v-if="row.type == 1" style="font-weight: 600; color: #ff6b35;">
              {{ (row.discount_rate / 10) }}折
            </span>
            <span v-else style="font-weight: 600; color: #ff6b35;">
              ¥{{ row.discount_amount || 0 }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="最低消费" width="100" align="center">
          <template #default="{ row }">¥{{ row.min_amount || 0 }}</template>
        </el-table-column>
        <el-table-column label="使用情况" width="130" align="center">
          <template #default="{ row }">
            {{ row.used_count || 0 }} / {{ row.total_count || 0 }}
          </template>
        </el-table-column>
        <el-table-column label="有效期" width="90" align="center">
          <template #default="{ row }">{{ row.valid_days }}天</template>
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
    <el-dialog v-model="showDialog" :title="isEdit ? '编辑优惠券' : '添加优惠券'" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="优惠券名称" required>
          <el-input v-model="form.name" placeholder="请输入优惠券名称" />
        </el-form-item>
        <el-form-item label="优惠类型" required>
          <el-radio-group v-model="form.type">
            <el-radio :value="1">折扣券</el-radio>
            <el-radio :value="2">抵扣券</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-if="form.type == 1" label="折扣率" required>
          <el-input-number v-model="form.discount_rate" :min="10" :max="100" :step="10" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">{{ (form.discount_rate / 10) }}折</span>
        </el-form-item>
        <el-form-item v-if="form.type == 2" label="抵扣金额" required>
          <el-input-number v-model="form.discount_amount" :min="0" :precision="2" :step="10" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">元</span>
        </el-form-item>
        <el-form-item label="最低消费">
          <el-input-number v-model="form.min_amount" :min="0" :precision="2" :step="10" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">元</span>
        </el-form-item>
        <el-form-item label="发放总量" required>
          <el-input-number v-model="form.total_count" :min="1" :step="50" style="width: 180px" />
          <span style="margin-left: 8px; color: #909399;">张</span>
        </el-form-item>
        <el-form-item label="有效期" required>
          <el-input-number v-model="form.valid_days" :min="1" :step="7" style="width: 180px" />
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
import { getCouponsList, createCoupon, updateCoupon, deleteCoupon } from '@/api/coupons'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const isEdit = ref(false)
const editId = ref(null)

const filters = reactive({ type: '', status: '', keyword: '' })
const stats = reactive({ total: 0, active: 0, discount: 0, deduct: 0 })

const defaultForm = { name: '', type: 1, min_amount: 0, discount_amount: 0, discount_rate: 100, total_count: 100, valid_days: 30, status: 1 }
const form = reactive({ ...defaultForm })

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.keyword) params.keyword = filters.keyword
    if (filters.type !== '') params.type = filters.type
    if (filters.status !== '') params.status = filters.status
    const res = await getCouponsList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr
    total.value = res.total || arr.length
    stats.total = total.value
    stats.active = arr.filter(i => i.status == 1).length
    stats.discount = arr.filter(i => i.type == 1).length
    stats.deduct = arr.filter(i => i.type == 2).length
  } catch {
    console.error('[coupons] error:');
    ElMessage.error('加载优惠券列表失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.type = ''
  filters.status = ''
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
  form.min_amount = row.min_amount ?? 0
  form.discount_amount = row.discount_amount ?? 0
  form.discount_rate = row.discount_rate ?? 100
  form.total_count = row.total_count ?? 100
  form.valid_days = row.valid_days ?? 30
  form.status = row.status ?? 1
  showDialog.value = true
}

async function handleSave() {
  if (!form.name) {
    ElMessage.warning('请填写优惠券名称')
    return
  }
  saving.value = true
  try {
    const data = {
      name: form.name,
      type: form.type,
      min_amount: form.min_amount,
      discount_amount: form.discount_amount,
      discount_rate: form.discount_rate,
      total_count: form.total_count,
      valid_days: form.valid_days,
      status: form.status,
    }
    if (isEdit.value && editId.value) {
      await updateCoupon(editId.value, data)
      ElMessage.success('编辑成功')
    } else {
      await createCoupon(data)
      ElMessage.success('添加成功')
    }
    showDialog.value = false
    loadData()
  } catch {
    console.error('[coupons] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除优惠券「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await deleteCoupon(row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[coupons] error:');
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
