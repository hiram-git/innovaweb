import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { Zap, Send, RefreshCw, CheckCircle, XCircle, Clock, AlertTriangle, FileX, FileMinus, X, Printer } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { Toast } from '@/components/ui/Toast'

interface FEDocumento {
  CONTROLMAESTRO:    string
  NROFAC:            string
  NOMCLIENTE:        string
  MONTOTOT:          number
  FECHA:             string   // FECEMISS — YYYYMMDD string
  FE_ESTADO:         string | null
  URLCONSULTAFEL:    string | null
  FECHA_CER:         string | null
  PROTO_AUTORIZACION: string | null
  FE_MENSAJE:        string | null
}

interface FEStats {
  pendientes: number
  enviados:   number
  aceptados:  number
  rechazados: number
}

// ─── Modal Nota de Crédito / Débito ────────────────────────────────────────────

type TipoNota = 'credito' | 'debito'

interface NotaModalProps {
  doc:   FEDocumento
  tipo:  TipoNota
  onClose: () => void
  onSuccess: (msg: string) => void
}

function NotaModal({ doc, tipo, onClose, onSuccess }: NotaModalProps) {
  const [motivo, setMotivo] = useState('')
  const [monto,  setMonto]  = useState('')
  const [error,  setError]  = useState<string | null>(null)

  const endpoint = tipo === 'credito'
    ? `/facturacion-electronica/nota-credito/${doc.CONTROLMAESTRO}`
    : `/facturacion-electronica/nota-debito/${doc.CONTROLMAESTRO}`

  const label = tipo === 'credito' ? 'Nota de Crédito' : 'Nota de Débito'

  const mutation = useMutation({
    mutationFn: () =>
      api.post(endpoint, {
        motivo,
        monto: parseFloat(monto),
      }).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['fe-docs'] })
      queryClient.invalidateQueries({ queryKey: ['fe-stats'] })
      onSuccess(`${label} emitida correctamente`)
      onClose()
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
        ?? `Error al emitir ${label}`
      setError(msg)
    },
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setError(null)
    if (!motivo.trim()) { setError('El motivo es requerido'); return }
    const m = parseFloat(monto)
    if (isNaN(m) || m <= 0) { setError('Ingrese un monto válido mayor a 0'); return }
    if (m > doc.MONTOTOT) { setError(`El monto no puede superar el total de la factura ($${doc.MONTOTOT.toFixed(2)})`); return }
    mutation.mutate()
  }

  return (
    <>
      <div className="fixed inset-0 bg-black/60 z-40" onClick={onClose} />
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div className="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 shadow-2xl">
          {/* Header */}
          <div className="flex items-center justify-between px-5 py-4 border-b border-slate-700">
            <div className="flex items-center gap-2">
              {tipo === 'credito'
                ? <FileMinus className="h-5 w-5 text-orange-400" />
                : <FileX     className="h-5 w-5 text-orange-400" />}
              <h2 className="text-base font-semibold text-white">{label}</h2>
            </div>
            <button onClick={onClose} className="rounded-md p-1.5 text-slate-400 hover:text-white hover:bg-slate-700">
              <X className="h-4 w-4" />
            </button>
          </div>

          {/* Factura referenciada */}
          <div className="mx-5 mt-4 rounded-lg bg-slate-800 px-4 py-3 text-sm">
            <p className="text-slate-400 text-xs mb-1">Factura referenciada</p>
            <p className="text-white font-mono font-medium">{doc.NROFAC}</p>
            <p className="text-slate-400 text-xs truncate">{doc.NOMCLIENTE}</p>
            <p className="text-white text-xs mt-0.5">Total: <span className="font-semibold">${doc.MONTOTOT.toFixed(2)}</span></p>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="px-5 py-4 space-y-4">
            {error && (
              <div className="rounded-lg bg-red-900/30 border border-red-700 p-3 text-sm text-red-300">
                {error}
              </div>
            )}

            <div>
              <label className="mb-1.5 block text-sm font-medium text-slate-300">
                Motivo *
              </label>
              <textarea
                value={motivo}
                onChange={e => setMotivo(e.target.value)}
                rows={3}
                placeholder={tipo === 'credito' ? 'Ej: Devolución de mercancía, error en precio…' : 'Ej: Ajuste por diferencia de precio…'}
                className="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
              />
            </div>

            <Input
              label="Monto *"
              type="number"
              step="0.01"
              min="0.01"
              max={doc.MONTOTOT}
              value={monto}
              onChange={e => setMonto(e.target.value)}
              placeholder={`Máx. $${doc.MONTOTOT.toFixed(2)}`}
            />
          </form>

          {/* Footer */}
          <div className="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-700">
            <Button type="button" variant="ghost" size="sm" onClick={onClose}>
              Cancelar
            </Button>
            <Button
              type="button"
              size="sm"
              loading={mutation.isPending}
              onClick={handleSubmit as unknown as React.MouseEventHandler}
              variant={tipo === 'credito' ? 'primary' : 'secondary'}
            >
              Emitir {tipo === 'credito' ? 'Nota de Crédito' : 'Nota de Débito'}
            </Button>
          </div>
        </div>
      </div>
    </>
  )
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function estadoBadge(estado: string | null) {
  switch (estado) {
    case 'ACEPTADO':  return <Badge color="green">Aceptado</Badge>
    case 'RECHAZADO': return <Badge color="red">Rechazado</Badge>
    case 'ENVIADO':   return <Badge color="blue">Enviado</Badge>
    case 'PENDIENTE': return <Badge color="yellow">Pendiente</Badge>
    default:          return <Badge color="gray">Sin FE</Badge>
  }
}

