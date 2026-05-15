<template>
  <div>
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem;">
      <div>
        <h1>Inventory</h1>
        <p>{{ auth.isManager ? 'Monitor and manage stock levels' : 'View current stock levels' }}</p>
      </div>
      <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
        <span class="badge badge-red">{{ outOfStockCount }} Out of Stock</span>
        <span class="badge badge-yellow">{{ lowStockCount }} Low Stock</span>
        <span class="badge badge-green">{{ inStockCount }} In Stock</span>
      </div>
    </div>

    <div class="card">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" class="form-input" placeholder="Search by name, SKU or category…" />
      </div>

      <div v-if="store.loading" class="loading-center"><div class="spinner"></div></div>
      <div v-else-if="store.error" class="alert alert-error">{{ store.error }}</div>

      <template v-else>
        <div class="table-wrap table-desktop">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>SKU</th>
                <th>Stock</th>
                <th>Status</th>
                <th v-if="auth.isManager">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filtered.length === 0">
                <td :colspan="auth.isManager ? 6 : 5" style="text-align:center;color:var(--text-muted);padding:2rem;">No items found</td>
              </tr>
              <tr v-for="item in filtered" :key="item.id" :class="{ 'row-danger': item.stock_quantity === 0, 'row-warn': item.stock_quantity > 0 && item.stock_quantity < 5 }">
                <td><strong>{{ item.name }}</strong></td>
                <td>{{ item.category }}</td>
                <td><code>{{ item.sku }}</code></td>
                <td><strong :style="item.stock_quantity === 0 ? 'color:var(--red)' : item.stock_quantity < 5 ? 'color:var(--yellow)' : ''">{{ item.stock_quantity }}</strong></td>
                <td>
                  <span v-if="item.stock_quantity === 0" class="badge badge-red">Out of Stock</span>
                  <span v-else-if="item.stock_quantity < 5" class="badge badge-yellow">Low Stock</span>
                  <span v-else class="badge badge-green">In Stock</span>
                </td>
                <td v-if="auth.isManager">
                  <button class="btn btn-primary btn-sm" @click="openStockIn(item)">+ Stock In</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mobile-card-list">
          <div v-if="filtered.length === 0" class="empty-state" style="padding:1.5rem;"><p>No items found</p></div>
          <div v-for="item in filtered" :key="item.id" class="mobile-card"
               :style="item.stock_quantity === 0 ? 'border-left:3px solid var(--red)' : item.stock_quantity < 5 ? 'border-left:3px solid var(--yellow)' : ''">
            <div class="mobile-card-header">
              <span class="mch-id">{{ item.name }}</span>
              <span v-if="item.stock_quantity === 0" class="badge badge-red">Out of Stock</span>
              <span v-else-if="item.stock_quantity < 5" class="badge badge-yellow">Low Stock</span>
              <span v-else class="badge badge-green">In Stock</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">SKU</span>
              <code>{{ item.sku }}</code>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Category</span>
              <span class="mcr-val">{{ item.category }}</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Stock Quantity</span>
              <strong :style="item.stock_quantity === 0 ? 'color:var(--red)' : item.stock_quantity < 5 ? 'color:var(--yellow)' : 'color:var(--green)'">
                {{ item.stock_quantity }} units
              </strong>
            </div>
            <button v-if="auth.isManager" class="btn btn-primary btn-sm btn-full" style="margin-top:0.6rem;" @click="openStockIn(item)">
              + Stock In
            </button>
          </div>
        </div>

        <div v-if="store.meta && store.meta.last_page > 1"
             style="display:flex; justify-content:center; gap:0.5rem; margin-top:1rem; flex-wrap:wrap;">
          <button class="btn btn-outline btn-sm" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">← Prev</button>
          <span style="padding:0.35rem 0.75rem; font-size:0.85rem; color:var(--text-muted);">{{ currentPage }} / {{ store.meta.last_page }}</span>
          <button class="btn btn-outline btn-sm" :disabled="currentPage === store.meta.last_page" @click="changePage(currentPage + 1)">Next →</button>
        </div>
      </template>
    </div>
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Add Stock</h2>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
        <div style="background:var(--bg-elevated); border-radius:8px; padding:0.75rem; margin-bottom:1rem; font-size:0.875rem; border:1px solid var(--border);">
          <div style="font-weight:600; color:var(--text-primary); margin-bottom:0.25rem;">{{ selectedItem?.name }}</div>
          <div style="color:var(--text-muted);">
            Current stock:
            <strong :style="selectedItem?.stock_quantity === 0 ? 'color:var(--red)' : selectedItem?.stock_quantity < 5 ? 'color:var(--yellow)' : 'color:var(--green)'">
              {{ selectedItem?.stock_quantity }} units
            </strong>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Quantity to Add *</label>
          <input v-model.number="stockQty" type="number" min="1" class="form-input" placeholder="e.g. 50" />
        </div>
        <div v-if="stockQty > 0" style="font-size:0.8rem; color:var(--text-muted); margin-top:-0.5rem; margin-bottom:0.75rem;">
          New total will be: <strong class="text-accent">{{ (selectedItem?.stock_quantity || 0) + stockQty }} units</strong>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showModal = false">Cancel</button>
          <button class="btn btn-primary" :disabled="saving || stockQty < 1" @click="doStockIn">
            {{ saving ? 'Adding…' : `Add ${stockQty} Units` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInventoryStore } from '../stores/inventory.js'
import { useAuthStore } from '../stores/auth.js'

const store        = useInventoryStore()
const auth         = useAuthStore()
const search       = ref('')
const currentPage  = ref(1)
const showModal    = ref(false)
const selectedItem = ref(null)
const stockQty     = ref(1)
const saving       = ref(false)
const modalError   = ref('')

onMounted(() => store.fetchInventory(1))

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return store.items
  return store.items.filter(i =>
    i.name.toLowerCase().includes(q) || i.sku.toLowerCase().includes(q) || i.category.toLowerCase().includes(q)
  )
})

const outOfStockCount = computed(() => store.items.filter(i => i.stock_quantity === 0).length)
const lowStockCount   = computed(() => store.items.filter(i => i.stock_quantity > 0 && i.stock_quantity < 5).length)
const inStockCount    = computed(() => store.items.filter(i => i.stock_quantity >= 5).length)

function changePage(p) { currentPage.value = p; store.fetchInventory(p) }

function openStockIn(item) {
  selectedItem.value = item; stockQty.value = 1; modalError.value = ''; showModal.value = true
}

async function doStockIn() {
  if (stockQty.value < 1) return
  saving.value = true; modalError.value = ''
  try {
    await store.stockIn(selectedItem.value.id, stockQty.value)
    showModal.value = false
    store.fetchInventory(currentPage.value)
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Failed to add stock.'
  } finally { saving.value = false }
}
</script>

<style scoped>
.row-danger td { background: rgba(239,68,68,0.04); }
.row-warn td   { background: rgba(234,179,8,0.04); }
</style>
