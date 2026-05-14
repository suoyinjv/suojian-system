import api from './request'

// ========== 套餐管理 ==========
export function getPackagesList({ page = 1, pageSize = 10, keyword = '', type = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  if (type !== '') params.append('type', type)
  return api.get('/m/Admin/c/Api/a/packagesList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function createPackage(data) {
  const params = new URLSearchParams(data)
  return api.post('/m/Admin/c/Api/a/packageCreate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function updatePackage(id, data) {
  const params = new URLSearchParams({ id, ...data })
  return api.post('/m/Admin/c/Api/a/packageUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deletePackage(id) {
  return api.get('/m/Admin/c/Api/a/packageDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}
