<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>冒泡广场</h2>
        <p>用户动态内容管理</p>
      </div>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="搜索昵称/内容" clearable style="width: 280px">
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
        <el-table-column label="用户昵称" width="130" prop="nickname" />
        <el-table-column label="内容" min-width="300">
          <template #default="{ row }">
            <div class="content-ellipsis">{{ row.content || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="图片" width="160" align="center">
          <template #default="{ row }">
            <div v-if="row.images && row.images.length" class="image-preview">
              <el-image
                v-for="(img, idx) in row.images.slice(0, 3)"
                :key="idx"
                :src="img"
                :preview-src-list="row.images"
                fit="cover"
                class="thumb-img"
              />
              <span v-if="row.images.length > 3" class="more-img">+{{ row.images.length - 3 }}</span>
            </div>
            <span v-else style="color:#c0c4cc">-</span>
          </template>
        </el-table-column>
        <el-table-column label="发布时间" width="160">
          <template #default="{ row }">{{ formatTime(row.create_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right" align="center">
          <template #default="{ row }">
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
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Delete } from '@element-plus/icons-vue'
import { getBubbleList, deleteBubble } from '@/api/bubbles'

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)

const filters = reactive({ keyword: '' })

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
    const res = await getBubbleList(params)
    const arr = Array.isArray(res.list) ? res.list : []
    list.value = arr.map(item => ({
      ...item,
      images: parseImages(item.images),
    }))
    total.value = res.total || arr.length
  } catch {
    console.error('[bubbles] error:');
    ElMessage.error('加载冒泡列表失败')
  } finally {
    loading.value = false
  }
}

function parseImages(images) {
  if (!images) return []
  if (Array.isArray(images)) return images
  try {
    const parsed = JSON.parse(images)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    console.error('[bubbles] error:');
    return []
  }
}

function resetFilters() {
  filters.keyword = ''
  page.value = 1
  loadData()
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除「${row.nickname}」的这条冒泡吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await deleteBubble(row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[bubbles] error:');
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
.content-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 300px; color: #606266; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.image-preview { display: flex; gap: 4px; align-items: center; justify-content: center; }
.thumb-img { width: 40px; height: 40px; border-radius: 6px; cursor: pointer; border: 1px solid #ebeef5; }
.more-img { font-size: 11px; color: #909399; margin-left: 2px; }
</style>
