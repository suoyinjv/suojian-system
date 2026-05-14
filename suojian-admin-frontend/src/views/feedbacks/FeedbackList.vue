<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>反馈管理</h2>
        <p>查看和处理用户提交的反馈意见</p>
      </div>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="page-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总反馈</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.pending }}</span>
          <span class="stat-label">待处理</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.processing }}</span>
          <span class="stat-label">处理中</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.resolved }}</span>
          <span class="stat-label">已解决</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="待处理" value="pending" />
            <el-option label="处理中" value="processing" />
            <el-option label="已解决" value="resolved" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="搜索反馈内容" clearable style="width: 240px">
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
        <el-table-column label="用户" width="130" prop="username" />
        <el-table-column label="联系方式" width="150" prop="contact" />
        <el-table-column label="反馈内容" min-width="300">
          <template #default="{ row }">
            <div class="content-ellipsis">{{ row.content || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="110" align="center">
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
            <el-button size="small" text type="primary" @click="openProcess(row)">
              <el-icon><Edit /></el-icon>{{ row.status === 'resolved' ? '查看' : '处理' }}
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

    <!-- Process Dialog -->
    <el-dialog v-model="showDialog" title="处理反馈" width="560px" :close-on-click-modal="false">
      <div class="feedback-detail">
        <div class="detail-row">
          <span class="detail-label">用户：</span>
          <span>{{ currentRow?.username || '-' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">联系方式：</span>
          <span>{{ currentRow?.contact || '-' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">反馈内容：</span>
          <div class="detail-content">{{ currentRow?.content || '-' }}</div>
        </div>
      </div>
      <el-form :model="form" label-width="90px" class="dialog-form" style="margin-top: 12px;">
        <el-form-item label="处理状态">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="待处理" value="pending" />
            <el-option label="处理中" value="processing" />
            <el-option label="已解决" value="resolved" />
          </el-select>
        </el-form-item>
        <el-form-item label="处理回复">
          <el-input v-model="form.reply" type="textarea" :rows="4" placeholder="请输入处理回复" />
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
import { getFeedbackList, updateFeedback } from '@/api/feedbacks'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)
const currentRow = ref(null)

const filters = reactive({ status: '', keyword: '' })
const stats = reactive({ total: 0, pending: 0, processing: 0, resolved: 0 })
const form = reactive({ status: 'pending', reply: '' })

function statusName(v) {
  return { pending: '待处理', processing: '处理中', resolved: '已解决' }[v] || '未知'
}
function statusType(v) {
  return { pending: 'danger', processing: 'warning', resolved: 'success' }[v] || 'info'
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
    const res = await getFeedbackList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr
    total.value = res.total || arr.length
    stats.total = total.value
    stats.pending = arr.filter(i => i.status === 'pending').length
    stats.processing = arr.filter(i => i.status === 'processing').length
    stats.resolved = arr.filter(i => i.status === 'resolved').length
  } catch {
    console.error('[feedbacks] error:');
    ElMessage.error('加载反馈列表失败')
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

function openProcess(row) {
  currentRow.value = row
  form.status = row.status || 'pending'
  form.reply = row.reply || ''
  showDialog.value = true
}

async function handleSave() {
  saving.value = true
  try {
    await updateFeedback(currentRow.value.id, {
      status: form.status,
      reply: form.reply,
    })
    ElMessage.success('处理成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[feedbacks] error:');
    ElMessage.error('处理失败')
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
.page-stats { margin-bottom: 20px; }
.stat-item { background: #fff; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #ebeef5; transition: transform 0.2s; }
.stat-item:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(79,195,247,0.15); }
.stat-num { display: block; font-size: 28px; font-weight: 700; background: linear-gradient(135deg,#4fc3f7,#7c4dff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; display: block; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.content-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 300px; color: #606266; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
.feedback-detail { background: #f5f7fa; border-radius: 8px; padding: 16px; }
.detail-row { display: flex; margin-bottom: 8px; font-size: 14px; }
.detail-row:last-child { margin-bottom: 0; }
.detail-label { color: #909399; width: 80px; flex-shrink: 0; }
.detail-content { color: #303133; line-height: 1.6; flex: 1; }
</style>
