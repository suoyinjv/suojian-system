import api from './request'

// ========== 优惠券管理 ==========
export function getCouponsList({ page = 1, pageSize = 10, keyword = '', type = '', status = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  if (type !== '') params.append('type', type)
  if (status !== '') params.append('status', status)
  return api.get('/m/Admin/c/Api/a/couponsList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function createCoupon(data) {
  const params = new URLSearchParams(data)
  return api.post('/m/Admin/c/Api/a/couponCreate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function updateCoupon(id, data) {
  const params = new URLSearchParams({ id, ...data })
  return api.post('/m/Admin/c/Api/a/couponUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteCoupon(id) {
  return api.get('/m/Admin/c/Api/a/couponDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}
