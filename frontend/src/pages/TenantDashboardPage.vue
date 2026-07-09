<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'

import { useTenantAuthStore } from '../stores/tenantAuth'

const router = useRouter()
const tenantAuthStore = useTenantAuthStore()

const slug = computed(() => tenantAuthStore.tenantSlug)

function logout() {
  tenantAuthStore.clearSession()
  router.push({ name: 'tenant-login', params: { slug: slug.value } })
}
</script>

<template>
  <section class="admin-grid">
    <Card>
      <template #title>Bienvenido a tu agenda</template>
      <template #content>
        <div class="stack-md">
          <Message severity="info" :closable="false">
            Esta es tu area de trabajo. Aqui gestiras tus turnos y agenda.
          </Message>

          <div class="metric-row">
            <span class="metric-label">Tenant</span>
            <strong>{{ tenantAuthStore.tenantName }}</strong>
          </div>

          <div class="metric-row">
            <span class="metric-label">Usuario</span>
            <strong>{{ tenantAuthStore.displayName }}</strong>
          </div>

          <div class="metric-row">
            <span class="metric-label">Email</span>
            <strong>{{ tenantAuthStore.user?.email }}</strong>
          </div>

          <div class="metric-row">
            <span class="metric-label">Rol</span>
            <strong>{{ tenantAuthStore.user?.role }}</strong>
          </div>

          <Button label="Cerrar sesion" icon="pi pi-sign-out" severity="secondary" outlined @click="logout" />
        </div>
      </template>
    </Card>
  </section>
</template>
