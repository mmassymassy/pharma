<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '../../../api/client'
import StatusBadge from '../../../components/StatusBadge.vue'
import WhatsAppButton from '../../../components/WhatsAppButton.vue'

const route = useRoute()
const client = ref(null)
const orders = ref([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/clients/${route.params.id}`)
    client.value = data.data
    orders.value = data.orders || []
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div v-if="loading" class="text-slate-400">Loading…</div>

  <div v-else-if="client" class="max-w-3xl">
    <RouterLink :to="{ name: 'admin-clients' }" class="text-sm text-slate-500 hover:underline">← Back to clients</RouterLink>

    <div class="flex flex-wrap items-center justify-between gap-4 mt-3 mb-6">
      <h1 class="text-2xl font-bold text-slate-900">{{ client.name }}</h1>
      <WhatsAppButton :url="client.whatsapp_url" />
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-6 mb-6 grid sm:grid-cols-2 gap-4 text-sm">
      <div><span class="text-slate-400">Phone</span><p class="font-medium">{{ client.phone }}</p></div>
      <div><span class="text-slate-400">WhatsApp</span><p class="font-medium">{{ client.whatsapp || '—' }}</p></div>
      <div><span class="text-slate-400">Country / City</span><p class="font-medium">{{ client.country || '—' }} / {{ client.city || '—' }}</p></div>
      <div><span class="text-slate-400">Address</span><p class="font-medium">{{ client.address || '—' }}</p></div>
      <div><span class="text-slate-400">Source</span><p class="font-medium capitalize">{{ client.source }}</p></div>
      <div v-if="client.notes"><span class="text-slate-400">Notes</span><p class="font-medium">{{ client.notes }}</p></div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-6">
      <h2 class="font-semibold text-slate-900 mb-4">Order history</h2>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-slate-400 border-b border-slate-100">
            <th class="py-2 font-medium">Reference</th>
            <th class="py-2 font-medium">Status</th>
            <th class="py-2 pr-6 font-medium text-right">Total</th>
            <th class="py-2 font-medium">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="orders.length === 0"><td colspan="4" class="py-6 text-center text-slate-400">No orders yet.</td></tr>
          <tr
            v-for="order in orders"
            :key="order.id"
            class="border-b border-slate-50 last:border-0 hover:bg-slate-50 cursor-pointer"
            @click="$router.push({ name: 'admin-orders-show', params: { id: order.id } })"
          >
            <td class="py-3 font-mono">{{ order.reference }}</td>
            <td class="py-3"><StatusBadge :status="order.status" /></td>
            <td class="py-3 pr-6 text-right font-medium">${{ order.total.toFixed(2) }}</td>
            <td class="py-3 text-slate-500">{{ new Date(order.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
