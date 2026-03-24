import { useState } from 'react'
import { useLocation } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { DollarSign, Search, Plus, X, Trash2, AlertCircle } from 'lucide-react'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import { Button } from '@/components/ui/Button'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Toast } from '@/components/ui/Toast'
import type { FacturaMaestro } from '@/types'

interface CobroPayload {
  controlmaestro: string
  monto:          number
  instrumento:    string
  referencia?:    string
}

interface InstrumentType { CODINSTRUMENTO: string; DESCRINSTRUMENTO: string }

const cobroSchema = z.object({
  controlmaestro: z.string().min(1),
  monto:          z.number().min(0.01, 'El monto debe ser mayor a 0'),
  instrumento:    z.string().min(1),
  referencia:     z.string().optional(),
})
type CobroForm = z.infer<typeof cobroSchema>

export function CobrosPage() {
  const location = useLocation()
  const clienteNav = (location.state as { cliente?: { NOMBRE?: string; CODIGO?: string } } | null)?.cliente

  const [search, setSearch]   = useState(clienteNav?.NOMBRE ?? '')
  const [selected, setSelected] = useState<FacturaMaestro | null>(null)
  const [toast, setToast]     = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Facturas a crédito con saldo pendiente
  const { data: facturas, isLoading } = useQuery({
    queryKey: ['facturas-credito', search],
    queryFn: () =>
      api.get<{ data: FacturaMaestro[] }>('/facturas', {
        params: { q: search || undefined, tipo: 'CREDITO', con_saldo: 1 },
      }).then(r => r.data.data),
    placeholderData: prev => prev,
  })

  const { data: instrumentos } = useQuery({
    queryKey: ['instrumentos'],
    queryFn: () => api.get<InstrumentType[]>('/instrumentos').then(r => r.data),
    staleTime: Infinity,
  })

  const { register, handleSubmit, reset, formState: { errors } } = useForm<CobroForm>({
    resolver: zodResolver(cobroSchema),
    defaultValues: { instrumento: '' },
  })

  const crearCobro = useMutation({
    mutationFn: (d: CobroPayload) => api.post('/cobros', d).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['facturas-credito'] })
      reset()
      setSelected(null)
      setToast({ type: 'success', message: 'Cobro registrado exitosamente' })
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al registrar cobro'
      setToast({ type: 'error', message: msg })
    },
  })

  const onSubmit = (d: CobroForm) => {
    if (!selected) return
    crearCobro.mutate({ ...d, controlmaestro: selected.CONTROLMAESTRO })
  }

  return (
    <div className="space-y-4">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      <div>
        <h1 className="text-xl font-bold text-white">Cobros</h1>
        <p className="text-sm text-slate-400">Facturas a crédito con saldo pendiente</p>
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
        <input type="text" placeholder="Buscar por número o cliente…" value={search}
          onChange={e => setSearch(e.target.value)}
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none" />
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        {/* Lista de facturas */}
        <div className="space-y-2">
          {isLoading ? (
            <div className="flex justify-center py-12"><Spinner /></div>
          ) : !facturas || facturas.length === 0 ? (
            <div className="rounded-lg border border-slate-700 bg-slate-900 py-16 text-center">
              <DollarSign className="mx-auto h-10 w-10 text-slate-600 mb-3" />
              <p className="text-slate-400">No hay facturas con saldo pendiente</p>
            </div>
          ) : (
            facturas.map(f => (
              <button key={f.CONTROLMAESTRO} type="button"
                onClick={() => { setSelected(f); reset({ controlmaestro: f.CONTROLMAESTRO, instrumento: instrumentos?.[0]?.CODINSTRUMENTO ?? '', monto: Number(f.MONTOSAL) }) }}
                className={`w-full rounded-lg border p-4 text-left transition-colors
                  ${selected?.CONTROLMAESTRO === f.CONTROLMAESTRO
                    ? 'border-orange-600 bg-orange-900/20'
                    : 'border-slate-700 bg-slate-900 hover:border-slate-600'}`}
              >
                <div className="flex items-center justify-between mb-1">
                  <span className="font-mono text-orange-400 text-sm">{f.NROFAC}</span>
                  <Badge color="yellow">Saldo: ${Number(f.MONTOSAL).toFixed(2)}</Badge>
                </div>
                <p className="text-white text-sm font-medium">{f.NOMCLIENTE}</p>
                <div className="flex justify-between mt-1 text-xs text-slate-400">
                  <span>Total: ${Number(f.MONTOTOT).toFixed(2)}</span>
                  <span>{f.FECHA ? new Date(f.FECHA).toLocaleDateString('es-PA') : ''}</span>
                </div>
              </button>
            ))
          )}
        </div>

        {/* Formulario de cobro */}
        {selected && (
          <div className="rounded-lg border border-slate-600 bg-slate-900 p-5 space-y-4 h-fit">
            <div className="flex items-center justify-between">
              <h2 className="text-sm font-semibold text-white">Registrar Cobro</h2>
              <button onClick={() => setSelected(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <div className="rounded-lg bg-slate-800 p-3 text-sm">
              <p className="text-slate-400 text-xs">Factura</p>
              <p className="text-white font-medium">{selected.NROFAC} — {selected.NOMCLIENTE}</p>
              <div className="flex gap-4 mt-1 text-xs text-slate-400">
                <span>Total: ${Number(selected.MONTOTOT).toFixed(2)}</span>
                <span className="text-yellow-400 font-medium">Saldo: ${Number(selected.MONTOSAL).toFixed(2)}</span>
              </div>
            </div>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
              <input type="hidden" {...register('controlmaestro')} value={selected.CONTROLMAESTRO} />

              <div>
                <label className="mb-1 block text-xs text-slate-400">Instrumento de pago *</label>
                <select {...register('instrumento')}
                  className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none">
                  {instrumentos?.map(ins => (
                    <option key={ins.CODINSTRUMENTO} value={ins.CODINSTRUMENTO}>{ins.DESCRINSTRUMENTO}</option>
                  ))}
                </select>
                {errors.instrumento && <p className="mt-1 text-xs text-red-400">{errors.instrumento.message}</p>}
              </div>

              <div>
                <label className="mb-1 block text-xs text-slate-400">Monto *</label>
                <input type="number" step="0.01" max={Number(selected.MONTOSAL)}
                  {...register('monto', { valueAsNumber: true })}
                  className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none" />
                {errors.monto && <p className="mt-1 text-xs text-red-400">{errors.monto.message}</p>}
              </div>

              <div>
                <label className="mb-1 block text-xs text-slate-400">Referencia</label>
                <input type="text" {...register('referencia')} placeholder="N° cheque, transferencia…"
                  className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none" />
              </div>

              <div className="flex items-start gap-2 rounded-lg bg-yellow-900/20 border border-yellow-800/40 p-3">
                <AlertCircle className="h-4 w-4 text-yellow-400 shrink-0 mt-0.5" />
                <p className="text-xs text-yellow-400">
                  El monto máximo es el saldo pendiente: <strong>${Number(selected.MONTOSAL).toFixed(2)}</strong>
                </p>
              </div>

              <div className="flex gap-3 justify-end">
                <Button variant="secondary" type="button" onClick={() => setSelected(null)}>Cancelar</Button>
                <Button type="submit" loading={crearCobro.isPending}>
                  <DollarSign className="h-4 w-4 mr-1" /> Registrar Cobro
                </Button>
              </div>
            </form>
          </div>
        )}
      </div>
    </div>
  )
}
