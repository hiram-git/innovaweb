import { useState, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useMutation, useQuery } from '@tanstack/react-query'
import { Plus, Trash2, ChevronLeft, WifiOff, Package } from 'lucide-react'
import { api } from '@/lib/axios'
import { useFacturaStore } from '@/stores/facturaStore'
import { useOnlineStatus } from '@/hooks/useOnlineStatus'
import { queryClient } from '@/lib/queryClient'
import { db } from '@/lib/db'
import { Button } from '@/components/ui/Button'
import { Toast } from '@/components/ui/Toast'
import { ClienteSelector } from '@/components/ui/ClienteSelector'
import { BuscadorProductoModal } from '@/components/ui/BuscadorProductoModal'
import { useAuthStore } from '@/stores/authStore'
import type { ItemFactura, FormaPago, NuevaFacturaPayload } from '@/types'

/* ─── helpers ─── */

function calcItem(i: ItemFactura) {
  const base   = i.cantidad * (i.precio - (i.descuento || 0))
  const itbms  = base * (i.imppor / 100)
  return { base, itbms, total: base + itbms }
}

/* ─── Forma de pago ─── */
// API aliases CODTAR→CODINSTRUMENTO, NOMBRE→DESCRINSTRUMENTO for compatibility
type InstrumentType = { CODINSTRUMENTO: string; DESCRINSTRUMENTO: string; FUNCION: number }

function FormasPagoSection({
  total, formasPago, onChange,
}: { total: number; formasPago: FormaPago[]; onChange: (fps: FormaPago[]) => void }) {
  const { data: instrumentos } = useQuery({
    queryKey: ['instrumentos'],
    queryFn: () => api.get<InstrumentType[]>('/instrumentos').then(r => r.data),
    staleTime: Infinity,
  })

  const add = () => {
    const first = instrumentos?.[0]
    if (!first) return
    onChange([...formasPago, { instrumento: first.CODINSTRUMENTO, descripcion: first.DESCRINSTRUMENTO, monto: 0, referencia: '' }])
  }

  const update = (i: number, partial: Partial<FormaPago>) => {
    const updated = [...formasPago]
    updated[i] = { ...updated[i], ...partial }
    onChange(updated)
  }

  const remove = (i: number) => onChange(formasPago.filter((_, idx) => idx !== i))

  const pagado    = formasPago.reduce((s, fp) => s + (fp.monto || 0), 0)
  const cambio    = Math.max(0, pagado - total)
  const pendiente = Math.max(0, total - pagado)

  return (
    <div className="space-y-3">
      <div className="flex items-center justify-between">
        <p className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Formas de pago</p>
        <button type="button" onClick={add}
          className="flex items-center gap-1 text-xs text-orange-400 hover:text-orange-300">
          <Plus className="h-3.5 w-3.5" /> Agregar
        </button>
      </div>

      {formasPago.length === 0 ? (
        <p className="text-center text-sm text-slate-500 py-4">Sin formas de pago</p>
      ) : (
        <div className="space-y-2">
          {formasPago.map((fp, i) => (
            <div key={i} className="flex items-center gap-2">
              <select
                value={fp.instrumento}
                onChange={e => {
                  const inst = instrumentos?.find(ins => ins.CODINSTRUMENTO === e.target.value)
                  update(i, { instrumento: e.target.value, descripcion: inst?.DESCRINSTRUMENTO ?? '' })
                }}
                className="flex-1 rounded-lg border border-slate-700 bg-slate-800 py-1.5 px-2 text-sm text-white focus:border-orange-500 focus:outline-none"
              >
                {instrumentos?.map(ins => (
                  <option key={ins.CODINSTRUMENTO} value={ins.CODINSTRUMENTO}>{ins.DESCRINSTRUMENTO}</option>
                ))}
              </select>
              <input type="number" step="0.01" value={fp.monto || ''}
                onChange={e => update(i, { monto: parseFloat(e.target.value) || 0 })}
                placeholder="Monto"
                className="w-28 rounded-lg border border-slate-700 bg-slate-800 py-1.5 px-2 text-sm text-white focus:border-orange-500 focus:outline-none" />
              <input type="text" value={fp.referencia || ''}
                onChange={e => update(i, { referencia: e.target.value })}
                placeholder="Ref."
                className="w-24 rounded-lg border border-slate-700 bg-slate-800 py-1.5 px-2 text-sm text-white focus:border-orange-500 focus:outline-none" />
              <button type="button" onClick={() => remove(i)} className="text-red-400 hover:text-red-300 p-1">
                <Trash2 className="h-4 w-4" />
              </button>
            </div>
          ))}
        </div>
      )}

      <div className="rounded-lg bg-slate-800 p-3 grid grid-cols-3 gap-2 text-center text-xs">
        <div>
          <p className="text-slate-400">Total</p>
          <p className="text-white font-bold text-sm">${total.toFixed(2)}</p>
        </div>
        <div>
          <p className="text-slate-400">Pagado</p>
          <p className={`font-bold text-sm ${pagado >= total ? 'text-green-400' : 'text-yellow-400'}`}>${pagado.toFixed(2)}</p>
        </div>
        <div>
          {cambio > 0 ? (
            <>
              <p className="text-slate-400">Cambio</p>
              <p className="text-green-400 font-bold text-sm">${cambio.toFixed(2)}</p>
            </>
          ) : (
            <>
              <p className="text-slate-400">Pendiente</p>
              <p className={`font-bold text-sm ${pendiente > 0 ? 'text-red-400' : 'text-slate-500'}`}>${pendiente.toFixed(2)}</p>
            </>
          )}
        </div>
      </div>
    </div>
  )
}

