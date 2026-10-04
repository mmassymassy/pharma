<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  product: { type: Object, required: true },
})

const availabilityLabel = {
  in_stock: 'In stock',
  on_request: 'On request',
  unavailable: 'Unavailable',
}
</script>

<template>
  <RouterLink
    :to="{ name: 'product-detail', params: { id: product.id } }"
    class="group border border-slate-100 rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col"
  >
    <div class="aspect-square bg-slate-50 overflow-hidden flex items-center justify-center">
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition"
      />
      <span v-else class="text-slate-300 text-sm">No image</span>
    </div>
    <div class="p-4 flex-1 flex flex-col">
      <p class="text-xs text-slate-400 uppercase tracking-wide">{{ product.category }}</p>
      <h3 class="font-medium text-slate-900 mt-1 line-clamp-2">{{ product.name }}</h3>
      <div class="mt-auto pt-3 flex items-center justify-between">
        <span class="font-semibold">${{ product.price.toFixed(2) }}</span>
        <span
          class="text-xs px-2 py-1 rounded-full"
          :class="product.availability === 'in_stock' ? 'bg-emerald-100 text-emerald-700' : product.availability === 'on_request' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'"
        >
          {{ availabilityLabel[product.availability] }}
        </span>
      </div>
    </div>
  </RouterLink>
</template>
