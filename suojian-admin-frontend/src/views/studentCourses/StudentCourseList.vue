<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>课时管理</h2>
        <p>管理学员课时包与课消记录</p>
      </div>
      <el-button type="primary" @click="showCreate = true">
        <el-icon><Plus /></el-icon>新建课时包
      </el-button>
    </div>
    <el-card class="card" shadow="never">
      <el-form :model="filters" inline>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="学员姓名" clearable style="width:200px" @clear="load" @keyup.enter="load" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="load">查询</el-button>
          <el-button @click="filters.keyword='';load()">重置</el-button>
        </el-form-item>
      </el-form>
      <el-table :data="list" stripe v-loading="loading">
        <el-table-column type="index" width="50" align="center" />
        <el-table-column prop="student_name" label="学员" min-width="120" />
        <el-table-column prop="course_name" label="课程" min-width="140" />
        <el-table-column label="总课时" width="90" align="center">
          <template #default="{row}"><el-tag type="info">{{ row.total_hours }}</el-tag></template>
        </el-table-column>
        <el-table-column label="已消耗" width="90" align="center">
          <template #default="{row}"><el-tag type="warning">{{ row.used_hours }}</el-tag></template>
        </el-table-column>
        <el-table-column label="剩余" width="90" align="center">
          <template #default="{row}"><el-tag :type="row.remaining_hours > 0 ? 'success' : 'danger'">{{ row.remaining_hours }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="expire_date" label="到期时间" width="160">
          <template #default="{row}">{{ row.expire_date ? new Date(row.expire_date*1000).toLocaleDateString() : '-' }}</template>
        </el-table-column>
        <el-table-column label="状态" width="90" align="center">
          <template #default="{row}">
            <el-tag :type="row.status == 1 ? 'success' : 'danger'" size="small">{{ row.status == 1 ? '正常' : '已过期' }}</el-tag>
          </template>
        </el-table-column>
      </el-table>
      <div class="table-footer">
        <el-pagination v-model:current-page="page" :page-size="15" :total="total" layout="total, prev, pager, next" background small @current-change="load" />
      </div>
    </el-card>

    <!-- 新建弹窗 -->
    <el-dialog v-model="showCreate" title="新建课时包" width="500px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="学员" required>
          <el-input v-model="form.studentName" placeholder="学员姓名" />
        </el-form-item>
        <el-form-item label="课程" required>
          <el-input v-model="form.courseName" placeholder="课程名称" />
        </el-form-item>
        <el-form-item label="总课时" required>
          <el-input-number v-model="form.totalHours" :min="1" :max="999" />
        </el-form-item>
        <el-form-item label="到期日期">
          <el-date-picker v-model="form.expireDate" type="date" value-format="timestamp" placeholder="选填" style="width:100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreate=false">取消</el-button>
        <el-button type="primary" @click="handleCreate" :loading="submitting">创建</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'

const BASE = 'http://47.114.125.123'
const list = ref([])
const loading = ref(false)
const total = ref(0)
const page = ref(1)
const filters = reactive({ keyword: '' })
const showCreate = ref(false)
const submitting = ref(false)
const form = reactive({ studentName: '', courseName: '', totalHours: 30, expireDate: null })

async function axiosGet(url) {
  const resp = await fetch(url)
  return resp.json()
}
async function axiosPost(url, data) {
  const resp = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(data) })
  return resp.json()
}

async function load() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value, pageSize: 15 })
    if (filters.keyword) params.append('keyword', filters.keyword)
    const res = await axiosGet(BASE + '/m/Admin/c/Api/a/studentCourses?' + params.toString())
    if (res.code === 0) {
      list.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (e) {
    console.error('[studentCourses] error:');
    console.error('[studentCourses] error:'); ElMessage.error('加载失败')  }
  finally { loading.value = false }
}

async function handleCreate() {
  if (!form.studentName || !form.courseName) { ElMessage.warning('请填写完整信息'); return }
  submitting.value = true
  try {
    const res = await axiosPost(BASE + '/m/Admin/c/Api/a/studentCourseCreate', {
      studentId: form.studentName,
      courseId: form.courseName,
      totalHours: form.totalHours,
      expireDate: form.expireDate ? Math.floor(form.expireDate / 1000) : 0,
    })
    if (res.code === 0) {
      ElMessage.success('创建成功')
      showCreate.value = false
      load()
    } else ElMessage.error(res.msg || '创建失败')
  } catch (e) {
    console.error('[studentCourses] error:');
    console.error('[studentCourses] error:'); ElMessage.error('网络错误')  }
  finally { submitting.value = false }
}

onMounted(load)
</script>

<style scoped>
.page { animation: fadeIn .4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.card { border-radius: 12px; border: 1px solid #ebeef5; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
</style>
