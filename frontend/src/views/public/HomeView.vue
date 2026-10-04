<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../api/client'
import ProductCard from '../../components/ProductCard.vue'

const products = ref([])
const categories = ref([])
const search = ref('')
const category = ref('')
const loading = ref(true)

async function loadProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/public/products', {
      params: { search: search.value || undefined, category: category.value || undefined, per_page: 24 },
    })
    products.value = data.data
    categories.value = [...new Set(products.value.map((p) => p.category).filter(Boolean))]
  } finally {
    loading.value = false
  }
}

let debounceTimer
watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(loadProducts, 300)
})
watch(category, loadProducts)

onMounted(loadProducts)
</script>

<template>
  <section class="max-w-6xl mx-auto px-4 pt-16 pb-20 text-center">
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-slate-900">
      Order your favorite products,<br class="hidden sm:block" /> confirmed instantly on WhatsApp
    </h1>
    <p class="mt-5 text-slate-600 max-w-xl mx-auto">
      Browse our catalog, place your order, and confirm delivery details directly with us on WhatsApp.
      No account needed.
    </p>
    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
      <a href="#products" class="px-6 py-3 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition">
        Browse products
      </a>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-4 pb-20">
    <h2 class="text-center text-2xl font-semibold text-slate-900 mb-10">How it works</h2>
    <div class="grid sm:grid-cols-3 gap-8">
      <div class="text-center">
        <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center mx-auto font-semibold">1</div>
        <h3 class="mt-4 font-medium">Pick your products</h3>
        <p class="mt-2 text-sm text-slate-500">Browse the catalog and add what you need to your cart.</p>
      </div>
      <div class="text-center">
        <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center mx-auto font-semibold">2</div>
        <h3 class="mt-4 font-medium">Send your delivery info</h3>
        <p class="mt-2 text-sm text-slate-500">Fill in your name, phone and address in a simple form.</p>
      </div>
      <div class="text-center">
        <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center mx-auto font-semibold">3</div>
        <h3 class="mt-4 font-medium">Confirm on WhatsApp</h3>
        <p class="mt-2 text-sm text-slate-500">Tap one button to confirm your order in WhatsApp chat.</p>
      </div>
    </div>
  </section>

  <section id="products" class="max-w-6xl mx-auto px-4 pb-24 scroll-mt-20">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <h2 class="text-2xl font-semibold text-slate-900">Our products</h2>
      <div class="flex gap-3">
        <input
          v-model="search"
          type="search"
          placeholder="Search products…"
          class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-48 focus:outline-none focus:ring-2 focus:ring-slate-900"
        />
        <select
          v-model="category"
          class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
        >
          <option value="">All categories</option>
          <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-center text-slate-400 py-16">Loading products…</div>
    <div v-else-if="products.length === 0" class="text-center text-slate-400 py-16">No products found.</div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
      <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
  </section>
</template>
