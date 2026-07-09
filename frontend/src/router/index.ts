import { createRouter, createWebHistory } from 'vue-router'

import AdminLoginPage from '../pages/AdminLoginPage.vue'
import AdminTenantsPage from '../pages/AdminTenantsPage.vue'
import DashboardPage from '../pages/DashboardPage.vue'
import PublicDisplayPage from '../pages/PublicDisplayPage.vue'
import TenantChangePasswordPage from '../pages/TenantChangePasswordPage.vue'
import TenantDashboardPage from '../pages/TenantDashboardPage.vue'
import TenantForgotPasswordPage from '../pages/TenantForgotPasswordPage.vue'
import TenantLoginPage from '../pages/TenantLoginPage.vue'
import TenantResetPasswordPage from '../pages/TenantResetPasswordPage.vue'
import { useAdminAuthStore } from '../stores/adminAuth'
import { useTenantAuthStore } from '../stores/tenantAuth'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardPage,
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: AdminLoginPage,
      meta: {
        guestOnly: true,
      },
    },
    {
      path: '/admin',
      redirect: { name: 'admin-tenants' },
    },
    {
      path: '/admin/tenants',
      name: 'admin-tenants',
      component: AdminTenantsPage,
      meta: {
        requiresAdminAuth: true,
      },
    },
    {
      path: '/display',
      name: 'display',
      component: PublicDisplayPage,
    },
    // Tenant routes
    {
      path: '/t/:slug/login',
      name: 'tenant-login',
      component: TenantLoginPage,
      meta: {
        tenantGuestOnly: true,
      },
    },
    {
      path: '/t/:slug/forgot-password',
      name: 'tenant-forgot-password',
      component: TenantForgotPasswordPage,
      meta: {
        tenantGuestOnly: true,
      },
    },
    {
      path: '/t/:slug/reset-password',
      name: 'tenant-reset-password',
      component: TenantResetPasswordPage,
      meta: {
        tenantGuestOnly: true,
      },
    },
    {
      path: '/t/:slug/change-password',
      name: 'tenant-change-password',
      component: TenantChangePasswordPage,
      meta: {
        requiresTenantAuth: true,
      },
    },
    {
      path: '/t/:slug',
      redirect: { name: 'tenant-dashboard' },
    },
    {
      path: '/t/:slug/dashboard',
      name: 'tenant-dashboard',
      component: TenantDashboardPage,
      meta: {
        requiresTenantAuth: true,
      },
    },
  ],
})

router.beforeEach((to) => {
  const adminAuthStore = useAdminAuthStore()
  const tenantAuthStore = useTenantAuthStore()

  if (to.meta.requiresAdminAuth && !adminAuthStore.isAuthenticated) {
    return { name: 'admin-login' }
  }

  if (to.meta.guestOnly && adminAuthStore.isAuthenticated) {
    return { name: 'admin-tenants' }
  }

  if (to.meta.requiresTenantAuth) {
    if (!tenantAuthStore.isAuthenticated) {
      return { name: 'tenant-login', params: { slug: to.params.slug } }
    }

    if (tenantAuthStore.tenantSlug !== to.params.slug) {
      tenantAuthStore.clearSession()
      return { name: 'tenant-login', params: { slug: to.params.slug } }
    }
  }

  if (to.meta.tenantGuestOnly) {
    if (tenantAuthStore.isAuthenticated && tenantAuthStore.tenantSlug === to.params.slug) {
      return { name: 'tenant-dashboard', params: { slug: to.params.slug } }
    }

    if (tenantAuthStore.isAuthenticated && tenantAuthStore.tenantSlug !== to.params.slug) {
      tenantAuthStore.clearSession()
    }
  }

  return true
})
