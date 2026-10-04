<script setup>
import { reactive, watch, ref } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  client: { type: Object, default: null },
})
const emit = defineEmits(['close', 'submit'])

const form = reactive({
  name: '', phone: '', whatsapp: '', country: '', city: '', address: '', notes: '', source: 'manual',
})
const errors = ref({})

watch(
  () => props.client,
  (client) => {
    errors.value = {}
    Object.assign(form, {
      name: client?.name || '',
      phone: client?.phone || '',
      whatsapp: client?.whatsapp || '',
      country: client?.country || '',
      city: client?.city || '',
      address: client?.address || '',
      notes: client?.notes || '',
      source: client?.source || 'manual',
    })
  },
  { immediate: true },
)

function submit() {
  emit('submit', { ...form }, (validationErrors) => {
    errors.value = validationErrors
  })
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
      <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ client ? 'Edit client' : 'Add client' }}</h3>

      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <input v-model="form.name" placeholder="Name" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name[0] }}</p>
        </div>
        <div>
          <input v-model="form.phone" placeholder="Phone" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.phone" class="text-xs text-red-600 mt-1">{{ errors.phone[0] }}</p>
        </div>
        <input v-model="form.whatsapp" placeholder="WhatsApp (optional)" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <select v-model="form.source" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
          <option value="manual">Manual</option>
          <option value="whatsapp">WhatsApp</option>
          <option value="website">Website</option>
        </select>
        <input v-model="form.country" placeholder="Country" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.city" placeholder="City" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.address" placeholder="Address" class="sm:col-span-2 border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <textarea v-model="form.notes" placeholder="Notes" rows="2" class="sm:col-span-2 border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" @click="emit('close')">
          Cancel
        </button>
        <button class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800" @click="submit">
          Save
        </button>
      </div>
    </div>
  </div>
</template>
