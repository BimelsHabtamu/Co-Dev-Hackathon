<template>
  <div class="app-shell">

    <!-- ── Mobile top bar ── -->
    <header class="topbar">
      <button class="hamburger" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
        <span :class="['ham-line', sidebarOpen && 'open']"></span>
        <span :class="['ham-line', sidebarOpen && 'open']"></span>
        <span :class="['ham-line', sidebarOpen && 'open']"></span>
      </button>
      <div class="topbar-brand">
        <img src="/logo.png" alt="Qelem Meda" class="topbar-logo" />
        <span class="topbar-name">Qelem Meda</span>
      </div>
      <div class="topbar-user">
        <div class="user-avatar-sm">{{ userInitial }}</div>
      </div>
    </header>

    <!-- ── Sidebar overlay (mobile) ── -->
    <div
      v-if="sidebarOpen"
      class="sidebar-overlay"
      @click="sidebarOpen = false"
    ></div>

    <!-- ── Sidebar ── -->
    <aside :class="['sidebar', sidebarOpen && 'sidebar-open']">
      <div class="sidebar-brand">
        <img src="/logo.png" alt="Qelem Meda" class="brand-logo" />
        <span class="brand-name">Qelem Meda</span>
        <button class="sidebar-close" @click="sidebarOpen = false" aria-label="Close menu">✕</button>
      </div>

      <nav class="sidebar-nav" @click="sidebarOpen = false">
        <RouterLink to="/dashboard" class="nav-item" active-class="active">
          <span class="nav-icon">⊞</span>
          <span>Dashboard</span>
        </RouterLink>
        <RouterLink to="/products" class="nav-item" active-class="active">
          <span class="nav-icon">◫</span>
          <span>Products</span>
        </RouterLink>
        <RouterLink to="/new-sale" class="nav-item" active-class="active">
          <span class="nav-icon">🛒</span>
          <span>New Sale</span>
        </RouterLink>
        <RouterLink to="/my-sales" class="nav-item" active-class="active">
          <span class="nav-icon">📋</span>
          <span>{{ auth.isManager ? 'All Sales' : 'My Sales' }}</span>
        </RouterLink>

        <!-- Manager-only nav items -->
        <template v-if="auth.isManager">
          <RouterLink to="/inventory" class="nav-item" active-class="active">
            <span class="nav-icon">📦</span>
            <span>Inventory</span>
          </RouterLink>
          <RouterLink to="/users" class="nav-item" active-class="active">
            <span class="nav-icon">👥</span>
            <span>Users</span>
          </RouterLink>
        </template>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ userInitial }}</div>
          <div class="user-details">
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-role">{{ roleLabel }}</div>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout" title="Logout">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </div>
    </aside>

    <!-- ── Main content ── -->
    <main class="main-content">
      <RouterView />
    </main>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const auth        = useAuthStore()
const router      = useRouter()
const sidebarOpen = ref(false)

const userInitial = computed(() => auth.user?.name?.[0]?.toUpperCase() || 'U')
const roleLabel   = computed(() => auth.user?.role === 'manager' ? 'Manager' : 'Sales Officer')

async function handleLogout() {
  sidebarOpen.value = false
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: var(--bg-base);
}

/* ── Mobile top bar ── */
.topbar {
  display: none;
  position: fixed;
  top: 0; left: 0; right: 0;
  height: var(--topbar-h);
  background: var(--bg-sidebar);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  align-items: center;
  justify-content: space-between;
  padding: 0 1rem;
  z-index: 200;
}
.topbar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}
.topbar-logo { width: 26px; height: 26px; border-radius: 5px; object-fit: contain; }
.topbar-name { font-weight: 700; font-size: 0.95rem; color: var(--text-on-dark); }
.topbar-user { display: flex; align-items: center; }
.user-avatar-sm {
  width: 28px; height: 28px;
  background: var(--accent-dim);
  color: var(--accent);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.75rem;
  border: 1px solid var(--accent);
}

