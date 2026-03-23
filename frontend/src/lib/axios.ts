import axios, { type AxiosError } from 'axios'
import type { ApiError } from '@/types'

const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_URL ?? ''}/api/v1`,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  timeout: 30_000,
})

// ─── Request interceptor: inyectar token ──────────────────────────────────
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('innovaweb_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// ─── Response interceptor: manejo global de errores ───────────────────────
api.interceptors.response.use(
  (res) => res,
  (error: AxiosError<ApiError>) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('innovaweb_token')
      localStorage.removeItem('innovaweb_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export { api }
export default api
