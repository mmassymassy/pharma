<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Are you sure?' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Delete' },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
      <p v-if="message" class="mt-2 text-sm text-slate-600">{{ message }}</p>
      <div class="mt-6 flex justify-end gap-3">
        <button
          class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50"
          @click="emit('cancel')"
        >
          Cancel
        </button>
        <button
          class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700"
          @click="emit('confirm')"
        >
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
