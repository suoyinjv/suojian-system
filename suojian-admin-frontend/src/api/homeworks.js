import api from './request'

// ========== 作业管理 ==========
export function getHomeworkList({ page = 1, pageSize = 10, keyword = '', course_id = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (keyword) params.append('keyword', keyword)
  if (course_id !== '') params.append('course_id', course_id)
  return api.get('/m/Admin/c/Api/a/homeworkList?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function createHomework(data) {
  const params = new URLSearchParams(data)
  return api.post('/m/Admin/c/Api/a/homeworkCreate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function updateHomework(id, data) {
  const params = new URLSearchParams({ id, ...data })
  return api.post('/m/Admin/c/Api/a/homeworkUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteHomework(id) {
  return api.get('/m/Admin/c/Api/a/homeworkDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}
