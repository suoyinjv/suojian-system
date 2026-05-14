<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>资源库</h2>
        <p>管理文件上传与资源存储</p>
      </div>
      <el-button type="primary" @click="handleUpload">
        <el-icon><Upload /></el-icon>上传文件
      </el-button>
    </div>

    <!-- Resource Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading" empty-text="暂无文件数据">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column label="ID" width="70" prop="id" align="center" />
        <el-table-column label="文件名" min-width="220" prop="name" show-overflow-tooltip />
        <el-table-column label="文件类型" width="120" align="center">
          <template #default="{ row }">
            <el-tag size="small">{{ row.type || row.ext || "-" }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="文件大小" width="120" align="center">
          <template #default="{ row }">{{ row.size ? formatSize(row.size) : "-" }}</template>
        </el-table-column>
        <el-table-column label="上传时间" width="160" align="center">
          <template #default="{ row }">{{ row.create_time || row.add_time || "-" }}</template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="handleDownload(row)">
              <el-icon><Download /></el-icon>下载
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
          @current-change="loadData"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { ElMessage } from "element-plus"
import { Upload, Download } from "@element-plus/icons-vue"
import http from "../../utils/http"

const BASE = "http://47.114.125.123"

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)

function getToken() {
  return sessionStorage.getItem("token") || ""
}

function formatSize(bytes) {
  if (!bytes) return "-"
  if (typeof bytes === "string") bytes = parseInt(bytes, 10)
  if (bytes < 1024) return bytes + " B"
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB"
  return (bytes / (1024 * 1024)).toFixed(2) + " MB"
}

async function loadData() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value, pageSize })
    const resp = await http.get(BASE + "/m/Admin/c/Api/a/resourceList?" + params.toString(), {
      headers: { Authorization: "Bearer " + getToken() },
      timeout: 15000,
    })
    const res = resp.data
    if (res.code === 0) {
      const data = res.data || {}
      list.value = data.list || []
      total.value = data.total || 0
    } else {
      ElMessage.error(res.msg || "获取资源列表失败")
    }
  } catch (e) {
    console.error('[resources] error:');
    console.error('[resources] error:');
    ElMessage.error("请求失败: " + (e.message || "网络错误"))
   } finally {
    loading.value = false
  }
}

function handleUpload() {
  ElMessage.info("文件上传功能待接入")
}

function handleDownload(row) {
  if (row.url) {
    window.open(row.url, "_blank")
  } else {
    ElMessage.info("下载链接待接入: ID=" + row.id)
  }
}

onMounted(loadData)
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
</style>