// ─── Main page ────────────────────────────────────────────────────────────────

export function FEPage() {
  const navigate = useNavigate()
  const [toast,  setToast]  = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const [filter, setFilter] = useState<string>('PENDIENTE')
  const [notaModal, setNotaModal] = useState<{ doc: FEDocumento; tipo: TipoNota } | null>(null)

  const { data: stats } = useQuery({
    queryKey: ['fe-stats'],
    queryFn: () => api.get<FEStats>('/facturacion-electronica/stats').then(r => r.data),
    refetchInterval: 30_000,
  })

  const { data: docs, isLoading } = useQuery({
    queryKey: ['fe-docs', filter],
    queryFn: () =>
      api.get<{ data: FEDocumento[] }>('/facturacion-electronica/documentos', {
        params: { estado: filter || undefined },
      }).then(r => r.data.data),
    placeholderData: prev => prev,
  })

  const enviarMutation = useMutation({
    mutationFn: (controlmaestro: string) =>
      api.post(`/facturacion-electronica/enviar/${controlmaestro}`).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['fe-docs'] })
      queryClient.invalidateQueries({ queryKey: ['fe-stats'] })
      setToast({ type: 'success', message: 'Factura enviada a DGI' })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al enviar'
      setToast({ type: 'error', message: msg })
    },
  })

  const reenviarMutation = useMutation({
    mutationFn: (controlmaestro: string) =>
      api.post(`/facturacion-electronica/reenviar/${controlmaestro}`).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['fe-docs'] })
      setToast({ type: 'success', message: 'Factura reenviada' })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al reenviar'
      setToast({ type: 'error', message: msg })
    },
  })

  return (
    <div className="space-y-6">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      {notaModal && (
        <NotaModal
          doc={notaModal.doc}
          tipo={notaModal.tipo}
          onClose={() => setNotaModal(null)}
          onSuccess={msg => setToast({ type: 'success', message: msg })}
        />
      )}

      <div className="flex items-center gap-3">
        <Zap className="h-6 w-6 text-orange-400" />
        <div>
          <h1 className="text-xl font-bold text-white">Facturación Electrónica / DGI</h1>
          <p className="text-sm text-slate-400">Gestión de documentos electrónicos TFHKA</p>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {[
          { label: 'Pendientes', value: stats?.pendientes ?? 0, color: 'text-yellow-400', icon: <Clock className="h-5 w-5" /> },
          { label: 'Enviados',   value: stats?.enviados   ?? 0, color: 'text-orange-400',   icon: <Send  className="h-5 w-5" /> },
          { label: 'Aceptados',  value: stats?.aceptados  ?? 0, color: 'text-green-400',  icon: <CheckCircle className="h-5 w-5" /> },
          { label: 'Rechazados', value: stats?.rechazados ?? 0, color: 'text-red-400',    icon: <XCircle className="h-5 w-5" /> },
        ].map(s => (
          <div key={s.label} className="rounded-lg border border-slate-700 bg-slate-900 p-4 flex items-center gap-3">
            <span className={s.color}>{s.icon}</span>
            <div>
              <p className="text-xs text-slate-400">{s.label}</p>
              <p className={`text-2xl font-bold ${s.color}`}>{s.value}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Filtro */}
      <div className="flex gap-2 flex-wrap">
        {['', 'PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO'].map(f => (
          <button key={f} onClick={() => setFilter(f)}
            className={`rounded-full px-3 py-1 text-xs font-medium transition-colors
              ${filter === f ? 'bg-orange-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'}`}>
            {f || 'Todos'}
          </button>
        ))}
      </div>

      {/* Tabla */}
      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : !docs || docs.length === 0 ? (
        <div className="rounded-lg border border-slate-700 bg-slate-900 py-16 text-center">
          <Zap className="mx-auto h-10 w-10 text-slate-600 mb-3" />
          <p className="text-slate-400">No hay documentos en este estado</p>
        </div>
      ) : (
        <div className="overflow-x-auto rounded-lg border border-slate-700">
          <table className="w-full text-sm">
            <thead className="bg-slate-800 text-slate-400 text-xs uppercase tracking-wide">
              <tr>
                <th className="px-4 py-3 text-left">N° Factura</th>
                <th className="px-4 py-3 text-left">Cliente</th>
                <th className="px-4 py-3 text-left">Fecha</th>
                <th className="px-4 py-3 text-right">Total</th>
                <th className="px-4 py-3 text-center">Estado</th>
                <th className="px-4 py-3 text-left">Consulta DGI</th>
                <th className="px-4 py-3 text-center">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800">
              {docs.map(doc => (
                <tr key={doc.CONTROLMAESTRO} className="bg-slate-900 hover:bg-slate-800">
                  <td className="px-4 py-3 font-mono text-orange-400">{doc.NROFAC}</td>
                  <td className="px-4 py-3 text-white max-w-[160px] truncate">{doc.NOMCLIENTE}</td>
                  <td className="px-4 py-3 text-slate-400">
                    {doc.FECHA && doc.FECHA.length === 8
                      ? `${doc.FECHA.slice(6, 8)}/${doc.FECHA.slice(4, 6)}/${doc.FECHA.slice(0, 4)}`
                      : '—'}
                  </td>
                  <td className="px-4 py-3 text-right text-white">${Number(doc.MONTOTOT).toFixed(2)}</td>
                  <td className="px-4 py-3 text-center">{estadoBadge(doc.FE_ESTADO)}</td>
                  <td className="px-4 py-3 text-slate-400 max-w-[120px]">
                    {doc.URLCONSULTAFEL ? (
                      <a
                        href={doc.URLCONSULTAFEL}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="font-mono text-xs text-orange-400 hover:underline truncate block"
                        title={doc.URLCONSULTAFEL}
                      >
                        Ver DGI
                      </a>
                    ) : (
                      <span className="text-slate-600">—</span>
                    )}
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center justify-center gap-1.5 flex-wrap">
                      {doc.URLCONSULTAFEL ? (
                        /* Con CUFE: imprimir ticket/A4 según config FELINNOVA */
                        <>
                          <button
                            title="Imprimir / Vista previa"
                            onClick={() => navigate(`/facturas/${btoa(doc.CONTROLMAESTRO)}/recibo`)}
                            className="flex items-center gap-1 rounded px-2 py-1 text-xs text-green-400 border border-green-800 hover:bg-green-900/30 transition-colors"
                          >
                            <Printer className="h-3.5 w-3.5" /> Imprimir
                          </button>

                          {/* Nota de Crédito / Débito — solo ACEPTADO */}
                          {doc.FE_ESTADO === 'ACEPTADO' && (
                            <>
                              <button
                                onClick={() => setNotaModal({ doc, tipo: 'credito' })}
                                title="Emitir Nota de Crédito"
                                className="flex items-center gap-1 rounded px-2 py-1 text-xs text-orange-400 border border-orange-800 hover:bg-orange-900/30 transition-colors"
                              >
                                <FileMinus className="h-3.5 w-3.5" /> N/C
                              </button>
                              <button
                                onClick={() => setNotaModal({ doc, tipo: 'debito' })}
                                title="Emitir Nota de Débito"
                                className="flex items-center gap-1 rounded px-2 py-1 text-xs text-orange-400 border border-orange-800 hover:bg-orange-900/30 transition-colors"
                              >
                                <FileX className="h-3.5 w-3.5" /> N/D
                              </button>
                            </>
                          )}
                        </>
                      ) : (
                        /* Sin CUFE: enviar o reenviar el documento electrónico */
                        <>
                          {(doc.FE_ESTADO === null || doc.FE_ESTADO === 'PENDIENTE') && (
                            <Button size="sm" onClick={() => enviarMutation.mutate(doc.CONTROLMAESTRO)}
                              loading={enviarMutation.isPending && enviarMutation.variables === doc.CONTROLMAESTRO}>
                              <Send className="h-3.5 w-3.5 mr-1" /> Enviar
                            </Button>
                          )}
                          {doc.FE_ESTADO === 'RECHAZADO' && (
                            <Button size="sm" variant="secondary"
                              onClick={() => reenviarMutation.mutate(doc.CONTROLMAESTRO)}
                              loading={reenviarMutation.isPending && reenviarMutation.variables === doc.CONTROLMAESTRO}>
                              <RefreshCw className="h-3.5 w-3.5 mr-1" /> Reenviar
                            </Button>
                          )}
                          {doc.FE_ESTADO === 'RECHAZADO' && doc.FE_MENSAJE && (
                            <div className="flex items-center gap-1 text-red-400 text-xs">
                              <AlertTriangle className="h-3.5 w-3.5" />
                              <span title={doc.FE_MENSAJE}>Ver error</span>
                            </div>
                          )}
                        </>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
