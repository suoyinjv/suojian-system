<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>自定义视图</h2>
        <p>管理自定义展示页面，支持富文本内容与图片上传</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>新增视图
      </el-button>
    </div>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="title" label="视图标题" min-width="180" />
        <el-table-column label="封面图" width="100" align="center">
          <template #default="{ row }">
            <el-image
              v-if="row.image"
              :src="row.image"
              style="width: 50px; height: 50px; border-radius: 6px;"
              fit="cover"
              :preview-src-list="[row.image]"
              preview-teleported
            />
            <span v-else class="no-image">无</span>
          </template>
        </el-table-column>
        <el-table-column label="内容预览" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <span class="content-preview">{{ stripHtml(row.content) || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="sort_order" label="排序" width="70" align="center" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? 'success' : 'info'" size="small" effect="dark">
              {{ row.status == 1 ? '启用' : '停用' }}
            </el-tag>
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
    <el-dialog
      v-model="showDialog"
      :title="isEdit ? '编辑视图' : '新增视图'"
      width="680px"
      :close-on-click-modal="false"
      destroy-on-close
    >
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="视图标题" required>
          <el-input v-model="form.title" placeholder="请输入视图标题" maxlength="60" show-word-limit />
        </el-form-item>

        <el-form-item label="封面图片">
          <el-upload
            class="image-uploader"
            action="#"
            :auto-upload="false"
            :limit="1"
            list-type="picture-card"
            :on-change="handleImageChange"
            :on-remove="handleImageRemove"
            :file-list="imageFileList"
          >
            <el-icon><Plus /></el-icon>
          </el-upload>
          <div class="upload-tip">建议尺寸 750×400 像素，支持 JPG / PNG</div>
        </el-form-item>

        <el-form-item label="视图内容">
          <el-input
            v-model="form.content"
            type="textarea"
            :rows="12"
            placeholder="请输入视图内容（支持 HTML 富文本）"
            class="rich-textarea"
          />
          <div class="upload-tip">支持 HTML 标签，如 &lt;b&gt;加粗&lt;/b&gt;、&lt;p&gt;段落&lt;/p&gt; 等</div>
        </el-form-item>

        <el-form-item label="排序">
          <el-input-number v-model="form.sort_order" :min="0" :max="999" style="width: 180px" />
        </el-form-item>

        <el-form-item label="状态">
          <el-switch
            v-model="form.status"
            :active-value="1"
            :inactive-value="0"
            active-text="启用"
            inactive-text="停用"
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
import { Plus, Edit, Delete } from '@element-plus/icons-vue'
import http from '../../utils/http'

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
const imageFileList = ref([])

const defaultForm = { title: '', content: '', image: '', sort_order: 0, status: 1 }
const form = reactive({ ...defaultForm })

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    const res = await http.get(BASE + '/m/Admin/c/Api/a/customViewList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, status: item.status ?? 1 }))
    total.value = data.total || arr.length
  } catch {
    console.error('[customViews] error:');
    ElMessage.error('加载自定义视图列表失败')
  } finally {
    loading.value = false
  }
}

function stripHtml(html) {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
}

function openAdd() {
  isEdit.value = false
  editId.value = null
  Object.assign(form, defaultForm)
  imageFileList.value = []
  showDialog.value = true
}

function openEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.title = row.title || ''
  form.content = row.content || ''
  form.image = row.image || ''
  form.sort_order = row.sort_order ?? 0
  form.status = row.status ?? 1
  if (row.image) {
    imageFileList.value = [{ name: '封面', url: row.image }]
  } else {
    imageFileList.value = []
  }
  showDialog.value = true
}

function handleImageChange(file) {
  form.image = file.raw
  imageFileList.value = [file]
}

function handleImageRemove() {
  form.image = ''
  imageFileList.value = []
}

async function uploadImage(file) {
  const fd = new FormData()
  fd.append('file', file)
  const res = await http.post(BASE + '/m/Admin/c/Api/a/uploadImage', fd, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  return res.data?.url || res.data?.data?.url || ''
}

async function handleSave() {
  if (!form.title) {
    ElMessage.warning('请填写视图标题')
    return
  }
  saving.value = true
  try {
    let imageUrl = form.image

    // Upload image if it's a File object (new upload)
    if (imageUrl && typeof imageUrl !== 'string') {
      try {
        const uploaded = await uploadImage(imageUrl)
        if (uploaded) imageUrl = uploaded
      } catch {
    console.error('[customViews] error:');
        ElMessage.warning('图片上传失败，将跳过图片')
        imageUrl = ''
      }
    }

    const params = new URLSearchParams()
    params.append('title', form.title)
    params.append('content', form.content || '')
    params.append('image', imageUrl || '')
    params.append('sort_order', form.sort_order)
    params.append('status', form.status)
    if (isEdit.value && editId.value) params.append('id', editId.value)

    const url = isEdit.value
      ? BASE + '/m/Admin/c/Api/a/customViewUpdate'
      : BASE + '/m/Admin/c/Api/a/customViewCreate'

    await http.post(url, params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(isEdit.value ? '编辑成功' : '新增成功')
    showDialog.value = false
    loadData()
  } catch (e) {
    console.error('[customViews] error:');
    console.error('[customViews] error:');
    ElMessage.error('操作失败')
   } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除视图「${row.title}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await http.get(BASE + '/m/Admin/c/Api/a/customViewDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[customViews] error:');
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
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.content-preview { color: #606266; font-size: 13px; }
.no-image { color: #c0c4cc; font-size: 12px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
.upload-tip { font-size: 12px; color: #909399; margin-top: 4px; line-height: 1.4; }
.image-uploader :deep(.el-upload--picture-card) { width: 120px; height: 120px; line-height: 120px; }
.rich-textarea :deep(.el-textarea__inner) { font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6; }
</style>
