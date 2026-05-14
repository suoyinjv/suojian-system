import api from './request'

// ========== 文章 ==========
export function getArticles({ page = 1, pageSize = 15, status = '', categoryId = '', keyword = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (status) params.append('status', status)
  if (categoryId) params.append('categoryId', categoryId)
  if (keyword) params.append('keyword', keyword)
  return api.get('/m/Admin/c/Api/a/articles?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      // 后端返回 {list:[], total:N} 直接用
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function createArticle({ title, categoryId, content, image }) {
  const params = new URLSearchParams({ title, categoryId, content: content || '', image: image || '' })
  return api.post('/m/Admin/c/Api/a/articleCreate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function updateArticle(id, { title, categoryId, content, image }) {
  const params = new URLSearchParams({ id, title, content: content || '', image: image || '' })
  if (categoryId) params.append('categoryId', categoryId)
  return api.post('/m/Admin/c/Api/a/articleUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteArticle(id) {
  return api.get('/m/Admin/c/Api/a/articleDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}

export function toggleArticleStatus(id, enable) {
  return api.get(`/m/Admin/c/Api/a/articleToggle?id=${id}&is_enable=${enable ? 1 : 0}`, {
    baseURL: 'http://47.114.125.123',
  })
}

// ========== 分类 ==========
export function getCategories() {
  return api.get('/m/Admin/c/Api/a/categories', { baseURL: 'http://47.114.125.123' })
}

export function createCategory(name) {
  const params = new URLSearchParams({ name })
  return api.post('/m/Admin/c/Api/a/categoryCreate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function updateCategory(id, name) {
  const params = new URLSearchParams({ id, name })
  return api.post('/m/Admin/c/Api/a/categoryUpdate', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteCategory(id) {
  return api.get('/m/Admin/c/Api/a/categoryDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}

// ========== 评论 ==========
export function getComments({ page = 1, pageSize = 10, status = '' } = {}) {
  const params = new URLSearchParams({ page, pageSize })
  if (status !== '') params.append('is_approved', status === 'approved' ? 1 : 0)
  return api.get('/m/Admin/c/Api/a/comments?' + params.toString(), { baseURL: 'http://47.114.125.123' })
    .then(res => {
      if (Array.isArray(res)) return { list: res, total: res.length }
      return typeof res === 'object' && 'total' in res ? res : { list: [], total: 0 }
    })
}

export function auditComment(id, action) {
  const params = new URLSearchParams({ id, action })
  return api.post('/m/Admin/c/Api/a/commentAudit', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function deleteComment(id) {
  return api.get('/m/Admin/c/Api/a/commentDelete?id=' + id, { baseURL: 'http://47.114.125.123' })
}