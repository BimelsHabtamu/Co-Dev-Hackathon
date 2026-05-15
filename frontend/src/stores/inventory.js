import { defineStore } from 'pinia'
import api from '../api/axios.js'

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    items: [],
    meta: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchInventory(page = 1) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get(`/inventory?page=${page}`)
        this.items = data.data
        this.meta = data.meta || { current_page: data.current_page, last_page: data.last_page, total: data.total }
      } catch (e) {
        this.error = e.response?.data?.message || 'Failed to load inventory.'
      } finally {
        this.loading = false
      }
    },

    async stockIn(productId, quantity) {
      const { data } = await api.post(`/inventory/${productId}/stock-in`, { quantity })
      return data
    }
  }
})
