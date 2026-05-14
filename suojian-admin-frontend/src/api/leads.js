import api from './request'

// ========== 线索管理 ==========
export function getLeadsList({ page = 1, pageSize = 10, keyword = '', status = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  if (status !== '') params.append('status', status)
  return api.get('/m/Admin/c/Api/a/leadsList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function updateLead(id, data) {
  const params = new URLSearchParams({ id, ...data })
  return api.post('/m/Admin/c/Api/a/leadUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteLead(id) {
  return api.get('/m/Admin/c/Api/a/leadDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}
