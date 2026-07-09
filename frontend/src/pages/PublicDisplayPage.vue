<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

import { echo } from '../lib/echo'

type DisplayEvent = {
  channel: string
  desk: string
  message: string | null
  tenant_slug: string
  timestamp: string
  turn_code: string
}

const route = useRoute()
const activeTurn = ref<DisplayEvent | null>(null)
const status = ref('Esperando eventos de Reverb...')
const connected = ref(false)

const tenantSlug = computed(() => {
  const tenant = route.query.tenant

  return typeof tenant === 'string' && tenant.length > 0 ? tenant : 'public'
})

const channelName = computed(() => `public-display.${tenantSlug.value}`)

function subscribe() {
  echo.channel(channelName.value).listen('.PublicDisplayUpdated', (event: DisplayEvent) => {
    activeTurn.value = event
    connected.value = true
    status.value = `Último evento recibido en ${event.timestamp}`
  })
}

function unsubscribe() {
  echo.leave(channelName.value)
}

onMounted(subscribe)
onBeforeUnmount(unsubscribe)
</script>

<template>
  <section class="display-shell">
    <Card>
      <template #title>Pantalla pública</template>
      <template #content>
        <div class="display-meta">
          <div class="display-meta-item">
            <span class="metric-label">Canal</span>
            <strong>{{ channelName }}</strong>
          </div>
          <div class="display-meta-item">
            <span class="metric-label">Estado</span>
            <span class="display-status" :class="{ 'display-status--active': connected }">
              <i class="pi" :class="connected ? 'pi-circle-fill' : 'pi-circle'"></i>
              {{ connected ? 'Conectado' : 'Escuchando' }}
            </span>
          </div>
          <div class="display-meta-item">
            <span class="metric-label">Último evento</span>
            <strong>{{ status }}</strong>
          </div>
        </div>

        <div class="display-panels">
          <Panel header="Turno llamando">
            <template v-if="activeTurn">
              <h2 class="turn-code">{{ activeTurn.turn_code }}</h2>
              <p class="turn-desk">{{ activeTurn.desk }}</p>
              <p v-if="activeTurn.message" class="turn-message">{{ activeTurn.message }}</p>
            </template>
            <template v-else>
              <div class="display-idle">
                <div class="display-idle-icon">
                  <i class="pi pi-bell"></i>
                </div>
                <h3 class="display-idle-title">Sin llamados activos</h3>
                <p class="display-idle-desc">
                  Cuando se llame a un turno, aparecerá aquí con el código, módulo y mensaje correspondiente.
                </p>
                <p class="display-idle-hint">
                  Abre <code>/display?tenant=slug</code> para ver un tenant específico.
                </p>
              </div>
            </template>
          </Panel>

          <Panel header="Canal actual">
            <ul class="module-list compact">
              <li><strong>Tenant:</strong> {{ tenantSlug }}</li>
              <li><strong>Canal:</strong> {{ channelName }}</li>
              <li><strong>Estado:</strong> {{ connected ? 'Conectado y recibiendo' : 'Escuchando' }}</li>
            </ul>
          </Panel>
        </div>
      </template>
    </Card>
  </section>
</template>
