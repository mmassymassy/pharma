<script setup>
import { useToastStore } from '../stores/toast'

const toastStore = useToastStore()
</script>

<template>
  <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-80 max-w-[90vw]">
    <transition-group name="toast">
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="rounded-lg px-4 py-3 shadow-lg text-sm text-white flex items-start justify-between gap-3"
        :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
      >
        <span>{{ toast.message }}</span>
        <button class="opacity-80 hover:opacity-100" @click="toastStore.dismiss(toast.id)">✕</button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
