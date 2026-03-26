import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Plus, Search, FileText, Send, CheckCircle, XCircle, Clock,
  Printer, FileDown, Trash2, Loader2,
} from 'lucide-react'
import { api } from '@/lib/axios'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import type { FacturaMaestro } from '@/types'

type FEEstadoBadge = 'green' | 'red' | 'yellow' | 'blue' | 'gray'

function feStatusBadge(estado: string | null): { label: string; color: FEEstadoBadge; icon: React.ReactNode } {
  switch (estado) {
    case 'ACEPTADO':   return { label: 'Aceptado',   color: 'green',  icon: <CheckCircle className="h-3 w-3" /> }
    case 'RECHAZADO':  return { label: 'Rechazado',  color: 'red',    icon: <XCircle     className="h-3 w-3" /> }
    case 'PENDIENTE':  return { label: 'Pendiente',  color: 'yellow', icon: <Clock       className="h-3 w-3" /> }
    case 'ENVIADO':    return { label: 'Enviado',    color: 'blue',   icon: <Send        className="h-3 w-3" /> }
    default:           return { label: 'Sin FE',     color: 'gray',   icon: <FileText    className="h-3 w-3" /> }
  }
}

function openBase64Pdf(b64: string, filename: string) {
  const binary = atob(b64)
  const bytes  = new Uint8Array(binary.length)
  for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i)
  const blob = new Blob([bytes], { type: 'application/pdf' })
  const url  = URL.createObjectURL(blob)
  const win  = window.open(url, '_blank')
  win?.document.title !== undefined && (win.document.title = filename)
  setTimeout(() => URL.revokeObjectURL(url), 60_000)
}

function openPdfBlob(blob: Blob, filename: string) {
  const url = URL.createObjectURL(blob)
  const win = window.open(url, '_blank')
  win?.document.title !== undefined && (win.document.title = filename)
  setTimeout(() => URL.revokeObjectURL(url), 60_000)
}

