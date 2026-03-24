import { useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { ClipboardList, Search, ArrowRightLeft, CheckCircle, Clock, XCircle, Plus } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { Badge } from '@/components/ui/Badge'
import { Button } from '@/components/ui/Button'
import { Spinner } from '@/components/ui/Spinner'
import { Toast } from '@/components/ui/Toast'
import type { Presupuesto } from '@/types'

type BadgeColor = 'green' | 'red' | 'yellow' | 'blue' | 'gray'

function integraBadge(integrado: number | null): { label: string; color: BadgeColor } {
  if (integrado === 1) return { label: 'Facturado', color: 'green' }
  if (integrado === 2) return { label: 'Parcial',   color: 'blue' }
  if (integrado === 9) return { label: 'Anulado',   color: 'red' }
  return { label: 'Pendiente', color: 'yellow' }
}

export function PresupuestosPage() {
  const location   = useLocation()
  const navigate   = useNavigate()
  const clienteNav = (location.state as { cliente?: { NOMBRE?: string; CODIGO?: string } } | null)?.cliente

  const [search, setSearch]   = useState(clienteNav?.NOMBRE ?? '')
  const [page, setPage]       = useState(1)
  const [toast, setToast]     = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const [converting, setConverting] = useState<string | null>(null)

  const { data, isLoading } = useQuery({
    queryKey: ['presupuestos', search, page],
    queryFn: () =>
      api.get<{ data: Presupuesto[]; meta: { last_page: number; total: number } }>(
        '/presupuestos', { params: { q: search || undefined, page } }
      ).then(r => r.data),
    placeholderData: prev => prev,
  })

  const presupuestos = data?.data    ?? []
  const lastPage     = data?.meta?.last_page ?? 1
  const total        = data?.meta?.total     ?? 0

  const convertirMutation = useMutation({
    mutationFn: (controlmaestro: string) =>
      api.post(`/presupuestos/${controlmaestro}/convertir-a-factura`).then(r => r.data),
    onSuccess: (res) => {
      queryClient.invalidateQueries({ queryKey: ['presupuestos'] })
      queryClient.invalidateQueries({ queryKey: ['facturas'] })
      setConverting(null)
      setToast({ type: 'success', message: `Factura ${res.data?.NROFAC ?? ''} creada exitosamente` })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al convertir'
      setConverting(null)
      setToast({ type: 'error', message: msg })
    },
  })

  const handleConvertir = (p: Presupuesto) => {
    if (!confirm(`¿Convertir presupuesto ${p.NROFAC} a factura? Esta acción no se puede deshacer.`)) return
    setConverting(p.CONTROLMAESTRO)
    convertirMutation.mutate(p.CONTROLMAESTRO)
  }

  return (
    <div className="space-y-4">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-white">Presupuestos</h1>
          <p className="text-sm text-slate-400">{total} registros</p>
        </div>
        <Button onClick={() => navigate('/presupuestos/nuevo')}>
          <Plus className="h-4 w-4 mr-1" />Nuevo
        </Button>
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
        <input type="text" placeholder="Buscar por número, cliente…" value={search}
          onChange={e => { setSearch(e.target.value); setPage(1) }}
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none" />
      </div>

      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : presupuestos.length === 0 ? (
        <div className="rounded-lg border border-slate-700 bg-slate-900 py-16 text-center">
          <ClipboardList className="mx-auto h-10 w-10 text-slate-600 mb-3" />
          <p className="text-slate-400">No se encontraron presupuestos</p>
        </div>
      ) : (
        <>
          <div className="space-y-2">
            {presupuestos.map(p => {
              const badge = integraBadge(p.INTEGRADO ?? null)
              const isPending = p.INTEGRADO === null || p.INTEGRADO === 0
              return (
                <div key={p.CONTROLMAESTRO}
                  className="rounded-lg border border-slate-700 bg-slate-900 p-4 flex items-start justify-between gap-4">
                  <div className="flex items-start gap-3 flex-1 min-w-0">
                    <div className={`mt-0.5 h-8 w-8 rounded-full flex items-center justify-center shrink-0
                      ${isPending ? 'bg-yellow-900/40 text-yellow-400' : 'bg-green-900/40 text-green-400'}`}>
                      {isPending ? <Clock className="h-4 w-4" /> : <CheckCircle className="h-4 w-4" />}
                    </div>
                    <div className="min-w-0">
                      <div className="flex items-center gap-2 flex-wrap mb-1">
                        <span className="font-mono text-orange-400 text-sm">{p.NROFAC}</span>
                        <Badge color={badge.color}>{badge.label}</Badge>
                      </div>
                      <p className="text-white font-medium truncate">{p.NOMCLIENTE}</p>
                      <div className="flex gap-4 mt-1 text-xs text-slate-400">
                        <span>Total: <span className="text-white">${Number(p.MONTOTOT ?? 0).toFixed(2)}</span></span>
                        <span>{p.FECHA ? new Date(p.FECHA).toLocaleDateString('es-PA') : '—'}</span>
                        {p.ITBMS !== undefined && <span>ITBMS: ${Number(p.ITBMS ?? 0).toFixed(2)}</span>}
                      </div>
                    </div>
                  </div>

                  {isPending && (
                    <Button
                      size="sm"
                      variant="secondary"
                      onClick={() => handleConvertir(p)}
                      loading={convertirMutation.isPending && converting === p.CONTROLMAESTRO}
                      disabled={convertirMutation.isPending}
                    >
                      <ArrowRightLeft className="h-3.5 w-3.5 mr-1" />
                      Facturar
                    </Button>
                  )}
                </div>
              )
            })}
          </div>

          {lastPage > 1 && (
            <div className="flex items-center justify-between text-sm text-slate-400">
              <span>Página {page} de {lastPage}</span>
              <div className="flex gap-2">
                <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}
                  className="rounded px-3 py-1 bg-slate-800 disabled:opacity-40 hover:bg-slate-700">
                  Anterior
                </button>
                <button onClick={() => setPage(p => Math.min(lastPage, p + 1))} disabled={page === lastPage}
                  className="rounded px-3 py-1 bg-slate-800 disabled:opacity-40 hover:bg-slate-700">
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