/* ── Hamburger ── */
.hamburger {
  background: none; border: none;
  cursor: pointer; padding: 0.3rem;
  display: flex; flex-direction: column; gap: 5px;
  z-index: 201;
}
.ham-line {
  display: block; width: 22px; height: 2px;
  background: var(--text-on-dark);
  border-radius: 2px;
  transition: transform 0.2s, opacity 0.2s;
}
.ham-line.open:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.ham-line.open:nth-child(2) { opacity: 0; }
.ham-line.open:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ── Sidebar overlay ── */
.sidebar-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(28,25,23,0.55);
  z-index: 149;
  backdrop-filter: blur(2px);
}

/* ── Sidebar — dark like Filament ── */
.sidebar {
  width: var(--sidebar-w);
  min-width: var(--sidebar-w);
  background: var(--bg-sidebar);
  border-right: none;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 150;
  transition: transform 0.25s ease;
  box-shadow: 2px 0 8px rgba(28,25,23,0.15);
}

/* Brand area */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 1.1rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  flex-shrink: 0;
}
.brand-logo {
  width: 32px; height: 32px;
  object-fit: contain; border-radius: 6px; flex-shrink: 0;
}
.brand-name {
  color: var(--text-on-dark);
  font-weight: 700; font-size: 1rem;
  letter-spacing: 0.02em; flex: 1;
}
.sidebar-close {
  display: none; background: none; border: none;
  color: rgba(255,255,255,0.4);
  cursor: pointer; font-size: 1rem; padding: 0.2rem;
  transition: color 0.15s;
}
.sidebar-close:hover { color: var(--text-on-dark); }

/* Nav */
.sidebar-nav {
  flex: 1;
  padding: 0.75rem 0.6rem;
  display: flex; flex-direction: column; gap: 0.1rem;
  overflow-y: auto;
}
.nav-item {
  display: flex; align-items: center; gap: 0.65rem;
  padding: 0.6rem 0.75rem;
  border-radius: 7px;
  color: rgba(245,245,244,0.6);
  text-decoration: none;
  font-size: 0.875rem; font-weight: 500;
  transition: background 0.12s, color 0.12s;
}
.nav-item:hover {
  background: rgba(255,255,255,0.07);
  color: var(--text-on-dark);
}
/* Active — amber highlight like Filament */
.nav-item.active {
  background: var(--accent);
  color: #000;
  font-weight: 600;
}
.nav-item.active .nav-icon { opacity: 1; }
.nav-icon { font-size: 0.95rem; width: 18px; text-align: center; flex-shrink: 0; }

/* Footer */
.sidebar-footer {
  padding: 0.85rem 0.75rem;
  border-top: 1px solid rgba(255,255,255,0.08);
  display: flex; align-items: center; gap: 0.5rem;
  flex-shrink: 0;
}
.user-info {
  display: flex; align-items: center; gap: 0.6rem;
  flex: 1; min-width: 0;
}
.user-avatar {
  width: 30px; height: 30px;
  background: var(--accent);
  color: #000;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.8rem;
  flex-shrink: 0;
}
.user-details { min-width: 0; }
.user-name {
  color: var(--text-on-dark);
  font-size: 0.8rem; font-weight: 600;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.user-role {
  color: rgba(245,245,244,0.45);
  font-size: 0.72rem;
}
.logout-btn {
  background: none; border: none;
  color: rgba(245,245,244,0.4);
  cursor: pointer; padding: 0.3rem; border-radius: 5px;
  display: flex; align-items: center;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
}
.logout-btn:hover {
  color: #fca5a5;
  background: rgba(220,38,38,0.2);
}

/* ── Main content — light like Filament content area ── */
.main-content {
  margin-left: var(--sidebar-w);
  flex: 1;
  padding: 2rem;
  min-height: 100vh;
  background: var(--bg-base);
}

/* ── Mobile ── */
@media (max-width: 768px) {
  .topbar { display: flex; }
  .sidebar {
    transform: translateX(-100%);
    box-shadow: 4px 0 24px rgba(28,25,23,0.5);
  }
  .sidebar.sidebar-open { transform: translateX(0); }
  .sidebar-overlay { display: block; }
  .sidebar-close { display: block; }
  .main-content {
    margin-left: 0;
    padding: 1rem;
    padding-top: calc(var(--topbar-h) + 1rem);
  }
}
</style>