/* ─── Página principal ─── */
export function NuevaFacturaPage() {
  const navigate        = useNavigate()
  const isOnline        = useOnlineStatus()
  const permisos        = useAuthStore(s => s.user?.permisos)
  const [toast, setToast]         = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const [savingOffline, setSavingOffline] = useState(false)
  const [showBuscador, setShowBuscador]   = useState(false)

  const {
    cliente, setCliente,
    items, addItem, removeItem, updateItem,
    tipoFactura, setTipoFactura,
    diasVencimiento, setDiasVencimiento,
    descuentoGlobal, setDescuentoGlobal,
    observacion, setObservacion,
    formasPago, setFormasPago,
    totales, calcularTotales,
    reset,
  } = useFacturaStore()

  const handleAddItem = useCallback((item: ItemFactura) => {
    addItem(item)
  }, [addItem])

  const mutation = useMutation({
    mutationFn: (payload: NuevaFacturaPayload) => api.post('/facturas', payload).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['facturas'] })
      reset()
      setToast({ type: 'success', message: 'Factura creada exitosamente' })
      setTimeout(() => navigate('/facturas'), 1500)
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al crear factura'
      setToast({ type: 'error', message: msg })
    },
  })

  const saveOffline = async (payload: NuevaFacturaPayload) => {
    setSavingOffline(true)
    try {
      await db.facturas_offline.add({
        tempId:   `offline-${Date.now()}`,
        payload,
        creadaEn: new Date(),
        estado:   'pendiente',
      })
      reset()
      setToast({ type: 'success', message: 'Sin conexión: factura guardada para sincronizar al reconectar' })
      setTimeout(() => navigate('/facturas'), 2000)
    } catch {
      setToast({ type: 'error', message: 'No se pudo guardar la factura localmente' })
    } finally {
      setSavingOffline(false)
    }
  }

  // ── Credit / discount derived rules ──────────────────────────────────────
  const percredito   = Number(cliente?.PERCREDITO  ?? 0)   // 0=no credit, 1=allowed
  const limitecre    = Number(cliente?.LIMITECRE   ?? 0)
  const pormaxdesglo = Number(cliente?.PORMAXDESGLO ?? 0)  // max global discount %
  const maxDescGlobal = pormaxdesglo > 0
    ? (totales.subtotal * pormaxdesglo) / 100
    : Infinity

  const handleSubmit = () => {
    if (!cliente) { setToast({ type: 'error', message: 'Seleccione un cliente' }); return }
    if (items.length === 0) { setToast({ type: 'error', message: 'Agregue al menos un ítem' }); return }

    // Credit checks (mirrors legacy tarea_factura.php)
    const saldo = totales.total - formasPago.reduce((s, fp) => s + (fp.monto || 0), 0)
    if (percredito === 0 && saldo > 0) {
      setToast({ type: 'error', message: 'El cliente no permite cancelar a crédito' }); return
    }
    if (percredito === 1 && limitecre > 0 && saldo > limitecre) {
      setToast({ type: 'error', message: `El monto a crédito ($${saldo.toFixed(2)}) supera el límite permitido ($${limitecre.toFixed(2)})` }); return
    }

    // Discount checks
    if (pormaxdesglo > 0 && descuentoGlobal > maxDescGlobal) {
      setToast({ type: 'error', message: `Descuento global máximo: $${maxDescGlobal.toFixed(2)} (${pormaxdesglo}% del subtotal)` }); return
    }

    if (tipoFactura === 'CONTADO' && formasPago.length === 0) {
      setToast({ type: 'error', message: 'Agregue al menos una forma de pago' }); return
    }

    const payload: NuevaFacturaPayload = {
      codcliente:      cliente.CODIGO,
      tipoFactura,
      diasVencimiento: tipoFactura === 'CREDITO' ? diasVencimiento : 0,
      descuentoGlobal,
      observacion,
      items: items.map(i => ({
        codpro:    i.codpro,
        descrip:   i.descrip,
        cantidad:  i.cantidad,
        precio:    i.precio,
        descuento: i.descuento ?? 0,
        imppor:    i.imppor,
      })),
      formasPago: tipoFactura === 'CONTADO' ? formasPago : [],
    }

    if (!isOnline) {
      void saveOffline(payload)
    } else {
      mutation.mutate(payload)
    }
  }

  return (
    <div className="max-w-4xl space-y-6">
      {toast && (
        <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />
      )}

      {/* Header */}
      <div className="flex items-center gap-3">
        <button onClick={() => navigate('/facturas')}
          className="text-slate-400 hover:text-white transition-colors">
          <ChevronLeft className="h-5 w-5" />
        </button>
        <h1 className="text-xl font-bold text-white">Nueva Factura</h1>
      </div>

      {/* Cliente */}
      <section className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
        <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Cliente</h2>
        <ClienteSelector value={cliente} onSelect={setCliente} />
        {cliente && (
          <div className="rounded-lg bg-slate-800 px-4 py-3 grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
            <div><span className="text-slate-400 text-xs">Nombre</span><p className="text-white font-medium">{cliente.NOMBRE}</p></div>
            <div><span className="text-slate-400 text-xs">RUC</span><p className="text-white font-mono">{cliente.RIF}</p></div>
            <div><span className="text-slate-400 text-xs">Tipo</span><p className="text-white">{cliente.TIPOCLI}</p></div>
            <div><span className="text-slate-400 text-xs">Teléfono</span><p className="text-white">{cliente.NUMTEL ?? '—'}</p></div>
          </div>
        )}
      </section>

      {/* Configuración */}
      <section className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
        <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Configuración</h2>
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div>
            <label className="mb-1 block text-xs text-slate-400">Tipo</label>
            <select
              value={tipoFactura}
              onChange={e => setTipoFactura(e.target.value as 'CONTADO' | 'CREDITO')}
              className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none"
            >
              <option value="CONTADO">Contado</option>
              <option value="CREDITO" disabled={percredito === 0}>
                Crédito{percredito === 0 ? ' (no permitido)' : ''}
              </option>
            </select>
            {percredito === 0 && cliente && (
              <p className="mt-1 text-xs text-red-400">Este cliente no tiene crédito habilitado</p>
            )}
            {percredito === 1 && limitecre > 0 && (
              <p className="mt-1 text-xs text-slate-500">Límite: ${limitecre.toFixed(2)}</p>
            )}
          </div>
          {tipoFactura === 'CREDITO' && (
            <div>
              <label className="mb-1 block text-xs text-slate-400">Días vencimiento</label>
              <input type="number" min={1} value={diasVencimiento}
                onChange={e => setDiasVencimiento(Number(e.target.value))}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none" />
            </div>
          )}
          {(permisos?.desctoglo ?? 1) !== 0 && (
            <div>
              <label className="mb-1 block text-xs text-slate-400">Descuento global $</label>
              <input type="number" step="0.01" min={0} value={descuentoGlobal}
                onChange={e => { setDescuentoGlobal(Number(e.target.value)); calcularTotales() }}
                className={`w-full rounded-lg border py-2 px-3 text-sm text-white focus:outline-none
                  ${pormaxdesglo > 0 && descuentoGlobal > maxDescGlobal
                    ? 'border-red-500 bg-red-900/20 focus:border-red-400'
                    : 'border-slate-700 bg-slate-800 focus:border-orange-500'}`}
              />
              {pormaxdesglo > 0 && (
                <p className={`mt-1 text-xs ${descuentoGlobal > maxDescGlobal ? 'text-red-400' : 'text-slate-500'}`}>
                  Máx: ${maxDescGlobal === Infinity ? '—' : maxDescGlobal.toFixed(2)} ({pormaxdesglo}%)
                </p>
              )}
            </div>
          )}
          <div className="sm:col-span-2">
            <label className="mb-1 block text-xs text-slate-400">Observación</label>
            <input type="text" value={observacion}
              onChange={e => setObservacion(e.target.value)}
              className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none" />
          </div>
        </div>
      </section>

      {/* Ítems */}
      <section className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Ítems</h2>
          <Button size="sm" variant="secondary" onClick={() => setShowBuscador(true)}>
            <Plus className="h-4 w-4 mr-1" />Agregar producto
          </Button>
        </div>

        {items.length === 0 ? (
          <div className="rounded-lg border-2 border-dashed border-slate-700 py-12 text-center">
            <Package className="mx-auto h-8 w-8 text-slate-600 mb-2" />
            <p className="text-sm text-slate-500">Sin productos · haz clic en "Agregar producto"</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="text-xs text-slate-400 border-b border-slate-700">
                <tr>
                  <th className="pb-2 text-left">Producto</th>
                  <th className="pb-2 text-right">Cant.</th>
                  <th className="pb-2 text-right">Precio</th>
                  <th className="pb-2 text-right">Desc.</th>
                  <th className="pb-2 text-right">ITBMS</th>
                  <th className="pb-2 text-right">Total</th>
                  <th className="pb-2"></th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800">
                {items.map((item, idx) => {
                  const { total } = calcItem(item)
                  return (
                    <tr key={idx} className="text-white">
                      <td className="py-2 pr-4 max-w-[180px]">
                        <p className="truncate">{item.descrip}</p>
                        <p className="text-xs text-slate-500 font-mono">{item.codpro}</p>
                      </td>
                      <td className="py-2 text-right">
                        <input type="number" step="0.001" value={item.cantidad}
                          onChange={e => updateItem(idx, { cantidad: parseFloat(e.target.value) || 0 })}
                          className="w-16 text-right bg-transparent border-b border-slate-700 text-sm text-white focus:border-orange-500 focus:outline-none" />
                      </td>
                      <td className="py-2 text-right font-mono">${Number(item.precio).toFixed(2)}</td>
                      <td className="py-2 text-right text-yellow-400">${Number(item.descuento || 0).toFixed(2)}</td>
                      <td className="py-2 text-right text-slate-400">{item.imppor}%</td>
                      <td className="py-2 text-right font-medium">${total.toFixed(2)}</td>
                      <td className="py-2 pl-2">
                        <button type="button" onClick={() => removeItem(idx)}
                          className="text-red-400 hover:text-red-300 p-0.5">
                          <Trash2 className="h-3.5 w-3.5" />
                        </button>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Totales */}
        <div className="flex justify-end">
          <div className="w-full max-w-xs space-y-1 text-sm">
            <div className="flex justify-between text-slate-400">
              <span>Subtotal</span><span>${totales.subtotal.toFixed(2)}</span>
            </div>
            <div className="flex justify-between text-slate-400">
              <span>ITBMS</span><span>${totales.itbms.toFixed(2)}</span>
            </div>
            {totales.descuento > 0 && (
              <div className="flex justify-between text-yellow-400">
                <span>Descuento</span><span>-${totales.descuento.toFixed(2)}</span>
              </div>
            )}
            <div className="flex justify-between border-t border-slate-700 pt-1 text-white font-bold text-base">
              <span>Total</span><span>${totales.total.toFixed(2)}</span>
            </div>
            {totales.cambio > 0 && (
              <div className="flex justify-between text-green-400">
                <span>Cambio</span><span>${totales.cambio.toFixed(2)}</span>
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Formas de pago (solo contado) */}
      {tipoFactura === 'CONTADO' && (
        <section className="rounded-lg border border-slate-700 bg-slate-900 p-5">
          <FormasPagoSection
            total={totales.total}
            formasPago={formasPago}
            onChange={setFormasPago}
          />
        </section>
      )}

      {/* Banner sin conexión */}
      {!isOnline && (
        <div className="flex items-center gap-2 rounded-lg border border-yellow-700 bg-yellow-900/20 px-4 py-3 text-sm text-yellow-300">
          <WifiOff className="h-4 w-4 shrink-0" />
          Sin conexión — la factura se guardará localmente y se sincronizará al reconectar.
        </div>
      )}

      {/* Acciones */}
      <div className="flex gap-3 justify-end pb-6">
        <Button variant="secondary" onClick={() => { reset(); navigate('/facturas') }}>
          Cancelar
        </Button>
        <Button
          onClick={handleSubmit}
          loading={mutation.isPending || savingOffline}
          disabled={mutation.isPending || savingOffline}
        >
          {isOnline ? 'Emitir Factura' : 'Guardar offline'}
        </Button>
      </div>

      {/* Modal buscador */}
      {showBuscador && (
        <BuscadorProductoModal
          modo="factura"
          ventamenos={permisos?.ventamenos}
          actfacexi={permisos?.actfacexi}
          cambiarprecio={permisos?.cambiarprecio}
          onSelect={item => { handleAddItem(item); setShowBuscador(false) }}
          onClose={() => setShowBuscador(false)}
        />
      )}
    </div>
  )
}
