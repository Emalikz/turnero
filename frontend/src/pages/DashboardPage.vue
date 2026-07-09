<script setup lang="ts">
import { onMounted, ref } from 'vue'

import { http } from '../lib/http'

type HealthPayload = {
  data: {
    app: string
    status: string
    tenant: null | {
      id: number
      name: string
      schema: string
      slug: string
    }
  }
  meta: {
    timestamp: string
  }
}

const health = ref<HealthPayload['data'] | null>(null)
const timestamp = ref<string | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const broadcasting = ref(false)
const broadcastResult = ref<{ severity: 'success' | 'error'; text: string } | null>(null)

async function loadHealth() {
  loading.value = true
  error.value = null

  try {
    const response = await http.get<HealthPayload>('/api/v1/health')

    health.value = response.data.data
    timestamp.value = response.data.meta.timestamp
  } catch {
    error.value = 'No se pudo consultar el backend. Revisa que Laravel esté corriendo en el puerto 8000.'
  } finally {
    loading.value = false
  }
}

onMounted(loadHealth)

async function triggerDisplayCall() {
  broadcasting.value = true
  broadcastResult.value = null

  try {
    const response = await http.post('/api/v1/display/demo-call', {
      turn_code: 'A-101',
      desk: 'Módulo 4',
      message: 'Paciente pasar a ventanilla.',
    })

    broadcastResult.value = {
      severity: 'success',
      text: `Evento enviado al canal ${response.data.data.channel}`,
    }
  } catch {
    broadcastResult.value = {
      severity: 'error',
      text: 'No se pudo emitir el evento demo.',
    }
  } finally {
    broadcasting.value = false
  }
}
</script>

<template>
  <section class="dashboard-grid">
    <Card>
      <template #title>Bienvenido a Turnero</template>
      <template #content>
        <p class="muted">
          Gestión de colas y turnos para sucursales. Configura tus módulos, genera turnos y
          proyecta llamados en pantalla pública.
        </p>
      </template>
    </Card>

    <Card>
      <template #title>Estado de la API</template>
      <template #content>
        <div v-if="loading" class="stack-sm">
          <Skeleton height="1.5rem" />
          <Skeleton height="4rem" />
        </div>

        <Message v-else-if="error" severity="error" :closable="false">
          {{ error }}
        </Message>

        <div v-else class="stack-sm">
          <Tag severity="success" :value="health?.status.toUpperCase()" />
          <div class="metric-row">
            <span class="metric-label">Aplicación</span>
            <strong>{{ health?.app }}</strong>
          </div>
          <div class="metric-row">
            <span class="metric-label">Tenant actual</span>
            <strong>{{ health?.tenant?.name ?? 'Sin tenant' }}</strong>
          </div>
          <div class="metric-row">
            <span class="metric-label">Último ping</span>
            <strong>{{ timestamp }}</strong>
          </div>
          <Button label="Refrescar" icon="pi pi-refresh" outlined @click="loadHealth" />
        </div>
      </template>
    </Card>

    <Card>
      <template #title>Próximos módulos</template>
      <template #content>
        <ul class="module-list">
          <li>Autenticación con Sanctum y SSO</li>
          <li>Gestor de colas y turnos</li>
          <li>Page builder de pantalla pública</li>
          <li>Dashboard operativo multi-sucursal</li>
        </ul>

        <div class="stack-sm action-block">
          <Button
            label="Emitir llamado demo"
            icon="pi pi-megaphone"
            :loading="broadcasting"
            @click="triggerDisplayCall"
          />

          <Message
            v-if="broadcastResult"
            :severity="broadcastResult.severity"
            :closable="false"
          >
            {{ broadcastResult.text }}
          </Message>
        </div>
      </template>
    </Card>
  </section>
</template>
