<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { http } from '../lib/http'
import { useTenantAuthStore } from '../stores/tenantAuth'
import type { ApiEnvelope, TenantSession } from '../types/tenant'

const route = useRoute()
const router = useRouter()
const tenantAuthStore = useTenantAuthStore()

const slug = route.params.slug as string

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const errorMessage = ref<string | null>(null)
const slugValid = ref<boolean | null>(null)

onMounted(async () => {
  try {
    await http.get('/api/v1/health', {
      headers: { 'X-Tenant': slug },
    })
    slugValid.value = true
  } catch {
    slugValid.value = false
    errorMessage.value = 'El enlace no es válido o el tenant no existe.'
  }
})

async function submit() {
  loading.value = true
  errorMessage.value = null

  try {
    const response = await http.post<ApiEnvelope<TenantSession>>('/api/v1/auth/tenant/login', form, {
      headers: { 'X-Tenant': slug },
    })

    const session = response.data.data
    tenantAuthStore.setSession(session)

    if (session.user.must_change_password) {
      await router.push({ name: 'tenant-change-password', params: { slug } })
    } else {
      await router.push({ name: 'tenant-dashboard', params: { slug } })
    }
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo iniciar sesión.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="login-layout login-layout--tenant">
    <div class="login-brand login-brand--tenant">
      <div class="login-brand-inner">
        <span class="brand-chip">Turnero</span>
        <h1 class="login-headline">Tu agenda, en orden</h1>
        <p class="login-subheadline">
          Gestiona turnos, llama a clientes y mantén tu fila organizada.
          Todo desde un solo lugar, sin complicaciones.
        </p>

        <ul class="trust-list">
          <li>
            <i class="pi pi-shield"></i>
            <span>Tus datos están protegidos y aislados</span>
          </li>
          <li>
            <i class="pi pi-lock"></i>
            <span>Conexión segura con cifrado SSL</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="login-form-side">
      <Card class="auth-card">
        <template #title>
          <div class="auth-card-header">
            <div class="tenant-badge">
              <i class="pi pi-building"></i>
              <span>{{ slug }}</span>
            </div>
            <h2>Acceso al tenant</h2>
            <p class="muted">Inicia sesión para gestionar tu agenda</p>
          </div>
        </template>
        <template #content>
          <form class="stack-md" @submit.prevent="submit">
            <Message v-if="errorMessage" severity="error" :closable="false">
              {{ errorMessage }}
            </Message>

            <template v-if="slugValid !== false">
              <div class="field-stack">
                <label for="email">Email</label>
                <InputText id="email" v-model="form.email" type="email" fluid autocomplete="username" placeholder="usuario@empresa.com" />
              </div>

              <div class="field-stack">
                <label for="password">Contraseña</label>
                <Password id="password" v-model="form.password" fluid toggle-mask :feedback="false" autocomplete="current-password" placeholder="••••••••" />
              </div>

              <Button type="submit" label="Entrar" icon="pi pi-sign-in" :loading="loading" fluid />

              <div class="flex justify-content-center">
                <RouterLink :to="{ name: 'tenant-forgot-password', params: { slug } }" class="text-sm text-primary">
                  ¿Olvidaste tu contraseña?
                </RouterLink>
              </div>
            </template>
          </form>
        </template>
      </Card>

      <p class="login-footer muted">
        ¿No tienes acceso? Solicítalo a tu administrador.
      </p>
    </div>
  </section>
</template>
