import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
})

// Attach token to every request
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Handle 401 and 403 globally
api.interceptors.response.use(
  res => res,
  err => {
    const status = err.response?.status

    // 401 — token expired or missing → force re-login
    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }

    // 403 — authenticated but wrong role → redirect to dashboard
    // The individual views also show inline errors, but this catches
    // any unhandled case (e.g. direct API call from a sales officer).
    if (status === 403) {
      window.location.href = '/dashboard'
    }

    return Promise.reject(err)
  }
)

export default api
