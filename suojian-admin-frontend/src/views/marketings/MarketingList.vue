<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>营销活动</h2>
        <p>创建和管理营销推广活动</p>
      </div>
      <el-button type="primary" size="large" @click="openCreate">
        <el-icon><Plus /></el-icon>新建活动
      </el-button>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="类型">
          <el-select v-model="filters.type" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="促销" value="promotion" />
            <el-option label="体验课" value="trial" />
            <el-option label="讲座" value="lecture" />
            <el-option label="其他" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="活动标题" clearable style="width: 240px">
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
        <el-table-column label="标题" min-width="200" prop="title" />
        <el-table-column label="类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag size="small" effect="plain">{{ typeName(row.type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="内容" min-width="280">
          <template #default="{ row }">
            <div class="content-ellipsis">{{ row.content || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="开始时间" width="160">
          <template #default="{ row }">{{ formatTime(row.start_time) }}</template>
        </el-table-column>
        <el-table-column label="结束时间" width="160">
          <template #default="{ row }">{{ formatTime(row.end_time) }}</template>
        </el-table-column>
        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
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

    <!-- Create Dialog -->
    <el-dialog v-model="showDialog" title="新建活动" width="600px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="标题" required>
          <el-input v-model="form.title" placeholder="请输入活动标题" maxlength="60" show-word-limit />
        </el-form-item>
        <el-form-item label="类型" required>
          <el-select v-model="form.type" style="width: 100%">
            <el-option label="促销" value="promotion" />
            <el-option label="体验课" value="trial" />
            <el-option label="讲座" value="lecture" />
            <el-option label="其他" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="内容">
          <el-input v-model="form.content" type="textarea" :rows="4" placeholder="请输入活动描述" />
        </el-form-item>
        <el-form-item label="开始时间" required>
          <el-date-picker
            v-model="form.start_time"
            type="datetime"
            placeholder="选择开始时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="结束时间" required>
          <el-date-picker
            v-model="form.end_time"
            type="datetime"
            placeholder="选择结束时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="handleCreate" :loading="saving">创建</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import { getMarketingList, createMarketing } from '@/api/marketings'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)

const filters = reactive({ type: '', keyword: '' })
const form = reactive({ title: '', content: '', type: 'promotion', start_time: '', end_time: '' })

function typeName(v) {
  return { promotion: '促销', trial: '体验课', lecture: '讲座', other: '其他' }[v] || v || '-'
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
    if (filters.type !== '') params.type = filters.type
    const res = await getMarketingList(params)
    list.value = Array.isArray(res.list) ? res.list : []
    total.value = res.total || list.value.length
  } catch {
    console.error('[marketings] error:');
    ElMessage.error('加载营销活动列表失败')
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

function openCreate() {
  Object.assign(form, { title: '', content: '', type: 'promotion', start_time: '', end_time: '' })
  showDialog.value = true
}

async function handleCreate() {
  if (!form.title || !form.type || !form.start_time || !form.end_time) {
    ElMessage.warning('请填写完整信息')
    return
  }
  saving.value = true
  try {
    await createMarketing({
      title: form.title,
      content: form.content,
      type: form.type,
      start_time: form.start_time,
      end_time: form.end_time,
    })
    ElMessage.success('创建成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[marketings] error:');
    ElMessage.error('创建失败')
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
.content-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 280px; color: #606266; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
