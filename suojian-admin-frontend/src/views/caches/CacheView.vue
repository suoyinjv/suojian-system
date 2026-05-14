<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>缓存管理</h2>
        <p>查看系统缓存状态并手动清理缓存</p>
      </div>
    </div>

    <!-- Cache Status -->
    <el-row :gutter="20" class="cache-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#4fc3f7">
          <div class="stat-icon" style="background:#e3f2fd"><el-icon size="22" color="#4fc3f7"><DataBoard /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ cacheInfo.total || '-' }}</span>
            <span class="stat-label">缓存总数</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#00c853">
          <div class="stat-icon" style="background:#e8f5e9"><el-icon size="22" color="#00c853"><Coin /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ cacheInfo.size || '0 KB' }}</span>
            <span class="stat-label">缓存大小</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#ff6b35">
          <div class="stat-icon" style="background:#fff3e0"><el-icon size="22" color="#ff6b35"><Timer /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ cacheInfo.expire || '-' }}</span>
            <span class="stat-label">过期时间</span>
          </div>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-card" style="--accent:#7c4dff">
          <div class="stat-icon" style="background:#f3e5f5"><el-icon size="22" color="#7c4dff"><Refresh /></el-icon></div>
          <div class="stat-info">
            <span class="stat-value">{{ cacheInfo.driver || '-' }}</span>
            <span class="stat-label">缓存驱动</span>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Cache List -->
    <el-card class="table-card" shadow="never">
      <template #header>
        <div class="card-header">
          <span class="card-title">缓存列表</span>
          <el-button type="danger" plain :loading="clearing" @click="handleClearAll">
            <el-icon><Delete /></el-icon>清理全部缓存
          </el-button>
        </div>
      </template>
      <el-table :data="cacheList" stripe v-loading="loading">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column prop="key" label="缓存键" min-width="220">
          <template #default="{ row }">
            <code style="color:#409eff;font-size:13px;">{{ row.key || '-' }}</code>
          </template>
        </el-table-column>
        <el-table-column prop="value" label="缓存值" min-width="260" show-overflow-tooltip />
        <el-table-column prop="expire_at" label="过期时间" width="160">
          <template #default="{ row }">{{ formatTime(row.expire_at) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="danger" @click="handleClearOne(row)">
              <el-icon><Delete /></el-icon>清理
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { DataBoard, Coin, Timer, Refresh, Delete } from '@element-plus/icons-vue'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const loading = ref(false)
const clearing = ref(false)
const cacheList = ref([])
const cacheInfo = reactive({ total: '-', size: '-', expire: '-', driver: '-' })

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts * 1000)
  return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

async function loadCacheStatus() {
  loading.value = true
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/cacheInfo')
    const data = res.data
    if (data && typeof data === 'object') {
      cacheInfo.total = data.total ?? '-'
      cacheInfo.size = data.size ?? '-'
      cacheInfo.expire = data.expire ?? '-'
      cacheInfo.driver = data.driver ?? '-'
      cacheList.value = Array.isArray(data.list) ? data.list : []
    }
  } catch {
    console.error('[caches] error:');
    // defaults stay
  } finally {
    loading.value = false
  }
}

async function handleClearAll() {
  ElMessageBox.confirm('确定要清理全部缓存吗？此操作不可恢复。', '确认清理', { type: 'warning' })
    .then(async () => {
      clearing.value = true
      try {
        await axios.post(BASE + '/m/Admin/c/Api/a/cacheClear', null, {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        ElMessage.success('全部缓存已清理')
        loadCacheStatus()
      } catch {
    console.error('[caches] error:');
        ElMessage.error('清理缓存失败')
      } finally {
        clearing.value = false
      }
    })
    .catch(() => {})
}

async function handleClearOne(row) {
  ElMessageBox.confirm(`确定清理缓存「${row.key}」吗？`, '确认清理', { type: 'warning' })
    .then(async () => {
      try {
        const params = new URLSearchParams()
        params.append('key', row.key)
        await axios.post(BASE + '/m/Admin/c/Api/a/cacheClear', params.toString(), {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        ElMessage.success('缓存已清理')
        loadCacheStatus()
      } catch {
    console.error('[caches] error:');
        ElMessage.error('清理失败')
      }
    })
    .catch(() => {})
}

onMounted(() => { loadCacheStatus() })
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.cache-stats { margin-bottom: 20px; }
.stat-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; border: 1px solid #ebeef5; transition: all 0.3s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 24px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
.stat-label { font-size: 13px; color: #909399; margin-top: 4px; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; }
.card-header { display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
</style>
