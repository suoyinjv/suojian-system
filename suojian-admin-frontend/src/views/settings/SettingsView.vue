<template>
  <div class="settings-page">
    <div class="page-header">
      <div class="page-title">
        <h2>系统设置</h2>
        <p>配置系统参数与个人信息</p>
      </div>
    </div>

    <el-row :gutter="20">
      <!-- Left Nav -->
      <el-col :span="5">
        <el-card class="nav-card" shadow="never">
          <el-menu :default-active="activeTab" @select="activeTab = $event">
            <el-menu-item index="basic">
              <el-icon><Setting /></el-icon>
              <span>基本设置</span>
            </el-menu-item>
            <el-menu-item index="security">
              <el-icon><Lock /></el-icon>
              <span>安全设置</span>
            </el-menu-item>
            <el-menu-item index="notify">
              <el-icon><Bell /></el-icon>
              <span>通知设置</span>
            </el-menu-item>
            <el-menu-item index="about">
              <el-icon><InfoFilled /></el-icon>
              <span>关于系统</span>
            </el-menu-item>
          </el-menu>
        </el-card>
      </el-col>

      <!-- Right Content -->
      <el-col :span="19">
        <!-- Basic Settings -->
        <el-card v-show="activeTab === 'basic'" class="settings-card" shadow="never">
          <template #header><span class="card-title">基本设置</span></template>
          <el-form label-width="120px" class="settings-form">
            <el-form-item label="网站名称">
              <el-input v-model="form.siteName" placeholder="请输入网站名称" />
            </el-form-item>
            <el-form-item label="网站Logo">
              <el-upload action="#" :auto-upload="false" list-type="picture-card">
                <el-icon><Plus /></el-icon>
              </el-upload>
            </el-form-item>
            <el-form-item label="联系电话">
              <el-input v-model="form.phone" placeholder="请输入联系电话" />
            </el-form-item>
            <el-form-item label="联系邮箱">
              <el-input v-model="form.email" placeholder="请输入邮箱" />
            </el-form-item>
            <el-form-item label="学校地址">
              <el-input v-model="form.address" type="textarea" :rows="2" placeholder="请输入地址" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveBasic">保存设置</el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <!-- Security -->
        <el-card v-show="activeTab === 'security'" class="settings-card" shadow="never">
          <template #header><span class="card-title">安全设置</span></template>
          <div class="security-item">
            <div class="sec-info">
              <div class="sec-title">修改密码</div>
              <div class="sec-desc">定期更换密码可提高账号安全性</div>
            </div>
            <el-button type="primary" plain @click="showPwdDialog = true">修改密码</el-button>
          </div>
          <el-divider />
          <div class="security-item">
            <div class="sec-info">
              <div class="sec-title">两步验证</div>
              <div class="sec-desc">启用后登录需要额外验证手机验证码</div>
            </div>
            <el-switch v-model="form.twoFactor" active-text="已启用" inactive-text="未启用" />
          </div>
          <el-divider />
          <div class="security-item">
            <div class="sec-info">
              <div class="sec-title">登录日志</div>
              <div class="sec-desc">查看账号的登录历史记录</div>
            </div>
            <el-button type="default" plain>查看日志</el-button>
          </div>
        </el-card>

        <!-- Notifications -->
        <el-card v-show="activeTab === 'notify'" class="settings-card" shadow="never">
          <template #header><span class="card-title">通知设置</span></template>
          <div class="notify-item">
            <div class="notify-info">
              <div class="notify-title">邮件通知</div>
              <div class="notify-desc">有新文章发布、用户注册等重要事件时发送邮件</div>
            </div>
            <el-switch v-model="form.emailNotify" />
          </div>
          <el-divider />
          <div class="notify-item">
            <div class="notify-info">
              <div class="notify-title">系统公告</div>
              <div class="notify-desc">显示系统公告与维护通知</div>
            </div>
            <el-switch v-model="form.sysNotify" />
          </div>
          <el-divider />
          <div class="notify-item">
            <div class="notify-info">
              <div class="notify-title">操作日志</div>
              <div class="notify-desc">记录管理员的所有操作行为</div>
            </div>
            <el-switch v-model="form.opLog" />
          </div>
        </el-card>

        <!-- About -->
        <el-card v-show="activeTab === 'about'" class="settings-card" shadow="never">
          <template #header><span class="card-title">关于系统</span></template>
          <div class="about-info">
            <div class="about-logo">SUOJIAN</div>
            <div class="about-version">版本 v1.0.0</div>
            <div class="about-desc">
              基于 Vue 3 + ThinkPHP 3.2 构建的现代化学校管理系统。<br />
              采用前后端分离架构，支持多校区管理、权限控制、数据分析等功能。
            </div>
            <el-divider />
            <div class="tech-stack">
              <el-tag v-for="t in techs" :key="t" style="margin: 4px">{{ t }}</el-tag>
            </div>
            <el-divider />
            <div class="about-links">
              <el-link type="primary">使用文档</el-link>
              <el-link type="primary">技术社区</el-link>
              <el-link type="primary">反馈问题</el-link>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Password Dialog -->
    <el-dialog v-model="showPwdDialog" title="修改密码" width="420px">
      <el-form label-width="100px">
        <el-form-item label="当前密码">
          <el-input type="password" show-password v-model="pwdForm.old" />
        </el-form-item>
        <el-form-item label="新密码">
          <el-input type="password" show-password v-model="pwdForm.new" />
        </el-form-item>
        <el-form-item label="确认密码">
          <el-input type="password" show-password v-model="pwdForm.confirm" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showPwdDialog = false">取消</el-button>
        <el-button type="primary" @click="changePwd">确认修改</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Setting, Lock, Bell, InfoFilled, Plus } from '@element-plus/icons-vue'
