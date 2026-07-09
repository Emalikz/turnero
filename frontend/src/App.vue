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
    return 'Administración central'
  }

  if (route.name === 'display') {
    return 'Pantalla pública'
  }

  if (route.name === 'tenant-change-password') {
    return 'Actualizar contraseña'
  }

  if (tenantAuthStore.isAuthenticated && tenantAuthStore.tenantName) {
    return tenantAuthStore.tenantName
  }

  return 'Turnero'
})

const contextLine = computed(() => {
  if (route.name === 'admin-login' || route.name === 'admin-tenants') {
    return 'Operación central y alta de tenants'
  }

  if (route.name === 'display') {
    return 'Monitoreo de llamados en tiempo real'
  }

  if (route.name === 'tenant-change-password') {
    return 'Seguridad y acceso del tenant'
  }

  if (route.name === 'tenant-dashboard') {
    return 'Agenda, turnos y operación diaria'
  }

  if (route.name === 'dashboard') {
    return 'Panel de control'
  }

  return ''
})
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <div class="title-block">
        <span class="brand-chip">Turnero</span>
        <h1>{{ title }}</h1>
        <p class="context-line">{{ contextLine }}</p>
      </div>
    </header>

    <main class="main-content">
      <RouterView v-slot="{ Component }">
        <Transition name="page" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>
  </div>
</template>
