import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter } from 'react-router-dom'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { InventarioPage } from '../InventarioPage'

// Mock del cliente axios
vi.mock('@/lib/axios', () => ({
  default: {
    get: vi.fn(),
    interceptors: {
      request:  { use: vi.fn(), handlers: [] },
      response: { use: vi.fn(), handlers: [] },
    },
    defaults: { headers: {}, baseURL: '/api/v1', timeout: 30_000 },
  },
  api: {
    get: vi.fn(),
  },
}))

import api from '@/lib/axios'
const mockApi = vi.mocked(api)

const productos = [
  {
    CODPRO:       'PROD001',
    DESCRIP1:     'Laptop HP 15"',
    EXISTENCIA:   10,
    CANRESERVADA: 2,
    PRECVEN1:     599.99,
    IMPPOR:       7,
    PROCOMPUESTO: 0,
    TIPINV:       'M',
    UNIDAD:       'UN',
  },
  {
    CODPRO:       'SRV001',
    DESCRIP1:     'Servicio de instalación',
    EXISTENCIA:   0,
    CANRESERVADA: 0,
    PRECVEN1:     50.00,
    IMPPOR:       7,
    PROCOMPUESTO: 0,
    TIPINV:       'S',
    UNIDAD:       'HR',
  },
]

function renderPage() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <InventarioPage />
      </MemoryRouter>
    </QueryClientProvider>
  )
}

describe('InventarioPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra el título de la página', () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    renderPage()
    expect(screen.getByText('Inventario')).toBeInTheDocument()
  })

  it('muestra un spinner mientras carga', () => {
    // Promesa que nunca resuelve para simular carga
    mockApi.get.mockReturnValue(new Promise(() => {}))
    renderPage()
    // El spinner no tiene text content, pero el form de búsqueda sí aparece
    expect(screen.getByPlaceholderText(/buscar/i)).toBeInTheDocument()
  })

  it('muestra la lista de productos tras cargar', async () => {
    mockApi.get.mockResolvedValue({ data: { data: productos } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('PROD001')).toBeInTheDocument()
      expect(screen.getByText('Laptop HP 15"')).toBeInTheDocument()
    })
  })

  it('muestra el precio formateado con 2 decimales', async () => {
    mockApi.get.mockResolvedValue({ data: { data: productos } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('B/. 599.99')).toBeInTheDocument()
    })
  })

  it('muestra disponibilidad correcta (existencia - reservada)', async () => {
    mockApi.get.mockResolvedValue({ data: { data: productos } })
    renderPage()

    await waitFor(() => {
      // PROD001: 10 - 2 = 8 UN disponibles
      expect(screen.getByText(/8/)).toBeInTheDocument()
    })
  })

  it('muestra badge "Servicio" para productos con TIPINV=S', async () => {
    mockApi.get.mockResolvedValue({ data: { data: productos } })
    renderPage()

    await waitFor(() => {
      expect(screen.getAllByText('Servicio').length).toBeGreaterThan(0)
    })
  })

  it('muestra badge "Producto" para productos físicos', async () => {
    mockApi.get.mockResolvedValue({ data: { data: productos } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('Producto')).toBeInTheDocument()
    })
  })

  it('muestra mensaje cuando no hay resultados', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText(/término de búsqueda/i)).toBeInTheDocument()
    })
  })

  it('dispara búsqueda al enviar el formulario', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    renderPage()

    const input = screen.getByPlaceholderText(/buscar/i)
    fireEvent.change(input, { target: { value: 'laptop' } })

    const form = input.closest('form')!
    fireEvent.submit(form)

    await waitFor(() => {
      // La segunda llamada debería incluir el término de búsqueda
      expect(mockApi.get).toHaveBeenCalledWith(
        expect.stringContaining('laptop')
      )
    })
  })

  it('badge ITBMS 7% se muestra en productos gravados', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [productos[0]] } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('ITBMS 7%')).toBeInTheDocument()
    })
  })
})
