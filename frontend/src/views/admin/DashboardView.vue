<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../api/client'
import StatusBadge from '../../components/StatusBadge.vue'

const stats = ref(null)
const loading = ref(true)

const statusOrder = [
  'request_received',
  'availability_check',
  'ordered_from_supplier',
  'paid',
  'shipped',
  'delivered',
  'cancelled',
]

const statusLabels = {
  request_received: 'Request received',
  availability_check: 'Availability check',
  ordered_from_supplier: 'Ordered from supplier',
  paid: 'Paid',
  shipped: 'Shipped',
  delivered: 'Delivered',
  cancelled: 'Cancelled',
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value = data
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Dashboard</h1>

    <div v-if="loading" class="text-slate-400">Loading…</div>

    <template v-else-if="stats">
      <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-100 p-5">
          <p class="text-sm text-slate-500">Total clients</p>
          <p class="text-3xl font-bold text-slate-900 mt-1">{{ stats.total_clients }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5">
          <p class="text-sm text-slate-500">Orders this month</p>
          <p class="text-3xl font-bold text-slate-900 mt-1">{{ stats.orders_this_month }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5">
          <p class="text-sm text-slate-500">Total orders</p>
          <p class="text-3xl font-bold text-slate-900 mt-1">
            {{ Object.values(stats.status_counts).reduce((a, b) => a + b, 0) }}
          </p>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-100 p-5 mb-8">
        <h2 class="font-semibold text-slate-900 mb-4">Orders per status</h2>
        <div class="grid sm:grid-cols-4 gap-3">
          <div v-for="status in statusOrder" :key="status" class="border border-slate-100 rounded-lg p-3">
            <StatusBadge :status="status" />
            <p class="text-2xl font-bold text-slate-900 mt-2">{{ stats.status_counts[status] ?? 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-100 p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-slate-900">Latest orders</h2>
          <RouterLink :to="{ name: 'admin-orders' }" class="text-sm text-slate-500 hover:underline">View all</RouterLink>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
              <th class="py-2 font-medium">Reference</th>
              <th class="py-2 font-medium">Client</th>
              <th class="py-2 font-medium">Status</th>
              <th class="py-2 font-medium text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="order in stats.latest_orders"
              :key="order.id"
              class="border-b border-slate-50 last:border-0 hover:bg-slate-50 cursor-pointer"
              @click="$router.push({ name: 'admin-orders-show', params: { id: order.id } })"
            >
              <td class="py-3 font-mono">{{ order.reference }}</td>
              <td class="py-3">{{ order.client?.name }}</td>
              <td class="py-3"><StatusBadge :status="order.status" /></td>
              <td class="py-3 text-right font-medium">${{ order.total.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
