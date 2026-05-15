<template>
  <div>
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem;">
      <div>
        <h1>New Sale</h1>
        <p>{{ filteredProducts.length }} products available</p>
      </div>
      <button class="btn btn-primary show-mobile" style="display:none;" @click="showCart = !showCart">
        🛒 Cart ({{ cart.length }}) · {{ formatCurrency(total) }} ETB
      </button>
    </div>

    <div class="pos-layout">
      <div class="pos-products" :class="{ 'mobile-hidden': showCart }">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input v-model="search" class="form-input" placeholder="Search by name or SKU…" />
        </div>

        <div v-if="loadingProducts" class="loading-center"><div class="spinner"></div></div>

        <div v-else-if="filteredProducts.length === 0" class="empty-state">
          <div class="empty-icon">📦</div>
          <p>No products found</p>
        </div>

        <div v-else class="product-list">
          <div
            v-for="p in filteredProducts"
            :key="p.id"
            class="product-row"
            :class="{ 'out-of-stock': p.stock_quantity === 0 }"
            @click="p.stock_quantity > 0 && addToCart(p)"
          >
            <div class="product-info">
              <div class="product-name">{{ p.name }}</div>
              <div class="product-meta">
                <code style="font-size:0.7rem;">{{ p.sku }}</code>
                · Stock:
                <span :style="p.stock_quantity < 5 ? 'color:var(--red)' : 'color:var(--green)'">
                  {{ p.stock_quantity }}
                </span>
              </div>
            </div>
            <div class="product-right">
              <span class="product-price">{{ formatCurrency(p.selling_price) }}</span>
              <button
                class="add-btn"
                :disabled="p.stock_quantity === 0"
                @click.stop="addToCart(p)"
              >+</button>
            </div>
          </div>
        </div>
      </div>

      <div class="pos-cart" :class="{ 'mobile-visible': showCart }">

        <button class="btn btn-outline btn-sm show-mobile" style="display:none; margin-bottom:0.75rem; width:100%;" @click="showCart = false">
          ← Back to Products
        </button>

        <div class="cart-header">
          🛒 Cart
          <span v-if="cart.length > 0" class="badge badge-amber" style="margin-left:0.5rem;">{{ cart.length }}</span>
        </div>

        <div v-if="cart.length === 0" class="empty-state" style="padding:1.5rem 0;">
          <div class="empty-icon">🛒</div>
          <p>Cart is empty</p>
        </div>

        <div v-else class="cart-items">
          <div v-for="item in cart" :key="item.product_id" class="cart-item">
            <div class="cart-item-info">
              <div class="cart-item-name">{{ item.name }}</div>
              <div class="cart-item-price">{{ formatCurrency(item.unit_price) }} ETB each</div>
            </div>
            <div class="cart-item-controls">
              <button class="qty-btn" @click="decreaseQty(item)">−</button>
              <span class="qty-val">{{ item.quantity }}</span>
              <button class="qty-btn" @click="increaseQty(item)" :disabled="item.quantity >= item.stock_quantity">+</button>
              <span class="cart-item-subtotal">{{ formatCurrency(item.unit_price * item.quantity) }}</span>
              <button class="remove-btn" @click="removeFromCart(item)" title="Remove">✕</button>
            </div>
          </div>
        </div>

        <div class="cart-divider"></div>

        <div class="cart-row">
          <label class="cart-label">Discount (%)</label>
          <input
            v-model.number="discount"
            type="number" min="0" max="100"
            class="form-input"
            style="width:80px; text-align:right;"
          />
        </div>

        <div class="cart-divider"></div>

        <!-- Totals -->
        <div class="cart-totals">
          <div class="total-row">
            <span>Subtotal</span>
            <span>{{ formatCurrency(subtotal) }} ETB</span>
          </div>
          <div class="total-row" style="color:var(--text-muted);">
            <span>Discount ({{ discount }}%)</span>
            <span>− {{ formatCurrency(discountAmount) }} ETB</span>
          </div>
          <div class="total-row" style="color:var(--text-muted);">
            <span>VAT (15%)</span>
            <span>{{ formatCurrency(vatAmount) }} ETB</span>
          </div>
          <div class="total-row total-final">
            <span>Total</span>
            <span>{{ formatCurrency(total) }} ETB</span>
          </div>
        </div>

        <textarea
          v-model="note"
          class="form-input"
          placeholder="Note (optional)"
          rows="2"
          style="margin-top:0.75rem; resize:none;"
        ></textarea>

        <div v-if="total > 50000" class="alert alert-warning" style="margin-top:0.75rem; font-size:0.8rem;">
          ⚠ Total exceeds 50,000 ETB — requires manager approval.
        </div>

        <div v-if="saleError" class="alert alert-error" style="margin-top:0.75rem;">{{ saleError }}</div>

        <button
          class="btn btn-primary btn-full"
          style="margin-top:0.75rem;"
          :disabled="cart.length === 0 || submitting"
          @click="completeSale"
        >
          {{ submitting ? 'Processing…' : `Complete Sale · ${formatCurrency(total)} ETB` }}
        </button>
      </div>
    </div>

    <div v-if="showSuccess" class="modal-overlay">
      <div class="modal" style="text-align:center;">
        <div style="font-size:3rem; margin-bottom:0.75rem;"></div>
        <h2 style="margin-bottom:0.5rem; color:var(--text-primary);">{{ successMessage }}</h2>
        <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:0.5rem;">Sale #{{ lastSaleId }} recorded</p>
        <p style="color:var(--accent); font-weight:700; font-size:1.1rem; margin-bottom:1.25rem;">{{ formatCurrency(lastTotal) }} ETB</p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
          <button class="btn btn-outline" @click="goToSales">View Sales</button>
          <button class="btn btn-primary" @click="resetSale">New Sale</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api/axios.js'

