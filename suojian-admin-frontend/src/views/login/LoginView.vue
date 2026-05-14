<template>
  <div class="login-container">
    <div class="login-bg">
      <div class="bg-circle c1"></div>
      <div class="bg-circle c2"></div>
      <div class="bg-circle c3"></div>
    </div>

    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">SUOJIAN</div>
        <p class="login-subtitle">学校管理系统 · 管理后台</p>
      </div>

      <el-form ref="formRef" :model="form" :rules="rules" class="login-form" @keyup.enter="handleLogin">
        <el-form-item prop="username">
          <el-input v-model="form.username" placeholder="请输入用户名" :prefix-icon="User" size="large" />
        </el-form-item>
        <el-form-item prop="password">
          <el-input v-model="form.password" type="password" placeholder="请输入密码" :prefix-icon="Lock" size="large" show-password />
        </el-form-item>
        <div class="form-options">
          <el-checkbox v-model="remember">记住我</el-checkbox>
        </div>
        <el-form-item>
          <el-button type="primary" size="large" class="login-btn" :loading="loading" @click="handleLogin">
            {{ loading ? '登录中...' : '登 录' }}
          </el-button>
        </el-form-item>
      </el-form>

      <div v-if="errorMsg" class="error-tip">
        <el-icon><CircleClose /></el-icon>
        {{ errorMsg }}
      </div>

      <div class="login-footer">
        <span>SUOJIAN © 2026</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { User, Lock, CircleClose } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useAppStore } from '../../stores/app'
import { login } from '../../api'

const router = useRouter()
const appStore = useAppStore()
const formRef = ref(null)
const loading = ref(false)
const remember = ref(false)
const errorMsg = ref('')

const form = reactive({ username: '', password: '' })

const rules = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

async function handleLogin() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  loading.value = true
  errorMsg.value = ''

  try {
    const res = await login(form.username, form.password)
    if (res.code === 0) {
      sessionStorage.setItem('token', res.data.token)
      appStore.setUser(res.data.user)
      ElMessage.success('登录成功')
      router.push('/dashboard')
    } else {
      errorMsg.value = res.msg || '登录失败'
    }
  } catch (e) {
    console.error('[login] error:');
    errorMsg.value = e.msg || '网络错误，请重试'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
  overflow: hidden;
}
.login-bg { position: absolute; width: 100%; height: 100%; }
.bg-circle { position: absolute; border-radius: 50%; opacity: 0.15; filter: blur(60px); }
.c1 { width: 500px; height: 500px; background: #4fc3f7; top: -100px; right: -100px; animation: float 8s ease-in-out infinite; }
.c2 { width: 400px; height: 400px; background: #7c4dff; bottom: -80px; left: -80px; animation: float 10s ease-in-out infinite reverse; }
.c3 { width: 300px; height: 300px; background: #ff6b35; top: 50%; left: 60%; animation: float 12s ease-in-out infinite; }
@keyframes float { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(30px,-30px) scale(1.1); } }
.login-card {
  width: 420px;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  z-index: 1;
  animation: slideUp 0.6s ease-out;
}
@keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
.login-header { text-align: center; margin-bottom: 36px; }
.login-logo { font-size: 36px; font-weight: 800; background: linear-gradient(135deg,#4fc3f7,#7c4dff); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing:4px; margin-bottom:8px; }
.login-subtitle { font-size:14px; color:#909399; }
.login-form { margin-bottom: 10px; }
.login-form :deep(.el-input__wrapper) { background:#f5f7fa; border-radius:10px; padding:4px 16px; box-shadow:none; border:1px solid transparent; transition:all 0.3s; }
.login-form :deep(.el-input__wrapper:hover), .login-form :deep(.el-input__wrapper.is-focus) { border-color:#4fc3f7; background:#fff; box-shadow:0 0 0 3px rgba(79,195,247,0.1); }
.login-form :deep(.el-input__inner) { height:48px; font-size:15px; }
.form-options { display:flex; justify-content:space-between; align-items:center; margin:-8px 0 20px; }
.login-btn { width:100%; height:48px; font-size:16px; border-radius:10px; background:linear-gradient(135deg,#4fc3f7,#2196f3); border:none; letter-spacing:4px; transition:all 0.3s; }
.login-btn:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(33,150,243,0.4); }
.error-tip { display:flex; align-items:center; gap:6px; color:#f56c6c; font-size:13px; margin-bottom:10px; justify-content:center; }
.login-footer { text-align:center; color:#c0c4cc; font-size:12px; margin-top:20px; }
</style>
