<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../../api/client'
import { useToastStore } from '../../../stores/toast'
import ProductFormModal from '../../../components/ProductFormModal.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'

const toast = useToastStore()

const products = ref([])
const suppliers = ref([])
const meta = ref(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

const modalOpen = ref(false)
const editingProduct = ref(null)
const confirmOpen = ref(false)
const productToDelete = ref(null)

const availabilityLabel = { in_stock: 'In stock', on_request: 'On request', unavailable: 'Unavailable' }

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/products', { params: { search: search.value || undefined, page: page.value } })
    products.value = data.data
    meta.value = data.meta
  } finally {
    loading.value = false
  }
}

async function loadSuppliers() {
  const { data } = await api.get('/admin/suppliers', { params: { per_page: 100 } })
  suppliers.value = data.data
}

let debounceTimer
watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; load() }, 300)
})
watch(page, load)
onMounted(() => { load(); loadSuppliers() })

function openCreate() { editingProduct.value = null; modalOpen.value = true }
function openEdit(p) { editingProduct.value = p; modalOpen.value = true }

function handleSaved() {
  modalOpen.value = false
  toast.success('Product saved.')
  load()
}

async function toggleActive(product) {
  try {
    await api.put(`/admin/products/${product.id}`, { is_active: !product.is_active })
    product.is_active = !product.is_active
    toast.success(product.is_active ? 'Product activated.' : 'Product deactivated.')
  } catch {
    toast.error('Failed to update product.')
  }
}

function askDelete(p) { productToDelete.value = p; confirmOpen.value = true }
async function confirmDelete() {
  try {
    await api.delete(`/admin/products/${productToDelete.value.id}`)
    toast.success('Product deleted.')
    confirmOpen.value = false
    load()
  } catch {
    toast.error('Failed to delete product.')
  }
}
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Products</h1>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition" @click="openCreate">
        + New product
      </button>
    </div>

    <input v-model="search" type="search" placeholder="Search products…" class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-64 mb-4" />

    <div class="bg-white rounded-xl border border-slate-100 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-slate-400 border-b border-slate-100">
            <th class="py-3 px-4 font-medium">Product</th>
            <th class="py-3 px-4 font-medium">Category</th>
            <th class="py-3 px-4 font-medium text-right">Price</th>
            <th class="py-3 px-4 font-medium">Availability</th>
            <th class="py-3 px-4 font-medium">Active</th>
            <th class="py-3 px-4 font-medium"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="py-8 text-center text-slate-400">Loading…</td></tr>
          <tr v-else-if="products.length === 0"><td colspan="6" class="py-8 text-center text-slate-400">No products found.</td></tr>
          <tr v-for="p in products" :key="p.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50">
            <td class="py-3 px-4 flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-slate-50 overflow-hidden flex items-center justify-center shrink-0">
                <img v-if="p.image_url" :src="p.image_url" class="w-full h-full object-cover" />
              </div>
              <div>
                <p class="font-medium">{{ p.name }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ p.reference }}</p>
              </div>
            </td>
            <td class="py-3 px-4 text-slate-500">{{ p.category || '—' }}</td>
            <td class="py-3 px-4 text-right font-medium">${{ p.price.toFixed(2) }}</td>
            <td class="py-3 px-4 text-slate-500">{{ availabilityLabel[p.availability] }}</td>
            <td class="py-3 px-4">
              <button
                class="w-10 h-5 rounded-full relative transition"
                :class="p.is_active ? 'bg-emerald-500' : 'bg-slate-200'"
                @click="toggleActive(p)"
              >
                <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white transition" :class="p.is_active ? 'left-5' : 'left-0.5'"></span>
              </button>
            </td>
            <td class="py-3 px-4">
              <div class="flex items-center justify-end gap-3">
                <button class="text-slate-400 hover:text-slate-900 text-xs" @click="openEdit(p)">Edit</button>
                <button class="text-slate-400 hover:text-red-500 text-xs" @click="askDelete(p)">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between mt-4 text-sm">
      <p class="text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
      <div class="flex gap-2">
        <button class="px-3 py-1.5 border border-slate-200 rounded-lg disabled:opacity-40" :disabled="page <= 1" @click="page--">Previous</button>
        <button class="px-3 py-1.5 border border-slate-200 rounded-lg disabled:opacity-40" :disabled="page >= meta.last_page" @click="page++">Next</button>
      </div>
    </div>

    <ProductFormModal :open="modalOpen" :product="editingProduct" :suppliers="suppliers" @close="modalOpen = false" @saved="handleSaved" />
    <ConfirmDialog
      :open="confirmOpen"
      title="Delete product?"
      :message="`This will permanently delete ${productToDelete?.name}.`"
      @cancel="confirmOpen = false"
      @confirm="confirmDelete"
    />
  </div>
</template>
