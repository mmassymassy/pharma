<script setup>
import { reactive, ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../api/client'
import { useToastStore } from '../../../stores/toast'

const router = useRouter()
const toast = useToastStore()

const clientMode = ref('existing') // 'existing' | 'new'
const clientSearch = ref('')
const clientResults = ref([])
const selectedClient = ref(null)

const newClient = reactive({ name: '', phone: '', whatsapp: '', country: '', city: '', address: '' })

const delivery = reactive({
  delivery_name: '',
  delivery_phone: '',
  delivery_country: '',
  delivery_city: '',
  delivery_address: '',
})

const source = ref('manual')
const notes = ref('')

const productSearch = ref('')
const productResults = ref([])
const items = ref([]) // { product_id, name, price, quantity }

const errors = ref({})
const submitting = ref(false)

async function searchClients() {
  if (clientSearch.value.trim().length < 2) {
    clientResults.value = []
    return
  }
  const { data } = await api.get('/admin/clients', { params: { search: clientSearch.value, per_page: 5 } })
  clientResults.value = data.data
}
watch(clientSearch, searchClients)

function pickClient(client) {
  selectedClient.value = client
  clientResults.value = []
  clientSearch.value = client.name
  delivery.delivery_name = client.name
  delivery.delivery_phone = client.phone
  delivery.delivery_country = client.country || ''
  delivery.delivery_city = client.city || ''
  delivery.delivery_address = client.address || ''
}

watch(newClient, (val) => {
  if (clientMode.value !== 'new') return
  delivery.delivery_name = val.name
  delivery.delivery_phone = val.phone
  delivery.delivery_country = val.country
  delivery.delivery_city = val.city
  delivery.delivery_address = val.address
}, { deep: true })

async function searchProducts() {
  const { data } = await api.get('/admin/products', { params: { search: productSearch.value || undefined, per_page: 8 } })
  productResults.value = data.data
}
watch(productSearch, searchProducts)
onMounted(searchProducts)

function addItem(product) {
  const existing = items.value.find((i) => i.product_id === product.id)
  if (existing) {
    existing.quantity++
  } else {
    items.value.push({ product_id: product.id, name: product.name, price: product.price, quantity: 1 })
  }
}

function removeItem(productId) {
  items.value = items.value.filter((i) => i.product_id !== productId)
}

const total = computed(() => items.value.reduce((sum, i) => sum + i.price * i.quantity, 0))

async function submit() {
  errors.value = {}

  if (items.value.length === 0) {
    toast.error('Add at least one product to the order.')
    return
  }

  const payload = {
    ...delivery,
    source: source.value,
    notes: notes.value || undefined,
    items: items.value.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
  }

  if (clientMode.value === 'existing') {
    if (!selectedClient.value) {
      toast.error('Please select an existing client.')
      return
    }
    payload.client_id = selectedClient.value.id
  } else {
    payload.new_client = { ...newClient }
  }

  submitting.value = true
  try {
    const { data } = await api.post('/admin/orders', payload)
    toast.success('Order created.')
    router.push({ name: 'admin-orders-show', params: { id: data.data.id } })
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
      toast.error('Please fix the highlighted fields.')
    } else {
      toast.error('Failed to create order.')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">New order</h1>

    <div class="bg-white rounded-xl border border-slate-100 p-6 mb-6">
      <h2 class="font-semibold text-slate-900 mb-4">Client</h2>
      <div class="flex gap-2 mb-4">
        <button
          class="px-3 py-1.5 rounded-lg text-sm border"
          :class="clientMode === 'existing' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-200'"
          @click="clientMode = 'existing'; selectedClient = null"
        >
          Existing client
        </button>
        <button
          class="px-3 py-1.5 rounded-lg text-sm border"
          :class="clientMode === 'new' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-200'"
          @click="clientMode = 'new'; selectedClient = null"
        >
          New client
        </button>
      </div>

      <div v-if="clientMode === 'existing'" class="relative">
        <input
          v-model="clientSearch"
          type="text"
          placeholder="Search client by name or phone…"
          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"
        />
        <div v-if="clientResults.length" class="absolute z-10 bg-white border border-slate-200 rounded-lg mt-1 w-full shadow-lg">
          <button
            v-for="c in clientResults"
            :key="c.id"
            class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50"
            @click="pickClient(c)"
          >
            {{ c.name }} — {{ c.phone }}
          </button>
        </div>
        <p v-if="selectedClient" class="text-xs text-emerald-600 mt-2">Selected: {{ selectedClient.name }}</p>
      </div>

      <div v-else class="grid sm:grid-cols-2 gap-3">
        <input v-model="newClient.name" placeholder="Name" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="newClient.phone" placeholder="Phone" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="newClient.whatsapp" placeholder="WhatsApp (optional)" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="newClient.country" placeholder="Country" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="newClient.city" placeholder="City" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="newClient.address" placeholder="Address" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-6 mb-6">
      <h2 class="font-semibold text-slate-900 mb-4">Delivery details</h2>
      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <input v-model="delivery.delivery_name" placeholder="Delivery name" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.delivery_name" class="text-xs text-red-600 mt-1">{{ errors.delivery_name[0] }}</p>
        </div>
        <div>
          <input v-model="delivery.delivery_phone" placeholder="Delivery phone" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.delivery_phone" class="text-xs text-red-600 mt-1">{{ errors.delivery_phone[0] }}</p>
        </div>
        <input v-model="delivery.delivery_country" placeholder="Country" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="delivery.delivery_city" placeholder="City" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <div class="sm:col-span-2">
          <input v-model="delivery.delivery_address" placeholder="Address" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.delivery_address" class="text-xs text-red-600 mt-1">{{ errors.delivery_address[0] }}</p>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-3 mt-3">
        <select v-model="source" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
          <option value="manual">Manual</option>
          <option value="whatsapp">WhatsApp</option>
          <option value="website">Website</option>
        </select>
      </div>
      <textarea v-model="notes" placeholder="Notes (optional)" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mt-3"></textarea>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-6 mb-6">
      <h2 class="font-semibold text-slate-900 mb-4">Items</h2>

      <input
        v-model="productSearch"
        type="text"
        placeholder="Search products…"
        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mb-3"
      />
      <div class="flex flex-wrap gap-2 mb-5">
        <button
          v-for="p in productResults"
          :key="p.id"
          class="text-xs border border-slate-200 rounded-full px-3 py-1.5 hover:bg-slate-50"
          @click="addItem(p)"
        >
          + {{ p.name }} (${{ p.price.toFixed(2) }})
        </button>
      </div>

      <p v-if="errors.items" class="text-xs text-red-600 mb-2">{{ errors.items[0] }}</p>

      <div v-if="items.length" class="space-y-2">
        <div v-for="item in items" :key="item.product_id" class="flex items-center justify-between border-b border-slate-50 pb-2">
          <span class="text-sm">{{ item.name }}</span>
          <div class="flex items-center gap-3">
            <input v-model.number="item.quantity" type="number" min="1" class="w-16 border border-slate-200 rounded-lg px-2 py-1 text-sm text-center" />
            <span class="text-sm font-medium w-20 text-right">${{ (item.price * item.quantity).toFixed(2) }}</span>
            <button class="text-slate-400 hover:text-red-500" @click="removeItem(item.product_id)">✕</button>
          </div>
        </div>
        <div class="flex justify-between pt-2 font-semibold">
          <span>Total</span>
          <span>${{ total.toFixed(2) }}</span>
        </div>
      </div>
      <p v-else class="text-sm text-slate-400">No items added yet.</p>
    </div>

    <button
      :disabled="submitting"
      class="px-6 py-3 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition disabled:opacity-50"
      @click="submit"
    >
      {{ submitting ? 'Creating…' : 'Create order' }}
    </button>
  </div>
</template>
