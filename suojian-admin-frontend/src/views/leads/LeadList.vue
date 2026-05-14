<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>线索管理</h2>
        <p>管理潜在学员线索，跟进与转化</p>
      </div>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总线索</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.new }}</span>
          <span class="stat-label">新线索</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.followed }}</span>
          <span class="stat-label">已跟进</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.converted }}</span>
          <span class="stat-label">已转化</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="新线索" value="1" />
            <el-option label="已跟进" value="2" />
            <el-option label="已转化" value="3" />
            <el-option label="已流失" value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="姓名/手机/微信" clearable style="width: 240px">
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
        <el-table-column label="姓名" width="100">
          <template #default="{ row }">
            <div class="cell-row">
              <el-avatar :size="32" :style="{ background: row.color }">{{ row.name?.[0] || '?' }}</el-avatar>
              <span class="cell-name">{{ row.name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="手机" width="130" prop="phone" />
        <el-table-column label="微信" width="130" prop="wechat">
          <template #default="{ row }">{{ row.wechat || '-' }}</template>
        </el-table-column>
        <el-table-column label="来源" width="100" prop="source">
          <template #default="{ row }">{{ row.source || '-' }}</template>
        </el-table-column>
        <el-table-column label="兴趣" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="interestType(row.interest_level)" size="small">
              {{ interestName(row.interest_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="small" effect="dark">
              {{ statusName(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
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

    <!-- Edit Dialog -->
    <el-dialog v-model="showDialog" title="编辑线索" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="姓名" required>
          <el-input v-model="form.name" placeholder="请输入姓名" />
        </el-form-item>
        <el-form-item label="手机" required>
          <el-input v-model="form.phone" placeholder="请输入手机号" />
        </el-form-item>
        <el-form-item label="微信">
          <el-input v-model="form.wechat" placeholder="请输入微信号" />
        </el-form-item>
        <el-form-item label="来源">
          <el-input v-model="form.source" placeholder="如：转介绍、公众号" />
        </el-form-item>
        <el-form-item label="兴趣程度">
          <el-select v-model="form.interest_level" style="width: 100%">
            <el-option label="高" :value="3" />
            <el-option label="中" :value="2" />
            <el-option label="低" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="新线索" :value="1" />
            <el-option label="已跟进" :value="2" />
            <el-option label="已转化" :value="3" />
            <el-option label="已流失" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="跟进记录">
          <el-input v-model="form.follow_record" type="textarea" :rows="3" placeholder="跟进记录" />
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
import { Search, Edit, Delete } from '@element-plus/icons-vue'
import { getLeadsList, updateLead, deleteLead } from '@/api/leads'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const editId = ref(null)

const filters = reactive({ status: '', keyword: '' })
const stats = reactive({ total: 0, new: 0, followed: 0, converted: 0 })

const form = reactive({ name: '', phone: '', wechat: '', source: '', interest_level: 2, status: 1, follow_record: '' })
const colors = ['#4fc3f7', '#7c4dff', '#00c853', '#ff6b35', '#ffb300', '#e91e63']

function interestName(v) {
  return { 3: '高', 2: '中', 1: '低' }[v] || '-'
}
function interestType(v) {
  return { 3: 'success', 2: 'warning', 1: 'info' }[v] || 'info'
}
function statusName(v) {
  return { 1: '新线索', 2: '已跟进', 3: '已转化', 4: '已流失' }[v] || '未知'
}
function statusType(v) {
  return { 1: 'primary', 2: 'warning', 3: 'success', 4: 'danger' }[v] || 'info'
}

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
    if (filters.status !== '') params.status = filters.status
    const res = await getLeadsList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr.map(item => ({
      ...item,
      color: colors[Math.floor(Math.random() * colors.length)],
    }))
    total.value = res.total || arr.length
    stats.total = total.value
    stats.new = arr.filter(i => i.status == 1).length
    stats.followed = arr.filter(i => i.status == 2).length
    stats.converted = arr.filter(i => i.status == 3).length
  } catch {
    console.error('[leads] error:');
    ElMessage.error('加载线索列表失败')
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

function openEdit(row) {
  editId.value = row.id
  form.name = row.name || ''
  form.phone = row.phone || ''
  form.wechat = row.wechat || ''
  form.source = row.source || ''
  form.interest_level = row.interest_level ?? 2
  form.status = row.status ?? 1
  form.follow_record = row.follow_record || ''
  showDialog.value = true
}

async function handleSave() {
  if (!form.name || !form.phone) {
    ElMessage.warning('请填写姓名和手机号')
    return
  }
  saving.value = true
  try {
    const data = {
      name: form.name,
      phone: form.phone,
      wechat: form.wechat,
      source: form.source,
      interest_level: form.interest_level,
      status: form.status,
      follow_record: form.follow_record,
    }
    await updateLead(editId.value, data)
    ElMessage.success('编辑成功')
    showDialog.value = false
    editId.value = null
    loadData()
  } catch {
    console.error('[leads] error:');
    ElMessage.error('编辑失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除线索「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await deleteLead(row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[leads] error:');
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
.cell-row { display: flex; align-items: center; gap: 8px; }
.cell-name { font-size: 14px; color: #303133; font-weight: 500; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
