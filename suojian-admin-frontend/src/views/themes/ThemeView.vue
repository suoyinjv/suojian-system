<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>主题管理</h2>
        <p>切换系统主题风格</p>
      </div>
    </div>

    <!-- Current Theme -->
    <el-card class="table-card" shadow="never">
      <template #header>
        <span><el-icon><MagicStick /></el-icon> 当前主题</span>
      </template>
      <div class="current-theme">
        <div class="theme-preview" :class="'theme-' + currentTheme">
          <div class="preview-bar top-bar"></div>
          <div class="preview-body">
            <div class="preview-sidebar"></div>
            <div class="preview-content">
              <div class="preview-line w60"></div>
              <div class="preview-line w40"></div>
              <div class="preview-line w80"></div>
            </div>
          </div>
        </div>
        <div class="theme-info">
          <h3>{{ themeName(currentTheme) }}</h3>
          <p>{{ themeDesc(currentTheme) }}</p>
        </div>
      </div>
    </el-card>

    <!-- Theme Selector -->
    <el-card class="table-card" shadow="never">
      <template #header>
        <span><el-icon><Brush /></el-icon> 选择主题</span>
      </template>
      <el-row :gutter="20">
        <el-col :xs="12" :sm="8" :md="6" v-for="t in themes" :key="t.value">
          <div
            class="theme-card"
            :class="{ active: currentTheme === t.value }"
            @click="switchTheme(t.value)"
          >
            <div class="theme-card-preview" :class="'theme-' + t.value">
              <div class="preview-bar top-bar"></div>
              <div class="preview-body">
                <div class="preview-sidebar"></div>
                <div class="preview-content">
                  <div class="preview-line w60"></div>
                  <div class="preview-line w40"></div>
                </div>
              </div>
            </div>
            <div class="theme-card-info">
              <span class="theme-card-name">{{ t.label }}</span>
              <el-icon v-if="currentTheme === t.value" color="#409eff"><CircleCheck /></el-icon>
            </div>
          </div>
        </el-col>
      </el-row>
    </el-card>

    <!-- Color Customization (placeholder) -->
    <el-card class="table-card" shadow="never">
      <template #header>
        <span><el-icon><Setting /></el-icon> 颜色定制（功能开发中）</span>
      </template>
      <el-alert
        title="颜色自定义功能正在开发中，敬请期待"
        type="warning"
        :closable="false"
        show-icon
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import { MagicStick, Brush, CircleCheck, Setting } from '@element-plus/icons-vue'

const themes = [
  { value: 'default', label: '默认主题', desc: '经典蓝白配色，适合大多数场景' },
  { value: 'dark', label: '深色主题', desc: '深色背景，适合夜间使用' },
  { value: 'green', label: '清新绿', desc: '绿色主调，清新自然' },
  { value: 'purple', label: '优雅紫', desc: '紫色主调，优雅大气' },
]

const currentTheme = ref(localStorage.getItem('app-theme') || 'default')

function themeName(val) {
  return themes.find(t => t.value === val)?.label || '默认主题'
}

function themeDesc(val) {
  return themes.find(t => t.value === val)?.desc || ''
}

function switchTheme(val) {
  currentTheme.value = val
  localStorage.setItem('app-theme', val)
  document.documentElement.setAttribute('data-theme', val)
  ElMessage.success(`已切换为「${themeName(val)}」`)
}
</script>

<style scoped>
.page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }

.current-theme { display: flex; align-items: center; gap: 24px; padding: 8px 0; }
.theme-info h3 { font-size: 18px; font-weight: 600; color: #1a1a2e; margin: 0 0 4px; }
.theme-info p { font-size: 13px; color: #909399; margin: 0; }

.theme-preview {
  width: 240px;
  height: 150px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #ebeef5;
  flex-shrink: 0;
}
.preview-bar { height: 16px; }
.preview-body { display: flex; height: calc(100% - 16px); }
.preview-sidebar { width: 50px; }
.preview-content { flex: 1; padding: 8px; display: flex; flex-direction: column; gap: 6px; }
.preview-line { height: 8px; border-radius: 4px; background: #e0e0e0; }
.w60 { width: 60%; }
.w40 { width: 40%; }
.w80 { width: 80%; }

/* Theme previews */
.theme-default .top-bar { background: linear-gradient(135deg, #4fc3f7, #7c4dff); }
.theme-default .preview-sidebar { background: #1a1a2e; }
.theme-default .preview-content { background: #f0f2f5; }

.theme-dark .top-bar { background: #2d2d3a; }
.theme-dark .preview-sidebar { background: #1a1a2e; }
.theme-dark .preview-content { background: #2d2d3a; }
.theme-dark .preview-line { background: #3d3d4a; }

.theme-green .top-bar { background: linear-gradient(135deg, #43e97b, #38f9d7); }
.theme-green .preview-sidebar { background: #1a3a2e; }
.theme-green .preview-content { background: #f0faf5; }

.theme-purple .top-bar { background: linear-gradient(135deg, #a18cd1, #fbc2eb); }
.theme-purple .preview-sidebar { background: #2a1a3e; }
.theme-purple .preview-content { background: #faf0fc; }

/* Theme cards */
.theme-card {
  border: 2px solid #ebeef5;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 16px;
}
.theme-card:hover { border-color: #4fc3f7; transform: translateY(-2px); box-shadow: 0 4px 20px rgba(79,195,247,0.15); }
.theme-card.active { border-color: #409eff; box-shadow: 0 0 0 2px rgba(64,158,255,0.2); }
.theme-card-preview { height: 100px; }
.theme-card-info { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; }
.theme-card-name { font-size: 14px; font-weight: 500; color: #1a1a2e; }
</style>
