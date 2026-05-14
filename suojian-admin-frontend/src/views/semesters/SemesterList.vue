<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>学期管理</h2>
        <p>管理所有学期信息与时间配置</p>
      </div>
      <el-button type="primary" @click="openAdd">
        <el-icon><Plus /></el-icon>添加学期
      </el-button>
    </div>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="name" label="学期名称" min-width="160" />
        <el-table-column prop="start_date" label="开始日期" width="120" align="center" />
        <el-table-column prop="end_date" label="结束日期" width="120" align="center" />
        <el-table-column prop="sort_order" label="排序" width="80" align="center" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? 'success' : 'info'" size="small" effect="dark">
              {{ row.status == 1 ? '启用' : '停用' }}
            </el-tag>
          </template>
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

    <!-- Add Dialog -->
    <el-dialog v-model="showDialog" title="添加学期" width="520px" :close-on-click-modal="false">
      <el-form :model="form" label-width="90px" class="dialog-form">
        <el-form-item label="学期名称" required>
          <el-input v-model="form.name" placeholder="请输入学期名称，如：2026年春季" />
        </el-form-item>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="开始日期" required>
              <el-date-picker v-model="form.start_date" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="结束日期">
              <el-date-picker v-model="form.end_date" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort_order" :min="0" :max="999" style="width: 180px" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.status" :active-value="1" :inactive-value="0" active-text="启用" inactive-text="停用" />
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
import { Plus, Delete } from '@element-plus/icons-vue'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)
const saving = ref(false)
const showDialog = ref(false)

const form = reactive({
  name: '', start_date: '', end_date: '', sort_order: 0, status: 1
})

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/semesterList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr.map(item => ({ ...item, status: item.status ?? 1 }))
    total.value = data.total || arr.length
  } catch {
    console.error('[semesters] error:');
    ElMessage.error('加载学期列表失败')
  } finally {
    loading.value = false
  }
}

function openAdd() {
  form.name = ''
  form.start_date = ''
  form.end_date = ''
  form.sort_order = 0
  form.status = 1
  showDialog.value = true
}

async function handleSave() {
  if (!form.name) {
    ElMessage.warning('请填写学期名称')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('name', form.name)
    if (form.start_date) params.append('start_date', form.start_date)
    if (form.end_date) params.append('end_date', form.end_date)
    params.append('sort_order', form.sort_order)
    params.append('status', form.status)
    await axios.post(BASE + '/m/Admin/c/Api/a/semesterCreate', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('添加成功')
    showDialog.value = false
    loadData()
  } catch {
    console.error('[semesters] error:');
    ElMessage.error('操作失败')
  } finally {
    saving.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除学期「${row.name}」吗？`, '确认删除', { type: 'warning' })
    .then(async () => {
      try {
        await axios.get(BASE + '/m/Admin/c/Api/a/semesterDelete?id=' + row.id)
        ElMessage.success('删除成功')
        loadData()
      } catch {
    console.error('[semesters] error:');
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
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
