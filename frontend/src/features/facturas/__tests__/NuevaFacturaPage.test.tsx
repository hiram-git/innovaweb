import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter } from 'react-router-dom'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { NuevaFacturaPage } from '../NuevaFacturaPage'

// ─── Mocks ───────────────────────────────────────────────────────────────────

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

const mockNavigate = vi.fn()
vi.mock('react-router-dom', async (importOriginal) => {
  const actual = await importOriginal<typeof import('react-router-dom')>()
  return { ...actual, useNavigate: () => mockNavigate }
})

// Mock the facturaStore so we control state
const mockReset = vi.fn()
const mockAddItem = vi.fn()
const mockSetCliente = vi.fn()
const mockSetFormasPago = vi.fn()
const mockSetTipoFactura = vi.fn()
const mockMutate = vi.fn()

vi.mock('@/stores/facturaStore', () => ({
  useFacturaStore: vi.fn(() => ({
    cliente:          null,
    setCliente:       mockSetCliente,
    items:            [],
    addItem:          mockAddItem,
    removeItem:       vi.fn(),
    updateItem:       vi.fn(),
    tipoFactura:      'CONTADO' as const,
    setTipoFactura:   mockSetTipoFactura,
    diasVencimiento:  30,
    setDiasVencimiento: vi.fn(),
    descuentoGlobal:  0,
    setDescuentoGlobal: vi.fn(),
    observacion:      '',
    setObservacion:   vi.fn(),
    formasPago:       [],
    setFormasPago:    mockSetFormasPago,
    totales:          { subtotal: 0, itbms: 0, descuento: 0, total: 0, cambio: 0 },
    calcularTotales:  vi.fn(),
    reset:            mockReset,
  })),
}))

import { useFacturaStore } from '@/stores/facturaStore'
import { api } from '@/lib/axios'
const mockApiNamed = vi.mocked(api)
const mockUseFacturaStore = vi.mocked(useFacturaStore)

// ─── Helpers ─────────────────────────────────────────────────────────────────

function renderPage() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  })
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <NuevaFacturaPage />
      </MemoryRouter>
    </QueryClientProvider>
  )
}

const fakeCliente = {
  CODIGO: 'CLI001', NOMBRE: 'Demo S.A.', RIF: '8-123-456', NIT: '01',
  TIPOCLI: 'Contribuyente', TIPOCOMERCIO: 1, DIRECC1: null, NUMTEL: '123-4567',
  DIRCORREO: null, DIASCRE: 30, CONESPECIAL: 0, PORRETIMP: 0,
  provincia: null, distrito: null, corregimiento: null,
}

const fakeItems = [
  { codpro: 'PROD1', descrip: 'Laptop HP', cantidad: 1, precio: 100, descuento: 0, imppor: 7 },
]

const fakeFormasPago = [
  { instrumento: 'EFE', descripcion: 'Efectivo', monto: 107, referencia: '' },
]

// ─── Tests ───────────────────────────────────────────────────────────────────

