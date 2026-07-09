import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import type { TenantSession, TenantUser } from '../types/tenant'

const STORAGE_KEY = 'turnero.tenant-auth'

function loadPersistedAuth(): TenantSession | null {
  const raw = localStorage.getItem(STORAGE_KEY)

  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw) as TenantSession
  } catch {
    localStorage.removeItem(STORAGE_KEY)
    return null
  }
}

export const useTenantAuthStore = defineStore('tenant-auth', () => {
  const persisted = loadPersistedAuth()

  const token = ref<string | null>(persisted?.token ?? null)
  const user = ref<TenantUser | null>(persisted?.user ?? null)
  const tenantSlug = ref<string | null>(persisted?.tenant?.slug ?? null)
  const tenantName = ref<string | null>(persisted?.tenant?.name ?? null)

  const isAuthenticated = computed(() => Boolean(token.value && user.value))
  const mustChangePassword = computed(() => user.value?.must_change_password ?? false)
  const displayName = computed(() => user.value?.name ?? 'Sin sesión')

  function setSession(payload: TenantSession) {
    token.value = payload.token
    user.value = payload.user
    tenantSlug.value = payload.tenant.slug
    tenantName.value = payload.tenant.name
    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload))
  }

  function clearSession() {
    token.value = null
    user.value = null
    tenantSlug.value = null
    tenantName.value = null
    localStorage.removeItem(STORAGE_KEY)
  }

  return {
    clearSession,
    displayName,
    isAuthenticated,
    mustChangePassword,
    setSession,
    tenantName,
    tenantSlug,
    token,
    user,
  }
})
