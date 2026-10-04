<script setup>
import { ref, onMounted, watch, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../api/client'
import { useToastStore } from '../../../stores/toast'
import ClientFormModal from '../../../components/ClientFormModal.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'
import WhatsAppButton from '../../../components/WhatsAppButton.vue'

const router = useRouter()
const toast = useToastStore()

const clients = ref([])
const meta = ref(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

const modalOpen = ref(false)
const editingClient = ref(null)

const confirmOpen = ref(false)
const clientToDelete = ref(null)

const quickForm = reactive({ name: '', phone: '', country: '', address: '' })
const quickSaving = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/clients', { params: { search: search.value || undefined, page: page.value } })
    clients.value = data.data
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

function openCreate() {
  editingClient.value = null
  modalOpen.value = true
}
function openEdit(client) {
  editingClient.value = client
  modalOpen.value = true
}

async function handleSubmit(form, setErrors) {
  try {
    if (editingClient.value) {
      await api.put(`/admin/clients/${editingClient.value.id}`, form)
      toast.success('Client updated.')
    } else {
      await api.post('/admin/clients', form)
      toast.success('Client created.')
    }
    modalOpen.value = false
    load()
  } catch (e) {
    if (e.response?.status === 422) {
      setErrors(e.response.data.errors || {})
    } else {
      toast.error('Failed to save client.')
    }
  }
}

function askDelete(client) {
  clientToDelete.value = client
  confirmOpen.value = true
}
async function confirmDelete() {
  try {
    await api.delete(`/admin/clients/${clientToDelete.value.id}`)
    toast.success('Client deleted.')
    confirmOpen.value = false
    load()
  } catch {
    toast.error('Failed to delete client.')
  }
}

async function quickAdd() {
  quickSaving.value = true
  try {
    await api.post('/admin/clients', { ...quickForm, source: 'whatsapp' })
    toast.success('Client added from WhatsApp.')
    Object.assign(quickForm, { name: '', phone: '', country: '', address: '' })
    load()
  } catch {
    toast.error('Failed to add client.')
  } finally {
    quickSaving.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Clients</h1>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition" @click="openCreate">
        + New client
      </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-5 mb-6">
      <h2 class="font-semibold text-slate-900 mb-3">Quick add from WhatsApp</h2>
      <p class="text-sm text-slate-500 mb-3">For clients who wrote to you directly on WhatsApp.</p>
      <form class="grid sm:grid-cols-4 gap-3" @submit.prevent="quickAdd">
        <input v-model="quickForm.name" placeholder="Name" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="quickForm.phone" placeholder="Phone" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="quickForm.country" placeholder="Country" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="quickForm.address" placeholder="Address" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <button
          type="submit"
          :disabled="quickSaving"
          class="sm:col-span-4 justify-self-start px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition disabled:opacity-50"
        >
          {{ quickSaving ? 'Adding…' : 'Add client' }}
        </button>
      </form>
    </div>

    <input
      v-model="search"
      type="search"
      placeholder="Search by name or phone…"
      class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-64 mb-4"
    />

    <div class="bg-white rounded-xl border border-slate-100 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-slate-400 border-b border-slate-100">
            <th class="py-3 px-4 font-medium">Name</th>
            <th class="py-3 px-4 font-medium">Phone</th>
            <th class="py-3 px-4 font-medium">Source</th>
            <th class="py-3 px-4 font-medium text-right">Orders</th>
            <th class="py-3 px-4 font-medium"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="py-8 text-center text-slate-400">Loading…</td></tr>
          <tr v-else-if="clients.length === 0"><td colspan="5" class="py-8 text-center text-slate-400">No clients found.</td></tr>
          <tr v-for="client in clients" :key="client.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50">
            <td class="py-3 px-4 cursor-pointer" @click="router.push({ name: 'admin-clients-show', params: { id: client.id } })">
              {{ client.name }}
            </td>
            <td class="py-3 px-4 text-slate-500">{{ client.phone }}</td>
            <td class="py-3 px-4 capitalize text-slate-500">{{ client.source }}</td>
            <td class="py-3 px-4 text-right">{{ client.orders_count }}</td>
            <td class="py-3 px-4">
              <div class="flex items-center justify-end gap-2">
                <WhatsAppButton :url="client.whatsapp_url" label="WhatsApp" class="!px-2 !py-1 !text-xs" />
                <button class="text-slate-400 hover:text-slate-900 text-xs" @click="openEdit(client)">Edit</button>
                <button class="text-slate-400 hover:text-red-500 text-xs" @click="askDelete(client)">Delete</button>
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

    <ClientFormModal :open="modalOpen" :client="editingClient" @close="modalOpen = false" @submit="handleSubmit" />
    <ConfirmDialog
      :open="confirmOpen"
      title="Delete client?"
      :message="`This will permanently delete ${clientToDelete?.name}.`"
      @cancel="confirmOpen = false"
      @confirm="confirmDelete"
    />
  </div>
</template>
