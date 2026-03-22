import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { ProtectedRoute } from '../ProtectedRoute'

// Mock del store de autenticación
vi.mock('@/stores/authStore', () => ({
  useAuthStore: vi.fn(),
}))

import { useAuthStore } from '@/stores/authStore'

const mockUseAuthStore = vi.mocked(useAuthStore)

function renderWithRouter(isAuthenticated: boolean) {
  mockUseAuthStore.mockReturnValue({
    isAuthenticated: () => isAuthenticated,
    token: isAuthenticated ? 'fake-token' : null,
    user: null,
    expiresAt: null,
    setAuth: vi.fn(),
    clearAuth: vi.fn(),
    isExpired: () => false,
  })

  return render(
    <MemoryRouter initialEntries={['/dashboard']}>
      <Routes>
        <Route path="/login" element={<div data-testid="login-page">Login</div>} />
        <Route element={<ProtectedRoute />}>
          <Route path="/dashboard" element={<div data-testid="protected-content">Dashboard</div>} />
        </Route>
      </Routes>
    </MemoryRouter>
  )
}

describe('ProtectedRoute', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra el contenido protegido cuando el usuario está autenticado', () => {
    renderWithRouter(true)

    expect(screen.getByTestId('protected-content')).toBeInTheDocument()
    expect(screen.queryByTestId('login-page')).not.toBeInTheDocument()
  })

  it('redirige a /login cuando el usuario no está autenticado', () => {
    renderWithRouter(false)

    expect(screen.getByTestId('login-page')).toBeInTheDocument()
    expect(screen.queryByTestId('protected-content')).not.toBeInTheDocument()
  })

  it('llama a isAuthenticated del store para tomar la decisión', () => {
    const isAuthFn = vi.fn(() => true)
    mockUseAuthStore.mockReturnValue({
      isAuthenticated: isAuthFn,
      token: 'fake-token',
      user: null,
      expiresAt: null,
      setAuth: vi.fn(),
      clearAuth: vi.fn(),
      isExpired: () => false,
    })

    render(
      <MemoryRouter initialEntries={['/dashboard']}>
        <Routes>
          <Route path="/login" element={<div>Login</div>} />
          <Route element={<ProtectedRoute />}>
            <Route path="/dashboard" element={<div>Dashboard</div>} />
          </Route>
        </Routes>
      </MemoryRouter>
    )

    expect(isAuthFn).toHaveBeenCalled()
  })
})
