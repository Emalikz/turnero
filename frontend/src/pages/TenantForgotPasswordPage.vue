<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute } from 'vue-router'

import { http } from '../lib/http'
import type { ApiEnvelope } from '../types/tenant'

const route = useRoute()

const slug = route.params.slug as string

const form = reactive({
  email: '',
})

const loading = ref(false)
const successMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)

async function submit() {
  loading.value = true
  errorMessage.value = null
  successMessage.value = null

  try {
    await http.post<ApiEnvelope<{ message: string }>>('/api/v1/auth/tenant/forgot-password', form, {
      headers: { 'X-Tenant': slug },
    })

    successMessage.value = 'Si el email existe, recibiras un enlace para restablecer tu password.'
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo procesar la solicitud.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="admin-auth-layout">
    <Card class="auth-card">
      <template #title>Recuperar contrasena</template>
      <template #subtitle>Ingresa tu email y te enviaremos un enlace para restablecer tu contrasena.</template>
      <template #content>
        <form class="stack-md" @submit.prevent="submit">
          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <Message v-if="successMessage" severity="success" :closable="false">
            {{ successMessage }}
          </Message>

          <div class="field-stack">
            <label for="email">Email</label>
            <InputText id="email" v-model="form.email" type="email" fluid autocomplete="email" />
          </div>

          <Button type="submit" label="Enviar enlace" icon="pi pi-envelope" :loading="loading" fluid />

          <div class="flex justify-content-center">
            <RouterLink :to="{ name: 'tenant-login', params: { slug } }" class="text-sm text-primary">
              Volver al inicio de sesion
            </RouterLink>
          </div>
        </form>
      </template>
    </Card>
  </section>
</template>
