import { defineStore } from 'pinia'
import api from '../api/axios.js'

export const useProductStore = defineStore('products', {
  state: () => ({
    products: [],
    meta: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProducts(page = 1) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get(`/products?page=${page}`)
        this.products = data.data
        this.meta = data.meta || { current_page: data.current_page, last_page: data.last_page, total: data.total }
      } catch (e) {
        this.error = e.response?.data?.message || 'Failed to load products.'
      } finally {
        this.loading = false
      }
    },

    async createProduct(payload) {
      const { data } = await api.post('/products', payload)
      return data
    },

    async updateProduct(id, payload) {
      const { data } = await api.put(`/products/${id}`, payload)
      return data
    },

    async deleteProduct(id) {
      await api.delete(`/products/${id}`)
    }
  }
})
