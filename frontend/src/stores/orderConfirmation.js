import { defineStore } from 'pinia'

export const useOrderConfirmationStore = defineStore('orderConfirmation', {
  state: () => ({
    reference: null,
    whatsappUrl: null,
    total: null,
  }),
  actions: {
    set(reference, whatsappUrl, total) {
      this.reference = reference
      this.whatsappUrl = whatsappUrl
      this.total = total
    },
  },
})
