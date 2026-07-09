<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import { useTenantAuthStore } from './stores/tenantAuth'

const route = useRoute()
const tenantAuthStore = useTenantAuthStore()

const title = computed(() => {
  if (route.name === 'admin-login') {
    return 'Acceso administrativo'
  }

  if (route.name === 'admin-tenants') {
    return 'Administracion central'
  }

  if (route.name === 'display') {
    return 'Pantalla publica'
  }

  if (tenantAuthStore.isAuthenticated && tenantAuthStore.tenantName) {
    return tenantAuthStore.tenantName
  }

  return 'Foundation'
})
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <div>
        <p class="eyebrow">Turnero SaaS</p>
        <h1>{{ title }}</h1>
      </div>
    </header>

    <main class="main-content">
      <RouterView />
    </main>
  </div>
</template>