describe('NuevaFacturaPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    // Reset store to base state
    mockUseFacturaStore.mockReturnValue({
      cliente:          null,
      setCliente:       mockSetCliente,
      items:            [],
      addItem:          mockAddItem,
      removeItem:       vi.fn(),
      updateItem:       vi.fn(),
      tipoFactura:      'CONTADO' as const,
      setTipoFactura:   mockSetTipoFactura,
      diasVencimiento:  30,
      setDiasVencimiento: vi.fn(),
      descuentoGlobal:  0,
      setDescuentoGlobal: vi.fn(),
      observacion:      '',
      setObservacion:   vi.fn(),
      formasPago:       [],
      setFormasPago:    mockSetFormasPago,
      totales:          { subtotal: 0, itbms: 0, descuento: 0, total: 0, cambio: 0 },
      calcularTotales:  vi.fn(),
      reset:            mockReset,
    })
    mockApiNamed.get.mockResolvedValue({ data: { data: [] } })
  })

  it('muestra el título "Nueva Factura"', () => {
    renderPage()
    expect(screen.getByText('Nueva Factura')).toBeInTheDocument()
  })

  it('muestra la sección Cliente con buscador', () => {
    renderPage()
    expect(screen.getByText('Cliente')).toBeInTheDocument()
    expect(screen.getByPlaceholderText(/buscar cliente/i)).toBeInTheDocument()
  })

  it('muestra la sección Ítems con form para agregar', () => {
    renderPage()
    expect(screen.getByText('Ítems')).toBeInTheDocument()
    expect(screen.getByText(/agregar ítem/i)).toBeInTheDocument()
  })

  it('muestra la sección Formas de pago en modo CONTADO', () => {
    renderPage()
    expect(screen.getByText(/formas de pago/i)).toBeInTheDocument()
  })

  it('NO muestra Formas de pago en modo CREDITO', () => {
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      tipoFactura: 'CREDITO' as const,
    })
    renderPage()
    expect(screen.queryByText(/formas de pago/i)).not.toBeInTheDocument()
  })

  it('muestra toast de error si se intenta emitir sin cliente', async () => {
    renderPage()
    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(screen.getByText(/seleccione un cliente/i)).toBeInTheDocument()
    })
    expect(mockApiNamed.post).not.toHaveBeenCalled()
  })

  it('muestra toast de error si se intenta emitir sin ítems', async () => {
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      cliente: fakeCliente,
      items:   [],
    })
    renderPage()
    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(screen.getByText(/agregue al menos un ítem/i)).toBeInTheDocument()
    })
  })

  it('muestra toast de error si contado sin formas de pago', async () => {
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      cliente:    fakeCliente,
      items:      fakeItems,
      tipoFactura: 'CONTADO' as const,
      formasPago:  [],
    })
    renderPage()
    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(screen.getByText(/forma de pago/i)).toBeInTheDocument()
    })
  })

  it('llama a POST /facturas con el payload correcto en modo CONTADO', async () => {
    mockApiNamed.post.mockResolvedValue({ data: { message: 'OK' } })
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      cliente:     fakeCliente,
      items:       fakeItems,
      tipoFactura: 'CONTADO' as const,
      formasPago:  fakeFormasPago,
      totales:     { subtotal: 100, itbms: 7, descuento: 0, total: 107, cambio: 0 },
    })
    renderPage()

    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(mockApiNamed.post).toHaveBeenCalledWith('/facturas', expect.objectContaining({
        codcliente:  'CLI001',
        tipoFactura: 'CONTADO',
        formasPago:  fakeFormasPago,
      }))
    })
  })

  it('llama a POST /facturas sin formasPago en modo CREDITO', async () => {
    mockApiNamed.post.mockResolvedValue({ data: { message: 'OK' } })
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      cliente:         fakeCliente,
      items:           fakeItems,
      tipoFactura:     'CREDITO' as const,
      diasVencimiento: 15,
      formasPago:      fakeFormasPago, // store tiene datos, pero debe ignorarse
    })
    renderPage()

    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(mockApiNamed.post).toHaveBeenCalledWith('/facturas', expect.objectContaining({
        tipoFactura: 'CREDITO',
        diasVencimiento: 15,
        formasPago:  [],
      }))
    })
  })

  it('muestra toast de éxito y resetea el store tras crear factura', async () => {
    mockApiNamed.post.mockResolvedValue({ data: { message: 'OK' } })
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      cliente:     fakeCliente,
      items:       fakeItems,
      tipoFactura: 'CONTADO' as const,
      formasPago:  fakeFormasPago,
    })
    renderPage()

    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(screen.getByText(/factura creada exitosamente/i)).toBeInTheDocument()
      expect(mockReset).toHaveBeenCalled()
    })
  })

  it('muestra error API si falla POST /facturas', async () => {
    mockApiNamed.post.mockRejectedValue({
      response: { data: { message: 'Cliente sin crédito disponible.' } },
    })
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      cliente:     fakeCliente,
      items:       fakeItems,
      tipoFactura: 'CONTADO' as const,
      formasPago:  fakeFormasPago,
    })
    renderPage()

    fireEvent.click(screen.getByRole('button', { name: /emitir factura/i }))

    await waitFor(() => {
      expect(screen.getByText('Cliente sin crédito disponible.')).toBeInTheDocument()
    })
  })

  it('navega a /facturas al hacer clic en Cancelar', () => {
    renderPage()
    fireEvent.click(screen.getByRole('button', { name: /cancelar/i }))
    expect(mockNavigate).toHaveBeenCalledWith('/facturas')
  })

  it('muestra los totales correctamente', () => {
    mockUseFacturaStore.mockReturnValue({
      ...mockUseFacturaStore(),
      items:   fakeItems,
      totales: { subtotal: 100, itbms: 7, descuento: 5, total: 102, cambio: 5 },
    })
    renderPage()

    expect(screen.getByText('Subtotal')).toBeInTheDocument()
    expect(screen.getByText('ITBMS')).toBeInTheDocument()
    expect(screen.getByText('Total')).toBeInTheDocument()
  })
})
