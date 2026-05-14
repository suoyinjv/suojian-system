<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>海报管理</h2>
        <p>管理活动海报与宣传素材</p>
      </div>
    </div>

    <!-- Filters -->
    <el-card class="table-card" shadow="never">
      <el-form :model="filters" layout="inline" class="filter-form">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="搜索海报标题..." clearable style="width: 240px" @keyup.enter="loadData">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
          <el-button type="primary" @click="loadData" style="margin-left: 8px">查询</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Poster Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="list" stripe v-loading="loading" empty-text="暂无海报数据">
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column label="ID" width="70" prop="id" align="center" />
        <el-table-column label="标题" min-width="180" prop="title" show-overflow-tooltip />
        <el-table-column label="图片" width="120" align="center">
          <template #default="{ row }">
            <el-image
              v-if="row.image"
              :src="row.image"
              fit="cover"
              style="width: 60px; height: 60px; border-radius: 6px;"
              :preview-src-list="[row.image]"
              preview-teleported
            >
              <template #error>
                <div class="image-error">无图</div>
              </template>
            </el-image>
            <span v-else class="image-error">无图</span>
          </template>
        </el-table-column>
        <el-table-column label="宽x高" width="100" align="center">
          <template #default="{ row }">{{ row.width && row.height ? row.width + "x" + row.height : "-" }}</template>
        </el-table-column>
        <el-table-column label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status == 1 ? success : info" size="small">{{ row.status == 1 ? "启用" : "禁用" }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="140" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="handleEdit(row)">
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
          @current-change="loadData"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue"
import { ElMessage, ElMessageBox } from "element-plus"
import { Search, Edit, Delete } from "@element-plus/icons-vue"
import http from "../../utils/http"

const BASE = "http://47.114.125.123"

const list = ref([])
const page = ref(1)
const pageSize = 10
const total = ref(0)
const loading = ref(false)

const filters = reactive({ keyword: "" })

function getToken() {
  return sessionStorage.getItem("token") || ""
}

function resetFilters() {
  filters.keyword = ""
  page.value = 1
  loadData()
}

async function loadData() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value, pageSize })
    if (filters.keyword) params.append("keyword", filters.keyword)
    const resp = await http.get(BASE + "/m/Admin/c/Api/a/posterList?" + params.toString(), {
      headers: { Authorization: "Bearer " + getToken() },
      timeout: 15000,
    })
    const res = resp.data
    if (res.code === 0) {
      const data = res.data || {}
      list.value = data.list || []
      total.value = data.total || 0
    } else {
      ElMessage.error(res.msg || "获取海报列表失败")
    }
  } catch (e) {
    console.error('[posters] error:');
    console.error('[posters] error:');
    ElMessage.error("请求失败: " + (e.message || "网络错误"))
   } finally {
    loading.value = false
  }
}

async function handleEdit(row) {
  ElMessage.info("编辑功能待接入: ID=" + row.id)
}

async function handleDelete(row) {
  try {
    await ElMessageBox.confirm("确定删除海报「" + (row.title || row.id) + "」吗？", "确认删除", { type: "warning" })
    ElMessage.success("删除成功（待接入API）")
    // TODO: call posterDelete API
    loadData()
  } catch {
    console.error('[posters] error:');
    // cancelled
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
.filter-card { margin-bottom: 16px; border-radius: 12px; border: 1px solid #ebeef5; }
.filter-form { display: flex; flex-wrap: wrap; gap: 8px; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.image-error { color: #c0c4cc; font-size: 12px; }
</style>
