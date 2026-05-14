<template>
  <div class="comment-page">
    <div class="page-header">
      <div class="page-title">
        <h2>评论管理</h2>
        <p>审核与管理用户评论内容</p>
      </div>
      <div class="header-stats">
        <span class="stat-badge pending"><i class="el-icon-chat-dot-round"></i> {{ pendingCount }} 待审核</span>
        <span class="stat-badge total">共 {{ total }} 条评论</span>
      </div>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 130px">
            <el-option label="全部" value="" />
            <el-option label="待审核" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已驳回" value="rejected" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="搜索评论内容..." clearable style="width: 240px">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadComments">查询</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="comments" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column label="评论内容" min-width="280">
          <template #default="{ row }">
            <div class="comment-cell">
              <el-avatar :size="32" :style="{ background: row.avatar }">{{ row.user[0] }}</el-avatar>
              <div class="comment-info">
                <div class="comment-user">{{ row.user }}</div>
                <div class="comment-content">{{ row.content }}</div>
                <div class="comment-meta">
                  来自文章：<span class="article-link" @click="$router.push('/articles')">{{ row.article }}</span>
                  · {{ row.date }}
                </div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="date" label="时间" width="160" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="small">{{ statusLabel(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="220" fixed="right" align="center">
          <template #default="{ row }">
            <template v-if="row.status === 'pending'">
              <el-button size="small" type="success" @click="handleApprove(row)">
                <el-icon><Select /></el-icon>通过
              </el-button>
              <el-button size="small" type="danger" plain @click="handleReject(row)">
                <el-icon><Close /></el-icon>驳回
              </el-button>
            </template>
            <template v-else>
              <el-button size="small" text type="danger" @click="handleDelete(row)">
                <el-icon><Delete /></el-icon>删除
              </el-button>
            </template>
          </template>
        </el-table-column>
      </el-table>

      <div class="table-footer">
        <el-pagination
          v-model:current-page="page"
          :page-size="10"
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
import { ref, computed, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Delete, Select, Close } from '@element-plus/icons-vue'
import { getComments, approveComment, rejectComment, deleteComment } from '../../api/comment'

const loading = ref(false)
const comments = ref([])
const page = ref(1)
const total = ref(0)
const filters = reactive({ status: '', keyword: '' })

const pendingCount = computed(() => comments.value.filter(c => c.status === 'pending').length)

const statusType = (s) => ({ pending: 'warning', approved: 'success', rejected: 'danger' }[s] || 'info')
const statusLabel = (s) => ({ pending: '待审核', approved: '已通过', rejected: '已驳回' }[s] || s)

onMounted(loadComments)

async function loadComments() {
  loading.value = true
  try {
    const { list, total: t } = await getComments({ ...filters })
    comments.value = list
    total.value = t
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.status = ''
  filters.keyword = ''
  loadComments()
}

async function handleApprove(row) {
  await approveComment(row.id)
  row.status = 'approved'
  ElMessage.success('已通过审核')
}

async function handleReject(row) {
  await rejectComment(row.id)
  row.status = 'rejected'
  ElMessage.success('已驳回')
}

async function handleDelete(row) {
  await deleteComment(row.id)
  comments.value = comments.value.filter(c => c.id !== row.id)
  ElMessage.success('删除成功')
}
</script>

<style scoped>
.comment-page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.header-stats { display: flex; gap: 12px; align-items: center; }
.stat-badge { font-size: 13px; padding: 4px 12px; border-radius: 20px; }
.stat-badge.pending { background: #fff7e6; color: #fa8c16; }
.stat-badge.total { background: #f5f5f5; color: #666; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.comment-cell { display: flex; align-items: flex-start; gap: 10px; }
.comment-info { flex: 1; min-width: 0; }
.comment-user { font-size: 14px; font-weight: 500; color: #303133; margin-bottom: 4px; }
.comment-content { font-size: 13px; color: #606266; line-height: 1.6; margin-bottom: 4px; word-break: break-all; }
.comment-meta { font-size: 12px; color: #909399; }
.article-link { color: #4fc3f7; cursor: pointer; }
.article-link:hover { text-decoration: underline; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
</style>
