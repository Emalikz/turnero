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

const tenantSlug = computed(() => {
  const tenant = route.query.tenant

  return typeof tenant === 'string' && tenant.length > 0 ? tenant : 'public'
})

const channelName = computed(() => `public-display.${tenantSlug.value}`)

function subscribe() {
  echo.channel(channelName.value).listen('.PublicDisplayUpdated', (event: DisplayEvent) => {
    activeTurn.value = event
    status.value = `Ultimo evento recibido en ${event.timestamp}`
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
      <template #title>Pantalla publica</template>
      <template #content>
        <p class="muted">
          Escuchando el canal <strong>{{ channelName }}</strong>. Puedes abrir
          <code>/display?tenant=acme</code> para un tenant concreto.
        </p>

        <p class="muted">{{ status }}</p>

        <div class="display-panels">
          <Panel header="Turno llamando">
            <h2 class="turn-code">{{ activeTurn?.turn_code ?? 'Sin llamados' }}</h2>
            <p class="muted">{{ activeTurn?.desk ?? 'Esperando modulo' }}</p>
            <p>{{ activeTurn?.message ?? 'Todavia no se recibieron eventos.' }}</p>
          </Panel>

          <Panel header="Canal actual">
            <ul class="module-list compact">
              <li><strong>Tenant:</strong> {{ tenantSlug }}</li>
              <li><strong>Canal:</strong> {{ channelName }}</li>
              <li><strong>Estado:</strong> {{ activeTurn ? 'Conectado y recibiendo' : 'Escuchando' }}</li>
            </ul>
          </Panel>
        </div>
      </template>
    </Card>
  </section>
</template>
