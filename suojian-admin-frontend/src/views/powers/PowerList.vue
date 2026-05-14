<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>权限管理</h2>
        <p>查看系统中的所有权限节点（只读）</p>
      </div>
    </div>

    <el-card class="table-card" shadow="never">
      <el-alert
        title="权限树为只读展示，不可编辑"
        type="info"
        :closable="false"
        show-icon
        style="margin-bottom: 16px;"
      />
      <el-tree
        ref="treeRef"
        :data="treeData"
        :props="treeProps"
        node-key="id"
        :highlight-current="true"
        :expand-on-click-node="true"
        default-expand-all
        v-loading="loading"
        class="power-tree"
      >
        <template #default="{ node, data }">
          <span class="tree-node">
            <span class="tree-node-label">{{ data.name || data.title || node.label }}</span>
            <span class="tree-node-key" v-if="data.key || data.permission_key">({{ data.key || data.permission_key }})</span>
            <el-tag v-if="data.type === 'menu'" size="small" type="primary" effect="plain" style="margin-left: 8px;">菜单</el-tag>
            <el-tag v-else-if="data.type === 'button'" size="small" type="success" effect="plain" style="margin-left: 8px;">按钮</el-tag>
            <el-tag v-else-if="data.type === 'api'" size="small" type="warning" effect="plain" style="margin-left: 8px;">接口</el-tag>
          </span>
        </template>
      </el-tree>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const loading = ref(false)
const treeData = ref([])

const treeProps = {
  children: 'children',
  label: 'name',
}

async function loadData() {
  loading.value = true
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/powerList')
    const data = res.data
    const list = Array.isArray(data.list) ? data.list : Array.isArray(data) ? data : []
    treeData.value = list
  } catch {
    console.error('[powers] error:');
    ElMessage.error('加载权限树失败')
  } finally {
    loading.value = false
  }
}

onMounted(() => { loadData() })
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.power-tree { padding: 8px 0; }
.tree-node { display: flex; align-items: center; font-size: 14px; }
.tree-node-label { font-weight: 500; color: #1a1a2e; }
.tree-node-key { font-size: 12px; color: #909399; margin-left: 8px; }
.power-tree :deep(.el-tree-node__content) { height: 40px; }
</style>