import { getSettings, saveSettings, changePassword } from '../../api/settings'

const activeTab = ref('basic')
const showPwdDialog = ref(false)

const form = reactive({
  siteName: 'SUOJIAN 学校管理系统',
  phone: '0571-88888888',
  email: 'admin@suojian.edu',
  address: '浙江省杭州市西湖区文三路123号',
  twoFactor: false,
  emailNotify: true,
  sysNotify: true,
  opLog: false,
})

const pwdForm = reactive({ old: '', new: '', confirm: '' })

const techs = ['Vue 3', 'Vite', 'Element Plus', 'ThinkPHP 3.2', 'MySQL', 'Nginx']

function saveBasic() { ElMessage.success('基本设置已保存') }
function changePwd() {
  if (pwdForm.new !== pwdForm.confirm) {
    ElMessage.error('两次输入的密码不一致')
    return
  }
  ElMessage.success('密码修改成功')
  showPwdDialog.value = false
  pwdForm.old = ''; pwdForm.new = ''; pwdForm.confirm = ''
}
</script>

<style scoped>
.settings-page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.page-header { margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.nav-card, .settings-card { border-radius: 12px; border: 1px solid #ebeef5; }
.settings-form { max-width: 600px; }
.card-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
.security-item, .notify-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
.sec-title, .notify-title { font-size: 15px; color: #303133; font-weight: 500; margin-bottom: 4px; }
.sec-desc, .notify-desc { font-size: 13px; color: #909399; }
.about-info { text-align: center; padding: 20px 0; }
.about-logo { font-size: 36px; font-weight: 800; background: linear-gradient(135deg, #4fc3f7, #7c4dff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 4px; margin-bottom: 8px; }
.about-version { font-size: 13px; color: #909399; margin-bottom: 16px; }
.about-desc { font-size: 14px; color: #606266; line-height: 1.8; }
.tech-stack { display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; }
.about-links { display: flex; justify-content: center; gap: 24px; }
</style>
