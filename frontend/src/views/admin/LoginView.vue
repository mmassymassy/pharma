<script setup>
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({ email: 'admin@example.com', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.email, form.password)
    router.push(route.query.redirect || { name: 'admin-dashboard' })
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <form class="w-full max-w-sm bg-white rounded-xl shadow-sm border border-slate-100 p-8" @submit.prevent="submit">
      <h1 class="text-xl font-bold text-slate-900 mb-1">Admin login</h1>
      <p class="text-sm text-slate-500 mb-6">Sign in to manage orders, clients and products.</p>

      <p v-if="error" class="mb-4 text-sm text-red-600">{{ error }}</p>

      <div class="mb-4">
        <label class="block text-sm text-slate-600 mb-1">Email</label>
        <input v-model="form.email" type="email" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
      </div>
      <div class="mb-6">
        <label class="block text-sm text-slate-600 mb-1">Password</label>
        <input v-model="form.password" type="password" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full px-4 py-2.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition disabled:opacity-50"
      >
        {{ loading ? 'Signing in…' : 'Sign in' }}
      </button>
    </form>
  </div>
</template>
