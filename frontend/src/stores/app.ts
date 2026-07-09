import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

export const useAppStore = defineStore('app', () => {
  const tenantSlug = ref<string | null>(null)

  const tenantHeader = computed(() => tenantSlug.value ?? 'public')

  function setTenantSlug(value: string | null) {
    tenantSlug.value = value
  }

  return {
    tenantHeader,
    tenantSlug,
    setTenantSlug,
  }
})
