import { useState } from 'react'
import { useLocation } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { ShoppingCart, Search, ArrowRightLeft, Clock, Trash2 } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { Badge } from '@/components/ui/Badge'
import { Button } from '@/components/ui/Button'
import { Spinner } from '@/components/ui/Spinner'
import { Toast } from '@/components/ui/Toast'
import type { Pedido } from '@/types'

export function PedidosPage() {
  const location  = useLocation()
  const clienteNav = (location.state as { cliente?: { NOMBRE?: string; CODIGO?: string } } | null)?.cliente

  const [search, setSearch]     = useState(clienteNav?.NOMBRE ?? '')
  const [page, setPage]         = useState(1)
  const [toast, setToast]       = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const [converting, setConverting] = useState<string | null>(null)
  const [deleting, setDeleting]     = useState<string | null>(null)

  const { data, isLoading } = useQuery({
    queryKey: ['pedidos', search, page],
    queryFn: () =>
      api.get<{ data: Pedido[]; meta: { last_page: number; total: number } }>(
        '/pedidos', { params: { q: search || undefined, page } }
      ).then(r => r.data),
    placeholderData: prev => prev,
  })

  const pedidos  = data?.data      ?? []
  const lastPage = data?.meta?.last_page ?? 1
  const total    = data?.meta?.total     ?? 0

  // Formatea INT YYYYMMDD → "DD/MM/YYYY"
  const fmtDate = (v: number | null) => {
    if (!v) return '—'
    const s = String(v)
    return `${s.slice(6, 8)}/${s.slice(4, 6)}/${s.slice(0, 4)}`
  }

  // ── Convertir a factura ────────────────────────────────────────────────────
  const convertirMutation = useMutation({
    mutationFn: (control: string) =>
      api.post(`/pedidos/${btoa(control)}/convertir-a-factura`, {
        tipoFactura: 'CONTADO',
        formasPago: [{ instrumento: 'EFE', monto: 0 }],
      }).then(r => r.data),
    onSuccess: (res) => {
      queryClient.invalidateQueries({ queryKey: ['pedidos'] })
      queryClient.invalidateQueries({ queryKey: ['facturas'] })
      setConverting(null)
      setToast({ type: 'success', message: `Factura ${res.numref ?? ''} creada exitosamente` })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al convertir'
      setConverting(null)
      setToast({ type: 'error', message: msg })
    },
  })

  const handleConvertir = (p: Pedido) => {
    if (!confirm(`¿Convertir pedido ${p.NUMREF} a factura? Esto deducirá el inventario reservado.`)) return
    setConverting(p.CONTROL)
    convertirMutation.mutate(p.CONTROL)
  }

  // ── Eliminar pedido ────────────────────────────────────────────────────────
  const deleteMutation = useMutation({
    mutationFn: (control: string) =>
      api.delete(`/pedidos/${btoa(control)}`).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pedidos'] })
      setDeleting(null)
      setToast({ type: 'success', message: 'Pedido eliminado y reservas liberadas' })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al eliminar'
      setDeleting(null)
      setToast({ type: 'error', message: msg })
    },
  })

  const handleEliminar = (p: Pedido) => {
    if (!confirm(`¿Eliminar pedido ${p.NUMREF}? Se liberarán las reservas de inventario.`)) return
    setDeleting(p.CONTROL)
    deleteMutation.mutate(p.CONTROL)
  }

  return (
    <div className="space-y-4">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-white">Pedidos</h1>
          <p className="text-sm text-slate-400">{total} registros · reservan inventario</p>
        </div>
      </div>

      {clienteNav && (
        <div className="flex items-center gap-2 rounded-lg bg-orange-900/20 border border-orange-800/40 px-3 py-2 text-sm text-orange-300">
          <span className="font-medium">Cliente:</span>
          <span className="truncate">{clienteNav.NOMBRE}</span>
          {clienteNav.CODIGO && <span className="font-mono text-xs text-orange-400/70">{clienteNav.CODIGO}</span>}
        </div>
      )}

      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
        <input
          type="text"
          placeholder="Buscar por número, cliente…"
          value={search}
          onChange={e => { setSearch(e.target.value); setPage(1) }}
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none"
        />
      </div>

      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : pedidos.length === 0 ? (
        <div className="rounded-lg border border-slate-700 bg-slate-900 py-16 text-center">
          <ShoppingCart className="mx-auto h-10 w-10 text-slate-600 mb-3" />
          <p className="text-slate-400">No se encontraron pedidos</p>
        </div>
      ) : (
        <>
          <div className="space-y-2">
            {pedidos.map(p => (
              <div
                key={p.CONTROL}
                className="rounded-lg border border-slate-700 bg-slate-900 p-4 flex items-start justify-between gap-4"
              >
                <div className="flex items-start gap-3 flex-1 min-w-0">
                  <div className="mt-0.5 h-8 w-8 rounded-full flex items-center justify-center shrink-0 bg-orange-900/40 text-orange-400">
                    <Clock className="h-4 w-4" />
                  </div>
                  <div className="min-w-0">
                    <div className="flex items-center gap-2 flex-wrap mb-1">
                      <span className="font-mono text-orange-400 text-sm">{p.NUMREF}</span>
                      <Badge color="yellow">Reservado</Badge>
                    </div>
                    <p className="text-white font-medium truncate">{p.NOMBRE}</p>
                    <div className="flex gap-4 mt-1 text-xs text-slate-400">
                      <span>Total: <span className="text-white">${Number(p.MONTOTOT ?? 0).toFixed(2)}</span></span>
                      <span>{fmtDate(p.FECEMIS)}</span>
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2 shrink-0">
                  <Button
                    size="sm"
                    variant="secondary"
                    onClick={() => handleConvertir(p)}
                    loading={convertirMutation.isPending && converting === p.CONTROL}
                    disabled={convertirMutation.isPending || deleteMutation.isPending}
                  >
                    <ArrowRightLeft className="h-3.5 w-3.5 mr-1" />
                    Facturar
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    onClick={() => handleEliminar(p)}
                    loading={deleteMutation.isPending && deleting === p.CONTROL}
                    disabled={convertirMutation.isPending || deleteMutation.isPending}
                  >
                    <Trash2 className="h-3.5 w-3.5" />
                  </Button>
                </div>
              </div>
            ))}
          </div>

          {lastPage > 1 && (
            <div className="flex items-center justify-between text-sm text-slate-400">
              <span>Página {page} de {lastPage}</span>
              <div className="flex gap-2">
                <button
                  onClick={() => setPage(p => Math.max(1, p - 1))}
                  disabled={page === 1}
                  className="rounded px-3 py-1 bg-slate-800 disabled:opacity-40 hover:bg-slate-700"
                >
                  Anterior
                </button>
                <button
                  onClick={() => setPage(p => Math.min(lastPage, p + 1))}
                  disabled={page === lastPage}
                  className="rounded px-3 py-1 bg-slate-800 disabled:opacity-40 hover:bg-slate-700"
                >
                  Siguiente
                </button>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  )
}
