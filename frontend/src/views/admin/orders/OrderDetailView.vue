<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../../api/client'
import { useToastStore } from '../../../stores/toast'
import StatusBadge from '../../../components/StatusBadge.vue'
import WhatsAppButton from '../../../components/WhatsAppButton.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const order = ref(null)
const loading = ref(true)
const statusSaving = ref(false)
const trackingSaving = ref(false)
const confirmOpen = ref(false)

const statuses = [
  'request_received',
  'availability_check',
  'ordered_from_supplier',
  'paid',
  'shipped',
  'delivered',
  'cancelled',
]

const tracking = reactive({ tracking_number: '', carrier: '' })

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/orders/${route.params.id}`)
    order.value = data.data
    tracking.tracking_number = order.value.tracking_number || ''
    tracking.carrier = order.value.carrier || ''
  } finally {
    loading.value = false
  }
}

async function changeStatus(newStatus) {
  if (newStatus === order.value.status) return
  statusSaving.value = true
  try {
    const { data } = await api.patch(`/admin/orders/${order.value.id}/status`, { status: newStatus })
    order.value = data.data
    toast.success('Order status updated.')
  } catch {
    toast.error('Failed to update status.')
  } finally {
    statusSaving.value = false
  }
}

async function saveTracking() {
  trackingSaving.value = true
  try {
    const { data } = await api.put(`/admin/orders/${order.value.id}`, tracking)
    order.value = data.data
    toast.success('Tracking info saved.')
  } catch {
    toast.error('Failed to save tracking info.')
  } finally {
    trackingSaving.value = false
  }
}

async function confirmDelete() {
  try {
    await api.delete(`/admin/orders/${order.value.id}`)
    toast.success('Order deleted.')
    router.push({ name: 'admin-orders' })
  } catch {
    toast.error('Failed to delete order.')
  }
}

onMounted(load)
</script>

<template>
  <div v-if="loading" class="text-slate-400">Loading…</div>

  <div v-else-if="order" class="max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 font-mono">{{ order.reference }}</h1>
        <p class="text-sm text-slate-500 mt-1">Placed {{ new Date(order.created_at).toLocaleString() }} via {{ order.source }}</p>
      </div>
      <div class="flex items-center gap-3">
        <WhatsAppButton :url="order.whatsapp_url" />
        <button
          class="px-4 py-2 rounded-lg border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition"
          @click="confirmOpen = true"
        >
          Delete order
        </button>
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-6">
      <div class="sm:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-slate-100 p-6">
          <h2 class="font-semibold text-slate-900 mb-4">Items</h2>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="py-2 font-medium">Product</th>
                <th class="py-2 font-medium text-right">Unit price</th>
                <th class="py-2 font-medium text-right">Qty</th>
                <th class="py-2 font-medium text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in order.items" :key="item.id" class="border-b border-slate-50 last:border-0">
                <td class="py-2">{{ item.product_name }}</td>
                <td class="py-2 text-right">${{ item.unit_price.toFixed(2) }}</td>
                <td class="py-2 text-right">{{ item.quantity }}</td>
                <td class="py-2 text-right font-medium">${{ item.subtotal.toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
          <div class="flex justify-between pt-4 font-semibold border-t border-slate-100 mt-2">
            <span>Total</span>
            <span>${{ order.total.toFixed(2) }}</span>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 p-6">
          <h2 class="font-semibold text-slate-900 mb-4">Status history</h2>
          <ol class="space-y-4">
            <li v-for="h in order.status_histories" :key="h.id" class="flex gap-3">
              <div class="w-2 h-2 rounded-full bg-slate-900 mt-1.5 shrink-0"></div>
              <div>
                <p class="text-sm">
                  <span v-if="h.from_status" class="text-slate-400">{{ h.from_status }} → </span>
                  <span class="font-medium">{{ h.to_status }}</span>
                </p>
                <p class="text-xs text-slate-400">
                  {{ new Date(h.created_at).toLocaleString() }} · {{ h.changed_by || 'system' }}
                </p>
              </div>
            </li>
          </ol>
        </div>
      </div>

      <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-100 p-6">
          <h2 class="font-semibold text-slate-900 mb-3">Status</h2>
          <StatusBadge :status="order.status" />
          <select
            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mt-3"
            :value="order.status"
            :disabled="statusSaving"
            @change="changeStatus($event.target.value)"
          >
            <option v-for="s in statuses" :key="s" :value="s">{{ s.replaceAll('_', ' ') }}</option>
          </select>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 p-6">
          <h2 class="font-semibold text-slate-900 mb-3">Client</h2>
          <p class="text-sm font-medium">{{ order.client?.name }}</p>
          <p class="text-sm text-slate-500">{{ order.client?.phone }}</p>
          <p class="text-sm text-slate-500">{{ order.delivery_address }}</p>
          <p class="text-sm text-slate-500">{{ order.delivery_city }}, {{ order.delivery_country }}</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 p-6">
          <h2 class="font-semibold text-slate-900 mb-3">Tracking</h2>
          <input
            v-model="tracking.tracking_number"
            placeholder="Tracking number"
            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mb-2"
          />
          <input
            v-model="tracking.carrier"
            placeholder="Carrier"
            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mb-3"
          />
          <button
            :disabled="trackingSaving"
            class="w-full px-3 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition disabled:opacity-50"
            @click="saveTracking"
          >
            {{ trackingSaving ? 'Saving…' : 'Save tracking' }}
          </button>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :open="confirmOpen"
      title="Delete order?"
      :message="`This will permanently delete order ${order.reference}.`"
      @cancel="confirmOpen = false"
      @confirm="confirmDelete"
    />
  </div>
</template>
