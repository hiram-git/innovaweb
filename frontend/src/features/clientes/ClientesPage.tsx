import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Search, Plus, Phone, Mail, MapPin } from 'lucide-react'
import { Card } from '@/components/ui/Card'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Button } from '@/components/ui/Button'
import api from '@/lib/axios'
import type { Cliente } from '@/types'

export function ClientesPage() {
  const [q, setQ] = useState('')
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['clientes', search],
    queryFn: () => api.get(`/clientes?q=${encodeURIComponent(search)}&per_page=50`).then(r => r.data.data as Cliente[]),
  })

  const tipoColor = (tipo: string): 'blue' | 'green' | 'yellow' | 'gray' => {
    if (tipo.includes('Contribuyente')) return 'blue'
    if (tipo.includes('Gobierno')) return 'green'
    if (tipo.includes('Consumidor')) return 'yellow'
    return 'gray'
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">Clientes</h1>
          <p className="text-sm text-slate-400">Gestión de clientes y proveedores</p>
        </div>
        <Button size="sm"><Plus className="h-4 w-4" />Nuevo</Button>
      </div>

      {/* Buscador */}
      <form onSubmit={(e) => { e.preventDefault(); setSearch(q) }} className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            value={q}
            onChange={e => setQ(e.target.value)}
            placeholder="Buscar por nombre, RUC o código..."
            className="w-full rounded-lg border border-slate-600 bg-slate-800 pl-10 pr-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
    </div>
  )
}
