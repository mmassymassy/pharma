<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../../api/client'
import { useToastStore } from '../../../stores/toast'
import SupplierFormModal from '../../../components/SupplierFormModal.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'

const toast = useToastStore()

const suppliers = ref([])
const meta = ref(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

const modalOpen = ref(false)
const editingSupplier = ref(null)
const confirmOpen = ref(false)
const supplierToDelete = ref(null)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/suppliers', { params: { search: search.value || undefined, page: page.value } })
    suppliers.value = data.data
    meta.value = data.meta
  } finally {
    loading.value = false
  }
}

let debounceTimer
watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; load() }, 300)
})
watch(page, load)
onMounted(load)

function openCreate() { editingSupplier.value = null; modalOpen.value = true }
function openEdit(s) { editingSupplier.value = s; modalOpen.value = true }

async function handleSubmit(form, setErrors) {
  try {
    if (editingSupplier.value) {
      await api.put(`/admin/suppliers/${editingSupplier.value.id}`, form)
      toast.success('Supplier updated.')
    } else {
      await api.post('/admin/suppliers', form)
      toast.success('Supplier created.')
    }
    modalOpen.value = false
    load()
  } catch (e) {
    if (e.response?.status === 422) setErrors(e.response.data.errors || {})
    else toast.error('Failed to save supplier.')
  }
}

function askDelete(s) { supplierToDelete.value = s; confirmOpen.value = true }
async function confirmDelete() {
  try {
    await api.delete(`/admin/suppliers/${supplierToDelete.value.id}`)
    toast.success('Supplier deleted.')
    confirmOpen.value = false
    load()
  } catch {
    toast.error('Failed to delete supplier.')
  }
}
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Suppliers</h1>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition" @click="openCreate">
        + New supplier
      </button>
    </div>

    <input v-model="search" type="search" placeholder="Search suppliers…" class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-64 mb-4" />

    <div class="bg-white rounded-xl border border-slate-100 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-slate-400 border-b border-slate-100">
            <th class="py-3 px-4 font-medium">Name</th>
            <th class="py-3 px-4 font-medium">Contact</th>
            <th class="py-3 px-4 font-medium">Country</th>
            <th class="py-3 px-4 font-medium text-right">Lead time</th>
            <th class="py-3 px-4 font-medium text-right">Products</th>
            <th class="py-3 px-4 font-medium"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="py-8 text-center text-slate-400">Loading…</td></tr>
          <tr v-else-if="suppliers.length === 0"><td colspan="6" class="py-8 text-center text-slate-400">No suppliers found.</td></tr>
          <tr v-for="s in suppliers" :key="s.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50">
            <td class="py-3 px-4">{{ s.name }}</td>
            <td class="py-3 px-4 text-slate-500">{{ s.contact_name || '—' }}</td>
            <td class="py-3 px-4 text-slate-500">{{ s.country || '—' }}</td>
            <td class="py-3 px-4 text-right">{{ s.lead_time_days ?? '—' }}d</td>
            <td class="py-3 px-4 text-right">{{ s.products_count }}</td>
            <td class="py-3 px-4">
              <div class="flex items-center justify-end gap-3">
                <button class="text-slate-400 hover:text-slate-900 text-xs" @click="openEdit(s)">Edit</button>
                <button class="text-slate-400 hover:text-red-500 text-xs" @click="askDelete(s)">Delete</button>
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

    <SupplierFormModal :open="modalOpen" :supplier="editingSupplier" @close="modalOpen = false" @submit="handleSubmit" />
    <ConfirmDialog
      :open="confirmOpen"
      title="Delete supplier?"
      :message="`This will permanently delete ${supplierToDelete?.name}.`"
      @cancel="confirmOpen = false"
      @confirm="confirmDelete"
    />
  </div>
</template>
