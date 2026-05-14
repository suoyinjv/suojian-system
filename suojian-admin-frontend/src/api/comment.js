import http from '../utils/http'

const BASE_URL = 'http://47.114.125.123'

/**
 * 后端字段 → 前端字段映射
 * is_approved(1/0) → status('approved'/'pending')
 * add_time        → date
 * user_name       → user
 * article_title   → article
 */
function mapComment(item) {
  let status = 'pending'
  if (item.is_approved === 1) status = 'approved'
  // is_approved === 0 保持 pending（待审核）
  return {
    id: item.id,
    article_id: item.article_id,
    article: item.article_title,
    user: item.user_name,
    content: item.content,
    date: item.add_time,
    status,
  }
}

/**
 * 通用 GET 请求封装（基于 baseURL 构建）
 */
function apiGet(action, extraParams = {}) {
  return http.get(BASE_URL + '/admin.php', {
    params: {
      m: 'Admin',
      c: 'Api',
      a: action,
      ...extraParams,
    },
    timeout: 15000,
  }).then(res => res.data)
}

/**
 * 获取评论列表
 * GET /admin.php?m=Admin&c=Api&a=comments
 */
export function getComments(params = {}) {
  return apiGet('comments', params).then(res => {
    // 兼容后端返回 { list: [...], total: N }  或直接数组
    const list = Array.isArray(res) ? res : (res.list || res.data || [])
    const total = res.total != null ? res.total : list.length
    return {
      list: list.map(mapComment),
      total,
    }
  })
}

/**
 * 审核通过评论
 * GET /admin.php?m=Admin&c=Api&a=commentAudit&id=X&is_approved=1
 */
export function approveComment(id) {
  return apiGet('commentAudit', { id, is_approved: 1 })
}

/**
 * 驳回评论（设置为未审核）
 * GET /admin.php?m=Admin&c=Api&a=commentAudit&id=X&is_approved=0
 */
export function rejectComment(id) {
  return apiGet('commentAudit', { id, is_approved: 0 })
}

/**
 * 删除评论
 * GET /admin.php?m=Admin&c=Api&a=commentDelete&id=X
 */
export function deleteComment(id) {
  return apiGet('commentDelete', { id })
}
