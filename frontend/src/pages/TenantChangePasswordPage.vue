<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { http } from '../lib/http'
import { useTenantAuthStore } from '../stores/tenantAuth'
import type { ApiEnvelope } from '../types/tenant'

const route = useRoute()
const router = useRouter()
const tenantAuthStore = useTenantAuthStore()

const slug = route.params.slug as string

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const loading = ref(false)
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)

async function submit() {
  loading.value = true
  errorMessage.value = null
  successMessage.value = null

  try {
    await http.post<ApiEnvelope<{ message: string }>>('/api/v1/tenant/change-password', form)

    if (tenantAuthStore.user) {
      tenantAuthStore.user.must_change_password = false
    }

    successMessage.value = 'Contraseña actualizada. Redirigiendo al dashboard...'

    setTimeout(async () => {
      await router.push({ name: 'tenant-dashboard', params: { slug } })
    }, 1500)
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo actualizar la contraseña.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="admin-auth-layout">
    <Card class="auth-card">
      <template #title>Cambiar contraseña</template>
      <template #subtitle>Debes cambiar tu contraseña antes de continuar.</template>
      <template #content>
        <form class="stack-md" @submit.prevent="submit">
          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <Message v-if="successMessage" severity="success" :closable="false">
            {{ successMessage }}
          </Message>

          <div class="field-stack">
            <label for="current_password">Contraseña actual</label>
            <Password id="current_password" v-model="form.current_password" fluid toggle-mask :feedback="false" autocomplete="current-password" />
          </div>

          <div class="field-stack">
            <label for="new_password">Nueva contraseña</label>
            <Password id="new_password" v-model="form.new_password" fluid toggle-mask autocomplete="new-password" />
          </div>

          <div class="field-stack">
            <label for="new_password_confirmation">Confirmar nueva contraseña</label>
            <Password id="new_password_confirmation" v-model="form.new_password_confirmation" fluid toggle-mask :feedback="false" autocomplete="new-password" />
          </div>

          <Button type="submit" label="Actualizar contraseña" icon="pi pi-check" :loading="loading" fluid />
        </form>
      </template>
    </Card>
  </section>
</template>
