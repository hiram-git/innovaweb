/**
 * NuevaSolicitudPage — formulario compartido para Presupuesto y Pedido.
 *
 * Diferencias vs. NuevaFacturaPage:
 *  - No solicita descuento global
 *  - No solicita formas de pago
 *  - Tipo de documento viene de la prop `tipo`
 */
import { useState, useCallback } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import { useMutation } from '@tanstack/react-query'
import { ChevronLeft, Plus, Trash2, ClipboardList, ShoppingCart, Package } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { BuscadorProductoModal } from '@/components/ui/BuscadorProductoModal'
import { Button } from '@/components/ui/Button'
import { Toast } from '@/components/ui/Toast'
import { ClienteSelector } from '@/components/ui/ClienteSelector'
import { useAuthStore } from '@/stores/authStore'
import type { Cliente, ItemFactura } from '@/types'

// ─── Helpers ──────────────────────────────────────────────────────────────────

function calcItem(i: ItemFactura) {
  const base  = i.cantidad * i.precio
  const itbms = base * (i.imppor / 100)
  return { base, itbms, total: base + itbms }
}

// ─── Props ────────────────────────────────────────────────────────────────────

interface Props {
  tipo: 'presupuesto' | 'pedido'
}

// ─── Componente ───────────────────────────────────────────────────────────────

