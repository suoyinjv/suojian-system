import api from './request'

// ========== 财务管理 ==========
export function getFinanceStats() {
  return api.get('/m/Admin/c/Api/a/financeStats', { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' ? res : {}
    })
}

export function getOrders({ page = 1, pageSize = 10 } = {}) {
  return api.get('/m/Admin/c/Api/a/orders?' + new URLSearchParams({ page, pageSize }).toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}
