/**
 * Store de la factura en construcción
 * Mantiene el estado del formulario mientras el usuario agrega ítems
 */
import { create } from 'zustand'
import type { Cliente, ItemFactura, FormaPago, TipoFactura } from '@/types'

interface FacturaStore {
  // Cliente seleccionado
  cliente: Cliente | null
  setCliente: (c: Cliente | null) => void

  // Líneas de la factura
  items: ItemFactura[]
  addItem: (item: ItemFactura) => void
  updateItem: (index: number, item: Partial<ItemFactura>) => void
  removeItem: (index: number) => void

  // Configuración de la factura
  tipoFactura: TipoFactura
  setTipoFactura: (t: TipoFactura) => void
  diasVencimiento: number
  setDiasVencimiento: (d: number) => void
  descuentoGlobal: number
  setDescuentoGlobal: (d: number) => void
  observacion: string
  setObservacion: (o: string) => void

  // Formas de pago
  formasPago: FormaPago[]
  setFormasPago: (fps: FormaPago[]) => void

  // Totales calculados (readonly, se calculan automáticamente)
  totales: {
    subtotal: number
    itbms: number
    descuento: number
    total: number
    cambio: number
  }

  // Acciones
  calcularTotales: () => void
  reset: () => void
}

const initialState = {
  cliente: null,
  items: [],
  tipoFactura: 'CONTADO' as TipoFactura,
  diasVencimiento: 0,
  descuentoGlobal: 0,
  observacion: '',
  formasPago: [],
  totales: { subtotal: 0, itbms: 0, descuento: 0, total: 0, cambio: 0 },
}

export const useFacturaStore = create<FacturaStore>()((set, get) => ({
  ...initialState,

  setCliente: (c) => set({ cliente: c }),

  addItem: (item) => {
    set((s) => ({ items: [...s.items, item] }))
    get().calcularTotales()
  },

  updateItem: (index, partial) => {
    set((s) => {
      const items = [...s.items]
      items[index] = { ...items[index], ...partial }
      return { items }
    })
    get().calcularTotales()
  },

  removeItem: (index) => {
    set((s) => ({ items: s.items.filter((_, i) => i !== index) }))
    get().calcularTotales()
  },

  setTipoFactura: (t) => set({ tipoFactura: t }),
  setDiasVencimiento: (d) => set({ diasVencimiento: d }),
  setDescuentoGlobal: (d) => { set({ descuentoGlobal: d }); get().calcularTotales() },
  setObservacion: (o) => set({ observacion: o }),
  setFormasPago: (fps) => {
    set({ formasPago: fps })
    get().calcularTotales()
  },

  calcularTotales: () => {
    const { items, descuentoGlobal, formasPago } = get()
    let subtotal = 0
    let itbms    = 0

    for (const item of items) {
      const baseItem = item.cantidad * (item.precio - (item.descuento || 0))
      const itbmsItem = baseItem * (item.imppor / 100)
      subtotal += baseItem
      itbms    += itbmsItem
    }

    const descuento = descuentoGlobal
    const total     = subtotal + itbms - descuento
    const pagado    = formasPago.reduce((s, fp) => s + fp.monto, 0)
    const cambio    = Math.max(0, pagado - total)

    set({ totales: {
      subtotal: +subtotal.toFixed(2),
      itbms:    +itbms.toFixed(2),
      descuento:+descuento.toFixed(2),
      total:    +total.toFixed(2),
      cambio:   +cambio.toFixed(2),
    }})
  },

  reset: () => set(initialState),
}))
