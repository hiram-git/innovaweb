import { describe, it, expect, beforeEach } from 'vitest'
import { useFacturaStore } from '../facturaStore'

/**
 * Tests del store de facturas
 *
 * useFacturaStore administra el estado de la factura en construcción:
 * cliente, ítems, formas de pago, y cálculo de totales.
 */
describe('facturaStore', () => {
  // Reset antes de cada test para tener estado limpio
  beforeEach(() => {
    useFacturaStore.getState().reset()
  })

  // ─── addItem ──────────────────────────────────────────────────────────────

  describe('addItem', () => {
    it('agrega un ítem a la lista', () => {
      const { addItem, items } = useFacturaStore.getState()
      expect(items).toHaveLength(0)

      addItem({ codpro: 'P001', descrip: 'Producto 1', cantidad: 1, precio: 100, descuento: 0, imppor: 7 })

      expect(useFacturaStore.getState().items).toHaveLength(1)
    })

    it('puede agregar múltiples ítems', () => {
      const { addItem } = useFacturaStore.getState()
      addItem({ codpro: 'P001', descrip: 'A', cantidad: 1, precio: 100, descuento: 0, imppor: 7 })
      addItem({ codpro: 'P002', descrip: 'B', cantidad: 2, precio: 50,  descuento: 0, imppor: 7 })

      expect(useFacturaStore.getState().items).toHaveLength(2)
    })

    it('recalcula totales al agregar ítem', () => {
      useFacturaStore.getState().addItem({
        codpro: 'P001', descrip: 'X', cantidad: 1, precio: 100, descuento: 0, imppor: 7,
      })

      const { totales } = useFacturaStore.getState()
      expect(totales.subtotal).toBe(100)
      expect(totales.itbms).toBe(7)
      expect(totales.total).toBe(107)
    })
  })

  // ─── removeItem ───────────────────────────────────────────────────────────

  describe('removeItem', () => {
    it('elimina un ítem por índice', () => {
      const { addItem, removeItem } = useFacturaStore.getState()
      addItem({ codpro: 'P001', descrip: 'A', cantidad: 1, precio: 100, descuento: 0, imppor: 7 })
      addItem({ codpro: 'P002', descrip: 'B', cantidad: 1, precio: 50,  descuento: 0, imppor: 7 })

      removeItem(0)

      const { items } = useFacturaStore.getState()
      expect(items).toHaveLength(1)
      expect(items[0].codpro).toBe('P002')
    })

    it('recalcula totales al eliminar', () => {
      const { addItem, removeItem } = useFacturaStore.getState()
      addItem({ codpro: 'P001', descrip: 'A', cantidad: 1, precio: 100, descuento: 0, imppor: 7 })
      addItem({ codpro: 'P002', descrip: 'B', cantidad: 1, precio: 50,  descuento: 0, imppor: 0 })

      removeItem(0)  // eliminar P001 (107)

      const { totales } = useFacturaStore.getState()
      expect(totales.subtotal).toBe(50)
      expect(totales.total).toBe(50)
    })
  })

  // ─── updateItem ───────────────────────────────────────────────────────────

  describe('updateItem', () => {
    it('actualiza la cantidad y recalcula', () => {
      useFacturaStore.getState().addItem({
        codpro: 'P001', descrip: 'A', cantidad: 1, precio: 100, descuento: 0, imppor: 7,
      })
      useFacturaStore.getState().updateItem(0, { cantidad: 3 })

      const { totales } = useFacturaStore.getState()
      expect(totales.subtotal).toBe(300)
      expect(totales.itbms).toBe(21)
      expect(totales.total).toBe(321)
    })
  })

  // ─── calcularTotales ──────────────────────────────────────────────────────

  describe('calcularTotales', () => {
    it('calcula subtotal, ITBMS y total correctamente', () => {
      useFacturaStore.getState().addItem({
        codpro: 'A', descrip: 'X', cantidad: 2, precio: 50, descuento: 0, imppor: 7,
      })
      // subtotal = 2 * 50 = 100; itbms = 7; total = 107
      const { totales } = useFacturaStore.getState()
      expect(totales.subtotal).toBe(100)
      expect(totales.itbms).toBe(7)
      expect(totales.total).toBe(107)
    })

    it('aplica descuento por ítem en el cálculo de base', () => {
      useFacturaStore.getState().addItem({
        codpro: 'A', descrip: 'X', cantidad: 1, precio: 100, descuento: 10, imppor: 7,
      })
      // base = 100 - 10 = 90; itbms = 90 * 0.07 = 6.3; total = 96.3
      const { totales } = useFacturaStore.getState()
      expect(totales.subtotal).toBe(90)
      expect(totales.itbms).toBe(6.3)
      expect(totales.total).toBe(96.3)
    })

    it('aplica descuento global al total', () => {
      useFacturaStore.getState().addItem({
        codpro: 'A', descrip: 'X', cantidad: 1, precio: 100, descuento: 0, imppor: 7,
      })
      useFacturaStore.getState().setDescuentoGlobal(10)

      const { totales } = useFacturaStore.getState()
      expect(totales.descuento).toBe(10)
      expect(totales.total).toBe(97)  // 107 - 10
    })

    it('calcula cambio cuando pagado supera el total', () => {
      useFacturaStore.getState().addItem({
        codpro: 'A', descrip: 'X', cantidad: 1, precio: 100, descuento: 0, imppor: 0,
      })
      useFacturaStore.getState().setFormasPago([
        { instrumento: 'EFE', monto: 120, referencia: '' },
      ])

      const { totales } = useFacturaStore.getState()
      expect(totales.total).toBe(100)
      expect(totales.cambio).toBe(20)
    })

    it('no genera cambio negativo cuando pagado es menor al total', () => {
      useFacturaStore.getState().addItem({
        codpro: 'A', descrip: 'X', cantidad: 1, precio: 100, descuento: 0, imppor: 0,
      })
      useFacturaStore.getState().setFormasPago([
        { instrumento: 'EFE', monto: 50, referencia: '' },
      ])

      const { totales } = useFacturaStore.getState()
      expect(totales.cambio).toBe(0)
    })

    it('maneja múltiples tasas ITBMS en la misma factura', () => {
      const { addItem } = useFacturaStore.getState()
      addItem({ codpro: 'A', descrip: 'X', cantidad: 1, precio: 100, descuento: 0, imppor: 7  })
      addItem({ codpro: 'B', descrip: 'Y', cantidad: 1, precio: 200, descuento: 0, imppor: 10 })
      addItem({ codpro: 'C', descrip: 'Z', cantidad: 1, precio: 50,  descuento: 0, imppor: 0  })

      const { totales } = useFacturaStore.getState()
      expect(totales.subtotal).toBe(350)
      expect(totales.itbms).toBeCloseTo(7 + 20)   // 7 + 20 = 27
      expect(totales.total).toBeCloseTo(377)
    })
  })

  // ─── reset ────────────────────────────────────────────────────────────────

  describe('reset', () => {
    it('limpia todos los datos al resetear', () => {
      const s = useFacturaStore.getState()
      s.addItem({ codpro: 'A', descrip: 'X', cantidad: 1, precio: 100, descuento: 0, imppor: 7 })
      s.setCliente({ CODCLIENTE: 'CLI01', NOMBRE: 'Test', RIF: null, TIPOCLI: null, TEL1: null, EMAIL: null, DIRECC1: null, PROVINCIA: null, DISTRITO: null, CORREGIMIENTO: null, DIASCRE: null })
      s.setDescuentoGlobal(5)

      s.reset()

      const fresh = useFacturaStore.getState()
      expect(fresh.items).toHaveLength(0)
      expect(fresh.cliente).toBeNull()
      expect(fresh.descuentoGlobal).toBe(0)
      expect(fresh.totales.total).toBe(0)
    })
  })
})
