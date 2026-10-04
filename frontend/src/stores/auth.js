import { defineStore } from 'pinia'
import api, { ensureCsrfCookie } from '../api/client'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    checked: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async login(email, password) {
      await ensureCsrfCookie()
      await api.post('/admin/login', { email, password })
      await this.fetchUser()
    },
    async logout() {
      await api.post('/admin/logout')
      this.user = null
    },
    async fetchUser() {
      try {
        const { data } = await api.get('/admin/me')
        this.user = data.user
      } catch {
        this.user = null
      } finally {
        this.checked = true
      }
    },
  },
})
