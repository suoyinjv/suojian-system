<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>短信记录</h2>
        <p>查看系统发送的短信记录</p>
      </div>
    </div>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 130px">
            <el-option label="全部" value="" />
            <el-option label="成功" value="1" />
            <el-option label="失败" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="手机号">
          <el-input v-model="filters.phone" placeholder="请输入手机号" clearable style="width: 180px">
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
        <el-table-column prop="phone" label="手机号" width="140" />
        <el-table-column prop="content" label="短信内容" min-width="260" show-overflow-tooltip />
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? 'success' : 'danger'" size="small" effect="dark">
              {{ row.status == 1 ? '成功' : '失败' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="发送时间" width="170">
          <template #default="{ row }">{{ row.send_time || '-' }}</template>
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
import { ElMessage } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)

const filters = reactive({ status: '', phone: '' })

async function loadData() {
  loading.value = true
  try {
    const params = { page: page.value, pageSize }
    if (filters.status !== '') params.status = filters.status
    if (filters.phone) params.phone = filters.phone
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/smsList', { params })
    const data = res.data
    const arr = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    list.value = arr
    total.value = data.total || arr.length
  } catch {
    console.error('[sms] error:');
    ElMessage.error('加载短信记录失败')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.status = ''
  filters.phone = ''
  page.value = 1
  loadData()
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
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
</style>
