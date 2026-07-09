<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import { http } from '../lib/http'
import { useAdminAuthStore } from '../stores/adminAuth'
import type { ApiEnvelope, Tenant } from '../types/admin'
import { slugify } from '../utils/slug'

const router = useRouter()
const adminAuthStore = useAdminAuthStore()

const tenants = ref<Tenant[]>([])
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const search = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const slugTouched = ref(false)
const createdTenant = ref<Tenant | null>(null)
const copiedUrl = ref(false)

const form = reactive({
  name: '',
  slug: '',
  primary_domain: '',
  timezone: 'America/Bogota',
})

const filteredTenants = computed(() => {
  const query = search.value.trim().toLowerCase()

  if (!query) {
    return tenants.value
  }

  return tenants.value.filter((tenant) =>
    [tenant.name, tenant.slug, tenant.schema, tenant.primary_domain].some((value) =>
      value.toLowerCase().includes(query),
    ),
  )
})

const tenantLoginUrl = computed(() => {
  if (!createdTenant.value) return ''
  const base = window.location.origin
  return `${base}/t/${createdTenant.value.slug}/login`
})

watch(
  () => form.name,
  (value) => {
    if (!slugTouched.value) {
      form.slug = slugify(value)
    }
  },
)

async function loadTenants() {
  loading.value = true
  errorMessage.value = null

  try {
    const response = await http.get<ApiEnvelope<Tenant[]>>('/api/v1/admin/tenants')
    tenants.value = response.data.data
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo cargar la lista de tenants.'
  } finally {
    loading.value = false
  }
}

async function createTenant() {
  saving.value = true
  errorMessage.value = null
  successMessage.value = null
  fieldErrors.value = {}
  createdTenant.value = null

  try {
    const response = await http.post<ApiEnvelope<Tenant>>('/api/v1/admin/tenants', {
      name: form.name,
      slug: form.slug,
      primary_domain: form.primary_domain,
      settings: {
        timezone: form.timezone,
      },
    })

    createdTenant.value = response.data.data
    successMessage.value = `Tenant ${response.data.data.name} creado correctamente.`
    form.name = ''
    form.slug = ''
    form.primary_domain = ''
    form.timezone = 'America/Bogota'
    slugTouched.value = false

    await loadTenants()
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error?.message ?? 'No se pudo crear el tenant.'
    fieldErrors.value = error.response?.data?.error?.details ?? {}
  } finally {
    saving.value = false
  }
}

function copyLoginUrl() {
  if (!tenantLoginUrl.value) return
  navigator.clipboard.writeText(tenantLoginUrl.value)
  copiedUrl.value = true
  setTimeout(() => { copiedUrl.value = false }, 2000)
}

function goToTenantLogin() {
  if (!createdTenant.value) return
  router.push({ name: 'tenant-login', params: { slug: createdTenant.value.slug } })
}

function dismissSuccess() {
  successMessage.value = null
  createdTenant.value = null
  copiedUrl.value = false
}

function onSlugInput(value: string | undefined) {
  slugTouched.value = true
  form.slug = slugify(value ?? '')
}

function logout() {
  adminAuthStore.clearSession()
  router.push({ name: 'admin-login' })
}

onMounted(loadTenants)
</script>

