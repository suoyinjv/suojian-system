import api from './request'

// ========== 数据统计 ==========
export function getStatsOverview() {
  return api.get('/m/Admin/c/Api/a/statsOverview', { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' ? res : {}
    })
}

export function getStatsRevenue({ range = 'month' } = {}) {
  return api.get('/m/Admin/c/Api/a/statsRevenue?range=' + range, { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return Array.isArray(res) ? res : []
    })
}

export function getStatsStudent({ range = 'month' } = {}) {
  return api.get('/m/Admin/c/Api/a/statsStudent?range=' + range, { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return Array.isArray(res) ? res : []
    })
}
