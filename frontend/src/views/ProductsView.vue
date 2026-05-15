<template>
  <div>
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem;">
      <div>
        <h1>Products</h1>
        <p>{{ auth.isManager ? 'Manage your product catalog' : 'Browse available products' }}</p>
      </div>
      <button v-if="auth.isManager" class="btn btn-primary" @click="openCreate">+ Add Product</button>
    </div>

    <div class="card">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" class="form-input" placeholder="Search by name, SKU or category…" />
      </div>

      <div v-if="store.loading" class="loading-center"><div class="spinner"></div></div>
      <div v-else-if="store.error" class="alert alert-error">{{ store.error }}</div>

      <template v-else>
        <!-- Desktop table -->
        <div class="table-wrap table-desktop">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Category</th>
                <th>SKU</th>
                <th>Cost</th>
                <th>Price</th>
                <th>Stock</th>
                <th v-if="auth.isManager">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filtered.length === 0">
                <td :colspan="auth.isManager ? 7 : 6" style="text-align:center;color:var(--text-muted);padding:2rem;">No products found</td>
              </tr>
              <tr v-for="p in filtered" :key="p.id">
                <td><strong>{{ p.name }}</strong></td>
                <td>{{ p.category }}</td>
                <td><code>{{ p.sku }}</code></td>
                <td>{{ formatCurrency(p.cost_price) }}</td>
                <td class="text-accent fw-600">{{ formatCurrency(p.selling_price) }}</td>
                <td>
                  <span :class="p.stock_quantity === 0 ? 'badge badge-red' : p.stock_quantity < 5 ? 'badge badge-yellow' : 'badge badge-green'">
                    {{ p.stock_quantity }}
                  </span>
                </td>
                <td v-if="auth.isManager">
                  <div style="display:flex; gap:0.5rem;">
                    <button class="btn btn-outline btn-sm" @click="openEdit(p)">Edit</button>
                    <button class="btn btn-danger btn-sm" @click="confirmDelete(p)">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile cards -->
        <div class="mobile-card-list">
          <div v-if="filtered.length === 0" class="empty-state" style="padding:1.5rem;"><p>No products found</p></div>
          <div v-for="p in filtered" :key="p.id" class="mobile-card">
            <div class="mobile-card-header">
              <span class="mch-id">{{ p.name }}</span>
              <span :class="p.stock_quantity === 0 ? 'badge badge-red' : p.stock_quantity < 5 ? 'badge badge-yellow' : 'badge badge-green'">
                {{ p.stock_quantity }} units
              </span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">SKU</span>
              <code>{{ p.sku }}</code>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Category</span>
              <span class="mcr-val">{{ p.category }}</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Cost / Price</span>
              <span class="mcr-val">{{ formatCurrency(p.cost_price) }} / <strong class="text-accent">{{ formatCurrency(p.selling_price) }}</strong> ETB</span>
            </div>
            <div v-if="auth.isManager" style="display:flex; gap:0.5rem; margin-top:0.6rem;">
              <button class="btn btn-outline btn-sm" style="flex:1;" @click="openEdit(p)">Edit</button>
              <button class="btn btn-danger btn-sm" style="flex:1;" @click="confirmDelete(p)">Delete</button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="store.meta && store.meta.last_page > 1"
             style="display:flex; justify-content:center; gap:0.5rem; margin-top:1rem; flex-wrap:wrap;">
          <button class="btn btn-outline btn-sm" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">← Prev</button>
          <span style="padding:0.35rem 0.75rem; font-size:0.85rem; color:var(--text-muted);">{{ currentPage }} / {{ store.meta.last_page }}</span>
          <button class="btn btn-outline btn-sm" :disabled="currentPage === store.meta.last_page" @click="changePage(currentPage + 1)">Next →</button>
        </div>
      </template>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editing ? 'Edit Product' : 'Add Product' }}</h2>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
        <form @submit.prevent="saveProduct">
          <div class="form-grid">
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label">Product Name *</label>
              <input v-model="form.name" class="form-input" required placeholder="e.g. Nike Air Max" />
            </div>
            <div class="form-group">
              <label class="form-label">Category *</label>
              <input v-model="form.category" class="form-input" required placeholder="e.g. Shoes" />
            </div>
            <div class="form-group">
              <label class="form-label">SKU *</label>
              <input v-model="form.sku" class="form-input" required placeholder="e.g. NIKE-001" />
            </div>
            <div class="form-group">
              <label class="form-label">Cost Price (ETB) *</label>
              <input v-model.number="form.cost_price" type="number" step="0.01" min="0" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">Selling Price (ETB) *</label>
              <input v-model.number="form.selling_price" type="number" step="0.01" min="0" class="form-input" required />
            </div>
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label">Stock Quantity</label>
              <input v-model.number="form.stock_quantity" type="number" min="0" class="form-input" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Saving…' : (editing ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Delete Product</h2>
          <button class="modal-close" @click="showDeleteModal = false">✕</button>
        </div>
        <p style="color:var(--text-secondary); font-size:0.9rem;">
          Delete <strong class="text-accent">{{ deleteTarget?.name }}</strong>? This cannot be undone.
        </p>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteModal = false">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doDelete">{{ saving ? 'Deleting…' : 'Delete' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useProductStore } from '../stores/products.js'
import { useAuthStore } from '../stores/auth.js'

const store       = useProductStore()
const auth        = useAuthStore()
const search      = ref('')
const currentPage = ref(1)
const showModal   = ref(false)
const showDeleteModal = ref(false)
const editing     = ref(null)
const deleteTarget = ref(null)
const saving      = ref(false)
const modalError  = ref('')

const emptyForm = () => ({ name:'', category:'', sku:'', cost_price:0, selling_price:0, stock_quantity:0 })
const form = ref(emptyForm())

onMounted(() => store.fetchProducts(1))

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return store.products
  return store.products.filter(p =>
    p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q) || p.category.toLowerCase().includes(q)
  )
})

function changePage(p) { currentPage.value = p; store.fetchProducts(p) }

function openCreate() {
  editing.value = null; form.value = emptyForm(); modalError.value = ''; showModal.value = true
}
function openEdit(p) {
  editing.value = p
  form.value = { name:p.name, category:p.category, sku:p.sku, cost_price:p.cost_price, selling_price:p.selling_price, stock_quantity:p.stock_quantity }
  modalError.value = ''; showModal.value = true
}
function confirmDelete(p) { deleteTarget.value = p; showDeleteModal.value = true }

async function saveProduct() {
  saving.value = true; modalError.value = ''
  try {
    if (editing.value) await store.updateProduct(editing.value.id, form.value)
    else await store.createProduct(form.value)
    showModal.value = false
    store.fetchProducts(currentPage.value)
  } catch (e) {
    const errs = e.response?.data?.errors
    modalError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Failed to save.')
  } finally { saving.value = false }
}

async function doDelete() {
  saving.value = true
  try { await store.deleteProduct(deleteTarget.value.id); showDeleteModal.value = false; store.fetchProducts(currentPage.value) }
  catch { showDeleteModal.value = false }
  finally { saving.value = false }
}

function formatCurrency(v) {
  return Number(v || 0).toLocaleString('en-ET', { minimumFractionDigits:2, maximumFractionDigits:2 })
}
</script>

<style scoped>
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
@media (max-width: 480px) { .form-grid { grid-template-columns: 1fr; } }
</style>
