/**
 * BuscadorProductoModal
 *
 * Modal de búsqueda y selección de producto compartido entre
 * Presupuestos, Pedidos y Facturas.
 *
 * Paso 1 — búsqueda: muestra lista de productos con disponibilidad.
 * Paso 2 — confirmación: ingresa cantidad, precio e ITBMS antes de agregar.
 *
 * modo:
 *  'presupuesto' — todos los productos son seleccionables (no mueve inventario)
 *  'pedido'      — advierte si no hay stock; bloquea productos sin disponibilidad
 *                  excepto servicios (TIPINV S/SRV) y compuestos (PROCOMPUESTO=1)
 *  'factura'     — igual que pedido
 */
import { useState, useEffect, useRef } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Search, ArrowLeft, Package, X, AlertTriangle, CheckCircle, Layers, ShoppingCart } from 'lucide-react'
import { api } from '@/lib/axios'
import { Button } from './Button'
import type { ItemFactura } from '@/types'

// ─── Tipos ────────────────────────────────────────────────────────────────────

interface ProductoBusqueda {
  CODPRO:         string
  DESCRIP1:       string
  PRECVEN1:       number | null
  EXISTENCIA:     number | null
  CANRESERVADA:   number | null
  CANVEN:         number | null
  PROCOMPUESTO:   number | null
  TIPINV:         string | null
  IMPPOR:         number | null
  EXENTO:         number | null
  CANTIDAD_EMP:   number | null
  PRECIO_EMPAQUE: number | null
}

interface Componente {
  CODPRO:    string
  DESCRIP1:  string
  CANTIDAD:  number
  PRECVEN1:  number
  disponible: number
  TIPINV:    string
  IMPPOR:    number
}

const ITBMS_RATES = [0, 7, 10, 15] as const

export interface BuscadorProductoModalProps {
  modo:          'presupuesto' | 'pedido' | 'factura'
  onSelect:      (item: ItemFactura) => void
  onClose:       () => void
  ventamenos?:   number   // 1 = puede vender con stock en negativo
  actfacexi?:    number   // 1 = puede facturar ignorando stock
  cambiarprecio?: number  // 0 = precio readonly (no puede cambiarlo)
}

// ─── Helpers de disponibilidad ────────────────────────────────────────────────

function getDisponible(p: ProductoBusqueda): number {
  return (p.EXISTENCIA ?? 0) - (p.CANRESERVADA ?? 0) - (p.CANVEN ?? 0)
}

function isServicio(p: ProductoBusqueda): boolean {
  return ['S', 'SRV', '1'].includes(p.TIPINV ?? '')
}

function isCompuesto(p: ProductoBusqueda): boolean {
  return (p.PROCOMPUESTO ?? 0) == 1
}

function tieneEmpaque(p: ProductoBusqueda): boolean {
  return (p.CANTIDAD_EMP ?? 0) > 0
}

function puedeSeleccionar(
  p: ProductoBusqueda,
  modo: BuscadorProductoModalProps['modo'],
  ventamenos = 0,
  actfacexi  = 0,
): boolean {
  if (modo === 'presupuesto') return true
  // mirrors buscar_prod.php logic
  return isServicio(p) || isCompuesto(p) || getDisponible(p) > 0
      || ventamenos === 1 || actfacexi === 1
}

// ─── Sub-modal: componentes de un producto compuesto ─────────────────────────

