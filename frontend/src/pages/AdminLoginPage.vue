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
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo iniciar sesión.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="login-layout">
    <div class="login-brand">
      <div class="login-brand-inner">
        <span class="brand-chip">Turnero</span>
        <h1 class="login-headline">Gestión de turnos para tu negocio</h1>
        <p class="login-subheadline">
          Administra sucursales, filas y llamados desde un solo panel.
          Los clientes llegan, toman número y son llamados en orden.
        </p>

        <ul class="trust-list">
          <li>
            <i class="pi pi-shield"></i>
            <span>Cifrado en tránsito y en reposo</span>
          </li>
          <li>
            <i class="pi pi-verified"></i>
            <span>Aislamiento de datos por tenant</span>
          </li>
          <li>
            <i class="pi pi-clock"></i>
            <span>Actualizaciones sin tiempo de inactividad</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="login-form-side">
      <Card class="auth-card">
        <template #title>
          <div class="auth-card-header">
            <h2>Acceso administrativo</h2>
            <p class="muted">Panel central para alta y configuración de tenants</p>
          </div>
        </template>
        <template #content>
          <form class="stack-md" @submit.prevent="submit">
            <Message v-if="errorMessage" severity="error" :closable="false">
              {{ errorMessage }}
            </Message>

            <div class="field-stack">
              <label for="email">Email</label>
              <InputText id="email" v-model="form.email" type="email" fluid autocomplete="username" placeholder="admin@turnero.app" />
            </div>

            <div class="field-stack">
              <label for="password">Contraseña</label>
              <Password id="password" v-model="form.password" fluid toggle-mask :feedback="false" autocomplete="current-password" placeholder="••••••••" />
            </div>

            <Button type="submit" label="Entrar al panel" icon="pi pi-sign-in" :loading="loading" fluid />
          </form>
        </template>
      </Card>

      <p class="login-footer muted">
        ¿Necesitas acceso? Contacta al administrador del sistema.
      </p>
    </div>
  </section>
</template>
