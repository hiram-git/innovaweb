import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Search, Package, AlertTriangle } from 'lucide-react'
import { Card } from '@/components/ui/Card'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import { Button } from '@/components/ui/Button'
import api from '@/lib/axios'
import type { Producto } from '@/types'

export function InventarioPage() {
  const [q, setQ] = useState('')
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['inventario', search],
    queryFn: () => api.get(`/inventario?q=${encodeURIComponent(search)}&per_page=50`).then(r => r.data.data as Producto[]),
  })

  const disponible = (p: Producto) => p.EXISTENCIA - p.CANRESERVADA

  const tasaLabel = (imppor: number) => imppor === 0 ? 'Exento' : `ITBMS ${imppor}%`
  const tasaColor = (imppor: number): 'gray' | 'blue' | 'yellow' | 'red' =>
    imppor === 0 ? 'gray' : imppor === 7 ? 'blue' : imppor === 10 ? 'yellow' : 'red'

  return (
    <div className="space-y-5">
      <div>
        <h1 className="text-2xl font-bold text-white">Inventario</h1>
        <p className="text-sm text-slate-400">Catálogo de productos y servicios</p>
      </div>

      <form onSubmit={(e) => { e.preventDefault(); setSearch(q) }} className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            value={q}
            onChange={e => setQ(e.target.value)}
            placeholder="Buscar por código o descripción..."
            className="w-full rounded-lg border border-slate-600 bg-slate-800 pl-10 pr-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <Button type="submit" variant="secondary">Buscar</Button>
      </form>

      {isLoading ? (
        <div className="flex justify-center py-12"><Spinner /></div>
      ) : (
        <div className="overflow-x-auto rounded-xl border border-slate-700">
          <table className="w-full text-sm">
            <thead className="bg-slate-800/80 text-xs text-slate-400 uppercase tracking-wide">
              <tr>
                {['Código','Descripción','Precio','Disponible','ITBMS','Tipo'].map(h => (
                  <th key={h} className="px-4 py-3 text-left">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-700/50">
              {data?.map((p) => (
                <tr key={p.CODPRO} className="hover:bg-slate-800/30 transition-colors">
                  <td className="px-4 py-3 font-mono text-orange-400 text-xs">{p.CODPRO}</td>
                  <td className="px-4 py-3 text-white max-w-[200px] truncate">{p.DESCRIP1}</td>
                  <td className="px-4 py-3 text-right font-medium text-white">B/. {Number(p.PRECVEN1).toFixed(2)}</td>
                  <td className="px-4 py-3 text-right">
                    <span className={disponible(p) > 0 ? 'text-green-400' : 'text-red-400 font-medium'}>
                      {disponible(p) <= 0 && <AlertTriangle className="inline h-3.5 w-3.5 mr-1" />}
                      {disponible(p).toFixed(0)} {p.UNIDAD}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    <Badge color={tasaColor(p.IMPPOR)}>{tasaLabel(p.IMPPOR)}</Badge>
                  </td>
                  <td className="px-4 py-3">
                    <Badge color={p.TIPINV === 'S' || p.TIPINV === 'SRV' ? 'blue' : 'gray'}>
                      {p.TIPINV === 'S' || p.TIPINV === 'SRV' ? 'Servicio' : 'Producto'}
                    </Badge>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {!data?.length && (
            <p className="py-12 text-center text-slate-500">
              {search ? 'Sin resultados' : 'Ingresa un término de búsqueda'}
            </p>
          )}
        </div>
      )}
    </div>
  )
}
