<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

const links = [
  { name: 'admin-dashboard', label: 'Dashboard' },
  { name: 'admin-orders', label: 'Orders' },
  { name: 'admin-clients', label: 'Clients' },
  { name: 'admin-suppliers', label: 'Suppliers' },
  { name: 'admin-products', label: 'Products' },
]

async function logout() {
  await auth.logout()
  router.push({ name: 'admin-login' })
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50">
    <aside
      class="w-64 bg-slate-900 text-slate-200 shrink-0 fixed inset-y-0 left-0 z-30 transform transition-transform lg:translate-x-0"
      :class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="px-6 py-5 font-bold text-white text-lg border-b border-slate-800">Pharma Orders</div>
      <nav class="p-3 space-y-1">
        <RouterLink
          v-for="link in links"
          :key="link.name"
          :to="{ name: link.name }"
          class="block px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition"
          active-class="bg-slate-800 text-white"
          @click="menuOpen = false"
        >
          {{ link.label }}
        </RouterLink>
      </nav>
      <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-slate-800">
        <p class="px-3 text-xs text-slate-400 truncate">{{ auth.user?.email }}</p>
        <button
          class="w-full mt-2 px-3 py-2 rounded-lg text-sm font-medium text-left hover:bg-slate-800 transition"
          @click="logout"
        >
          Log out
        </button>
      </div>
    </aside>

    <div class="flex-1 lg:ml-64">
      <header class="lg:hidden flex items-center justify-between px-4 py-3 bg-white border-b border-slate-100 sticky top-0 z-20">
        <span class="font-bold">Pharma Orders</span>
        <button class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm" @click="menuOpen = !menuOpen">Menu</button>
      </header>
      <main class="p-4 sm:p-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>
