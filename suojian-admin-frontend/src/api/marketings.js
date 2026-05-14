import api from './request'

// ========== 营销活动 ==========
export function getMarketingList({ page = 1, pageSize = 10, keyword = '', type = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  if (type !== '') params.append('type', type)
  return api.get('/m/Admin/c/Api/a/marketingList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function createMarketing(data) {
  const params = new URLSearchParams(data)
  return api.post('/m/Admin/c/Api/a/marketingCreate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}
