import { createRouter, createWebHistory } from 'vue-router'

import AdminLoginPage from '../pages/AdminLoginPage.vue'
import AdminTenantsPage from '../pages/AdminTenantsPage.vue'
import DashboardPage from '../pages/DashboardPage.vue'
import PublicDisplayPage from '../pages/PublicDisplayPage.vue'
import { useAdminAuthStore } from '../stores/adminAuth'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'foundation',
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
  ],
})

router.beforeEach((to) => {
  const adminAuthStore = useAdminAuthStore()

  if (to.meta.requiresAdminAuth && !adminAuthStore.isAuthenticated) {
    return { name: 'admin-login' }
  }

  if (to.meta.guestOnly && adminAuthStore.isAuthenticated) {
    return { name: 'admin-tenants' }
  }

  return true
})
