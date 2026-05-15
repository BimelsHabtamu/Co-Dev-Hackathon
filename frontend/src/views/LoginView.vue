<template>
  <div class="login-page">
    <div class="login-card">

      <div class="login-brand">
        <img src="/logo.png" alt="Semre Logo" class="login-logo" />
        <h1>Qelem Meda</h1>
        <p>Inventory &amp; Sales Management</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div v-if="auth.error" class="alert alert-error">{{ auth.error }}</div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input v-model="email" type="email" class="form-input" placeholder="you@semre.com" required />
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input v-model="password" type="password" class="form-input" placeholder="••••••••" required />
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;" :disabled="auth.loading">
          {{ auth.loading ? 'Signing in…' : 'Sign In' }}
        </button>
      </form>
      
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const auth     = useAuthStore()
const router   = useRouter()
const email    = ref('')
const password = ref('')

async function handleLogin() {
  const ok = await auth.login(email.value, password.value)
  if (ok) router.push('/dashboard')
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: var(--bg-base);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.login-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 2.5rem 2rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}

.login-brand {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
  margin: 0 auto 0.75rem;
  display: block;
  border-radius: 12px;
}

.login-brand h1 {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--text-primary);
}

.login-brand p {
  color: var(--text-muted);
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.login-hint {
  margin-top: 1.5rem;
  padding: 0.75rem;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 0.78rem;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.hint-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.hint-role {
  background: var(--accent-dim);
  color: var(--accent);
  padding: 1px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  white-space: nowrap;
}
</style>
