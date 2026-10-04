<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import api from '../../api/client'
import { useCartStore } from '../../stores/cart'
import { useOrderConfirmationStore } from '../../stores/orderConfirmation'

const cart = useCartStore()
const router = useRouter()
const confirmation = useOrderConfirmationStore()

const form = reactive({
  name: '',
  phone: '',
  country: '',
  city: '',
  address: '',
  notes: '',
})

const errors = ref({})
const submitting = ref(false)
const generalError = ref('')

async function submit() {
  errors.value = {}
  generalError.value = ''

  if (cart.items.length === 0) {
    generalError.value = 'Your cart is empty.'
    return
  }

  submitting.value = true
  try {
    const { data } = await api.post('/public/orders', {
      ...form,
      items: cart.items.map((item) => ({ product_id: item.id, quantity: item.quantity })),
    })
    confirmation.set(data.reference, data.whatsapp_url, data.total)
    cart.clear()
    router.push({ name: 'order-confirmed', params: { reference: data.reference } })
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      generalError.value = 'Something went wrong. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-slate-900 mb-8">Your order</h1>

    <div v-if="cart.items.length === 0" class="text-center py-16 text-slate-500">
      Your cart is empty. <RouterLink to="/" class="underline">Browse products</RouterLink>
    </div>

    <div v-else class="grid sm:grid-cols-5 gap-10">
      <div class="sm:col-span-3">
        <div class="space-y-4 mb-10">
          <div v-for="item in cart.items" :key="item.id" class="flex items-center gap-4 border-b border-slate-100 pb-4">
            <div class="w-16 h-16 bg-slate-50 rounded-lg overflow-hidden flex items-center justify-center shrink-0">
              <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-slate-900 truncate">{{ item.name }}</p>
              <p class="text-sm text-slate-500">${{ item.price.toFixed(2) }} each</p>
            </div>
            <div class="flex items-center border border-slate-200 rounded-lg">
              <button class="w-8 h-8" @click="cart.updateQuantity(item.id, item.quantity - 1)">-</button>
              <span class="w-8 text-center text-sm">{{ item.quantity }}</span>
              <button class="w-8 h-8" @click="cart.updateQuantity(item.id, item.quantity + 1)">+</button>
            </div>
            <p class="w-20 text-right font-medium">${{ (item.price * item.quantity).toFixed(2) }}</p>
            <button class="text-slate-400 hover:text-red-500" @click="cart.remove(item.id)">✕</button>
          </div>
        </div>
        <div class="flex justify-between text-lg font-semibold">
          <span>Total</span>
          <span>${{ cart.total.toFixed(2) }}</span>
        </div>
      </div>

      <form class="sm:col-span-2 space-y-4" @submit.prevent="submit">
        <h2 class="font-semibold text-slate-900">Delivery details</h2>

        <p v-if="generalError" class="text-sm text-red-600">{{ generalError }}</p>

        <div>
          <label class="block text-sm text-slate-600 mb-1">Full name</label>
          <input v-model="form.name" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" required />
          <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name[0] }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-600 mb-1">Phone / WhatsApp</label>
          <input v-model="form.phone" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" required />
          <p v-if="errors.phone" class="text-xs text-red-600 mt-1">{{ errors.phone[0] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Country</label>
            <input v-model="form.country" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">City</label>
            <input v-model="form.city" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm text-slate-600 mb-1">Address</label>
          <input v-model="form.address" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" required />
          <p v-if="errors.address" class="text-xs text-red-600 mt-1">{{ errors.address[0] }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-600 mb-1">Notes (optional)</label>
          <textarea v-model="form.notes" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
        </div>

        <button
          type="submit"
          :disabled="submitting"
          class="w-full px-5 py-3 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition disabled:opacity-50"
        >
          {{ submitting ? 'Placing order…' : 'Place order' }}
        </button>
      </form>
    </div>
  </div>
</template>
