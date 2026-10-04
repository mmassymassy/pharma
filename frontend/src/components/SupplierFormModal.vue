<script setup>
import { reactive, watch, ref } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  supplier: { type: Object, default: null },
})
const emit = defineEmits(['close', 'submit'])

const form = reactive({ name: '', contact_name: '', phone: '', email: '', country: '', lead_time_days: '', notes: '' })
const errors = ref({})

watch(
  () => props.supplier,
  (s) => {
    errors.value = {}
    Object.assign(form, {
      name: s?.name || '',
      contact_name: s?.contact_name || '',
      phone: s?.phone || '',
      email: s?.email || '',
      country: s?.country || '',
      lead_time_days: s?.lead_time_days ?? '',
      notes: s?.notes || '',
    })
  },
  { immediate: true },
)

function submit() {
  emit('submit', { ...form }, (validationErrors) => { errors.value = validationErrors })
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
      <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ supplier ? 'Edit supplier' : 'Add supplier' }}</h3>

      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <input v-model="form.name" placeholder="Company name" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name[0] }}</p>
        </div>
        <input v-model="form.contact_name" placeholder="Contact name" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.phone" placeholder="Phone" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.email" placeholder="Email" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.country" placeholder="Country" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <input v-model.number="form.lead_time_days" type="number" min="0" placeholder="Lead time (days)" class="border border-slate-200 rounded-lg px-3 py-2 text-sm" />
        <textarea v-model="form.notes" placeholder="Notes" rows="2" class="sm:col-span-2 border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" @click="emit('close')">Cancel</button>
        <button class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800" @click="submit">Save</button>
      </div>
    </div>
  </div>
</template>
