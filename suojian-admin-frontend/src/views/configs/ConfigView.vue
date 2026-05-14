<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>站点设置</h2>
        <p>配置网站基本信息、联系方式与系统参数</p>
      </div>
    </div>

    <el-card class="form-card" shadow="never">
      <el-form :model="form" label-width="130px" class="site-form" v-loading="loading">
        <!-- Basic -->
        <div class="form-section">
          <div class="section-title">基础信息</div>
          <el-form-item label="网站名称" required>
            <el-input v-model="form.site_name" placeholder="请输入网站名称" maxlength="100" show-word-limit />
          </el-form-item>
          <el-form-item label="网站简称">
            <el-input v-model="form.site_short_name" placeholder="简称（如：SUOJIAN）" maxlength="50" />
          </el-form-item>
          <el-form-item label="网站 Logo">
            <el-input v-model="form.site_logo" placeholder="Logo 图片 URL">
              <template #prepend><el-icon><Picture /></el-icon></template>
            </el-input>
          </el-form-item>
          <el-form-item label="网站 ICP 备案号">
            <el-input v-model="form.icp" placeholder="如：浙ICP备XXXXXXXX号" />
          </el-form-item>
        </div>

        <el-divider />

        <!-- Contact -->
        <div class="form-section">
          <div class="section-title">联系方式</div>
          <el-form-item label="联系电话">
            <el-input v-model="form.phone" placeholder="请输入联系电话" />
          </el-form-item>
          <el-form-item label="联系邮箱">
            <el-input v-model="form.email" placeholder="请输入联系邮箱" />
          </el-form-item>
          <el-form-item label="学校地址">
            <el-input v-model="form.address" type="textarea" :rows="2" placeholder="请输入详细地址" />
          </el-form-item>
        </div>

        <el-divider />

        <!-- System -->
        <div class="form-section">
          <div class="section-title">系统参数</div>
          <el-form-item label="每页条数">
            <el-input-number v-model="form.page_size" :min="5" :max="200" :step="5" />
          </el-form-item>
          <el-form-item label="开放注册">
            <el-switch v-model="form.register_open" :active-value="1" :inactive-value="0" />
          </el-form-item>
          <el-form-item label="维护模式">
            <el-switch v-model="form.maintenance" :active-value="1" :inactive-value="0" />
          </el-form-item>
        </div>

        <el-divider />

        <!-- Submit -->
        <el-form-item>
          <el-button type="primary" size="large" @click="handleSave" :loading="saving">
            <el-icon><Check /></el-icon>保存设置
          </el-button>
          <el-button size="large" @click="loadData">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Check, Picture } from '@element-plus/icons-vue'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const loading = ref(false)
const saving = ref(false)

const form = reactive({
  site_name: '',
  site_short_name: '',
  site_logo: '',
  icp: '',
  phone: '',
  email: '',
  address: '',
  page_size: 20,
  register_open: 1,
  maintenance: 0,
})

async function loadData() {
  loading.value = true
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/siteConfig')
    const data = res.data
    if (data && typeof data === 'object') {
      form.site_name = data.site_name || data.siteName || ''
      form.site_short_name = data.site_short_name || data.siteShortName || ''
      form.site_logo = data.site_logo || data.siteLogo || ''
      form.icp = data.icp || data.icp_number || ''
      form.phone = data.phone || ''
      form.email = data.email || ''
      form.address = data.address || ''
      form.page_size = data.page_size ?? data.pageSize ?? 20
      form.register_open = data.register_open ?? data.registerOpen ?? 1
      form.maintenance = data.maintenance ?? 0
    }
  } catch {
    console.error('[configs] error:');
    ElMessage.error('加载站点配置失败')
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  if (!form.site_name) {
    ElMessage.warning('请填写网站名称')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('site_name', form.site_name)
    params.append('site_short_name', form.site_short_name)
    params.append('site_logo', form.site_logo)
    params.append('icp', form.icp)
    params.append('phone', form.phone)
    params.append('email', form.email)
    params.append('address', form.address)
    params.append('page_size', form.page_size)
    params.append('register_open', form.register_open)
    params.append('maintenance', form.maintenance)
    await axios.post(BASE + '/m/Admin/c/Api/a/siteConfigSave', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('站点设置已保存')
  } catch {
    console.error('[configs] error:');
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
.page-header { margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.form-card { border-radius: 12px; border: 1px solid #ebeef5; }
.site-form { max-width: 720px; padding: 10px 0; }
.form-section { }
.section-title { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }
</style>
