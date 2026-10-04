<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api/client'
import { useCartStore } from '../../stores/cart'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()

const product = ref(null)
const loading = ref(true)
const quantity = ref(1)
const added = ref(false)

const availabilityLabel = {
  in_stock: 'In stock',
  on_request: 'On request',
  unavailable: 'Unavailable',
}

const canOrder = computed(() => product.value?.availability !== 'unavailable')

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/public/products/${route.params.id}`)
    product.value = data.data
  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
}

function addToCart() {
  cart.add(product.value, quantity.value)
  added.value = true
  setTimeout(() => (added.value = false), 2000)
}

function orderNow() {
  cart.add(product.value, quantity.value)
  router.push({ name: 'order' })
}

onMounted(load)
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-12">
    <div v-if="loading" class="text-center text-slate-400 py-24">Loading…</div>
    <div v-else-if="!product" class="text-center text-slate-400 py-24">Product not found.</div>
    <div v-else class="grid sm:grid-cols-2 gap-10">
      <div class="aspect-square bg-slate-50 rounded-xl overflow-hidden flex items-center justify-center">
        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
        <span v-else class="text-slate-300">No image</span>
      </div>
      <div>
        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ product.category }}</p>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ product.name }}</h1>
        <p class="mt-4 text-2xl font-semibold">${{ product.price.toFixed(2) }}</p>
        <span
          class="inline-block mt-2 text-xs px-2 py-1 rounded-full"
          :class="product.availability === 'in_stock' ? 'bg-emerald-100 text-emerald-700' : product.availability === 'on_request' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'"
        >
          {{ availabilityLabel[product.availability] }}
        </span>
        <p class="mt-6 text-slate-600 leading-relaxed">{{ product.description }}</p>

        <div v-if="canOrder" class="mt-8 flex items-center gap-4">
          <div class="flex items-center border border-slate-200 rounded-lg">
            <button class="w-9 h-9 text-lg" @click="quantity = Math.max(1, quantity - 1)">-</button>
            <span class="w-10 text-center">{{ quantity }}</span>
            <button class="w-9 h-9 text-lg" @click="quantity++">+</button>
          </div>
          <button
            class="px-5 py-2.5 rounded-lg border border-slate-900 text-slate-900 font-medium hover:bg-slate-50 transition"
            @click="addToCart"
          >
            {{ added ? 'Added ✓' : 'Add to cart' }}
          </button>
          <button
            class="px-5 py-2.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition"
            @click="orderNow"
          >
            Order this product
          </button>
        </div>
        <p v-else class="mt-8 text-sm text-slate-500">This product is currently unavailable.</p>
      </div>
    </div>
  </div>
</template>
