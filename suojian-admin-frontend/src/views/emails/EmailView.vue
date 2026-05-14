<template>
  <div class="page">
    <div class="page-header">
      <div class="page-title">
        <h2>邮箱配置</h2>
        <p>配置 SMTP 邮件服务，用于系统发送通知邮件</p>
      </div>
    </div>

    <el-card class="form-card" shadow="never">
      <el-form :model="form" label-width="140px" class="email-form" v-loading="loading">
        <div class="form-section">
          <div class="section-title">SMTP 服务器</div>
          <el-form-item label="SMTP 主机" required>
            <el-input v-model="form.smtp_host" placeholder="如：smtp.qq.com" />
          </el-form-item>
          <el-form-item label="SMTP 端口" required>
            <el-input-number v-model="form.smtp_port" :min="1" :max="65535" />
            <span class="form-hint">常见端口：25（无加密）、465（SSL）、587（TLS）</span>
          </el-form-item>
          <el-form-item label="加密方式">
            <el-select v-model="form.smtp_encrypt" style="width:200px">
              <el-option label="无" value="" />
              <el-option label="SSL" value="ssl" />
              <el-option label="TLS" value="tls" />
            </el-select>
          </el-form-item>
        </div>

        <el-divider />

        <div class="form-section">
          <div class="section-title">发件人信息</div>
          <el-form-item label="发件人邮箱" required>
            <el-input v-model="form.from_email" placeholder="如：noreply@yourdomain.com" />
          </el-form-item>
          <el-form-item label="发件人名称">
            <el-input v-model="form.from_name" placeholder="如：学校管理系统" />
          </el-form-item>
          <el-form-item label="SMTP 用户名" required>
            <el-input v-model="form.smtp_user" placeholder="通常为邮箱地址" />
          </el-form-item>
          <el-form-item label="SMTP 密码" required>
            <el-input v-model="form.smtp_pass" type="password" placeholder="SMTP 授权码或密码" show-password />
          </el-form-item>
        </div>

        <el-divider />

        <div class="form-section">
          <div class="section-title">测试发送</div>
          <el-form-item label="测试收件人">
            <el-input v-model="testEmail" placeholder="输入测试邮箱地址">
              <template #append>
                <el-button type="primary" :loading="testing" @click="handleTest">发送测试</el-button>
              </template>
            </el-input>
          </el-form-item>
        </div>

        <el-divider />

        <el-form-item>
          <el-button type="primary" size="large" @click="handleSave" :loading="saving">
            <el-icon><Check /></el-icon>保存配置
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
import { Check } from '@element-plus/icons-vue'
import axios from 'axios'

const BASE = 'http://47.114.125.123'
const loading = ref(false)
const saving = ref(false)
const testing = ref(false)
const testEmail = ref('')

const form = reactive({
  smtp_host: '',
  smtp_port: 465,
  smtp_encrypt: 'ssl',
  from_email: '',
  from_name: '',
  smtp_user: '',
  smtp_pass: '',
})

async function loadData() {
  loading.value = true
  try {
    const res = await axios.get(BASE + '/m/Admin/c/Api/a/emailConfig')
    const data = res.data
    if (data && typeof data === 'object') {
      form.smtp_host = data.smtp_host || data.host || ''
      form.smtp_port = data.smtp_port ?? data.port ?? 465
      form.smtp_encrypt = data.smtp_encrypt || data.encrypt || 'ssl'
      form.from_email = data.from_email || data.fromEmail || ''
      form.from_name = data.from_name || data.fromName || ''
      form.smtp_user = data.smtp_user || data.username || ''
      form.smtp_pass = data.smtp_pass || data.password || ''
    }
  } catch {
    console.error('[emails] error:');
    // defaults stay
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  if (!form.smtp_host || !form.from_email || !form.smtp_user || !form.smtp_pass) {
    ElMessage.warning('请填写完整的 SMTP 配置（主机、发件人、用户名、密码）')
    return
  }
  saving.value = true
  try {
    const params = new URLSearchParams()
    params.append('smtp_host', form.smtp_host)
    params.append('smtp_port', form.smtp_port)
    params.append('smtp_encrypt', form.smtp_encrypt)
    params.append('from_email', form.from_email)
    params.append('from_name', form.from_name)
    params.append('smtp_user', form.smtp_user)
    params.append('smtp_pass', form.smtp_pass)
    await axios.post(BASE + '/m/Admin/c/Api/a/emailConfigSave', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success('邮箱配置已保存')
  } catch {
    console.error('[emails] error:');
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
  }
}

async function handleTest() {
  if (!testEmail.value) {
    ElMessage.warning('请输入测试收件人邮箱')
    return
  }
  testing.value = true
  try {
    const params = new URLSearchParams()
    params.append('email', testEmail.value)
    await axios.post(BASE + '/m/Admin/c/Api/a/emailTest', params.toString(), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    ElMessage.success(`测试邮件已发送到 ${testEmail.value}`)
  } catch {
    console.error('[emails] error:');
    ElMessage.error('测试发送失败，请检查配置')
  } finally {
    testing.value = false
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
.email-form { max-width: 700px; padding: 10px 0; }
.form-section { }
.section-title { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }
.form-hint { font-size: 12px; color: #909399; margin-left: 12px; }
</style>
