import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Search, Plus, Phone, Mail, MapPin, X } from 'lucide-react'
import { Card } from '@/components/ui/Card'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import api from '@/lib/axios'
import type { Cliente } from '@/types'

// ─── Zod schema ───────────────────────────────────────────────────────────────

const nuevoClienteSchema = z.object({
  codigo:    z.string().min(1, 'Código requerido').max(10),
  nombre:    z.string().min(1, 'Nombre requerido').max(100),
  tipocli:   z.string().min(1, 'Tipo requerido'),
  rif:       z.string().optional(),
  nit:       z.string().optional(),
  direcc1:   z.string().optional(),
  numtel:    z.string().optional(),
  dircorreo: z.string().email('Email inválido').optional().or(z.literal('')),
  diascre:   z.coerce.number().min(0).optional(),
})

type NuevoClienteForm = z.infer<typeof nuevoClienteSchema>

// ─── Slide-over panel ─────────────────────────────────────────────────────────

interface NuevoClientePanelProps {
  onClose: () => void
}

function NuevoClientePanel({ onClose }: NuevoClientePanelProps) {
  const queryClient = useQueryClient()

  const { register, handleSubmit, formState: { errors } } = useForm<NuevoClienteForm>({
    resolver: zodResolver(nuevoClienteSchema),
    defaultValues: { diascre: 0 },
  })

  const mutation = useMutation({
    mutationFn: (data: NuevoClienteForm) => api.post('/clientes', data).then(r => r.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clientes'] })
      onClose()
    },
  })

  const onSubmit = (data: NuevoClienteForm) => mutation.mutate(data)

  return (
    <>
      {/* Overlay */}
      <div
        className="fixed inset-0 bg-black/50 z-40"
        onClick={onClose}
      />

      {/* Panel */}
      <div className="fixed right-0 top-0 h-full w-full max-w-md bg-slate-900 border-l border-slate-700 z-50 flex flex-col shadow-2xl">
        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b border-slate-700">
          <h2 className="text-lg font-semibold text-white">Nuevo Cliente</h2>
          <button
            onClick={onClose}
            className="rounded-md p-1.5 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} className="flex-1 overflow-y-auto px-5 py-4 space-y-4">
          {mutation.isError && (
            <div className="rounded-lg bg-red-900/30 border border-red-700 p-3 text-sm text-red-300">
              {(mutation.error as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al crear el cliente'}
            </div>
          )}

          <Input
            label="Código *"
            placeholder="CLI001"
            {...register('codigo')}
            error={errors.codigo?.message}
          />
          <Input
            label="Nombre / Razón Social *"
            placeholder="Empresa Demo S.A."
            {...register('nombre')}
            error={errors.nombre?.message}
          />

          <div>
            <label className="mb-1.5 block text-sm font-medium text-slate-300">
              Tipo de cliente *
            </label>
            <select
              {...register('tipocli')}
              className="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="">Seleccionar…</option>
              <option value="Contribuyente">Contribuyente</option>
              <option value="Consumidor Final">Consumidor Final</option>
              <option value="Gobierno">Gobierno</option>
              <option value="Extranjero">Extranjero</option>
            </select>
            {errors.tipocli && (
              <p className="mt-1 text-xs text-red-400">{errors.tipocli.message}</p>
            )}
          </div>

          <div className="grid grid-cols-2 gap-3">
            <Input
              label="RUC"
              placeholder="8-123-456"
              {...register('rif')}
              error={errors.rif?.message}
            />
            <Input
              label="DV"
              placeholder="01"
              {...register('nit')}
              error={errors.nit?.message}
            />
          </div>

          <Input
            label="Teléfono"
            placeholder="507-123-4567"
            {...register('numtel')}
            error={errors.numtel?.message}
          />
          <Input
            label="Email"
            type="email"
            placeholder="contacto@empresa.pa"
            {...register('dircorreo')}
            error={errors.dircorreo?.message}
          />
          <Input
            label="Dirección"
            placeholder="Calle 50, local 3"
            {...register('direcc1')}
            error={errors.direcc1?.message}
          />
          <Input
            label="Días de crédito"
            type="number"
            min={0}
            {...register('diascre')}
            error={errors.diascre?.message}
          />
        </form>

        {/* Footer */}
        <div className="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-700">
          <Button type="button" variant="ghost" size="sm" onClick={onClose}>
            Cancelar
          </Button>
          <Button
            type="submit"
            size="sm"
            loading={mutation.isPending}
            onClick={handleSubmit(onSubmit)}
          >
            Crear cliente
          </Button>
        </div>
      </div>
    </>
  )
}

// ─── Main page ────────────────────────────────────────────────────────────────

export function ClientesPage() {
  const [q, setQ]           = useState('')
  const [search, setSearch] = useState('')
  const [showPanel, setShowPanel] = useState(false)

  const { data, isLoading } = useQuery({
    queryKey: ['clientes', search],
    queryFn: () => api.get(`/clientes?q=${encodeURIComponent(search)}&per_page=50`).then(r => r.data.data as Cliente[]),
  })

  const tipoColor = (tipo: string | null): 'blue' | 'green' | 'yellow' | 'gray' => {
    if (!tipo) return 'gray'
    if (tipo.includes('Contribuyente')) return 'blue'
    if (tipo.includes('Gobierno'))      return 'green'
    if (tipo.includes('Consumidor'))    return 'yellow'
    return 'gray'
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">Clientes</h1>
          <p className="text-sm text-slate-400">Gestión de clientes y proveedores</p>
        </div>
        <Button size="sm" onClick={() => setShowPanel(true)}>
          <Plus className="h-4 w-4" />Nuevo
        </Button>
      </div>

      {/* Buscador */}
      <form onSubmit={(e) => { e.preventDefault(); setSearch(q) }} className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            value={q}
            onChange={e => setQ(e.target.value)}
            placeholder="Buscar por nombre, RUC o código..."
            className="w-full rounded-lg border border-slate-600 bg-slate-800 pl-10 pr-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <Button type="submit" variant="secondary" size="md">Buscar</Button>
      </form>

      {/* Lista */}
      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : (
        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          {data?.map((cli) => (
            <Card key={cli.CODIGO} className="hover:border-slate-600 transition-colors cursor-pointer">
              <div className="flex items-start justify-between gap-2">
                <div className="flex-1 min-w-0">
                  <p className="font-semibold text-white truncate">{cli.NOMBRE}</p>
                  <p className="text-xs text-slate-400 font-mono">{cli.CODIGO}</p>
                </div>
                <Badge color={tipoColor(cli.TIPOCLI)}>{cli.TIPOCLI}</Badge>
              </div>
              <div className="mt-3 space-y-1.5">
                {cli.RIF && (
                  <div className="flex items-center gap-2 text-xs text-slate-400">
                    <span className="font-medium text-slate-300">RUC:</span> {cli.RIF}
                    {cli.NIT && <span>DV: {cli.NIT}</span>}
                  </div>
                )}
                {cli.NUMTEL && (
                  <div className="flex items-center gap-2 text-xs text-slate-400">
                    <Phone className="h-3.5 w-3.5" />{cli.NUMTEL}
                  </div>
                )}
                {cli.DIRCORREO && (
                  <div className="flex items-center gap-2 text-xs text-slate-400">
                    <Mail className="h-3.5 w-3.5" /><span className="truncate">{cli.DIRCORREO}</span>
                  </div>
                )}
                {cli.provincia && (
                  <div className="flex items-center gap-2 text-xs text-slate-400">
                    <MapPin className="h-3.5 w-3.5" />{[cli.provincia, cli.distrito].filter(Boolean).join(', ')}
                  </div>
                )}
              </div>
            </Card>
          ))}
          {!data?.length && (
            <div className="col-span-full py-12 text-center text-slate-500">
              {search ? `No se encontraron clientes para "${search}"` : 'Escribe un término para buscar clientes'}
            </div>
          )}
        </div>
      )}

      {/* Slide-over panel */}
      {showPanel && <NuevoClientePanel onClose={() => setShowPanel(false)} />}
    </div>
  )
}
