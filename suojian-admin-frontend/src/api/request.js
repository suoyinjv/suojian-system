import axios from 'axios'

const api = axios.create({
  baseURL: '/api.php',
  timeout: 15000,
})

// 请求拦截 — 自动附加 Token
api.interceptors.request.use(config => {
  const token = sessionStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  // 表单数据用 x-www-form-urlencoded，后端 I() 才能取到
  if (config.method === 'post' && !config.headers['Content-Type']?.includes('json')) {
    config.headers['Content-Type'] = 'application/x-www-form-urlencoded'
  }
  return config
})

// 响应拦截 — 统一错误处理
api.interceptors.response.use(
  response => {
    const res = response.data
    if (res.code === undefined) return res // 非标准响应

    if (res.code === 0) return res.data

    // 401 跳转登录
    if (res.code === 401 || res.msg?.includes('未登录')) {
      sessionStorage.removeItem('token')
      window.location.href = '/suojian-admin/login'
      return Promise.reject(new Error(res.msg || '未登录'))
    }

    return Promise.reject(new Error(res.msg || '请求失败'))
  },
  error => {
    if (error.response?.status === 401) {
      sessionStorage.removeItem('token')
      window.location.href = '/suojian-admin/login'
    }
    return Promise.reject(error)
  }
)

export default api