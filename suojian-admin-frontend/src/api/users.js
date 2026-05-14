import api from './request'

// ========== 用户管理 ==========
export function getUserList({ page = 1, pageSize = 10, keyword = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  return api.get('/m/Admin/c/Api/a/users?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function updateUser(id, data) {
  const params = new URLSearchParams({ id, ...data })
  return api.post('/m/Admin/c/Api/a/userUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteUser(id) {
  return api.get('/m/Admin/c/Api/a/userDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}
