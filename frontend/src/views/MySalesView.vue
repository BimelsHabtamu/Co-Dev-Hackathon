<template>
  <div>
    <div class="page-header">
      <h1>{{ auth.isManager ? 'All Sales' : 'My Sales' }}</h1>
      <p>{{ auth.isManager ? 'View and manage all transactions' : 'Your personal sales history' }}</p>
    </div>

    <div v-if="auth.isManager && pendingSales.length > 0"
         class="alert alert-warning"
         style="margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
      <span>⚠ <strong>{{ pendingSales.length }}</strong> sale{{ pendingSales.length > 1 ? 's' : '' }} pending approval</span>
      <button class="btn btn-sm btn-primary" @click="statusFilter = 'pending_approval'">Show Pending</button>
    </div>

    <div class="card">
      <!-- Filters -->
      <div style="display:flex; gap:0.75rem; margin-bottom:1rem; flex-wrap:wrap;">
        <div class="search-wrap" style="flex:1; min-width:180px; margin-bottom:0;">
          <span class="search-icon">🔍</span>
          <input v-model="search" class="form-input" placeholder="Search by ID or cashier…" />
        </div>
        <select v-model="statusFilter" class="form-input" style="width:170px; flex-shrink:0;">
          <option value="">All Statuses</option>
          <option value="completed">Completed</option>
          <option value="pending_approval">Pending Approval</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <div v-if="store.loading" class="loading-center"><div class="spinner"></div></div>
      <div v-else-if="store.error" class="alert alert-error">{{ store.error }}</div>

      <template v-else>
        <!-- Desktop table -->
        <div class="table-wrap table-desktop">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th v-if="auth.isManager">Cashier</th>
                <th>Items</th>
                <th>Subtotal</th>
                <th>Disc.</th>
                <th>VAT</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filtered.length === 0">
                <td :colspan="auth.isManager ? 10 : 9" style="text-align:center;color:var(--text-muted);padding:2rem;">No sales found</td>
              </tr>
              <tr v-for="s in filtered" :key="s.id">
                <td style="color:var(--text-muted);">#{{ s.id }}</td>
                <td v-if="auth.isManager">{{ s.user?.name || '—' }}</td>
                <td>{{ s.items?.length || 0 }}</td>
                <td>{{ formatCurrency(s.subtotal) }}</td>
                <td>{{ s.discount }}%</td>
                <td>{{ formatCurrency(s.vat_amount) }}</td>
                <td><strong class="text-accent">{{ formatCurrency(s.total) }}</strong></td>
                <td><span :class="statusBadge(s.status)">{{ s.status?.replace('_',' ') }}</span></td>
                <td style="color:var(--text-muted); white-space:nowrap;">{{ formatDate(s.created_at) }}</td>
                <td><button class="btn btn-outline btn-sm" @click="viewSale(s)">View</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mobile-card-list">
          <div v-if="filtered.length === 0" class="empty-state" style="padding:1.5rem;">
            <p>No sales found</p>
          </div>
          <div v-for="s in filtered" :key="s.id" class="mobile-card">
            <div class="mobile-card-header">
              <span class="mch-id">#{{ s.id }}</span>
              <span class="mch-total">{{ formatCurrency(s.total) }} ETB</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Status</span>
              <span :class="statusBadge(s.status)">{{ s.status?.replace('_',' ') }}</span>
            </div>
            <div v-if="auth.isManager" class="mobile-card-row">
              <span class="mcr-label">Cashier</span>
              <span class="mcr-val">{{ s.user?.name || '—' }}</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Items / Discount / VAT</span>
              <span class="mcr-val">{{ s.items?.length || 0 }} · {{ s.discount }}% · {{ formatCurrency(s.vat_amount) }}</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Date</span>
              <span class="mcr-val">{{ formatDate(s.created_at) }}</span>
            </div>
            <button class="btn btn-outline btn-sm btn-full" style="margin-top:0.6rem;" @click="viewSale(s)">View Details</button>
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

    <div v-if="showDetail" class="modal-overlay" @click.self="showDetail = false">
      <div class="modal" style="max-width:560px;">
        <div class="modal-header">
          <h2>Sale #{{ selectedSale?.id }}</h2>
          <button class="modal-close" @click="showDetail = false">✕</button>
        </div>

        <div v-if="detailLoading" class="loading-center"><div class="spinner"></div></div>

        <template v-else-if="saleDetail">
          <div style="display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
            <span :class="statusBadge(saleDetail.status)">{{ saleDetail.status?.replace('_',' ') }}</span>
            <span class="badge badge-gray">{{ formatDate(saleDetail.created_at) }}</span>
            <span class="badge badge-gray">By: {{ saleDetail.user?.name }}</span>
          </div>

          <div class="table-wrap" style="margin-bottom:1rem;">
            <table>
              <thead>
                <tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
              </thead>
              <tbody>
                <tr v-for="item in saleDetail.items" :key="item.id">
                  <td>{{ item.product?.name }}</td>
                  <td><code>{{ item.product?.sku }}</code></td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ formatCurrency(item.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div style="background:var(--bg-elevated); border-radius:8px; padding:0.85rem; font-size:0.875rem; border:1px solid var(--border);">
            <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
              <span style="color:var(--text-secondary);">Subtotal</span><span style="color:var(--text-primary);">{{ formatCurrency(saleDetail.subtotal) }} ETB</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem; color:var(--text-muted);">
              <span>Discount ({{ saleDetail.discount }}%)</span><span>− {{ formatCurrency(saleDetail.discount_amount) }} ETB</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem; color:var(--text-muted);">
              <span>VAT (15%)</span><span>{{ formatCurrency(saleDetail.vat_amount) }} ETB</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1rem; padding-top:0.5rem; border-top:1px solid var(--border); color:var(--accent-dark);">
              <span>Total</span><span>{{ formatCurrency(saleDetail.total) }} ETB</span>
            </div>
          </div>

          <div v-if="saleDetail.note" style="margin-top:0.75rem; font-size:0.85rem; color:var(--text-muted); background:var(--bg-elevated); padding:0.6rem 0.75rem; border-radius:6px; border:1px solid var(--border);">
            &#128221; {{ saleDetail.note }}
          </div>
        </template>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDetail = false">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSaleStore } from '../stores/sales.js'
import { useAuthStore } from '../stores/auth.js'

const store         = useSaleStore()
const auth          = useAuthStore()
const search        = ref('')
const statusFilter  = ref('')
const currentPage   = ref(1)
const showDetail    = ref(false)
const selectedSale  = ref(null)
const saleDetail    = ref(null)
const detailLoading = ref(false)

onMounted(() => store.fetchSales(1))

const pendingSales = computed(() => store.sales.filter(s => s.status === 'pending_approval'))

const filtered = computed(() => {
  let list = store.sales
  if (statusFilter.value) list = list.filter(s => s.status === statusFilter.value)
  const q = search.value.toLowerCase()
  if (q) list = list.filter(s => String(s.id).includes(q) || s.user?.name?.toLowerCase().includes(q))
  return list
})

function changePage(p) { currentPage.value = p; store.fetchSales(p) }

async function viewSale(s) {
  selectedSale.value  = s
  showDetail.value    = true
  detailLoading.value = true
  saleDetail.value    = null
  try { saleDetail.value = await store.getSale(s.id) } catch {}
  detailLoading.value = false
}

function formatCurrency(v) {
  return Number(v || 0).toLocaleString('en-ET', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('en-ET', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '—'
}
function statusBadge(status) {
  const map = { completed:'badge badge-green', pending_approval:'badge badge-amber', approved:'badge badge-blue', rejected:'badge badge-red' }
  return map[status] || 'badge badge-gray'
}
</script>
