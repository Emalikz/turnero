<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

import { http } from '../lib/http'
import { useAdminAuthStore } from '../stores/adminAuth'
import type { AdminSession, ApiEnvelope } from '../types/admin'

const router = useRouter()
const adminAuthStore = useAdminAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const errorMessage = ref<string | null>(null)

async function submit() {
  loading.value = true
  errorMessage.value = null

  try {
    const response = await http.post<ApiEnvelope<AdminSession>>('/api/v1/admin/auth/login', form)

    adminAuthStore.setSession(response.data.data)

    await router.push({ name: 'admin-tenants' })
  } catch (error: any) {
    console.error('Error during admin login:', error)
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo iniciar sesion.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="admin-auth-layout">
    <Card class="auth-card">
      <template #title>Acceso administrativo</template>
      <template #subtitle>Panel central SaaS para alta de tenants y futuras configuraciones globales.</template>
      <template #content>
        <form class="stack-md" @submit.prevent="submit">
          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <Message severity="secondary" :closable="false">
            Este acceso es solo para administracion central del SaaS. Los tenants tendran su propio login mas adelante.
          </Message>

          <div class="field-stack">
            <label for="email">Email</label>
            <InputText id="email" v-model="form.email" type="email" fluid autocomplete="username" />
          </div>

          <div class="field-stack">
            <label for="password">Contrasena</label>
            <Password id="password" v-model="form.password" fluid toggle-mask :feedback="false" autocomplete="current-password" />
          </div>

          <Button type="submit" label="Entrar al panel" icon="pi pi-sign-in" :loading="loading" fluid />
        </form>
      </template>
    </Card>
  </section>
</template>
