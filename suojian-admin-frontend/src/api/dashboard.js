import api from './request'

const BASE = 'http://47.114.125.123'
const MOCK = false

export async function getDashboardStats() {
  if (MOCK) {
    await new Promise(r => setTimeout(r, 300))
    return {
      todayViews: 1284, totalUsers: 3621, totalArticles: 256, pendingComments: 12,
      viewTrend: 12, userTrend: 8, articleTrend: -3, commentTrend: 5,
      visitData: [45,62,38,78,55,88,72,60,90,42,68,50],
      visitLabels: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
    }
  }
  const res = await api.get('/m/Admin/c/Api/a/dashboardStats', { baseURL: BASE })
  // request.js 拦截器已解包 res.data，直接返回
  return res
}

export async function getRecentArticles() {
  if (MOCK) {
    await new Promise(r => setTimeout(r, 250))
    return [
      { id: 1, title: '学校2026年春季招生简章', category: '招生信息', views: 286, status: 'published', date: '2026-05-12' },
      { id: 2, title: '关于加强校园安全管理的通知', category: '通知公告', views: 234, status: 'published', date: '2026-05-10' },
      { id: 3, title: '优秀教师评选结果公示', category: '校园动态', views: 189, status: 'published', date: '2026-05-08' },
      { id: 4, title: '2026年高考志愿填报指南', category: '教学资源', views: 156, status: 'draft', date: '2026-05-06' },
      { id: 5, title: '校园开放日活动安排', category: '通知公告', views: 98, status: 'published', date: '2026-05-04' },
    ]
  }
  const res = await api.get('/m/Admin/c/Api/a/recentArticles', { baseURL: BASE })
  // request.js 拦截器已解包 res.data，直接返回
  return res
}
