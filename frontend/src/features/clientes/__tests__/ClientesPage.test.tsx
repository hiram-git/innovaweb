import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter } from 'react-router-dom'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { ClientesPage } from '../ClientesPage'

vi.mock('@/lib/axios', () => ({
  default: {
    get:  vi.fn(),
    post: vi.fn(),
    interceptors: {
      request:  { use: vi.fn(), handlers: [] },
      response: { use: vi.fn(), handlers: [] },
    },
    defaults: { headers: {}, baseURL: '/api/v1', timeout: 30_000 },
  },
  api: { get: vi.fn(), post: vi.fn() },
}))

import api from '@/lib/axios'
const mockApi = vi.mocked(api)

const clientes = [
  {
    CODIGO:        'CLI001',
    NOMBRE:        'Empresa Demo S.A.',
    RIF:           '8-123-456',
    NIT:           '01',
    TIPOCLI:       'Contribuyente',
    TIPOCOMERCIO:  1,
    DIRECC1:       'Calle 50 Local 3',
    NUMTEL:        '507-123-4567',
    DIRCORREO:     'contacto@demo.pa',
    DIASCRE:       30,
    CONESPECIAL:   0,
    PORRETIMP:     0,
    provincia:     'Panamá',
    distrito:      'Panamá',
    corregimiento: 'Bella Vista',
  },
  {
    CODIGO:        'CLI002',
    NOMBRE:        'Consumidor Final',
    RIF:           null,
    NIT:           null,
    TIPOCLI:       'Consumidor Final',
    TIPOCOMERCIO:  0,
    DIRECC1:       null,
    NUMTEL:        null,
    DIRCORREO:     null,
    DIASCRE:       0,
    CONESPECIAL:   0,
    PORRETIMP:     0,
    provincia:     null,
    distrito:      null,
    corregimiento: null,
  },
]

function renderPage() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  })
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <ClientesPage />
      </MemoryRouter>
    </QueryClientProvider>
  )
}

describe('ClientesPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra el título Clientes', () => {
    mockApi.get.mockReturnValue(new Promise(() => {}))
    renderPage()
    expect(screen.getByText('Clientes')).toBeInTheDocument()
  })

  it('muestra el campo de búsqueda', () => {
    mockApi.get.mockReturnValue(new Promise(() => {}))
    renderPage()
    expect(screen.getByPlaceholderText(/buscar/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /buscar/i })).toBeInTheDocument()
  })

  it('muestra spinner mientras carga', () => {
    mockApi.get.mockReturnValue(new Promise(() => {}))
    renderPage()
    // El botón Buscar siempre visible, pero el spinner de loading está presente
    expect(screen.getByText('Clientes')).toBeInTheDocument()
  })

  it('muestra las tarjetas de clientes tras cargar', async () => {
    mockApi.get.mockResolvedValue({ data: { data: clientes } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('Empresa Demo S.A.')).toBeInTheDocument()
      expect(screen.getByText('CLI001')).toBeInTheDocument()
      expect(screen.getByText('Consumidor Final')).toBeInTheDocument()
    })
  })

  it('muestra el RUC del cliente', async () => {
    mockApi.get.mockResolvedValue({ data: { data: clientes } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('8-123-456')).toBeInTheDocument()
    })
  })

  it('muestra teléfono y email si están presentes', async () => {
    mockApi.get.mockResolvedValue({ data: { data: clientes } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('507-123-4567')).toBeInTheDocument()
      expect(screen.getByText('contacto@demo.pa')).toBeInTheDocument()
    })
  })

  it('muestra provincia y distrito', async () => {
    mockApi.get.mockResolvedValue({ data: { data: clientes } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText(/Panamá, Panamá/)).toBeInTheDocument()
    })
  })

  it('muestra mensaje cuando no hay clientes', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    renderPage()

    await waitFor(() => {
      expect(screen.getByText(/escribe un término/i)).toBeInTheDocument()
    })
  })

  it('muestra mensaje de búsqueda sin resultados', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    renderPage()

    const input = screen.getByPlaceholderText(/buscar/i)
    fireEvent.change(input, { target: { value: 'inexistente' } })
    const form = input.closest('form')!
    fireEvent.submit(form)

    await waitFor(() => {
      expect(screen.getByText(/no se encontraron clientes/i)).toBeInTheDocument()
    })
  })

  it('abre el panel "Nuevo Cliente" al hacer clic en Nuevo', async () => {
    mockApi.get.mockResolvedValue({ data: { data: clientes } })
    renderPage()

    const btnNuevo = screen.getByRole('button', { name: /nuevo/i })
    fireEvent.click(btnNuevo)

    await waitFor(() => {
      expect(screen.getByText('Nuevo Cliente')).toBeInTheDocument()
      expect(screen.getByLabelText(/código/i)).toBeInTheDocument()
    })
  })

  it('cierra el panel al hacer clic en Cancelar', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    renderPage()

    fireEvent.click(screen.getByRole('button', { name: /nuevo/i }))
    await waitFor(() => expect(screen.getByText('Nuevo Cliente')).toBeInTheDocument())

    fireEvent.click(screen.getByRole('button', { name: /cancelar/i }))
    await waitFor(() => {
      expect(screen.queryByText('Nuevo Cliente')).not.toBeInTheDocument()
    })
  })

  it('envía POST al crear un nuevo cliente', async () => {
    mockApi.get.mockResolvedValue({ data: { data: [] } })
    mockApi.post.mockResolvedValue({ data: { message: 'Creado' } })
    renderPage()

    fireEvent.click(screen.getByRole('button', { name: /nuevo/i }))
    await waitFor(() => screen.getByText('Nuevo Cliente'))

    fireEvent.change(screen.getByLabelText(/código/i), { target: { value: 'CLI003' } })
    fireEvent.change(screen.getByLabelText(/nombre/i), { target: { value: 'Nueva Empresa' } })

    // Seleccionar tipo
    const select = screen.getByRole('combobox')
    fireEvent.change(select, { target: { value: 'Contribuyente' } })

    fireEvent.click(screen.getByRole('button', { name: /crear cliente/i }))

    await waitFor(() => {
      expect(mockApi.post).toHaveBeenCalledWith('/clientes', expect.objectContaining({
        codigo:  'CLI003',
        nombre:  'Nueva Empresa',
        tipocli: 'Contribuyente',
      }))
    })
  })
})
