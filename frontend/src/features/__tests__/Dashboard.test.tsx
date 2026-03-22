import { render, screen, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter } from 'react-router-dom'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { Dashboard } from '../Dashboard'

vi.mock('@/lib/axios', () => ({
  default: {
    get: vi.fn(),
    interceptors: {
      request:  { use: vi.fn(), handlers: [] },
      response: { use: vi.fn(), handlers: [] },
    },
    defaults: { headers: {}, baseURL: '/api/v1', timeout: 30_000 },
  },
  api: { get: vi.fn() },
}))

import api from '@/lib/axios'
const mockApi = vi.mocked(api)

const statsData = {
  kpis: {
    facturas_hoy:     5,
    total_cobrar:     12345.67,
    fe_aceptadas_mes: 42,
    clientes_activos: 18,
  },
  ultimas_facturas: [
    {
      CONTROLMAESTRO: 'CTL001',
      NROFAC:         'FAC-0001',
      NOMCLIENTE:     'Cliente Demo S.A.',
      FECHA:          '2026-03-22T00:00:00Z',
      TIPTRAN:        'CONTADO',
      MONTOTOT:       150.00,
      MONTOSAL:       0,
      MONTOIMP:       9.87,
      FE_ESTADO:      'ACEPTADO',
      FE_MENSAJE:     null,
      CUFE:           'abc123',
      INTEGRADO:      0,
    },
    {
      CONTROLMAESTRO: 'CTL002',
      NROFAC:         'FAC-0002',
      NOMCLIENTE:     'Otro Cliente',
      FECHA:          '2026-03-21T00:00:00Z',
      TIPTRAN:        'CREDITO',
      MONTOTOT:       300.00,
      MONTOSAL:       300.00,
      MONTOIMP:       19.74,
      FE_ESTADO:      'PENDIENTE',
      FE_MENSAJE:     null,
      CUFE:           null,
      INTEGRADO:      0,
    },
  ],
}

function renderPage() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <Dashboard />
      </MemoryRouter>
    </QueryClientProvider>
  )
}

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra el título Dashboard', () => {
    mockApi.get.mockReturnValue(new Promise(() => {}))
    renderPage()
    expect(screen.getByText('Dashboard')).toBeInTheDocument()
  })

  it('muestra skeletons mientras carga', () => {
    mockApi.get.mockReturnValue(new Promise(() => {}))
    renderPage()
    // Los 4 labels de KPI siempre están presentes
    expect(screen.getByText('Facturas hoy')).toBeInTheDocument()
    expect(screen.getByText('Clientes activos')).toBeInTheDocument()
    expect(screen.getByText('Por cobrar')).toBeInTheDocument()
    expect(screen.getByText(/FE aceptadas/i)).toBeInTheDocument()
  })

  it('muestra los KPIs correctamente tras cargar', async () => {
    mockApi.get.mockResolvedValue({ data: statsData })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('5')).toBeInTheDocument()   // facturas_hoy
      expect(screen.getByText('18')).toBeInTheDocument()  // clientes_activos
      expect(screen.getByText('42')).toBeInTheDocument()  // fe_aceptadas_mes
    })
  })

  it('formatea el total por cobrar como B/.', async () => {
    mockApi.get.mockResolvedValue({ data: statsData })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText(/B\/\.12\.345,67|B\/\.12,345\.67|12\.345/)).toBeInTheDocument()
    })
  })

  it('muestra las últimas facturas con número y cliente', async () => {
    mockApi.get.mockResolvedValue({ data: statsData })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('FAC-0001')).toBeInTheDocument()
      expect(screen.getByText('Cliente Demo S.A.')).toBeInTheDocument()
      expect(screen.getByText('FAC-0002')).toBeInTheDocument()
    })
  })

  it('muestra badge Contado y Crédito según TIPTRAN', async () => {
    mockApi.get.mockResolvedValue({ data: statsData })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('Contado')).toBeInTheDocument()
      expect(screen.getByText('Crédito')).toBeInTheDocument()
    })
  })

  it('muestra el mensaje de error si falla la API', async () => {
    mockApi.get.mockRejectedValue(new Error('Network Error'))
    renderPage()

    await waitFor(() => {
      expect(screen.getByText(/error al cargar/i)).toBeInTheDocument()
    })
  })

  it('muestra "No hay facturas recientes" si la lista está vacía', async () => {
    mockApi.get.mockResolvedValue({
      data: { ...statsData, ultimas_facturas: [] },
    })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText(/no hay facturas recientes/i)).toBeInTheDocument()
    })
  })

  it('contiene el enlace "Ver todas las facturas"', async () => {
    mockApi.get.mockResolvedValue({ data: statsData })
    renderPage()

    await waitFor(() => {
      const link = screen.getByRole('link', { name: /ver todas las facturas/i })
      expect(link).toBeInTheDocument()
      expect(link).toHaveAttribute('href', '/facturas')
    })
  })
})
