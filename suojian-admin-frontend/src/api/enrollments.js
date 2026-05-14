import api from './request'

// ========== 招生管理 ==========
export function getEnrollmentList({ page = 1, pageSize = 10, keyword = '', status = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  if (status !== '') params.append('status', status)
  return api.get('/m/Admin/c/Api/a/enrollmentList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function updateEnrollment(id, data) {
  const params = new URLSearchParams({ id, ...data })
  return api.post('/m/Admin/c/Api/a/enrollmentUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}
