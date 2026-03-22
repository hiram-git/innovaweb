import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import type { AuthUser } from '@/types'

interface AuthStore {
  token: string | null
  user: AuthUser | null
  expiresAt: string | null
  setAuth: (token: string, user: AuthUser, expiresAt: string) => void
  clearAuth: () => void
  isAuthenticated: () => boolean
  isExpired: () => boolean
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      token:     null,
      user:      null,
      expiresAt: null,

      setAuth: (token, user, expiresAt) => {
        localStorage.setItem('innovaweb_token', token)
        set({ token, user, expiresAt })
      },

      clearAuth: () => {
        localStorage.removeItem('innovaweb_token')
        set({ token: null, user: null, expiresAt: null })
      },

      isAuthenticated: () => {
        const { token } = get()
        return !!token && !get().isExpired()
      },

      isExpired: () => {
        const { expiresAt } = get()
        if (!expiresAt) return true
        return new Date(expiresAt) <= new Date()
      },
    }),
    {
      name: 'innovaweb_auth',
      // Solo persistir token y user (no funciones)
      partialize: (s) => ({ token: s.token, user: s.user, expiresAt: s.expiresAt }),
    }
  )
)
