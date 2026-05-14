import api from './request'

// ========== 冒泡广场 ==========
export function getBubbleList({ page = 1, pageSize = 10, keyword = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  return api.get('/m/Admin/c/Api/a/bubbleList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function deleteBubble(id) {
  return api.get('/m/Admin/c/Api/a/bubbleDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}