<template>
  <section class="admin-grid">
    <Card>
      <template #title>Panel administrativo central</template>
      <template #content>
        <div class="stack-md">
          <div class="metric-row">
            <span class="metric-label">Administrador</span>
            <strong>{{ adminAuthStore.displayName }}</strong>
          </div>
          <div class="metric-row">
            <span class="metric-label">Correo</span>
            <strong>{{ adminAuthStore.user?.email }}</strong>
          </div>
          <div class="metric-row">
            <span class="metric-label">Tenants registrados</span>
            <strong>{{ tenants.length }}</strong>
          </div>
          <div class="stack-sm">
            <Button label="Refrescar listado" icon="pi pi-refresh" outlined @click="loadTenants" />
            <Button label="Cerrar sesión" icon="pi pi-sign-out" severity="secondary" outlined @click="logout" />
          </div>
        </div>
      </template>
    </Card>

    <Card>
      <template #title>Alta de tenant</template>
      <template #content>
        <form class="stack-md" @submit.prevent="createTenant">
          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <Message v-if="successMessage" severity="success" :closable="false">
            <div class="success-content">
              <span>{{ successMessage }}</span>
              <Button
                v-if="createdTenant"
                icon="pi pi-times"
                severity="secondary"
                text
                size="small"
                @click="dismissSuccess"
              />
            </div>
          </Message>

          <div v-if="createdTenant" class="follow-up-actions">
            <p class="follow-up-label">Acciones rápidas</p>
            <div class="follow-up-url">
              <code class="url-text">{{ tenantLoginUrl }}</code>
              <Button
                :label="copiedUrl ? 'Copiado' : 'Copiar'"
                :icon="copiedUrl ? 'pi pi-check' : 'pi pi-copy'"
                severity="secondary"
                outlined
                size="small"
                @click="copyLoginUrl"
              />
            </div>
            <Button
              label="Ir al login del tenant"
              icon="pi pi-external-link"
              severity="secondary"
              outlined
              size="small"
              @click="goToTenantLogin"
            />
          </div>

          <div class="field-stack">
            <label for="tenant-name">Nombre comercial</label>
            <InputText id="tenant-name" v-model="form.name" fluid placeholder="Ej: Clínica Central" />
            <small v-if="fieldErrors.name?.[0]" class="field-error">{{ fieldErrors.name[0] }}</small>
          </div>

          <div class="field-stack">
            <label for="tenant-slug">Slug</label>
            <InputText id="tenant-slug" :model-value="form.slug" fluid @update:model-value="onSlugInput($event)" placeholder="clinica-central" />
            <small v-if="fieldErrors.slug?.[0]" class="field-error">{{ fieldErrors.slug[0] }}</small>
          </div>

          <div class="field-stack">
            <label for="tenant-domain">Dominio principal</label>
            <InputText id="tenant-domain" v-model="form.primary_domain" fluid placeholder="clinica.example.com" />
            <small v-if="fieldErrors.primary_domain?.[0]" class="field-error">{{ fieldErrors.primary_domain[0] }}</small>
          </div>

          <div class="field-stack">
            <label for="tenant-timezone">Timezone</label>
            <InputText id="tenant-timezone" v-model="form.timezone" fluid />
          </div>

          <Button type="submit" label="Crear tenant" icon="pi pi-plus" :loading="saving" />
        </form>
      </template>
    </Card>

    <Card class="admin-grid-span-2">
      <template #title>Tenants existentes</template>
      <template #content>
        <div class="stack-md">
          <div class="table-toolbar">
            <IconField>
              <InputIcon class="pi pi-search" />
              <InputText v-model="search" placeholder="Buscar por nombre, slug, schema o dominio" />
            </IconField>
          </div>

          <DataTable :value="filteredTenants" :loading="loading" striped-rows>
            <Column field="name" header="Nombre" />
            <Column field="slug" header="Slug" />
            <Column field="schema" header="Schema" />
            <Column field="primary_domain" header="Dominio" />
            <Column field="created_at" header="Creado" />

            <template #empty>
              <div class="empty-state">
                <div class="empty-state-icon">
                  <i class="pi pi-building"></i>
                </div>
                <h3 class="empty-state-title">No hay tenants registrados</h3>
                <p class="empty-state-desc">
                  Los tenants son las organizaciones que usan Turnero.
                  Cada tenant tiene su propia agenda, usuarios y configuración.
                </p>
                <p class="empty-state-hint">
                  Usa el formulario de arriba para crear el primer tenant.
                </p>
              </div>
            </template>
          </DataTable>
        </div>
      </template>
    </Card>
  </section>
</template>