export function NuevaSolicitudPage({ tipo }: Props) {
  const navigate   = useNavigate()
  const location   = useLocation()
  const permisos   = useAuthStore(s => s.user?.permisos)
  const clienteNav = (location.state as { cliente?: Cliente } | null)?.cliente

  const [cliente,     setCliente]     = useState<Cliente | null>(clienteNav ?? null)
  const [items,       setItems]       = useState<ItemFactura[]>([])
  const [observacion, setObservacion] = useState('')
  const [showModal,   setShowModal]   = useState(false)
  const [toast,       setToast]       = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  const backPath = tipo === 'presupuesto' ? '/presupuestos' : '/pedidos'
  const endpoint = tipo === 'presupuesto' ? '/presupuestos' : '/pedidos'
  const Icon     = tipo === 'presupuesto' ? ClipboardList : ShoppingCart
  const label    = tipo === 'presupuesto' ? 'Presupuesto' : 'Pedido'

  // ── Mutación ────────────────────────────────────────────────────────────────

  const mutation = useMutation({
    mutationFn: (payload: object) => api.post(endpoint, payload).then(r => r.data),
    onSuccess: (res) => {
      queryClient.invalidateQueries({ queryKey: [tipo === 'presupuesto' ? 'presupuestos' : 'pedidos'] })
      setToast({ type: 'success', message: `${label} ${res.numref ?? ''} creado exitosamente` })
      setTimeout(() => navigate(backPath), 1500)
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
        ?? `Error al crear el ${label.toLowerCase()}`
      setToast({ type: 'error', message: msg })
    },
  })

  // ── Items ────────────────────────────────────────────────────────────────────

  const handleAddItem = useCallback((item: ItemFactura) => {
    setItems(prev => {
      // Si ya existe el mismo producto, incrementar cantidad
      const idx = prev.findIndex(i => i.codpro === item.codpro)
      if (idx >= 0) {
        const updated = [...prev]
        updated[idx] = { ...updated[idx], cantidad: updated[idx].cantidad + item.cantidad }
        return updated
      }
      return [...prev, item]
    })
  }, [])

  const updateItem = (idx: number, patch: Partial<ItemFactura>) => {
    setItems(prev => prev.map((it, i) => i === idx ? { ...it, ...patch } : it))
  }

  const removeItem = (idx: number) => {
    setItems(prev => prev.filter((_, i) => i !== idx))
  }

  // ── Totales ──────────────────────────────────────────────────────────────────

  const totales = items.reduce(
    (acc, it) => {
      const { base, itbms, total } = calcItem(it)
      return { subtotal: acc.subtotal + base, itbms: acc.itbms + itbms, total: acc.total + total }
    },
    { subtotal: 0, itbms: 0, total: 0 }
  )

  // ── Submit ───────────────────────────────────────────────────────────────────

  const handleSubmit = () => {
    if (!cliente) { setToast({ type: 'error', message: 'Seleccione un cliente' }); return }
    if (items.length === 0) { setToast({ type: 'error', message: 'Agregue al menos un ítem' }); return }

    mutation.mutate({
      codcliente:      cliente.CODIGO,
      descuentoGlobal: 0,
      items: items.map(i => ({
        codpro:    i.codpro,
        descrip:   i.descrip,
        cantidad:  i.cantidad,
        precio:    i.precio,
        descuento: 0,
        imppor:    i.imppor,
      })),
      observacion,
    })
  }

  // ── Render ───────────────────────────────────────────────────────────────────

  return (
    <div className="max-w-4xl space-y-6">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      {/* Header */}
      <div className="flex items-center gap-3">
        <button onClick={() => navigate(backPath)} className="text-slate-400 hover:text-white transition-colors">
          <ChevronLeft className="h-5 w-5" />
        </button>
        <Icon className="h-5 w-5 text-orange-400" />
        <h1 className="text-xl font-bold text-white">Nuevo {label}</h1>
      </div>

      {/* Cliente */}
      <section className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
        <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Cliente</h2>
        <ClienteSelector value={cliente} onSelect={setCliente} />
        {cliente && (
          <div className="rounded-lg bg-slate-800 px-4 py-3 grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
            <div><span className="text-slate-400 text-xs">Nombre</span><p className="text-white font-medium">{cliente.NOMBRE}</p></div>
            <div><span className="text-slate-400 text-xs">RUC</span><p className="text-white font-mono">{cliente.RIF ?? '—'}</p></div>
            <div><span className="text-slate-400 text-xs">Tipo</span><p className="text-white">{cliente.TIPOCLI ?? '—'}</p></div>
            <div><span className="text-slate-400 text-xs">Teléfono</span><p className="text-white">{cliente.NUMTEL ?? '—'}</p></div>
          </div>
        )}
      </section>

      {/* Observación */}
      <section className="rounded-lg border border-slate-700 bg-slate-900 p-5">
        <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-3">Observación</h2>
        <input
          type="text"
          value={observacion}
          onChange={e => setObservacion(e.target.value)}
          placeholder="Opcional…"
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none"
        />
      </section>

      {/* Ítems */}
      <section className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Ítems</h2>
          <Button size="sm" variant="secondary" onClick={() => setShowModal(true)}>
            <Plus className="h-4 w-4 mr-1" />Agregar producto
          </Button>
        </div>

        {items.length === 0 ? (
          <div className="rounded-lg border-2 border-dashed border-slate-700 py-12 text-center">
            <Package className="mx-auto h-8 w-8 text-slate-600 mb-2" />
            <p className="text-sm text-slate-500">Sin productos · haz clic en "Agregar producto"</p>
          </div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="text-xs text-slate-400 border-b border-slate-700">
                  <tr>
                    <th className="pb-2 text-left">Producto</th>
                    <th className="pb-2 text-right">Cant.</th>
                    <th className="pb-2 text-right">Precio</th>
                    <th className="pb-2 text-right">ITBMS</th>
                    <th className="pb-2 text-right">Total</th>
                    <th className="pb-2" />
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
                          <input
                            type="number" step="0.001" min="0.001"
                            value={item.cantidad}
                            onChange={e => updateItem(idx, { cantidad: parseFloat(e.target.value) || 0 })}
                            className="w-16 text-right bg-transparent border-b border-slate-700 text-sm text-white focus:border-orange-500 focus:outline-none"
                          />
                        </td>
                        <td className="py-2 text-right">
                          <input
                            type="number" step="0.01" min="0"
                            value={item.precio}
                            onChange={e => updateItem(idx, { precio: parseFloat(e.target.value) || 0 })}
                            className="w-20 text-right bg-transparent border-b border-slate-700 text-sm text-white font-mono focus:border-orange-500 focus:outline-none"
                          />
                        </td>
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

            {/* Totales */}
            <div className="flex justify-end">
              <div className="w-full max-w-xs space-y-1 text-sm">
                <div className="flex justify-between text-slate-400">
                  <span>Subtotal</span><span>${totales.subtotal.toFixed(2)}</span>
                </div>
                <div className="flex justify-between text-slate-400">
                  <span>ITBMS</span><span>${totales.itbms.toFixed(2)}</span>
                </div>
                <div className="flex justify-between border-t border-slate-700 pt-1 text-white font-bold text-base">
                  <span>Total</span><span>${totales.total.toFixed(2)}</span>
                </div>
              </div>
            </div>
          </>
        )}
      </section>

      {/* Submit */}
      <div className="flex justify-end gap-3 pb-8">
        <Button variant="ghost" onClick={() => navigate(backPath)}>Cancelar</Button>
        <Button
          loading={mutation.isPending}
          disabled={!cliente || items.length === 0}
          onClick={handleSubmit}
        >
          Guardar {label}
        </Button>
      </div>

      {/* Modal buscador */}
      {showModal && (
        <BuscadorProductoModal
          modo={tipo}
          ventamenos={permisos?.ventamenos}
          actfacexi={permisos?.actfacexi}
          cambiarprecio={permisos?.cambiarprecio}
          onSelect={item => { handleAddItem(item); setShowModal(false) }}
          onClose={() => setShowModal(false)}
        />
      )}
    </div>
  )
}
