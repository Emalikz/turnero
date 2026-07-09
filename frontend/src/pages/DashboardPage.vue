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
const broadcastMessage = ref<string | null>(null)

async function loadHealth() {
  loading.value = true
  error.value = null

  try {
    const response = await http.get<HealthPayload>('/api/v1/health')

    health.value = response.data.data
    timestamp.value = response.data.meta.timestamp
  } catch {
    error.value = 'No se pudo consultar el backend. Revisa que Laravel este corriendo en el puerto 8000.'
  } finally {
    loading.value = false
  }
}

onMounted(loadHealth)

async function triggerDisplayCall() {
  broadcasting.value = true
  broadcastMessage.value = null

  try {
    const response = await http.post('/api/v1/display/demo-call', {
      turn_code: 'A-101',
      desk: 'Modulo 4',
      message: 'Paciente pasar a ventanilla.',
    })

    broadcastMessage.value = `Evento enviado al canal ${response.data.data.channel}`
  } catch {
    broadcastMessage.value = 'No se pudo emitir el evento demo.'
  } finally {
    broadcasting.value = false
  }
}
</script>

<template>
  <section class="dashboard-grid">
    <Card>
      <template #title>Foundation listo</template>
      <template #content>
        <p class="muted">
          Base SaaS con Laravel 12, Vue 3, PrimeVue, Redis, Reverb y tenancy por schema.
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
            <span class="metric-label">Aplicacion</span>
            <strong>{{ health?.app }}</strong>
          </div>
          <div class="metric-row">
            <span class="metric-label">Tenant actual</span>
            <strong>{{ health?.tenant?.name ?? 'Sin tenant' }}</strong>
          </div>
          <div class="metric-row">
            <span class="metric-label">Ultimo ping</span>
            <strong>{{ timestamp }}</strong>
          </div>
          <Button label="Refrescar" icon="pi pi-refresh" outlined @click="loadHealth" />
        </div>
      </template>
    </Card>

    <Card>
      <template #title>Proximos modulos</template>
      <template #content>
        <ul class="module-list">
          <li>Autenticacion con Sanctum y SSO</li>
          <li>Gestor de colas y turnos</li>
          <li>Page builder de pantalla publica</li>
          <li>Dashboard operativo multi-sucursal</li>
        </ul>

        <div class="stack-sm action-block">
          <Button
            label="Emitir llamado demo"
            icon="pi pi-megaphone"
            :loading="broadcasting"
            @click="triggerDisplayCall"
          />

          <small v-if="broadcastMessage" class="muted">{{ broadcastMessage }}</small>
        </div>
      </template>
    </Card>
  </section>
</template>