function ComponentesModal({
  codpro,
  descrip,
  onClose,
  onSelectComponente,
}: {
  codpro:               string
  descrip:              string
  onClose:              () => void
  onSelectComponente:   (c: Componente) => void
}) {
  const { data, isLoading } = useQuery({
    queryKey: ['componentes', codpro],
    queryFn: () =>
      api.get<{ data: Componente[] }>(`/inventario/${encodeURIComponent(codpro)}/componentes`)
        .then(r => r.data.data),
    staleTime: 60_000,
  })

  return (
    <>
      <div className="fixed inset-0 bg-black/80 z-60" onClick={onClose} />
      <div className="fixed inset-0 z-70 flex items-center justify-center p-4">
        <div className="w-full max-w-2xl max-h-[80vh] flex flex-col rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl overflow-hidden">

          {/* Header */}
          <div className="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-slate-800 shrink-0">
            <button onClick={onClose} className="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-slate-800">
              <X className="h-5 w-5" />
            </button>
            <div className="flex-1 min-w-0">
              <p className="text-xs text-slate-400">Componentes de producto compuesto</p>
              <p className="text-sm font-medium text-white truncate">{descrip}</p>
            </div>
          </div>

          {/* Body */}
          <div className="flex-1 overflow-y-auto">
            {isLoading ? (
              <div className="flex justify-center py-12">
                <div className="h-6 w-6 animate-spin rounded-full border-2 border-slate-600 border-t-orange-500" />
              </div>
            ) : !data?.length ? (
              <div className="py-12 text-center text-slate-500 text-sm">
                Este producto no tiene componentes registrados.
              </div>
            ) : (
              <table className="w-full text-sm">
                <thead className="text-xs text-slate-400 border-b border-slate-800 sticky top-0 bg-slate-900">
                  <tr>
                    <th className="px-4 py-2 text-left">Código</th>
                    <th className="px-4 py-2 text-left">Descripción</th>
                    <th className="px-4 py-2 text-right">Cant.</th>
                    <th className="px-4 py-2 text-right">Precio</th>
                    <th className="px-4 py-2 text-right">Disp.</th>
                    <th className="px-4 py-2"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800">
                  {data.map(c => (
                    <tr key={c.CODPRO} className="text-white hover:bg-slate-800/50">
                      <td className="px-4 py-2 font-mono text-xs text-orange-400">{c.CODPRO}</td>
                      <td className="px-4 py-2">{c.DESCRIP1}</td>
                      <td className="px-4 py-2 text-right text-slate-300">{c.CANTIDAD}</td>
                      <td className="px-4 py-2 text-right font-mono">${Number(c.PRECVEN1).toFixed(2)}</td>
                      <td className={`px-4 py-2 text-right font-medium ${c.disponible > 0 ? 'text-green-400' : 'text-red-400'}`}>
                        {Math.round(c.disponible)}
                      </td>
                      <td className="px-4 py-2 text-right">
                        <button
                          type="button"
                          onClick={() => onSelectComponente(c)}
                          className="text-xs text-orange-400 hover:text-orange-300 px-2 py-0.5 rounded border border-orange-500/40 hover:border-orange-400"
                        >
                          Agregar
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        </div>
      </div>
    </>
  )
}

// ─── Componente principal ─────────────────────────────────────────────────────

export function BuscadorProductoModal({
  modo, onSelect, onClose,
  ventamenos = 0, actfacexi = 0, cambiarprecio = 1,
}: BuscadorProductoModalProps) {
  const [q, setQ]             = useState('')
  const [dq, setDq]           = useState('')
  const [paso, setPaso]       = useState<1 | 2>(1)
  const [prod, setProd]       = useState<ProductoBusqueda | null>(null)
  const [qty, setQty]         = useState<number>(1)
  const [precio, setPrecio]   = useState<number>(0)
  const [imppor, setImppor]   = useState<number>(7)
  const [compuesto, setCompuesto] = useState<ProductoBusqueda | null>(null)
  const inputRef              = useRef<HTMLInputElement>(null)

  // Debounce
  useEffect(() => {
    const t = setTimeout(() => setDq(q), 300)
    return () => clearTimeout(t)
  }, [q])

  useEffect(() => { inputRef.current?.focus() }, [])

  const { data, isFetching } = useQuery({
    queryKey: ['buscador-productos', dq],
    queryFn: () =>
      api.get<{ data: ProductoBusqueda[] }>('/inventario', { params: { q: dq, per_page: 40 } })
        .then(r => r.data.data),
    enabled: dq.length >= 2,
    staleTime: 30_000,
  })

  // ── Paso 1: seleccionar producto ──────────────────────────────────────────

  const handleSelectProd = (p: ProductoBusqueda) => {
    if (!puedeSeleccionar(p, modo, ventamenos, actfacexi)) return
    setProd(p)
    setQty(1)
    setPrecio(Number(p.PRECVEN1 ?? 0))
    setImppor(Number(p.IMPPOR ?? 7))
    setPaso(2)
  }

  const handleSelectEmpaque = (p: ProductoBusqueda) => {
    setProd(p)
    setQty(Number(p.CANTIDAD_EMP ?? 1))
    setPrecio(Number(p.PRECIO_EMPAQUE ?? p.PRECVEN1 ?? 0))
    setImppor(Number(p.IMPPOR ?? 7))
    setPaso(2)
  }

  // ── Componente seleccionado desde modal de componentes ────────────────────

  const handleSelectComponente = (c: Componente) => {
    setCompuesto(null)
    onSelect({
      codpro:    c.CODPRO,
      descrip:   c.DESCRIP1,
      cantidad:  c.CANTIDAD,
      precio:    c.PRECVEN1,
      descuento: 0,
      imppor:    c.IMPPOR,
    })
    onClose()
  }

  // ── Paso 2: confirmar item ────────────────────────────────────────────────

  const handleConfirm = () => {
    if (!prod || qty <= 0) return
    onSelect({
      codpro:    prod.CODPRO,
      descrip:   prod.DESCRIP1,
      cantidad:  qty,
      precio:    precio,
      descuento: 0,
      imppor:    imppor,
    })
    onClose()
  }

  const disponible = prod ? getDisponible(prod) : 0

  return (
    <>
      {/* Overlay */}
      <div className="fixed inset-0 bg-black/70 z-40" onClick={onClose} />

      {/* Modal */}
      <div className="fixed inset-0 z-50 flex items-start justify-center sm:items-center p-0 sm:p-4">
        <div className="relative w-full sm:max-w-2xl h-full sm:h-auto sm:max-h-[85vh] flex flex-col rounded-none sm:rounded-2xl border-0 sm:border border-slate-700 bg-slate-900 shadow-2xl overflow-hidden">

          {/* ── PASO 1: búsqueda ─────────────────────────────────────────── */}
          {paso === 1 && (
            <>
              {/* Header */}
              <div className="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-slate-800 shrink-0">
                <button onClick={onClose} className="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-slate-800">
                  <X className="h-5 w-5" />
                </button>
                <div className="relative flex-1">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                  <input
                    ref={inputRef}
                    value={q}
                    onChange={e => setQ(e.target.value)}
                    placeholder="Buscar por código, descripción o referencia…"
                    className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-3 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none"
                  />
                </div>
              </div>

              {/* Results */}
              <div className="flex-1 overflow-y-auto">
                {dq.length < 2 ? (
                  <div className="flex flex-col items-center justify-center py-20 text-slate-500">
                    <Package className="h-10 w-10 mb-3 opacity-30" />
                    <p className="text-sm">Escribe al menos 2 caracteres para buscar</p>
                  </div>
                ) : isFetching ? (
                  <div className="flex justify-center py-12">
                    <div className="h-6 w-6 animate-spin rounded-full border-2 border-slate-600 border-t-orange-500" />
                  </div>
                ) : !data?.length ? (
                  <div className="py-12 text-center text-slate-500 text-sm">
                    No se encontraron productos para "<span className="text-white">{dq}</span>"
                  </div>
                ) : (
                  <div className="divide-y divide-slate-800">
                    {data.map(p => {
                      const disp   = getDisponible(p)
                      const canSel = puedeSeleccionar(p, modo, ventamenos, actfacexi)
                      const esSrv  = isServicio(p)
                      const esCmp  = isCompuesto(p)
                      const esEmp  = tieneEmpaque(p)

                      return (
                        <div key={p.CODPRO} className={`flex items-start gap-3 px-4 py-3 ${!canSel ? 'opacity-40' : ''}`}>
                          {/* Stock icon */}
                          <div className={`mt-0.5 shrink-0 h-8 w-8 rounded-full flex items-center justify-center
                            ${esSrv || esCmp ? 'bg-blue-900/40 text-blue-400'
                              : disp > 0     ? 'bg-green-900/40 text-green-400'
                                             : 'bg-red-900/40 text-red-400'}`}>
                            {esSrv || esCmp
                              ? <CheckCircle className="h-4 w-4" />
                              : disp > 0
                                ? <CheckCircle className="h-4 w-4" />
                                : <AlertTriangle className="h-4 w-4" />
                            }
                          </div>

                          {/* Info */}
                          <button
                            type="button"
                            disabled={!canSel}
                            onClick={() => handleSelectProd(p)}
                            className={`flex-1 min-w-0 text-left ${canSel ? 'cursor-pointer' : 'cursor-not-allowed'}`}
                          >
                            <div className="flex items-baseline gap-2 flex-wrap">
                              <span className="font-mono text-xs text-orange-400">{p.CODPRO}</span>
                              {esCmp && (
                                <span className="text-xs bg-blue-900/40 text-blue-300 px-1.5 py-0.5 rounded">Compuesto</span>
                              )}
                              {esSrv && (
                                <span className="text-xs bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded">Servicio</span>
                              )}
                              {esEmp && (
                                <span className="text-xs bg-amber-900/40 text-amber-300 px-1.5 py-0.5 rounded">
                                  Empaque ×{p.CANTIDAD_EMP}
                                </span>
                              )}
                            </div>
                            <p className="text-sm text-white font-medium mt-0.5 line-clamp-2">{p.DESCRIP1}</p>
                            <div className="flex flex-wrap gap-3 mt-1 text-xs text-slate-400">
                              <span>Precio: <span className="text-white font-mono">${Number(p.PRECVEN1 ?? 0).toFixed(2)}</span></span>
                              {!esSrv && (
                                <>
                                  <span>Exist: <span className="text-white">{Math.round(p.EXISTENCIA ?? 0)}</span></span>
                                  <span>Reserv: <span className="text-white">{Math.round(p.CANRESERVADA ?? 0)}</span></span>
                                  <span className={disp > 0 ? 'text-green-400' : 'text-red-400'}>
                                    Disp: <span className="font-medium">{Math.round(disp)}</span>
                                  </span>
                                </>
                              )}
                            </div>
                          </button>

                          {/* Action buttons */}
                          <div className="shrink-0 flex flex-col items-end gap-1.5">
                            <p className="text-orange-400 font-mono font-semibold text-sm">
                              ${Number(p.PRECVEN1 ?? 0).toFixed(2)}
                            </p>
                            <p className="text-xs text-slate-500">ITBMS {p.IMPPOR ?? 0}%</p>
                            {esCmp && canSel && (
                              <button
                                type="button"
                                onClick={e => { e.stopPropagation(); setCompuesto(p) }}
                                className="flex items-center gap-1 text-xs text-blue-400 hover:text-blue-300 px-1.5 py-0.5 rounded border border-blue-500/40 hover:border-blue-400"
                              >
                                <Layers className="h-3 w-3" />
                                Comp.
                              </button>
                            )}
                            {esEmp && canSel && (
                              <button
                                type="button"
                                onClick={e => { e.stopPropagation(); handleSelectEmpaque(p) }}
                                className="flex items-center gap-1 text-xs text-amber-400 hover:text-amber-300 px-1.5 py-0.5 rounded border border-amber-500/40 hover:border-amber-400"
                              >
                                <ShoppingCart className="h-3 w-3" />
                                Empaque
                              </button>
                            )}
                          </div>
                        </div>
                      )
                    })}
                  </div>
                )}
              </div>
            </>
          )}

          {/* ── PASO 2: confirmar cantidad / precio ───────────────────────── */}
          {paso === 2 && prod && (
            <>
              {/* Header */}
              <div className="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-slate-800 shrink-0">
                <button
                  onClick={() => setPaso(1)}
                  className="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-slate-800"
                >
                  <ArrowLeft className="h-5 w-5" />
                </button>
                <div className="flex-1 min-w-0">
                  <p className="font-mono text-xs text-orange-400">{prod.CODPRO}</p>
                  <p className="text-white font-medium text-sm truncate">{prod.DESCRIP1}</p>
                </div>
                <button onClick={onClose} className="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-slate-800">
                  <X className="h-4 w-4" />
                </button>
              </div>

              <div className="flex-1 overflow-y-auto p-4 space-y-4">
                {/* Disponibilidad */}
                {!isServicio(prod) && !isCompuesto(prod) && (
                  <div className={`rounded-lg px-3 py-2 text-xs flex items-center gap-2
                    ${disponible > 0 ? 'bg-green-900/20 border border-green-800/40 text-green-300'
                                     : 'bg-yellow-900/20 border border-yellow-800/40 text-yellow-300'}`}>
                    {disponible > 0
                      ? <CheckCircle className="h-3.5 w-3.5 shrink-0" />
                      : <AlertTriangle className="h-3.5 w-3.5 shrink-0" />
                    }
                    Disponible: <span className="font-semibold ml-1">{Math.round(disponible)}</span>
                    <span className="text-slate-500 ml-2">
                      (Exist: {Math.round(prod.EXISTENCIA ?? 0)} · Reserv: {Math.round(prod.CANRESERVADA ?? 0)})
                    </span>
                  </div>
                )}

                {/* Campos */}
                <div className="space-y-3">
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Cantidad</label>
                    <input
                      type="number"
                      step="0.001"
                      min="0.001"
                      value={qty}
                      onChange={e => setQty(parseFloat(e.target.value) || 0)}
                      autoFocus
                      className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none"
                    />
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">
                      Precio unitario ${cambiarprecio === 0 && <span className="ml-1 text-slate-500">(no editable)</span>}
                    </label>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      value={precio}
                      readOnly={cambiarprecio === 0}
                      onChange={e => cambiarprecio !== 0 && setPrecio(parseFloat(e.target.value) || 0)}
                      className={`w-full rounded-lg border py-2 px-3 text-sm text-white focus:outline-none
                        ${cambiarprecio === 0
                          ? 'border-slate-700 bg-slate-700/50 text-slate-400 cursor-not-allowed'
                          : 'border-slate-700 bg-slate-800 focus:border-orange-500'}`}
                    />
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">ITBMS %</label>
                    <div className="flex gap-2">
                      {ITBMS_RATES.map(r => (
                        <button
                          key={r}
                          type="button"
                          onClick={() => setImppor(r)}
                          className={`flex-1 py-2 rounded-lg text-sm font-medium transition-colors
                            ${imppor === r
                              ? 'bg-orange-600 text-white'
                              : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'}`}
                        >
                          {r}%
                        </button>
                      ))}
                    </div>
                  </div>
                </div>

                {/* Resumen */}
                {qty > 0 && (
                  <div className="rounded-lg bg-slate-800 px-4 py-3 space-y-1 text-sm">
                    <div className="flex justify-between text-slate-400">
                      <span>Subtotal ({qty} × ${precio.toFixed(2)})</span>
                      <span className="text-white">${(qty * precio).toFixed(2)}</span>
                    </div>
                    {imppor > 0 && (
                      <div className="flex justify-between text-slate-400">
                        <span>ITBMS {imppor}%</span>
                        <span className="text-white">${(qty * precio * imppor / 100).toFixed(2)}</span>
                      </div>
                    )}
                    <div className="flex justify-between font-semibold text-white border-t border-slate-700 pt-1">
                      <span>Total</span>
                      <span>${(qty * precio * (1 + imppor / 100)).toFixed(2)}</span>
                    </div>
                  </div>
                )}
              </div>

              {/* Footer */}
              <div className="px-4 pb-4 pt-2 border-t border-slate-800 shrink-0">
                <Button
                  className="w-full"
                  disabled={qty <= 0 || precio < 0}
                  onClick={handleConfirm}
                >
                  Agregar a la lista
                </Button>
              </div>
            </>
          )}
        </div>
      </div>

      {/* Sub-modal componentes */}
      {compuesto && (
        <ComponentesModal
          codpro={compuesto.CODPRO}
          descrip={compuesto.DESCRIP1}
          onClose={() => setCompuesto(null)}
          onSelectComponente={handleSelectComponente}
        />
      )}
    </>
  )
}
