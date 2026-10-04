<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { useCartStore } from '../stores/cart'

const cart = useCartStore()
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white text-slate-900">
    <header class="border-b border-slate-100 sticky top-0 bg-white/90 backdrop-blur z-40">
      <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <RouterLink to="/" class="font-bold text-xl tracking-tight">Pharma&nbsp;Orders</RouterLink>
        <RouterLink
          to="/order"
          class="relative inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition"
        >
          Cart
          <span
            v-if="cart.count > 0"
            class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-500 text-xs"
          >
            {{ cart.count }}
          </span>
        </RouterLink>
      </div>
    </header>

    <main class="flex-1">
      <RouterView />
    </main>

    <footer class="border-t border-slate-100 mt-16">
      <div class="max-w-6xl mx-auto px-4 py-10 text-sm text-slate-500 flex flex-col sm:flex-row justify-between gap-4">
        <p>&copy; {{ new Date().getFullYear() }} Pharma Orders. All rights reserved.</p>
        <p>Orders confirmed via WhatsApp.</p>
      </div>
    </footer>
  </div>
</template>
