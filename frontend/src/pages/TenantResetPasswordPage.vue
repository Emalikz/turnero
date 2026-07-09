<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { http } from '../lib/http'
import type { ApiEnvelope } from '../types/tenant'

const route = useRoute()
const router = useRouter()

const slug = route.params.slug as string
const token = route.query.token as string

const form = reactive({
  token: token ?? '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)

async function submit() {
  loading.value = true
  errorMessage.value = null
  successMessage.value = null

  try {
    await http.post<ApiEnvelope<{ message: string }>>('/api/v1/auth/tenant/reset-password', form, {
      headers: { 'X-Tenant': slug },
    })

    successMessage.value = 'Contrasena restablecida. Redirigiendo al login...'

    setTimeout(async () => {
      await router.push({ name: 'tenant-login', params: { slug } })
    }, 2000)
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo restablecer la contrasena.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="admin-auth-layout">
    <Card class="auth-card">
      <template #title>Restablecer contrasena</template>
      <template #subtitle>Crea una nueva contrasena para tu cuenta.</template>
      <template #content>
        <form class="stack-md" @submit.prevent="submit">
          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <Message v-if="successMessage" severity="success" :closable="false">
            {{ successMessage }}
          </Message>

          <div v-if="!token" class="field-stack">
            <label for="token">Token de recuperacion</label>
            <InputText id="token" v-model="form.token" fluid />
          </div>

          <div class="field-stack">
            <label for="password">Nueva contrasena</label>
            <Password id="password" v-model="form.password" fluid toggle-mask autocomplete="new-password" />
          </div>

          <div class="field-stack">
            <label for="password_confirmation">Confirmar contrasena</label>
            <Password id="password_confirmation" v-model="form.password_confirmation" fluid toggle-mask :feedback="false" autocomplete="new-password" />
          </div>

          <Button type="submit" label="Restablecer contrasena" icon="pi pi-check" :loading="loading" fluid />
        </form>
      </template>
    </Card>
  </section>
</template>
