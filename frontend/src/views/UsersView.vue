<template>
  <div>
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <h1>Users</h1>
        <p>Create and manage system accounts</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Add User</button>
    </div>

    <!-- Users table -->
    <div class="card">
      <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
      <div v-else-if="loadError" class="alert alert-error">{{ loadError }}</div>

      <template v-else>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="users.length === 0">
                <td colspan="4" style="text-align:center; color:var(--text-muted); padding:2rem;">No users found</td>
              </tr>
              <tr v-for="u in users" :key="u.id">
                <td style="color:var(--text-muted);">#{{ u.id }}</td>
                <td><strong>{{ u.name }}</strong></td>
                <td>{{ u.email }}</td>
                <td>
                  <span :class="u.role === 'manager' ? 'badge badge-amber' : 'badge badge-blue'">
                    {{ u.role === 'manager' ? 'Manager' : 'Sales Officer' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Add New User</h2>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>

        <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
        <div v-if="modalSuccess" class="alert alert-success">{{ modalSuccess }}</div>

        <form @submit.prevent="createUser">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input v-model="form.name" class="form-input" required placeholder="e.g. Abebe Kebede" />
          </div>
          <div class="form-group">
            <label class="form-label">Email *</label>
            <input v-model="form.email" type="email" class="form-input" required placeholder="user@semre.com" />
          </div>
          <div class="form-group">
            <label class="form-label">Role *</label>
            <select v-model="form.role" class="form-input" required>
              <option value="">Select role…</option>
              <option value="sales_officer">Sales Officer</option>
              <option value="manager">Manager</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Password *</label>
            <input v-model="form.password" type="password" class="form-input" required placeholder="Min. 8 characters" minlength="8" />
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input v-model="form.password_confirmation" type="password" class="form-input" required placeholder="Repeat password" />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Creating…' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api/axios.js'

const users       = ref([])
const loading     = ref(true)
const loadError   = ref('')
const showModal   = ref(false)
const saving      = ref(false)
const modalError  = ref('')
const modalSuccess = ref('')

const emptyForm = () => ({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
})
const form = ref(emptyForm())

// Load users list on mount
onMounted(fetchUsers)

async function fetchUsers() {
  loading.value   = true
  loadError.value = ''
  try {
    const { data } = await api.get('/users')
    users.value = data.data ?? data
  } catch (e) {
    loadError.value = e.response?.data?.message || 'Failed to load users.'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  form.value     = emptyForm()
  modalError.value  = ''
  modalSuccess.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function createUser() {
  saving.value      = true
  modalError.value  = ''
  modalSuccess.value = ''
  try {
    const { data } = await api.post('/auth/register', form.value)
    modalSuccess.value = `User "${data.user.name}" created successfully.`
    form.value = emptyForm()
    // Refresh list
    await fetchUsers()
  } catch (e) {
    const errs = e.response?.data?.errors
    if (errs) {
      modalError.value = Object.values(errs).flat().join(' ')
    } else {
      modalError.value = e.response?.data?.message || 'Failed to create user.'
    }
  } finally {
    saving.value = false
  }
}
</script>
