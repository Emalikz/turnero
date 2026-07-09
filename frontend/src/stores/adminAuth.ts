import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import type { AdminSession, AdminUser } from '../types/admin'

const STORAGE_KEY = 'turnero.admin-auth'

function loadPersistedAuth(): AdminSession | null {
  const raw = localStorage.getItem(STORAGE_KEY)

  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw) as AdminSession
  } catch {
    localStorage.removeItem(STORAGE_KEY)
    return null
  }
}

export const useAdminAuthStore = defineStore('admin-auth', () => {
  const persisted = loadPersistedAuth()

  const token = ref<string | null>(persisted?.token ?? null)
  const user = ref<AdminUser | null>(persisted?.user ?? null)

  const isAuthenticated = computed(() => Boolean(token.value && user.value?.is_platform_admin))
  const displayName = computed(() => user.value?.name ?? 'Sin sesion')

  function setSession(payload: AdminSession) {
    token.value = payload.token
    user.value = payload.user
    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload))
  }

  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem(STORAGE_KEY)
  }

  return {
    clearSession,
    displayName,
    isAuthenticated,
    setSession,
    token,
    user,
  }
})
