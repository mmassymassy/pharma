<script setup>
import { reactive, watch, ref } from 'vue'
import api from '../api/client'

const props = defineProps({
  open: { type: Boolean, default: false },
  product: { type: Object, default: null },
  suppliers: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'saved'])

const form = reactive({
  supplier_id: '', name: '', reference: '', description: '', category: '',
  price: '', buy_price: '', availability: 'in_stock', is_active: true,
})
const imageFile = ref(null)
const errors = ref({})
const saving = ref(false)

watch(
  () => props.product,
  (p) => {
    errors.value = {}
    imageFile.value = null
    Object.assign(form, {
      supplier_id: p?.supplier_id || '',
      name: p?.name || '',
      reference: p?.reference || '',
      description: p?.description || '',
      category: p?.category || '',
      price: p?.price ?? '',
      buy_price: p?.buy_price ?? '',
      availability: p?.availability || 'in_stock',
      is_active: p ? p.is_active : true,
    })
  },
  { immediate: true },
)

function onFileChange(e) {
  imageFile.value = e.target.files[0] || null
}

async function submit() {
  errors.value = {}
  saving.value = true

  const payload = new FormData()
  Object.entries(form).forEach(([key, value]) => {
    if (value === '' || value === null) return
    payload.append(key, key === 'is_active' ? (value ? '1' : '0') : value)
  })
  if (imageFile.value) payload.append('image', imageFile.value)

  try {
    if (props.product) {
      payload.append('_method', 'PUT')
      await api.post(`/admin/products/${props.product.id}`, payload)
    } else {
      await api.post('/admin/products', payload)
    }
    emit('saved')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
      <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ product ? 'Edit product' : 'Add product' }}</h3>

      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <input v-model="form.name" placeholder="Name" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name[0] }}</p>
        </div>
        <div>
          <input v-model="form.reference" placeholder="Reference" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.reference" class="text-xs text-red-600 mt-1">{{ errors.reference[0] }}</p>
        </div>
        <input v-model="form.category" placeholder="Category" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <select v-model="form.supplier_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
          <option value="">No supplier</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <div>
          <input v-model.number="form.price" type="number" step="0.01" min="0" placeholder="Sell price" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.price" class="text-xs text-red-600 mt-1">{{ errors.price[0] }}</p>
        </div>
        <input v-model.number="form.buy_price" type="number" step="0.01" min="0" placeholder="Buy price (admin only)" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <select v-model="form.availability" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
          <option value="in_stock">In stock</option>
          <option value="on_request">On request</option>
          <option value="unavailable">Unavailable</option>
        </select>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.is_active" type="checkbox" class="rounded" />
          Active (visible on the public site)
        </label>
        <textarea v-model="form.description" placeholder="Description" rows="3" class="sm:col-span-2 border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
        <div class="sm:col-span-2">
          <label class="block text-sm text-slate-600 mb-1">Image</label>
          <input type="file" accept="image/*" class="text-sm" @change="onFileChange" />
          <img v-if="product?.image_url" :src="product.image_url" class="w-20 h-20 object-cover rounded-lg mt-2" />
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" @click="emit('close')">Cancel</button>
        <button :disabled="saving" class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50" @click="submit">
          {{ saving ? 'Saving…' : 'Save' }}
        </button>
      </div>
    </div>
  </div>
</template>
