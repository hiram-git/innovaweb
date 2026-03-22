import { useState } from 'react'
import { useQuery, useMutation } from '@tanstack/react-query'
import { Zap, Send, RefreshCw, CheckCircle, XCircle, Clock, AlertTriangle } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Button } from '@/components/ui/Button'
import { Toast } from '@/components/ui/Toast'

interface FEDocumento {
  CONTROLMAESTRO: string
  NROFAC:         string
  NOMCLIENTE:     string
  MONTOTOT:       number
  FECHA:          string
  FE_ESTADO:      string | null
  CUFE:           string | null
  FE_MENSAJE:     string | null
}

interface FEStats {
  pendientes: number
  enviados:   number
  aceptados:  number
  rechazados: number
}

function estadoBadge(estado: string | null) {
  switch (estado) {
    case 'ACEPTADO':  return <Badge color="green">Aceptado</Badge>
    case 'RECHAZADO': return <Badge color="red">Rechazado</Badge>
    case 'ENVIADO':   return <Badge color="blue">Enviado</Badge>
    case 'PENDIENTE': return <Badge color="yellow">Pendiente</Badge>
    default:          return <Badge color="gray">Sin FE</Badge>
  }
}

export function FEPage() {
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const [filter, setFilter] = useState<string>('PENDIENTE')

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

      <div className="flex items-center gap-3">
        <Zap className="h-6 w-6 text-blue-400" />
        <div>
          <h1 className="text-xl font-bold text-white">Facturación Electrónica / DGI</h1>
          <p className="text-sm text-slate-400">Gestión de documentos electrónicos TFHKA</p>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {[
          { label: 'Pendientes', value: stats?.pendientes ?? 0, color: 'text-yellow-400', icon: <Clock className="h-5 w-5" /> },
          { label: 'Enviados',   value: stats?.enviados   ?? 0, color: 'text-blue-400',   icon: <Send  className="h-5 w-5" /> },
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
              ${filter === f ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'}`}>
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
                <th className="px-4 py-3 text-left">CUFE</th>
                <th className="px-4 py-3 text-center">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800">
              {docs.map(doc => (
                <tr key={doc.CONTROLMAESTRO} className="bg-slate-900 hover:bg-slate-800">
                  <td className="px-4 py-3 font-mono text-blue-400">{doc.NROFAC}</td>
                  <td className="px-4 py-3 text-white max-w-[160px] truncate">{doc.NOMCLIENTE}</td>
                  <td className="px-4 py-3 text-slate-400">
                    {doc.FECHA ? new Date(doc.FECHA).toLocaleDateString('es-PA') : '—'}
                  </td>
                  <td className="px-4 py-3 text-right text-white">${Number(doc.MONTOTOT).toFixed(2)}</td>
                  <td className="px-4 py-3 text-center">{estadoBadge(doc.FE_ESTADO)}</td>
                  <td className="px-4 py-3 text-slate-400 max-w-[120px]">
                    {doc.CUFE ? (
                      <span className="font-mono text-xs truncate block" title={doc.CUFE}>{doc.CUFE.slice(0, 16)}…</span>
                    ) : (
                      <span className="text-slate-600">—</span>
                    )}
                  </td>
                  <td className="px-4 py-3 text-center">
                    {doc.FE_ESTADO === null || doc.FE_ESTADO === 'PENDIENTE' ? (
                      <Button size="sm" onClick={() => enviarMutation.mutate(doc.CONTROLMAESTRO)}
                        loading={enviarMutation.isPending && enviarMutation.variables === doc.CONTROLMAESTRO}>
                        <Send className="h-3.5 w-3.5 mr-1" /> Enviar
                      </Button>
                    ) : doc.FE_ESTADO === 'RECHAZADO' ? (
                      <Button size="sm" variant="secondary"
                        onClick={() => reenviarMutation.mutate(doc.CONTROLMAESTRO)}
                        loading={reenviarMutation.isPending && reenviarMutation.variables === doc.CONTROLMAESTRO}>
                        <RefreshCw className="h-3.5 w-3.5 mr-1" /> Reenviar
                      </Button>
                    ) : doc.FE_ESTADO === 'RECHAZADO' && doc.FE_MENSAJE ? (
                      <div className="flex items-center gap-1 text-red-400 text-xs">
                        <AlertTriangle className="h-3.5 w-3.5" />
                        <span title={doc.FE_MENSAJE}>Ver error</span>
                      </div>
                    ) : null}
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
