import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { LoginPage } from '../LoginPage'

// ─── Mocks ───────────────────────────────────────────────────────────────────

vi.mock('@/lib/axios', () => ({
  default: {
    post: vi.fn(),
    interceptors: {
      request:  { use: vi.fn(), handlers: [] },
      response: { use: vi.fn(), handlers: [] },
    },
    defaults: { headers: {}, baseURL: '/api/v1', timeout: 30_000 },
  },
}))

const mockNavigate = vi.fn()
vi.mock('react-router-dom', async (importOriginal) => {
  const actual = await importOriginal<typeof import('react-router-dom')>()
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  }
})

const mockSetAuth = vi.fn()
vi.mock('@/stores/authStore', () => ({
  useAuthStore: vi.fn(() => ({ setAuth: mockSetAuth })),
}))

import api from '@/lib/axios'
const mockApi = vi.mocked(api)

// ─── Helpers ─────────────────────────────────────────────────────────────────

function renderPage() {
  return render(
    <MemoryRouter>
      <LoginPage />
    </MemoryRouter>
  )
}

const fakeUser = {
  id:      1,
  codigo:  'ADMIN',
  email:   'admin@empresa.pa',
  roles:   ['admin'],
  permisos: [],
  erp: { CODUSER: 'ADMIN', VALVENDEDOR: 1, VALDEPOSITO: 1, VALCONTADOR: 0 },
}

// ─── Tests ───────────────────────────────────────────────────────────────────

describe('LoginPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra el título y los campos del formulario', () => {
    renderPage()
    expect(screen.getByText('InnovaWeb')).toBeInTheDocument()
    expect(screen.getByPlaceholderText(/usuario/i)).toBeInTheDocument()
    expect(screen.getByPlaceholderText(/contraseña/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /ingresar/i })).toBeInTheDocument()
  })

  it('muestra errores de validación cuando se envía vacío', async () => {
    renderPage()
    fireEvent.click(screen.getByRole('button', { name: /ingresar/i }))

    await waitFor(() => {
      expect(screen.getByText(/ingresa tu usuario/i)).toBeInTheDocument()
      expect(screen.getByText(/ingresa tu contraseña/i)).toBeInTheDocument()
    })
  })

  it('no llama a la API cuando el formulario tiene errores', async () => {
    renderPage()
    fireEvent.click(screen.getByRole('button', { name: /ingresar/i }))

    await waitFor(() => {
      expect(screen.getByText(/ingresa tu usuario/i)).toBeInTheDocument()
    })
    expect(mockApi.post).not.toHaveBeenCalled()
  })

  it('llama a setAuth y redirige a / en login exitoso', async () => {
    mockApi.post.mockResolvedValue({
      data: {
        token:      'tok-abc',
        expires_at: '2030-01-01T00:00:00Z',
        usuario:    fakeUser,
      },
    })

    renderPage()

    fireEvent.change(screen.getByPlaceholderText(/usuario/i), { target: { value: 'ADMIN' } })
    fireEvent.change(screen.getByPlaceholderText(/contraseña/i), { target: { value: 'secret123' } })
    fireEvent.click(screen.getByRole('button', { name: /ingresar/i }))

    await waitFor(() => {
      expect(mockApi.post).toHaveBeenCalledWith('/login', {
        usuario:  'ADMIN',
        password: 'secret123',
      })
      expect(mockSetAuth).toHaveBeenCalledWith('tok-abc', fakeUser, '2030-01-01T00:00:00Z')
      expect(mockNavigate).toHaveBeenCalledWith('/', { replace: true })
    })
  })

  it('muestra el mensaje de error de la API en credenciales incorrectas', async () => {
    mockApi.post.mockRejectedValue({
      response: { data: { message: 'Credenciales incorrectas.' } },
    })

    renderPage()

    fireEvent.change(screen.getByPlaceholderText(/usuario/i), { target: { value: 'ADMIN' } })
    fireEvent.change(screen.getByPlaceholderText(/contraseña/i), { target: { value: 'wrong' } })
    fireEvent.click(screen.getByRole('button', { name: /ingresar/i }))

    await waitFor(() => {
      expect(screen.getByText('Credenciales incorrectas.')).toBeInTheDocument()
    })
    expect(mockSetAuth).not.toHaveBeenCalled()
    expect(mockNavigate).not.toHaveBeenCalled()
  })

  it('muestra errores de validación del servidor (422)', async () => {
    mockApi.post.mockRejectedValue({
      response: {
        data: {
          errors: {
            usuario:  ['El usuario no existe.'],
            password: ['La contraseña es incorrecta.'],
          },
        },
      },
    })

    renderPage()

    fireEvent.change(screen.getByPlaceholderText(/usuario/i), { target: { value: 'NOEXISTE' } })
    fireEvent.change(screen.getByPlaceholderText(/contraseña/i), { target: { value: 'x' } })
    fireEvent.click(screen.getByRole('button', { name: /ingresar/i }))

    await waitFor(() => {
      expect(screen.getByText(/El usuario no existe/)).toBeInTheDocument()
    })
  })

  it('muestra mensaje genérico si no hay response de la API', async () => {
    mockApi.post.mockRejectedValue(new Error('Network Error'))

    renderPage()

    fireEvent.change(screen.getByPlaceholderText(/usuario/i), { target: { value: 'ADMIN' } })
    fireEvent.change(screen.getByPlaceholderText(/contraseña/i), { target: { value: 'pass' } })
    fireEvent.click(screen.getByRole('button', { name: /ingresar/i }))

    await waitFor(() => {
      expect(screen.getByText(/error al iniciar sesión/i)).toBeInTheDocument()
    })
  })
})
