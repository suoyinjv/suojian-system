import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const sidebarCollapsed = ref(false)
  const user = ref(null)

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  function setUser(u) {
    user.value = u
  }

  function logout() {
    sessionStorage.removeItem('token')
    user.value = null
  }

  return { sidebarCollapsed, user, toggleSidebar, setUser, logout }
})