const router    = useRouter()
const showCart  = ref(false)

const allProducts     = ref([])
const loadingProducts = ref(true)
const search          = ref('')
const cart            = ref([])
const discount        = ref(0)
const note            = ref('')
const submitting      = ref(false)
const saleError       = ref('')
const showSuccess     = ref(false)
const lastSaleId      = ref(null)
const lastTotal       = ref(0)
const successMessage  = ref('')

onMounted(async () => {
  try {
    const res = await api.get('/products?page=1')
    allProducts.value = res.data.data || []
    const lastPage = res.data.last_page || 1
    if (lastPage > 1) {
      const pages = []
      for (let i = 2; i <= lastPage; i++) pages.push(api.get(`/products?page=${i}`))
      const results = await Promise.all(pages)
      results.forEach(r => { allProducts.value.push(...(r.data.data || [])) })
    }
  } catch {}
  loadingProducts.value = false
})

const filteredProducts = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return allProducts.value
  return allProducts.value.filter(p =>
    p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
  )
})

function addToCart(product) {
  const existing = cart.value.find(i => i.product_id === product.id)
  if (existing) {
    if (existing.quantity < product.stock_quantity) existing.quantity++
  } else {
    cart.value.push({
      product_id: product.id,
      name: product.name,
      unit_price: parseFloat(product.selling_price),
      quantity: 1,
      stock_quantity: product.stock_quantity,
    })
  }
}

function increaseQty(item) { if (item.quantity < item.stock_quantity) item.quantity++ }
function decreaseQty(item) { if (item.quantity > 1) item.quantity--; else removeFromCart(item) }
function removeFromCart(item) { cart.value = cart.value.filter(i => i.product_id !== item.product_id) }

const subtotal       = computed(() => cart.value.reduce((s, i) => s + i.unit_price * i.quantity, 0))
const discountAmount = computed(() => Math.round(subtotal.value * (discount.value / 100) * 100) / 100)
const afterDiscount  = computed(() => subtotal.value - discountAmount.value)
const vatAmount      = computed(() => Math.round(afterDiscount.value * 0.15 * 100) / 100)
const total          = computed(() => Math.round((afterDiscount.value + vatAmount.value) * 100) / 100)

