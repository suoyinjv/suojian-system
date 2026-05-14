import api from './request'

export function getSettings() {
  return api.get('/m/Admin/c/Api/a/settings?act=get', { baseURL: 'http://47.114.125.123' })
}

export function saveSettings(data) {
  const params = new URLSearchParams({ act: 'save', ...data })
  return api.post('/m/Admin/c/Api/a/settings', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}

export function changePassword(oldPwd, newPwd) {
  const params = new URLSearchParams({ act: 'changePwd', old_pwd: oldPwd, new_pwd: newPwd })
  return api.post('/m/Admin/c/Api/a/settings', params.toString(), {
    baseURL: 'http://47.114.125.123',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
}
