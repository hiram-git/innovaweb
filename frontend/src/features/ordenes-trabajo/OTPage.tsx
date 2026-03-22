import { useState } from 'react'
import { useQuery, useMutation } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Plus, Wrench, Search, X, CheckCircle, Clock } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { Button } from '@/components/ui/Button'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Toast } from '@/components/ui/Toast'
import type { OrdenTrabajo, Cliente } from '@/types'

const otSchema = z.object({
  codcliente:     z.string().min(1, 'Seleccione un cliente'),
  atendido:       z.string().min(1, 'Requerido'),
  fecha_entrega:  z.string().min(1, 'Requerido'),
  descripcion:    z.string().min(3, 'Mínimo 3 caracteres'),
  controlpres:    z.string().optional(),
})
type OTForm = z.infer<typeof otSchema>

function estadoBadge(estado: number) {
  if (estado === 1) return <Badge color="green">Cerrada</Badge>
  if (estado === 2) return <Badge color="red">Anulada</Badge>
  return <Badge color="yellow">Abierta</Badge>
}

function ClienteSearch({ onSelect, value }: { onSelect: (c: Cliente) => void; value: string }) {
  const [q, setQ]     = useState(value)
  const [open, setOpen] = useState(false)

  const { data } = useQuery({
    queryKey: ['clientes-search-ot', q],
    queryFn: () => api.get<{ data: Cliente[] }>('/clientes', { params: { q, limit: 6 } }).then(r => r.data.data),
    enabled: q.length >= 2,
  })

  return (
    <div className="relative">
      <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
      <input type="text" placeholder="Buscar cliente…" value={q}
        onChange={e => { setQ(e.target.value); setOpen(true) }}
        onFocus={() => setOpen(true)}
        className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none" />
      {open && data && data.length > 0 && (
        <ul className="absolute z-20 mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 shadow-xl max-h-48 overflow-y-auto">
          {data.map(c => (
            <li key={c.CODCLIENTE}>
              <button type="button" onClick={() => { onSelect(c); setQ(c.NOMBRE ?? ''); setOpen(false) }}
                className="w-full px-4 py-2 text-left text-sm hover:bg-slate-800 text-white">
                {c.NOMBRE} <span className="text-slate-400 text-xs ml-1">{c.RIF}</span>
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

export function OTPage() {
  const [showForm, setShowForm] = useState(false)
  const [search, setSearch]     = useState('')
  const [clienteLabel, setClienteLabel] = useState('')
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  const { data, isLoading } = useQuery({
    queryKey: ['ordenes-trabajo', search],
    queryFn: () =>
      api.get<{ data: OrdenTrabajo[] }>('/ordenes-trabajo', { params: { q: search || undefined } })
        .then(r => r.data.data),
    placeholderData: prev => prev,
  })

  const { register, handleSubmit, setValue, reset, formState: { errors } } = useForm<OTForm>({
    resolver: zodResolver(otSchema),
  })

  const createMutation = useMutation({
    mutationFn: (d: OTForm) => api.post('/ordenes-trabajo', d).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['ordenes-trabajo'] })
      reset()
      setShowForm(false)
      setClienteLabel('')
      setToast({ type: 'success', message: 'Orden de trabajo creada' })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al crear OT'
      setToast({ type: 'error', message: msg })
    },
  })

  const cerrarMutation = useMutation({
    mutationFn: (controlot: string) => api.patch(`/ordenes-trabajo/${controlot}`, { estado: 1 }).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['ordenes-trabajo'] })
      setToast({ type: 'success', message: 'OT cerrada exitosamente' })
    },
  })

  return (
    <div className="space-y-4">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-white">Órdenes de Trabajo</h1>
          <p className="text-sm text-slate-400">{data?.length ?? 0} registros</p>
        </div>
        <Button onClick={() => setShowForm(true)}>
          <Plus className="h-4 w-4 mr-1" /> Nueva OT
        </Button>
      </div>

      {/* Formulario */}
      {showForm && (
        <div className="rounded-lg border border-slate-600 bg-slate-900 p-5 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-sm font-semibold text-white">Nueva Orden de Trabajo</h2>
            <button onClick={() => setShowForm(false)} className="text-slate-400 hover:text-white">
              <X className="h-5 w-5" />
            </button>
          </div>

          <form onSubmit={handleSubmit(d => createMutation.mutate(d))} className="space-y-4">
            <div>
              <label className="mb-1 block text-xs text-slate-400">Cliente *</label>
              <ClienteSearch value={clienteLabel} onSelect={c => {
                setValue('codcliente', c.CODCLIENTE)
                setClienteLabel(c.NOMBRE ?? '')
              }} />
              {errors.codcliente && <p className="mt-1 text-xs text-red-400">{errors.codcliente.message}</p>}
              <input type="hidden" {...register('codcliente')} />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="mb-1 block text-xs text-slate-400">Atendido por *</label>
                <input {...register('atendido')}
                  className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none" />
                {errors.atendido && <p className="mt-1 text-xs text-red-400">{errors.atendido.message}</p>}
              </div>
              <div>
                <label className="mb-1 block text-xs text-slate-400">Fecha entrega *</label>
                <input type="date" {...register('fecha_entrega')}
                  className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none" />
                {errors.fecha_entrega && <p className="mt-1 text-xs text-red-400">{errors.fecha_entrega.message}</p>}
              </div>
            </div>

            <div>
              <label className="mb-1 block text-xs text-slate-400">Descripción del trabajo *</label>
              <textarea rows={3} {...register('descripcion')}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none resize-none" />
              {errors.descripcion && <p className="mt-1 text-xs text-red-400">{errors.descripcion.message}</p>}
            </div>

            <div className="flex justify-end gap-3">
              <Button variant="secondary" type="button" onClick={() => setShowForm(false)}>Cancelar</Button>
              <Button type="submit" loading={createMutation.isPending}>Crear OT</Button>
            </div>
          </form>
        </div>
      )}

      {/* Búsqueda */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
        <input type="text" placeholder="Buscar por número o cliente…" value={search}
          onChange={e => setSearch(e.target.value)}
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none" />
      </div>

      {/* Lista */}
      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : !data || data.length === 0 ? (
        <div className="rounded-lg border border-slate-700 bg-slate-900 py-16 text-center">
          <Wrench className="mx-auto h-10 w-10 text-slate-600 mb-3" />
          <p className="text-slate-400">No hay órdenes de trabajo</p>
        </div>
      ) : (
        <div className="space-y-2">
          {data.map(ot => (
            <div key={ot.CONTROLOT} className="rounded-lg border border-slate-700 bg-slate-900 p-4 flex items-start justify-between gap-4">
              <div className="flex items-start gap-3 flex-1 min-w-0">
                <div className={`mt-0.5 h-8 w-8 rounded-full flex items-center justify-center shrink-0
                  ${ot.ESTADO === 1 ? 'bg-green-900/40 text-green-400' : 'bg-yellow-900/40 text-yellow-400'}`}>
                  {ot.ESTADO === 1 ? <CheckCircle className="h-4 w-4" /> : <Clock className="h-4 w-4" />}
                </div>
                <div className="min-w-0">
                  <div className="flex items-center gap-2 flex-wrap">
                    <span className="font-mono text-blue-400 text-sm">{ot.CONTROLOT}</span>
                    {estadoBadge(ot.ESTADO ?? 0)}
                  </div>
                  <p className="text-white font-medium mt-0.5 truncate">{ot.NOMCLIENTE}</p>
                  <p className="text-slate-400 text-xs mt-1">{ot.DESCRIPCION}</p>
                  <div className="flex gap-4 mt-1 text-xs text-slate-500">
                    <span>Atendido: {ot.ATENDIDO}</span>
                    <span>Entrega: {ot.FECHA_ENTREGA ? new Date(ot.FECHA_ENTREGA).toLocaleDateString('es-PA') : '—'}</span>
                  </div>
                </div>
              </div>
              {(ot.ESTADO ?? 0) === 0 && (
                <Button size="sm" variant="secondary"
                  onClick={() => cerrarMutation.mutate(ot.CONTROLOT)}
                  loading={cerrarMutation.isPending && cerrarMutation.variables === ot.CONTROLOT}>
                  <CheckCircle className="h-3.5 w-3.5 mr-1" /> Cerrar
                </Button>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
