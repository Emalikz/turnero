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
    errorMessage.value = 'El enlace no es valido o el tenant no existe.'
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
    console.error('Error during tenant login:', error)
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo iniciar sesion.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="admin-auth-layout">
    <Card class="auth-card">
      <template #title>Acceso al tenant</template>
      <template #subtitle>Inicia sesion para gestionar tu agenda.</template>
      <template #content>
        <form class="stack-md" @submit.prevent="submit">
          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <template v-if="slugValid !== false">
            <div class="field-stack">
              <label for="email">Email</label>
              <InputText id="email" v-model="form.email" type="email" fluid autocomplete="username" />
            </div>

            <div class="field-stack">
              <label for="password">Contrasena</label>
              <Password id="password" v-model="form.password" fluid toggle-mask :feedback="false" autocomplete="current-password" />
            </div>

            <Button type="submit" label="Entrar" icon="pi pi-sign-in" :loading="loading" fluid />

            <div class="flex justify-content-center">
              <RouterLink :to="{ name: 'tenant-forgot-password', params: { slug } }" class="text-sm text-primary">
                Olvidaste tu contrasena?
              </RouterLink>
            </div>
          </template>
        </form>
      </template>
    </Card>
  </section>
</template>
