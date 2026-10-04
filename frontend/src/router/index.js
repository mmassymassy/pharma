import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior() {
    return { top: 0 }
  },
  routes: [
    {
      path: '/',
      component: () => import('../layouts/PublicLayout.vue'),
      children: [
        { path: '', name: 'home', component: () => import('../views/public/HomeView.vue') },
        { path: 'products/:id', name: 'product-detail', component: () => import('../views/public/ProductDetailView.vue') },
        { path: 'order', name: 'order', component: () => import('../views/public/OrderView.vue') },
        { path: 'order/confirmed/:reference', name: 'order-confirmed', component: () => import('../views/public/OrderConfirmedView.vue') },
      ],
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('../views/admin/LoginView.vue'),
    },
    {
      path: '/admin',
      component: () => import('../layouts/AdminLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', name: 'admin-dashboard', component: () => import('../views/admin/DashboardView.vue') },
        { path: 'orders', name: 'admin-orders', component: () => import('../views/admin/orders/OrdersListView.vue') },
        { path: 'orders/create', name: 'admin-orders-create', component: () => import('../views/admin/orders/OrderCreateView.vue') },
        { path: 'orders/:id', name: 'admin-orders-show', component: () => import('../views/admin/orders/OrderDetailView.vue') },
        { path: 'clients', name: 'admin-clients', component: () => import('../views/admin/clients/ClientsListView.vue') },
        { path: 'clients/:id', name: 'admin-clients-show', component: () => import('../views/admin/clients/ClientDetailView.vue') },
        { path: 'suppliers', name: 'admin-suppliers', component: () => import('../views/admin/suppliers/SuppliersListView.vue') },
        { path: 'products', name: 'admin-products', component: () => import('../views/admin/products/ProductsListView.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true

  const auth = useAuthStore()
  if (!auth.checked) {
    await auth.fetchUser()
  }

  if (!auth.isAuthenticated) {
    return { name: 'admin-login', query: { redirect: to.fullPath } }
  }

  return true
})

export default router
