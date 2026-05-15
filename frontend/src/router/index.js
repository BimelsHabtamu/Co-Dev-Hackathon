import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },

      // ── Both roles ──────────────────────────────────────────────────────────
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/DashboardView.vue')
      },
      {
        path: 'products',
        name: 'Products',
        component: () => import('../views/ProductsView.vue')
      },
      {
        path: 'new-sale',
        name: 'NewSale',
        component: () => import('../views/NewSaleView.vue')
      },
      {
        path: 'my-sales',
        name: 'MySales',
        component: () => import('../views/MySalesView.vue')
      },

      // ── Manager-only routes ─────────────────────────────────────────────────
      {
        path: 'inventory',
        name: 'Inventory',
        component: () => import('../views/InventoryView.vue'),
        meta: { requiresRole: 'manager' }
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/UsersView.vue'),
        meta: { requiresRole: 'manager' }
      },
    ]
  },
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, _from, next) => {
  const auth = useAuthStore()

  // Not logged in → go to login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next('/login')
  }

  // Already logged in → skip guest pages
  if (to.meta.guest && auth.isLoggedIn) {
    return next('/dashboard')
  }

  // Role guard — redirect sales officers away from manager-only pages
  if (to.meta.requiresRole && auth.user?.role !== to.meta.requiresRole) {
    return next('/dashboard')
  }

  next()
})

export default router