export function FacturasPage() {
  const [search, setSearch] = useState('')
  const [page, setPage]     = useState(1)
  const [actionId, setActionId] = useState<string | null>(null)
  const queryClient = useQueryClient()

  const { data, isLoading, isError } = useQuery({
    queryKey: ['facturas', search, page],
    queryFn: () =>
      api.get<{ data: FacturaMaestro[]; meta: { last_page: number; total: number } }>(
        '/facturas', { params: { q: search || undefined, page } }
      ).then(r => r.data),
    placeholderData: prev => prev,
  })

  // Acción: imprimir ticket via TCPDF
  const ticketMutation = useMutation({
    mutationFn: (id: string) =>
      api.get(`/facturas/${id}/ticket-pdf`, { responseType: 'blob' }).then(r => r.data as Blob),
    onSuccess: (blob) => openPdfBlob(blob, 'Ticket.pdf'),
  })

  // Acción: descargar/ver PDF DGI
  const pdfMutation = useMutation({
    mutationFn: (id: string) => api.get(`/facturas/${id}/pdf`).then(r => r.data),
    onSuccess: (data) => {
      if (data.tipo === 'dgi') {
        openBase64Pdf(data.pdf, `Factura-${data.numdocfiscal ?? 'DGI'}.pdf`)
      } else {
        alert('Esta factura aún no tiene PDF de la DGI. Envíela por el módulo de Facturación Electrónica.')
      }
    },
  })

  // Acción: anular factura
  const anularMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/facturas/${id}`).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['facturas'] })
      setActionId(null)
    },
  })

  const handleAnular = (id: string, nrofac: string | null) => {
    if (!confirm(`¿Confirma que desea anular la factura ${nrofac ?? id}? Esta acción no se puede deshacer.`)) return
    anularMutation.mutate(id)
  }

  const facturas = data?.data    ?? []
  const lastPage = data?.meta?.last_page ?? 1
  const total    = data?.meta?.total     ?? 0

  const isActing = (id: string) => actionId === id && (ticketMutation.isPending || pdfMutation.isPending || anularMutation.isPending)

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-white">Facturas</h1>
          <p className="text-sm text-slate-400">{total} registros</p>
        </div>
        <Link
          to="/facturas/nueva"
          className="flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-500 transition-colors"
        >
          <Plus className="h-4 w-4" />
          Nueva Factura
        </Link>
      </div>

      {/* Search */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
        <input
          type="text"
          placeholder="Buscar por número, cliente, RUC…"
          value={search}
          onChange={e => { setSearch(e.target.value); setPage(1) }}
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none"
        />
      </div>

      {/* Error banner */}
      {(anularMutation.isError || pdfMutation.isError || ticketMutation.isError) && (
        <div className="rounded-lg bg-red-900/30 border border-red-700 px-4 py-3 text-sm text-red-400">
          {(anularMutation.error as Error)?.message
            ?? (pdfMutation.error as Error)?.message
            ?? (ticketMutation.error as Error)?.message
            ?? 'Error al procesar la acción'}
        </div>
      )}

      {/* Table */}
      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : isError ? (
        <p className="text-center text-red-400 py-12">Error al cargar facturas</p>
      ) : facturas.length === 0 ? (
        <div className="rounded-lg border border-slate-700 bg-slate-900 py-16 text-center">
          <FileText className="mx-auto h-10 w-10 text-slate-600 mb-3" />
          <p className="text-slate-400">No se encontraron facturas</p>
        </div>
      ) : (
        <>
          <div className="overflow-x-auto rounded-lg border border-slate-700">
            <table className="w-full text-sm">
              <thead className="bg-slate-800 text-slate-400 text-xs uppercase tracking-wide">
                <tr>
                  <th className="px-4 py-3 text-left">N° Factura</th>
                  <th className="px-4 py-3 text-left">Cliente</th>
                  <th className="px-4 py-3 text-left">Fecha</th>
                  <th className="px-4 py-3 text-right">Total</th>
                  <th className="px-4 py-3 text-right">Saldo</th>
                  <th className="px-4 py-3 text-center">Tipo</th>
                  <th className="px-4 py-3 text-center">FE</th>
                  <th className="px-4 py-3 text-center">Acciones</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800">
                {facturas.map((f) => {
                  const fe      = feStatusBadge(f.FE_ESTADO ?? null)
                  const rowId   = f.CONTROLMAESTRO
                  const b64Id   = btoa(f.CONTROLMAESTRO)
                  const acting  = isActing(rowId)

                  return (
                    <tr key={rowId} className="bg-slate-900 hover:bg-slate-800 transition-colors">
                      <td className="px-4 py-3 font-mono text-orange-400">{f.NROFAC}</td>
                      <td className="px-4 py-3 text-white max-w-[200px] truncate">{f.NOMCLIENTE}</td>
                      <td className="px-4 py-3 text-slate-400">
                        {f.FECHA && f.FECHA.length === 8
                          ? `${f.FECHA.slice(6, 8)}/${f.FECHA.slice(4, 6)}/${f.FECHA.slice(0, 4)}`
                          : '—'}
                      </td>
                      <td className="px-4 py-3 text-right text-white font-medium">
                        ${Number(f.MONTOTOT ?? 0).toFixed(2)}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <span className={Number(f.MONTOSAL ?? 0) > 0 ? 'text-yellow-400' : 'text-slate-500'}>
                          ${Number(f.MONTOSAL ?? 0).toFixed(2)}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-center">
                        <Badge color={f.TIPTRAN === 'CONTADO' ? 'blue' : 'yellow'}>
                          {f.TIPTRAN === 'CONTADO' ? 'Contado' : 'Crédito'}
                        </Badge>
                      </td>
                      <td className="px-4 py-3 text-center">
                        <span className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium
                          ${fe.color === 'green'  ? 'bg-green-900/40 text-green-400'   :
                            fe.color === 'red'    ? 'bg-red-900/40 text-red-400'       :
                            fe.color === 'yellow' ? 'bg-yellow-900/40 text-yellow-400' :
                            fe.color === 'blue'   ? 'bg-orange-900/40 text-orange-400'     :
                                                    'bg-slate-800 text-slate-400'}`}>
                          {fe.icon}
                          {fe.label}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-center">
                        <div className="flex items-center justify-center gap-1">
                          {/* Ticket térmico */}
                          <button
                            title="Imprimir ticket"
                            disabled={acting}
                            onClick={() => { setActionId(rowId); ticketMutation.mutate(b64Id) }}
                            className="rounded p-1.5 text-slate-400 hover:bg-slate-700 hover:text-white disabled:opacity-40 transition-colors"
                          >
                            {acting && ticketMutation.isPending
                              ? <Loader2 className="h-4 w-4 animate-spin" />
                              : <Printer className="h-4 w-4" />
                            }
                          </button>
                          {/* PDF DGI (solo si tiene URLCONSULTAFEL) */}
                          {f.URLCONSULTAFEL && (
                            <button
                              title="Descargar PDF DGI"
                              disabled={acting}
                              onClick={() => { setActionId(rowId); pdfMutation.mutate(b64Id) }}
                              className="rounded p-1.5 text-slate-400 hover:bg-slate-700 hover:text-white disabled:opacity-40 transition-colors"
                            >
                              {acting && pdfMutation.isPending
                                ? <Loader2 className="h-4 w-4 animate-spin" />
                                : <FileDown className="h-4 w-4" />
                              }
                            </button>
                          )}
                          {/* Anular (solo sin FE emitida ni saldo) */}
                          {!f.URLCONSULTAFEL && Number(f.MONTOSAL ?? 0) === 0 && (
                            <button
                              title="Anular factura"
                              disabled={acting}
                              onClick={() => { setActionId(rowId); handleAnular(b64Id, f.NROFAC ?? null) }}
                              className="rounded p-1.5 text-slate-400 hover:bg-red-900/50 hover:text-red-400 disabled:opacity-40 transition-colors"
                            >
                              {acting && anularMutation.isPending
                                ? <Loader2 className="h-4 w-4 animate-spin" />
                                : <Trash2  className="h-4 w-4" />
                              }
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {lastPage > 1 && (
            <div className="flex items-center justify-between text-sm text-slate-400">
              <span>Página {page} de {lastPage}</span>
              <div className="flex gap-2">
                <button
                  onClick={() => setPage(p => Math.max(1, p - 1))}
                  disabled={page === 1}
                  className="rounded px-3 py-1 bg-slate-800 disabled:opacity-40 hover:bg-slate-700"
                >Anterior</button>
                <button
                  onClick={() => setPage(p => Math.min(lastPage, p + 1))}
                  disabled={page === lastPage}
                  className="rounded px-3 py-1 bg-slate-800 disabled:opacity-40 hover:bg-slate-700"
                >Siguiente</button>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  )
}
