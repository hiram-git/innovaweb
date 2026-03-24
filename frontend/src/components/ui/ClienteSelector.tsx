import { useState, useEffect } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Search, WifiOff } from 'lucide-react'
import { api } from '@/lib/axios'
import { db } from '@/lib/db'
import { useOnlineStatus } from '@/hooks/useOnlineStatus'
import type { Cliente } from '@/types'

interface Props {
  value:    Cliente | null
  onSelect: (c: Cliente) => void
}

export function ClienteSelector({ value, onSelect }: Props) {
  const [q, setQ]       = useState(value?.NOMBRE ?? '')
  const [open, setOpen] = useState(false)
  const isOnline        = useOnlineStatus()

  // Sync text when parent resets value
  useEffect(() => { setQ(value?.NOMBRE ?? '') }, [value])

  const { data } = useQuery({
    queryKey: ['clientes-search', q],
    queryFn: async () => {
      if (isOnline) {
        const result = await api.get<{ data: Cliente[] }>('/clientes', { params: { q, limit: 8 } })
          .then(r => r.data.data)
        if (result.length > 0) {
          await db.clientes.bulkPut(result).catch(() => {/* ignore quota errors */})
        }
        return result
      }
      return db.clientes
        .filter(c =>
          (c.NOMBRE ?? '').toLowerCase().includes(q.toLowerCase()) ||
          (c.RIF    ?? '').toLowerCase().includes(q.toLowerCase()) ||
          (c.CODIGO ?? '').toLowerCase().includes(q.toLowerCase())
        )
        .limit(8)
        .toArray()
    },
    enabled: q.length >= 2,
  })

  return (
    <div className="relative">
      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
        <input
          type="text"
          placeholder={isOnline ? 'Buscar cliente por nombre o RUC…' : 'Buscar en caché local…'}
          value={q}
          onChange={e => { setQ(e.target.value); setOpen(true) }}
          onFocus={() => setOpen(true)}
          className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 pl-9 pr-4 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none"
        />
        {!isOnline && (
          <WifiOff className="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-yellow-500" />
        )}
      </div>
      {open && data && data.length > 0 && (
        <ul className="absolute z-20 mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 shadow-xl max-h-52 overflow-y-auto">
          {data.map(c => (
            <li key={c.CODIGO}>
              <button
                type="button"
                onClick={() => { onSelect(c); setQ(c.NOMBRE ?? ''); setOpen(false) }}
                className="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-800 text-white"
              >
                <span className="font-medium">{c.NOMBRE}</span>
                <span className="ml-2 text-xs text-slate-400">{c.RIF}</span>
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
