import axios from 'axios'

// In production (Vercel) VITE_API_BASE_URL is injected at build time.
// In local dev the Vite proxy rewrites /api → http://localhost:8000/api.
const baseURL = import.meta.env.VITE_API_BASE_URL || '/api'

const api = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  res => res,
  err => {
    const status = err.response?.status
    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    if (status === 403) {
      window.location.href = '/dashboard'
    }
    return Promise.reject(err)
  }
)

export default api
