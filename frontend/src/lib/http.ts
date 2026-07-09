import axios from 'axios'
import { useRoute } from 'vue-router'

import { useAdminAuthStore } from '../stores/adminAuth'
import { useTenantAuthStore } from '../stores/tenantAuth'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000',
  headers: {
    Accept: 'application/json',
  },
})

http.interceptors.request.use((config) => {
  const adminAuthStore = useAdminAuthStore()
  const tenantAuthStore = useTenantAuthStore()

  if (adminAuthStore.token) {
    config.headers.Authorization = `Bearer ${adminAuthStore.token}`
  } else if (tenantAuthStore.token) {
    config.headers.Authorization = `Bearer ${tenantAuthStore.token}`
  }

  const route = useRoute()
  const slug = route?.params?.slug as string | undefined

  if (slug) {
    config.headers['X-Tenant'] = slug
  } else if (tenantAuthStore.tenantSlug) {
    config.headers['X-Tenant'] = tenantAuthStore.tenantSlug
  }

  return config
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    const adminAuthStore = useAdminAuthStore()
    const tenantAuthStore = useTenantAuthStore()

    if (error.response?.status === 401) {
      if (tenantAuthStore.isAuthenticated) {
        tenantAuthStore.clearSession()
      } else if (adminAuthStore.isAuthenticated) {
        adminAuthStore.clearSession()
      }
    }

    return Promise.reject(error)
  },
)
