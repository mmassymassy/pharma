<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api from '../../../api/client'
import StatusBadge from '../../../components/StatusBadge.vue'

const router = useRouter()
const orders = ref([])
const meta = ref(null)
const loading = ref(true)
const search = ref('')
const status = ref('')
const page = ref(1)

const statuses = [
  'request_received',
  'availability_check',
  'ordered_from_supplier',
  'paid',
  'shipped',
  'delivered',
  'cancelled',
]

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/orders', {
      params: { search: search.value || undefined, status: status.value || undefined, page: page.value },
    })
    orders.value = data.data
    meta.value = data.meta
  } finally {
    loading.value = false
  }
}

let debounceTimer
watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    page.value = 1
    load()
  }, 300)
})
watch(status, () => {
  page.value = 1
  load()
})
watch(page, load)

onMounted(load)
</script>

<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Orders</h1>
      <RouterLink
        :to="{ name: 'admin-orders-create' }"
        class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition"
      >
        + New order
      </RouterLink>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
      <input
        v-model="search"
        type="search"
        placeholder="Search by reference, name or phone…"
        class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-64"
      />
      <select v-model="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
        <option value="">All statuses</option>
        <option v-for="s in statuses" :key="s" :value="s">{{ s.replaceAll('_', ' ') }}</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-slate-400 border-b border-slate-100">
            <th class="py-3 px-4 font-medium">Reference</th>
            <th class="py-3 px-4 font-medium">Client</th>
            <th class="py-3 px-4 font-medium">Source</th>
            <th class="py-3 px-4 font-medium">Status</th>
            <th class="py-3 px-4 font-medium text-right">Total</th>
            <th class="py-3 px-4 font-medium">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="py-8 text-center text-slate-400">Loading…</td>
          </tr>
          <tr v-else-if="orders.length === 0">
            <td colspan="6" class="py-8 text-center text-slate-400">No orders found.</td>
          </tr>
          <tr
            v-for="order in orders"
            :key="order.id"
            class="border-b border-slate-50 last:border-0 hover:bg-slate-50 cursor-pointer"
            @click="router.push({ name: 'admin-orders-show', params: { id: order.id } })"
          >
            <td class="py-3 px-4 font-mono">{{ order.reference }}</td>
            <td class="py-3 px-4">{{ order.client?.name }}</td>
            <td class="py-3 px-4 capitalize text-slate-500">{{ order.source }}</td>
            <td class="py-3 px-4"><StatusBadge :status="order.status" /></td>
            <td class="py-3 px-4 text-right font-medium">${{ order.total.toFixed(2) }}</td>
            <td class="py-3 px-4 text-slate-500">{{ new Date(order.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between mt-4 text-sm">
      <p class="text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
      <div class="flex gap-2">
        <button
          class="px-3 py-1.5 border border-slate-200 rounded-lg disabled:opacity-40"
          :disabled="page <= 1"
          @click="page--"
        >
          Previous
        </button>
        <button
          class="px-3 py-1.5 border border-slate-200 rounded-lg disabled:opacity-40"
          :disabled="page >= meta.last_page"
          @click="page++"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
