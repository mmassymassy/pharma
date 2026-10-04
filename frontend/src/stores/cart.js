import { defineStore } from 'pinia'

const STORAGE_KEY = 'pharma-cart'

function loadFromStorage() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function saveToStorage(items) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items))
  } catch {
    // ignore storage errors (private mode, quota, etc.)
  }
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: loadFromStorage(),
  }),
  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    total: (state) => state.items.reduce((sum, item) => sum + item.price * item.quantity, 0),
  },
  actions: {
    add(product, quantity = 1) {
      const existing = this.items.find((item) => item.id === product.id)
      if (existing) {
        existing.quantity += quantity
      } else {
        this.items.push({
          id: product.id,
          name: product.name,
          price: product.price,
          image_url: product.image_url,
          quantity,
        })
      }
      this.persist()
    },
    updateQuantity(productId, quantity) {
      const item = this.items.find((item) => item.id === productId)
      if (!item) return
      if (quantity <= 0) {
        this.remove(productId)
        return
      }
      item.quantity = quantity
      this.persist()
    },
    remove(productId) {
      this.items = this.items.filter((item) => item.id !== productId)
      this.persist()
    },
    clear() {
      this.items = []
      this.persist()
    },
    persist() {
      saveToStorage(this.items)
    },
  },
})