async function completeSale() {
  if (cart.value.length === 0) return
  submitting.value = true
  saleError.value  = ''
  try {
    const res = await api.post('/sales', {
      items: cart.value.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
      discount: discount.value,
      note: note.value || null,
    })
    const sale = res.data
    lastSaleId.value     = sale.id
    lastTotal.value      = total.value
    successMessage.value = sale.status === 'pending_approval'
      ? ' Sale submitted for approval'
      : ' Sale completed successfully'
    showSuccess.value = true
  } catch (e) {
    saleError.value = e.response?.data?.message || 'Failed to process sale.'
  } finally {
    submitting.value = false
  }
}

function resetSale() {
  cart.value = []; discount.value = 0; note.value = ''
  saleError.value = ''; showSuccess.value = false; showCart.value = false
  api.get('/products?page=1').then(r => { allProducts.value = r.data.data || [] })
}

function goToSales() { router.push('/my-sales') }

function formatCurrency(v) {
  return Number(v || 0).toLocaleString('en-ET', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<style scoped>
.pos-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.25rem;
  align-items: start;
}
.pos-products {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 1.25rem;
}
.product-list { display: flex; flex-direction: column; gap: 0.5rem; }
.product-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border); border-radius: 8px;
  background: var(--bg-elevated);
  cursor: pointer;
  transition: background 0.1s, border-color 0.1s;
}
.product-row:hover { background: var(--bg-hover); border-color: var(--accent); }
.product-row.out-of-stock { opacity: 0.4; cursor: not-allowed; }
.product-name { font-weight: 600; font-size: 0.875rem; color: var(--text-primary); }
.product-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }
.product-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.product-price { font-weight: 700; color: var(--accent); font-size: 0.9rem; white-space: nowrap; }
.add-btn {
  width: 28px; height: 28px;
  background: var(--accent); color: #000;
  border: none; border-radius: 6px;
  font-size: 1rem; font-weight: 700; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s; flex-shrink: 0;
}
.add-btn:hover:not(:disabled) { background: var(--accent-hover); }
.add-btn:disabled { background: var(--bg-hover); color: var(--text-muted); cursor: not-allowed; }

.pos-cart {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 1.25rem;
  position: sticky;
  top: 1rem;
}
.cart-header { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; display: flex; align-items: center; }
.cart-items { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 0.75rem; }
.cart-item { display: flex; flex-direction: column; gap: 0.2rem; }
.cart-item-name  { font-weight: 600; font-size: 0.85rem; color: var(--text-primary); }
.cart-item-price { font-size: 0.75rem; color: var(--text-muted); }
.cart-item-controls { display: flex; align-items: center; gap: 0.4rem; }
.qty-btn {
  width: 24px; height: 24px;
  border: 1px solid var(--border); background: var(--bg-elevated);
  color: var(--text-primary); border-radius: 4px; cursor: pointer;
  font-size: 0.85rem; display: flex; align-items: center; justify-content: center;
  transition: background 0.1s;
}
.qty-btn:hover:not(:disabled) { background: var(--bg-hover); border-color: var(--accent); }
.qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.qty-val { min-width: 22px; text-align: center; font-weight: 600; font-size: 0.85rem; }
.cart-item-subtotal { margin-left: auto; font-weight: 700; font-size: 0.85rem; color: var(--accent-dark); }
.remove-btn { background: none; border: none; cursor: pointer; font-size: 0.8rem; color: var(--text-muted); padding: 0 2px; transition: color 0.15s; }
.remove-btn:hover { color: var(--red); }
.cart-divider { border: none; border-top: 1px solid var(--border); margin: 0.75rem 0; }
.cart-row { display: flex; align-items: center; justify-content: space-between; }
.cart-label { font-size: 0.85rem; color: var(--text-secondary); font-weight: 500; }
.cart-totals { display: flex; flex-direction: column; gap: 0.4rem; }
.total-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-secondary); }
.total-final { font-size: 1rem; font-weight: 700; color: var(--accent-dark); margin-top: 0.25rem; padding-top: 0.5rem; border-top: 1px solid var(--border); }

@media (max-width: 768px) {
  .pos-layout { grid-template-columns: 1fr; }
  .show-mobile { display: flex !important; }
  .pos-cart { display: none; position: static; }
  .pos-cart.mobile-visible { display: block; }
  .pos-products.mobile-hidden { display: none; }
}
</style>
