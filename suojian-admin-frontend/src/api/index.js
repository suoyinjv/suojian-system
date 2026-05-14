import api from './request'
import axios from 'axios'
export { default as request } from './request'
export { getArticles, createArticle, updateArticle, deleteArticle, toggleArticleStatus, getCategories } from './article'
export { getDashboardStats, getRecentArticles } from './dashboard'
export { getComments, approveComment, rejectComment, deleteComment } from './comment'
export { getUserList, updateUser, deleteUser } from './users'
export { getCouponsList, createCoupon, updateCoupon, deleteCoupon } from './coupons'
export { getPackagesList, createPackage, updatePackage, deletePackage } from './packages'
export { getLeadsList, updateLead, deleteLead } from './leads'
export { getHomeworkList, createHomework, updateHomework, deleteHomework } from './homeworks'
export { getEnrollmentList, updateEnrollment } from './enrollments'
export { getMarketingList, createMarketing } from './marketings'
export { getFeedbackList, updateFeedback } from './feedbacks'
export { getFinanceStats, getOrders } from './finance'
export { getStatsOverview, getStatsRevenue, getStatsStudent } from './stats'
export { getBubbleList, deleteBubble } from './bubbles'

const BASE = 'http://47.114.125.123'

// 登录 — 直接用原生 axios，绕过 request.js 的响应拦截
export async function login(username, password) {
  const params = new URLSearchParams({ username, password })
  const resp = await axios.post(BASE + '/m/Admin/c/Api/a/login', params.toString(), {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    timeout: 15000,
  })
  const res = resp.data
  if (res.code === 0) {
    sessionStorage.setItem('token', res.data.token)
    return res
  }
  throw new Error(res.msg || '登录失败')
}
