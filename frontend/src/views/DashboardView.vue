<template>
  <div>
    <div class="page-header">
      <h1>{{ auth.isManager ? ' Manager Dashboard' : ' Sales Dashboard' }}</h1>
      <p>Welcome back, <span class="text-accent">{{ auth.user?.name }}</span> · {{ today }}</p>
    </div>

    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>

    <template v-else>

      <div v-if="auth.isManager" class="stats-grid">
        <div class="stat-card stat-card--accent">
          <div class="stat-icon-wrap">&#128176;</div>
          <div class="stat-label">Today's Revenue</div>
          <div class="stat-value stat-accent">{{ formatCurrency(stats.todayRevenue) }}</div>
          <div class="stat-sub">ETB · {{ stats.todaySales }} transactions</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap">&#128197;</div>
          <div class="stat-label">Monthly Revenue</div>
          <div class="stat-value stat-accent">{{ formatCurrency(stats.monthRevenue) }}</div>
          <div class="stat-sub">{{ stats.monthSales }} transactions this month</div>
        </div>
        <div class="stat-card" :class="stats.pendingApproval > 0 ? 'stat-card--warn' : ''">
          <div class="stat-icon-wrap">&#9203;</div>
          <div class="stat-label">Pending Approval</div>
          <div class="stat-value" :style="stats.pendingApproval > 0 ? 'color:var(--yellow)' : ''">{{ stats.pendingApproval }}</div>
          <div class="stat-sub">large sales awaiting review</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap">&#128230;</div>
          <div class="stat-label">Total Products</div>
          <div class="stat-value">{{ stats.totalProducts }}</div>
          <div class="stat-sub">in catalog</div>
        </div>
        <div class="stat-card" :class="stats.lowStock > 0 ? 'stat-card--danger' : ''">
          <div class="stat-icon-wrap">&#9888;&#65039;</div>
          <div class="stat-label">Low / Out of Stock</div>
          <div class="stat-value" :style="stats.outOfStock > 0 ? 'color:var(--red)' : stats.lowStock > 0 ? 'color:var(--yellow)' : ''">
            {{ stats.lowStock }} / {{ stats.outOfStock }}
          </div>
          <div class="stat-sub">low stock / out of stock</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap">&#128101;</div>
          <div class="stat-label">Total Sales Officers</div>
          <div class="stat-value">{{ stats.totalOfficers }}</div>
          <div class="stat-sub">active accounts</div>
        </div>
      </div>

      <!-- ── SALES OFFICER STATS ── -->
      <div v-else class="stats-grid">
        <div class="stat-card stat-card--accent">
          <div class="stat-icon-wrap">&#128176;</div>
          <div class="stat-label">My Revenue Today</div>
          <div class="stat-value stat-accent">{{ formatCurrency(stats.todayRevenue) }}</div>
          <div class="stat-sub">ETB</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap">&#129534;</div>
          <div class="stat-label">My Sales Today</div>
          <div class="stat-value">{{ stats.todaySales }}</div>
          <div class="stat-sub">transactions</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap">&#128197;</div>
          <div class="stat-label">My Monthly Revenue</div>
          <div class="stat-value stat-accent">{{ formatCurrency(stats.monthRevenue) }}</div>
          <div class="stat-sub">{{ stats.monthSales }} sales this month</div>
        </div>
        <div class="stat-card" :class="stats.pendingApproval > 0 ? 'stat-card--warn' : ''">
          <div class="stat-icon-wrap">&#9203;</div>
          <div class="stat-label">Pending Approval</div>
          <div class="stat-value" :style="stats.pendingApproval > 0 ? 'color:var(--yellow)' : ''">{{ stats.pendingApproval }}</div>
          <div class="stat-sub">my sales awaiting review</div>
        </div>
      </div>

      <!-- ── MANAGER: Revenue Chart + Top Products ── -->
      <template v-if="auth.isManager">

        <!-- Revenue bar chart (last 7 days) -->
        <div class="card" style="margin-bottom:1.25rem;">
          <div class="section-header">
            <span class="section-title">&#128200; Revenue — Last 7 Days</span>
            <span class="badge badge-amber">ETB</span>
          </div>
          <div class="chart-wrap">
            <div class="bar-chart">
              <div v-for="d in chartDays" :key="d.label" class="bar-col">
                <div
                  class="bar-fill"
                  :style="{ height: barHeight(d.revenue) + 'px' }"
                >
                  <span class="bar-tooltip">{{ formatCurrency(d.revenue) }}</span>
                </div>
                <div class="bar-label">{{ d.label }}</div>
              </div>
            </div>
          </div>
          <div style="display:flex; gap:1.5rem; margin-top:0.75rem; flex-wrap:wrap;">
            <div style="font-size:0.78rem; color:var(--text-muted);">
              Peak day: <strong class="text-accent">{{ peakDay.label }}</strong>
              ({{ formatCurrency(peakDay.revenue) }} ETB)
            </div>
            <div style="font-size:0.78rem; color:var(--text-muted);">
              7-day total: <strong class="text-accent">{{ formatCurrency(chartDays.reduce((s,d)=>s+d.revenue,0)) }} ETB</strong>
            </div>
          </div>
        </div>

        <!-- Two-column: Top Products + Sales by Status -->
        <div class="two-col-grid" style="margin-bottom:1.25rem;">
          <!-- Top selling products -->
          <div class="card">
            <div class="section-header">
              <span class="section-title">&#127942; Top Selling Products</span>
            </div>
            <div v-if="topProducts.length === 0" class="empty-state" style="padding:1.5rem;">
              <p>No sales data yet</p>
            </div>
            <div v-else>
              <div v-for="(p, i) in topProducts" :key="p.name" class="progress-bar-wrap">
                <div class="progress-label-row">
                  <span>
                    <span class="rank-badge">{{ i + 1 }}</span>
                    {{ p.name }}
                  </span>
                  <span class="text-accent fw-600">{{ p.qty }} sold</span>
                </div>
                <div class="progress-track">
                  <div
                    class="progress-fill"
                    :class="['green','blue','purple','red',''][i] || ''"
                    :style="{ width: (p.qty / topProducts[0].qty * 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sales by status breakdown -->
          <div class="card">
            <div class="section-header">
              <span class="section-title">&#128202; Sales by Status</span>
            </div>
            <div v-for="s in statusBreakdown" :key="s.label" class="progress-bar-wrap">
              <div class="progress-label-row">
                <span>{{ s.icon }} {{ s.label }}</span>
                <span class="fw-600">{{ s.count }} ({{ s.pct }}%)</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" :class="s.color" :style="{ width: s.pct + '%' }"></div>
              </div>
            </div>
            <div style="margin-top:0.75rem; font-size:0.78rem; color:var(--text-muted);">
              Total: {{ allSalesCount }} sales recorded
            </div>
          </div>
        </div>

        <!-- Pending approvals alert -->
        <div v-if="stats.pendingApproval > 0" class="alert alert-warning" style="margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
          <span>&#9888; <strong>{{ stats.pendingApproval }}</strong> sale{{ stats.pendingApproval > 1 ? 's' : '' }} pending your approval</span>
          <RouterLink to="/my-sales" class="btn btn-sm btn-primary">Review Now &#8594;</RouterLink>
        </div>

      </template>

      <!-- ── SALES OFFICER: Quick Actions + Personal Stats ── -->
      <template v-else>

        <!-- Quick actions -->
        <div class="quick-actions">
          <RouterLink to="/new-sale" class="quick-card">
            <span class="qc-icon">&#128722;</span>
            <span class="qc-label">New Sale</span>
            <span class="qc-sub">Start selling</span>
          </RouterLink>
          <RouterLink to="/products" class="quick-card">
            <span class="qc-icon">&#128230;</span>
            <span class="qc-label">Products</span>
            <span class="qc-sub">Browse catalog</span>
          </RouterLink>
          <RouterLink to="/my-sales" class="quick-card">
            <span class="qc-icon">&#128203;</span>
            <span class="qc-label">My Sales</span>
            <span class="qc-sub">View history</span>
          </RouterLink>
          <div class="quick-card" style="cursor:default; border-color:var(--accent);">
            <span class="qc-icon">&#127919;</span>
            <span class="qc-label" style="color:var(--accent);">{{ stats.todaySales }} Sales</span>
            <span class="qc-sub">Today</span>
          </div>
        </div>

        <!-- Personal performance: chart + breakdown + best sale -->
        <div class="two-col-grid" style="margin-bottom:1.25rem;">
          <div class="card">
            <div class="section-header">
              <span class="section-title">&#128200; My Revenue — Last 7 Days</span>
            </div>
            <div class="chart-wrap">
              <div class="bar-chart">
                <div v-for="d in chartDays" :key="d.label" class="bar-col">
                  <div class="bar-fill" :style="{ height: barHeight(d.revenue) + 'px' }">
                    <span class="bar-tooltip">{{ formatCurrency(d.revenue) }}</span>
                  </div>
                  <div class="bar-label">{{ d.label }}</div>
                </div>
              </div>
            </div>
            <div style="margin-top:0.75rem; font-size:0.78rem; color:var(--text-muted);">
              7-day total: <strong class="text-accent">{{ formatCurrency(chartDays.reduce((s,d)=>s+d.revenue,0)) }} ETB</strong>
            </div>
          </div>

          <div class="card">
            <div class="section-header">
              <span class="section-title">&#128202; My Sales Breakdown</span>
            </div>
            <div v-for="s in statusBreakdown" :key="s.label" class="progress-bar-wrap">
              <div class="progress-label-row">
                <span>{{ s.icon }} {{ s.label }}</span>
                <span class="fw-600">{{ s.count }}</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" :class="s.color" :style="{ width: s.pct + '%' }"></div>
              </div>
            </div>

            <!-- Best single sale highlight -->
            <div v-if="bestSale" style="margin-top:1rem; padding:0.75rem; background:var(--accent-dim); border:1px solid var(--accent); border-radius:8px;">
              <div style="font-size:0.7rem; color:var(--accent); font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.3rem;">
                &#127942; My Best Sale
              </div>
              <div style="font-size:1.1rem; font-weight:700; color:var(--accent);">{{ formatCurrency(bestSale.total) }} ETB</div>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem;">
                Sale #{{ bestSale.id }} · {{ formatDate(bestSale.created_at) }}
              </div>
            </div>
          </div>
        </div>

      </template>

      <!-- ── RECENT SALES (both roles) ── -->
      <div class="card">
        <div class="section-header">
          <span class="section-title">{{ auth.isManager ? ' Recent Sales' : ' My Recent Sales' }}</span>
          <RouterLink to="/my-sales" class="btn btn-outline btn-sm">View All →</RouterLink>
        </div>

        <!-- Desktop table -->
        <div class="table-wrap table-desktop">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th v-if="auth.isManager">Cashier</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="recentSales.length === 0">
                <td :colspan="auth.isManager ? 6 : 5" style="text-align:center;color:var(--text-muted);padding:2rem;">No sales yet</td>
              </tr>
              <tr v-for="sale in recentSales" :key="sale.id">
                <td style="color:var(--text-muted);">#{{ sale.id }}</td>
                <td v-if="auth.isManager">{{ sale.user?.name || '—' }}</td>
                <td>{{ sale.items?.length || 0 }}</td>
                <td><strong class="text-accent">{{ formatCurrency(sale.total) }} ETB</strong></td>
                <td><span :class="statusBadge(sale.status)">{{ sale.status?.replace('_',' ') }}</span></td>
                <td style="color:var(--text-muted);">{{ formatDate(sale.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile cards -->
        <div class="mobile-card-list">
          <div v-if="recentSales.length === 0" class="empty-state" style="padding:1.5rem;">
            <p>No sales yet</p>
          </div>
          <div v-for="sale in recentSales" :key="sale.id" class="mobile-card">
            <div class="mobile-card-header">
              <span class="mch-id">#{{ sale.id }}</span>
              <span class="mch-total">{{ formatCurrency(sale.total) }} ETB</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Status</span>
              <span :class="statusBadge(sale.status)">{{ sale.status?.replace('_',' ') }}</span>
            </div>
            <div v-if="auth.isManager" class="mobile-card-row">
              <span class="mcr-label">Cashier</span>
              <span class="mcr-val">{{ sale.user?.name || '—' }}</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Items</span>
              <span class="mcr-val">{{ sale.items?.length || 0 }}</span>
            </div>
            <div class="mobile-card-row">
              <span class="mcr-label">Date</span>
              <span class="mcr-val">{{ formatDate(sale.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import api from '../api/axios.js'

const auth    = useAuthStore()
const loading = ref(true)
const recentSales  = ref([])
const allSalesData = ref([])
const allSalesCount = ref(0)

const stats = ref({
  todaySales: 0, todayRevenue: 0,
  totalProducts: 0, lowStock: 0,
  pendingApproval: 0,
  monthRevenue: 0, monthSales: 0,
})

const today = new Date().toLocaleDateString('en-ET', { weekday:'long', year:'numeric', month:'long', day:'numeric' })

onMounted(async () => {
  try {
    const [salesRes, productsRes] = await Promise.all([
      api.get('/sales?page=1'),
      api.get('/products?page=1'),
    ])
    const sales = salesRes.data.data || []
    allSalesData.value  = sales
    allSalesCount.value = salesRes.data.total || sales.length
    recentSales.value   = sales.slice(0, 8)

    const todayStr = new Date().toDateString()
    const nowMonth = new Date().getMonth()
    const nowYear  = new Date().getFullYear()

    const todaySales = sales.filter(s => new Date(s.created_at).toDateString() === todayStr)
    const monthSales = sales.filter(s => {
      const d = new Date(s.created_at)
      return d.getMonth() === nowMonth && d.getFullYear() === nowYear
    })

    stats.value.todaySales      = todaySales.length
    stats.value.todayRevenue    = todaySales.reduce((s, x) => s + parseFloat(x.total || 0), 0)
    stats.value.pendingApproval = sales.filter(s => s.status === 'pending_approval').length
    stats.value.monthRevenue    = monthSales.reduce((s, x) => s + parseFloat(x.total || 0), 0)
    stats.value.monthSales      = monthSales.length

    const products = productsRes.data.data || []
    stats.value.totalProducts = productsRes.data.total || products.length
    stats.value.lowStock      = products.filter(p => p.stock_quantity < 5).length
  } catch {}
  loading.value = false
})

const chartDays = computed(() => {
  const days = []
  for (let i = 6; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    const label   = d.toLocaleDateString('en-ET', { weekday: 'short' })
    const dateStr = d.toDateString()
    const revenue = allSalesData.value
      .filter(s => new Date(s.created_at).toDateString() === dateStr)
      .reduce((sum, s) => sum + parseFloat(s.total || 0), 0)
    days.push({ label, revenue })
  }
  return days
})

const peakDay = computed(() => {
  return chartDays.value.reduce((max, d) => d.revenue > max.revenue ? d : max, { label: '—', revenue: 0 })
})

function barHeight(revenue) {
  const max = Math.max(...chartDays.value.map(d => d.revenue), 1)
  return Math.max(Math.round((revenue / max) * 100), 3)
}

// ── Top products by qty sold ──
const topProducts = computed(() => {
  const map = {}
  allSalesData.value.forEach(sale => {
    (sale.items || []).forEach(item => {
      const name = item.product?.name || `#${item.product_id}`
      map[name] = (map[name] || 0) + (item.quantity || 0)
    })
  })
  return Object.entries(map)
    .map(([name, qty]) => ({ name, qty }))
    .sort((a, b) => b.qty - a.qty)
    .slice(0, 5)
})

// ── Status breakdown ──
const statusBreakdown = computed(() => {
  const total = allSalesData.value.length || 1
  const counts = { completed: 0, pending_approval: 0, approved: 0, rejected: 0 }
  allSalesData.value.forEach(s => { if (counts[s.status] !== undefined) counts[s.status]++ })
  return [
    { label: 'Completed',        icon: '', color: 'green',  count: counts.completed,        pct: Math.round(counts.completed / total * 100) },
    { label: 'Pending Approval', icon: '', color: '',       count: counts.pending_approval,  pct: Math.round(counts.pending_approval / total * 100) },
    { label: 'Approved',         icon: '', color: 'blue',   count: counts.approved,          pct: Math.round(counts.approved / total * 100) },
    { label: 'Rejected',         icon: '', color: 'red',    count: counts.rejected,          pct: Math.round(counts.rejected / total * 100) },
  ]
})

function formatCurrency(val) {
  return Number(val || 0).toLocaleString('en-ET', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('en-ET', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'
}
function statusBadge(status) {
  const map = { completed: 'badge badge-green', pending_approval: 'badge badge-amber', approved: 'badge badge-blue', rejected: 'badge badge-red' }
  return map[status] || 'badge badge-gray'
}
</script>

<style scoped>
.two-col-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px; height: 18px;
  background: var(--accent-dim);
  color: var(--accent);
  border-radius: 50%;
  font-size: 0.65rem;
  font-weight: 700;
  margin-right: 0.35rem;
}
@media (max-width: 768px) {
  .two-col-grid { grid-template-columns: 1fr; }
}
</style>
