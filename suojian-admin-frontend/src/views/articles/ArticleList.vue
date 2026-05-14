<template>
  <div class="article-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h2>文章管理</h2>
        <p>管理所有文章内容，包括发布、编辑和删除</p>
      </div>
      <el-button type="primary" size="large" @click="openDialog()">
        <el-icon><Plus /></el-icon>发布文章
      </el-button>
    </div>

    <!-- Filters -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline" class="filter-form">
        <el-form-item label="状态">
          <el-select v-model="filters.status" placeholder="全部" clearable style="width: 130px" @change="loadArticles">
            <el-option label="已发布" value="published" />
            <el-option label="草稿" value="draft" />
          </el-select>
        </el-form-item>
        <el-form-item label="分类">
          <el-select v-model="filters.categoryId" placeholder="全部分类" clearable style="width: 160px" @change="loadArticles">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="搜索文章标题..." clearable style="width: 240px" @keyup.enter="loadArticles">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
          <el-button type="primary" @click="loadArticles" style="margin-left: 8px">查询</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Article Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="articles" stripe v-loading="loading" @selection-change="handleSelection">
        <el-table-column type="selection" width="45" />
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="title" label="标题" min-width="260">
          <template #default="{ row }">
            <div class="article-title">{{ row.title }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="category" label="分类" width="120" align="center">
          <template #default="{ row }">
            <el-tag size="small" effect="plain">{{ row.category || '未分类' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="access_count" label="浏览" width="90" align="center">
          <template #default="{ row }">
            <span class="views-count">
              <el-icon><View /></el-icon>
              {{ row.access_count || row.views || 0 }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120" align="center">
          <template #default="{ row }">
            <el-switch
              :model-value="!!row.is_enable"
              active-text="已发布"
              inactive-text="草稿"
              inline-prompt
              @change="handleToggle(row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="date" label="日期" width="120" align="center">
          <template #default="{ row }">
            {{ row.date || formatDate(row.add_time) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="openDialog(row)">
              <el-icon><Edit /></el-icon>编辑
            </el-button>
            <el-button size="small" text type="danger" @click="handleDelete(row)">
              <el-icon><Delete /></el-icon>删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- Pagination -->
      <div class="table-footer">
        <span class="selected-info" v-if="selectedIds.length">
          已选择 <strong>{{ selectedIds.length }}</strong> 项
          <el-button text size="small" type="danger" @click="batchDelete">批量删除</el-button>
        </span>
        <el-pagination
          v-model:current-page="page"
          :page-size="pageSize"
          :total="total"
          layout="total, prev, pager, next"
          background
          small
          @current-change="loadArticles"
        />
      </div>
    </el-card>

    <!-- Add/Edit Dialog -->
    <el-dialog
      v-model="dialogVisible"
      :title="editingId ? '编辑文章' : '发布文章'"
      width="680px"
      :close-on-click-modal="false"
    >
      <el-form :model="form" label-width="80px" class="dialog-form" :rules="rules" ref="formRef">
        <el-form-item label="标题" prop="title">
          <el-input v-model="form.title" placeholder="请输入文章标题，限60字以内" maxlength="60" show-word-limit />
        </el-form-item>
        <el-form-item label="分类" prop="categoryId">
          <el-select v-model="form.categoryId" placeholder="请选择分类" style="width: 100%">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="封面图">
          <el-upload
            class="cover-upload"
            action="#"
            :auto-upload="false"
            :limit="1"
            list-type="picture-card"
            :on-change="handleCoverChange"
          >
            <el-icon><Plus /></el-icon>
          </el-upload>
          <div class="upload-tip">建议尺寸 750×400 像素</div>
        </el-form-item>
        <el-form-item label="正文" prop="content">
          <el-input
            v-model="form.content"
            type="textarea"
            :rows="10"
            placeholder="请输入文章正文内容…"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button @click="saveArticle('draft')" :loading="saving">保存草稿</el-button>
        <el-button type="primary" @click="saveArticle('publish')" :loading="saving">发布</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Edit, Delete, View } from '@element-plus/icons-vue'
import { getArticles, createArticle, updateArticle, deleteArticle, toggleArticleStatus } from '../../api/article'

const loading = ref(false)
const saving = ref(false)
const articles = ref([])
const page = ref(1)
const pageSize = ref(15)
const total = ref(0)
const dialogVisible = ref(false)
const editingId = ref(null)
const selectedIds = ref([])
const formRef = ref(null)

const filters = reactive({ status: '', categoryId: '', keyword: '' })

const form = reactive({ title: '', categoryId: '', category: '', content: '', image: '' })

const rules = {
  title: [{ required: true, message: '请输入文章标题', trigger: 'blur' }],
  categoryId: [{ required: true, message: '请选择分类', trigger: 'change' }],
}

const categories = [
  { id: 1, name: '通知公告' },
  { id: 2, name: '校园动态' },
  { id: 3, name: '招生信息' },
  { id: 4, name: '教学资源' },
]

onMounted(loadArticles)

async function loadArticles() {
  loading.value = true
  try {
    const { list, total: t } = await getArticles({
      page: page.value,
      pageSize: pageSize.value,
      ...filters,
    })
    articles.value = list
    total.value = t
  } catch (e) {
    console.error('[articles] error:');
    // error handled by interceptor
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.status = ''
  filters.categoryId = ''
  filters.keyword = ''
  page.value = 1
  loadArticles()
}

function handleSelection(rows) {
  selectedIds.value = rows.map(r => r.id)
}

function openDialog(row) {
  if (row) {
    editingId.value = row.id
    form.title = row.title
    form.categoryId = row.categoryId
    form.category = row.category
    form.content = row.content || ''
    form.image = row.image || ''
  } else {
    editingId.value = null
    Object.assign(form, { title: '', categoryId: '', category: '', content: '', image: '' })
  }
  dialogVisible.value = true
}

function handleCoverChange(file) {
  form.image = file.raw || ''
}

async function saveArticle(action = 'draft') {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  saving.value = true
  try {
    const cat = categories.find(c => c.id === form.categoryId)
    const data = {
      title: form.title,
      categoryId: form.categoryId,
      category: cat?.name || '',
      content: form.content,
      image: form.image,
      is_enable: action === 'publish' ? 1 : 0,
    }

    if (editingId.value) {
      await updateArticle(editingId.value, data)
    } else {
      await createArticle(data)
    }

    ElMessage.success(editingId.value ? '编辑成功' : (action === 'publish' ? '发布成功' : '草稿已保存'))
    dialogVisible.value = false
    loadArticles()
  } finally {
    saving.value = false
  }
}

async function handleToggle(row) {
  const newVal = row.is_enable ? 0 : 1
  try {
    await toggleArticleStatus(row.id, !!newVal)
    row.is_enable = newVal
    ElMessage.success(newVal ? '已发布' : '已设为草稿')
  } catch {
    console.error('[articles] error:');
    row.is_enable = newVal ? 0 : 1 // revert
  }
}

async function handleDelete(row) {
  await ElMessageBox.confirm(`确定删除「${row.title}」吗？`, '确认删除', { type: 'warning' })
  await deleteArticle(row.id)
  ElMessage.success('删除成功')
  loadArticles()
}

async function batchDelete() {
  await ElMessageBox.confirm(`确定删除选中的 ${selectedIds.value.length} 篇文章吗？`, '确认删除', { type: 'warning' })
  await Promise.all(selectedIds.value.map(id => deleteArticle(id)))
  ElMessage.success('批量删除成功')
  selectedIds.value = []
  loadArticles()
}

function formatDate(ts) {
  if (!ts) return ''
  return new Date(ts * 1000).toISOString().slice(0, 10)
}
</script>

<style scoped>
.article-page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.filter-card { margin-bottom: 16px; border-radius: 12px; border: 1px solid #ebeef5; }
.filter-form { display: flex; flex-wrap: wrap; gap: 8px; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; }
.article-title { font-size: 14px; color: #303133; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.views-count { display: flex; align-items: center; justify-content: center; gap: 4px; color: #909399; font-size: 13px; }
.table-footer { display: flex; justify-content: space-between; align-items: center; padding: 16px 0 0; }
.selected-info { font-size: 13px; color: #606266; }
.selected-info strong { color: #409eff; }
.dialog-form { padding: 10px 0; }
.upload-tip { font-size: 12px; color: #909399; margin-top: 4px; }
</style>
