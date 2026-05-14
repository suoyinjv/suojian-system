<template>
  <div class="not-found">
    <div class="not-found-content">
      <div class="error-code">404</div>
      <div class="error-title">页面不存在</div>
      <div class="error-desc">抱歉，您访问的页面不存在或已被移除</div>
      <el-button type="primary" @click="$router.push('/dashboard')">返回首页</el-button>
    </div>
  </div>
</template>

<style scoped>
.not-found {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
}
.not-found-content {
  text-align: center;
  color: #fff;
}
.error-code {
  font-size: 140px;
  font-weight: 800;
  background: linear-gradient(135deg, #4fc3f7, #7c4dff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  line-height: 1;
  margin-bottom: 16px;
}
.error-title {
  font-size: 28px;
  font-weight: 600;
  margin-bottom: 12px;
}
.error-desc {
  font-size: 16px;
  color: rgba(255,255,255,0.6);
  margin-bottom: 32px;
}
</style>
