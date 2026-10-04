<script setup>
import { RouterLink, useRoute } from 'vue-router'
import { useOrderConfirmationStore } from '../../stores/orderConfirmation'

const route = useRoute()
const confirmation = useOrderConfirmationStore()
</script>

<template>
  <div class="max-w-lg mx-auto px-4 py-20 text-center">
    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-3xl">
      ✓
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mt-6">Order received!</h1>
    <p class="mt-3 text-slate-600">
      Your order reference is
      <span class="font-mono font-semibold text-slate-900">{{ route.params.reference }}</span>.
    </p>
    <p v-if="confirmation.total" class="mt-1 text-slate-500 text-sm">Total: ${{ confirmation.total.toFixed(2) }}</p>

    <p class="mt-6 text-slate-600 text-sm">
      Tap the button below to confirm your order with us on WhatsApp — we'll take it from there.
    </p>

    <a
      v-if="confirmation.whatsappUrl"
      :href="confirmation.whatsappUrl"
      target="_blank"
      rel="noopener"
      class="mt-8 inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-green-600 text-white font-semibold text-lg hover:bg-green-700 transition"
    >
      <svg viewBox="0 0 32 32" class="w-6 h-6 fill-current"><path d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.63 4.62 1.735 6.555L4 29l7.62-1.702A11.94 11.94 0 0016.001 27C22.628 27 28 21.627 28 15S22.628 3 16.001 3zm0 21.75c-1.972 0-3.83-.54-5.418-1.48l-.389-.23-4.52 1.01 1.05-4.402-.253-.402A9.71 9.71 0 016.25 15c0-5.376 4.375-9.75 9.751-9.75S25.75 9.624 25.75 15 21.377 24.75 16.001 24.75zm5.344-7.302c-.293-.147-1.734-.856-2.003-.954-.269-.098-.465-.147-.66.147-.196.293-.758.954-.929 1.15-.171.196-.343.22-.636.073-.293-.147-1.235-.455-2.353-1.452-.87-.776-1.457-1.734-1.628-2.027-.171-.293-.018-.451.129-.598.132-.132.293-.343.44-.514.147-.171.196-.293.293-.489.098-.196.049-.367-.024-.514-.073-.147-.66-1.591-.905-2.18-.238-.573-.48-.496-.66-.505l-.562-.01c-.196 0-.514.073-.783.367-.269.293-1.027 1.004-1.027 2.448 0 1.444 1.052 2.84 1.198 3.036.147.196 2.07 3.16 5.017 4.43.701.302 1.248.483 1.674.618.703.224 1.343.192 1.849.117.564-.084 1.734-.709 1.978-1.394.245-.685.245-1.272.171-1.394-.073-.122-.269-.196-.562-.343z"/></svg>
      Confirm on WhatsApp
    </a>
    <p v-else class="mt-8 text-sm text-amber-600">
      Open this page from the order confirmation step to get your WhatsApp confirmation link.
    </p>

    <p class="mt-10">
      <RouterLink to="/" class="text-sm text-slate-500 underline">Back to home</RouterLink>
    </p>
  </div>
</template>
