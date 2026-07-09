<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import { useAdminAuthStore } from './stores/adminAuth'

const route = useRoute()
const adminAuthStore = useAdminAuthStore()

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

  return 'Foundation'
})

const showAdminStatus = computed(() => adminAuthStore.isAuthenticated)
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <div>
        <p class="eyebrow">Turnero SaaS</p>
        <h1>{{ title }}</h1>
      </div>

      <nav class="nav-links">
        <RouterLink to="/">Foundation</RouterLink>
        <RouterLink to="/admin/login">Admin</RouterLink>
        <RouterLink to="/admin/tenants">Tenants</RouterLink>
        <RouterLink to="/display">Pantalla publica</RouterLink>
      </nav>

      <Tag
        v-if="showAdminStatus"
        severity="contrast"
        :value="`Admin: ${adminAuthStore.displayName}`"
      />
    </header>

    <main class="main-content">
      <RouterView />
    </main>
  </div>
</template>
