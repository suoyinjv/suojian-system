<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>SEO设置</h2>
        <p>配置网站标题、关键词与描述，优化搜索引擎收录</p>
      </div>
    </div>

    <!-- SEO Form -->
    <el-card class="form-card" shadow="never">
      <el-form :model="form" label-width="120px" class="seo-form" v-loading="loading">
        <el-form-item label="网站标题 (Title)" required>
          <el-input
            v-model="form.title"
            placeholder="例如：学校管理系统 - 专业的教育管理平台"
            maxlength="200"
            show-word-limit
          />
          <div class="form-tip">浏览器标签页标题，建议不超过 60 个字符</div>
        </el-form-item>

        <el-form-item label="关键词 (Keywords)" required>
          <el-input
            v-model="form.keywords"
            type="textarea"
            :rows="3"
            placeholder="例如：学校管理,教务系统,教育平台"
            maxlength="500"
            show-word-limit
          />
          <div class="form-tip">多个关键词用英文逗号分隔，建议 3-5 个核心词</div>
        </el-form-item>

        <el-form-item label="描述 (Description)" required>
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="5"
            placeholder="例如：一个功能强大的学校管理系统，支持教务管理、学员管理、考勤打卡等全方位功能。"
            maxlength="1000"
            show-word-limit
          />
          <div class="form-tip">搜索引擎结果页中显示的描述文字，建议控制在 80-160 个字符</div>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" size="large" @click="handleSave" :loading="saving">
            <el-icon><Check /></el-icon>保存设置
          </el-button>
          <el-button size="large" @click="loadData">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Preview -->
    <el-card class="preview-card" shadow="never">
      <template #header>
        <span><el-icon><View /></el-icon> 搜索引擎预览</span>
      </template>
      <div class="seo-preview">
        <div class="preview-url">{{ previewUrl }}</div>
        <div class="preview-title">{{ form.title || '网站标题' }}</div>
        <div class="preview-desc">{{ form.description || '网站描述...' }}</div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Check, View } from '@element-plus/icons-vue'
import http from '../../utils/http'

const BASE = 'http://47.114.125.123'
const loading = ref(false)
const saving = ref(false)

const form = reactive({
  title: '',
  keywords: '',
  description: ''
})

const previewUrl = computed(() => {
  return 'www.yourschool.com / ' + (form.title || '学校管理系统')
})

async function loadData() {
  loading.value = true
  try {
    const res = await http.get(BASE + '/m/Admin/c/Api/a/seoConfig')
    const data = res.data
    if (data && typeof data === 'object') {
      form.title = data.title || ''
      form.keywords = data.keywords || ''
      form.description = data.description || ''
    }
  } catch {
    console.error('[seo] error:');
    ElMessage.error('加载SEO配置失败')
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  if (!form.title) {
    ElMessage.warning('请填写网站标题')
    return
  }
  if (!form.keywords) {
    ElMessage.warning('请填写关键词')
    return
  }
  if (!form.description) {
    ElMessage.warning('请填写网站描述')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('title', form.title)
    params.append('keywords', form.keywords)
    params.append('description', form.description)
    await http.post(BASE + '/m/Admin/c/Api/a/seoSave', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('SEO设置保存成功')
  } catch {
    console.error('[seo] error:');
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
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
.form-card, .preview-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.seo-form { max-width: 700px; padding: 20px 0; }
.form-tip { font-size: 12px; color: #909399; margin-top: 4px; line-height: 1.5; }
.preview-card :deep(.el-card__header) { font-weight: 600; font-size: 15px; color: #1a1a2e; }
.seo-preview { padding: 8px 0; }
.preview-url { font-size: 13px; color: #202124; line-height: 1.4; }
.preview-title { font-size: 18px; color: #1a0dab; font-weight: 400; line-height: 1.4; margin: 4px 0; cursor: pointer; }
.preview-title:hover { text-decoration: underline; }
.preview-desc { font-size: 13px; color: #545454; line-height: 1.5; word-break: break-all; }
</style>
