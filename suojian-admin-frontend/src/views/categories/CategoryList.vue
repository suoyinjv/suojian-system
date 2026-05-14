<template>
  <div class="category-page">
    <div class="page-header">
      <div class="page-title">
        <h2>分类管理</h2>
        <p>管理文章分类与栏目结构</p>
      </div>
      <el-button type="primary" @click="showDialog = true; editingId = null; form.name = ''; form.desc = ''">
        <el-icon><Plus /></el-icon>添加分类
      </el-button>
    </div>

    <el-row :gutter="20">
      <!-- Category Tree -->
      <el-col :span="14">
        <el-card class="tree-card" shadow="never">
          <template #header>
            <span class="card-title">分类结构</span>
          </template>
          <el-tree
            :data="treeData"
            node-key="id"
            :expand-on-click-node="false"
            default-expand-all
            class="cat-tree"
          >
            <template #default="{ node, data }">
              <span class="tree-node">
                <span class="node-name">
                  <el-icon><Folder /></el-icon>
                  {{ data.name }}
                </span>
                <span class="node-count">({{ data.count }})</span>
                <span class="node-actions">
                  <el-button size="small" text type="primary" @click.stop="editCat(data)">
                    <el-icon><Edit /></el-icon>
                  </el-button>
                  <el-button size="small" text type="danger" @click.stop="deleteCat(data)">
                    <el-icon><Delete /></el-icon>
                  </el-button>
                </span>
              </span>
            </template>
          </el-tree>
        </el-card>
      </el-col>

      <!-- Stats -->
      <el-col :span="10">
        <el-card class="stat-card" shadow="never">
          <template #header><span class="card-title">分类统计</span></template>
          <div class="cat-stats">
            <div v-for="c in categoryStats" :key="c.name" class="cat-stat-row">
              <div class="cat-stat-info">
                <span class="cat-dot" :style="{ background: c.color }"></span>
                <span class="cat-name">{{ c.name }}</span>
              </div>
              <div class="cat-stat-bar">
                <div class="bar-fill" :style="{ width: c.pct + '%', background: c.color }"></div>
              </div>
              <span class="cat-count">{{ c.count }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Dialog -->
    <el-dialog v-model="showDialog" :title="editingId ? '编辑分类' : '添加分类'" width="440px">
      <el-form label-width="80px">
        <el-form-item label="分类名称" required>
          <el-input v-model="form.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="分类描述">
          <el-input v-model="form.desc" type="textarea" :rows="3" placeholder="请输入描述" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" :max="99" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="saveCat">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Folder, Edit, Delete } from '@element-plus/icons-vue'

const showDialog = ref(false)
const editingId = ref(null)
const form = reactive({ name: '', desc: '', sort: 0 })

const treeData = ref([
  {
    id: 1, name: '通知公告', count: 24,
    children: [
      { id: 11, name: '系统通知', count: 8 },
      { id: 12, name: '活动通知', count: 16 },
    ]
  },
  {
    id: 2, name: '校园动态', count: 45,
    children: [
      { id: 21, name: '学术活动', count: 18 },
      { id: 22, name: '文体活动', count: 27 },
    ]
  },
  { id: 3, name: '招生信息', count: 12 },
  { id: 4, name: '教学资源', count: 38 },
  { id: 5, name: '教师园地', count: 20 },
])

const categoryStats = [
  { name: '校园动态', count: 45, pct: 100, color: '#4fc3f7' },
  { name: '教学资源', count: 38, pct: 84, color: '#7c4dff' },
  { name: '教师园地', count: 20, pct: 44, color: '#00c853' },
  { name: '通知公告', count: 24, pct: 53, color: '#ff6b35' },
  { name: '招生信息', count: 12, pct: 27, color: '#ffb300' },
]

function editCat(data) {
  editingId.value = data.id
  form.name = data.name
  form.desc = ''
  showDialog.value = true
}
function deleteCat(data) {
  ElMessageBox.confirm(`确定删除「${data.name}」及其子分类吗？`, '确认删除', { type: 'warning' })
    .then(() => ElMessage.success('删除成功')).catch(() => {})
}
function saveCat() {
  if (!form.name) { ElMessage.error('请输入分类名称'); return }
  ElMessage.success(editingId.value ? '编辑成功' : '添加成功')
  showDialog.value = false
}
</script>

<style scoped>
.category-page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.tree-card, .stat-card { border-radius: 12px; border: 1px solid #ebeef5; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
.tree-node { display: flex; align-items: center; width: 100%; padding: 4px 0; }
.node-name { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 14px; color: #303133; }
.node-count { font-size: 12px; color: #909399; margin-left: 4px; }
.node-actions { opacity: 0; transition: opacity 0.2s; }
.tree-node:hover .node-actions { opacity: 1; }
.cat-stats { display: flex; flex-direction: column; gap: 14px; }
.cat-stat-row { display: flex; align-items: center; gap: 10px; }
.cat-stat-info { display: flex; align-items: center; gap: 6px; width: 90px; flex-shrink: 0; }
.cat-dot { width: 8px; height: 8px; border-radius: 50%; }
.cat-name { font-size: 13px; color: #606266; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cat-stat-bar { flex: 1; height: 6px; background: #f0f0f0; border-radius: 3px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 3px; transition: width 0.6s ease; }
.cat-count { width: 30px; text-align: right; font-size: 13px; color: #606266; font-weight: 500; }
</style>
