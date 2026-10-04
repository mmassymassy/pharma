<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const colors = {
  request_received: 'bg-slate-100 text-slate-700',
  availability_check: 'bg-amber-100 text-amber-800',
  ordered_from_supplier: 'bg-blue-100 text-blue-800',
  paid: 'bg-indigo-100 text-indigo-800',
  shipped: 'bg-purple-100 text-purple-800',
  delivered: 'bg-emerald-100 text-emerald-800',
  cancelled: 'bg-red-100 text-red-800',
}

const labels = {
  request_received: 'Request received',
  availability_check: 'Availability check',
  ordered_from_supplier: 'Ordered from supplier',
  paid: 'Paid',
  shipped: 'Shipped',
  delivered: 'Delivered',
  cancelled: 'Cancelled',
}

const classes = computed(() => colors[props.status] || 'bg-slate-100 text-slate-700')
const label = computed(() => labels[props.status] || props.status)
</script>

<template>
  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium" :class="classes">
    {{ label }}
  </span>
</template>
