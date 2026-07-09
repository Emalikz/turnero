import axios from 'axios'

import { useAdminAuthStore } from '../stores/adminAuth'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000',
  headers: {
    Accept: 'application/json',
  },
})

http.interceptors.request.use((config) => {
  const adminAuthStore = useAdminAuthStore()

  if (adminAuthStore.token) {
    config.headers.Authorization = `Bearer ${adminAuthStore.token}`
  }

  return config
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    const adminAuthStore = useAdminAuthStore()

    if (error.response?.status === 401) {
      adminAuthStore.clearSession()
    }

    return Promise.reject(error)
  },
)
