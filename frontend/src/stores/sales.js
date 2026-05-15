import { defineStore } from 'pinia'
import api from '../api/axios.js'

export const useSaleStore = defineStore('sales', {
  state: () => ({
    sales: [],
    meta: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchSales(page = 1) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get(`/sales?page=${page}`)
        this.sales = data.data
        this.meta = data.meta || { current_page: data.current_page, last_page: data.last_page, total: data.total }
      } catch (e) {
        this.error = e.response?.data?.message || 'Failed to load sales.'
      } finally {
        this.loading = false
      }
    },

    async createSale(payload) {
      const { data } = await api.post('/sales', payload)
      return data
    },

    async getSale(id) {
      const res = await api.get(`/sales/${id}`)
      // backend returns the sale object directly
      return res.data.data ?? res.data
    }
  }
})
