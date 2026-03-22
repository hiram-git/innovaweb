import { useState, useEffect } from 'react'
import { WifiOff, Upload, Trash2, RefreshCw, CheckCircle } from 'lucide-react'
import { db } from '@/lib/db'
import { useOnlineStatus } from '@/hooks/useOnlineStatus'
import { api } from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import type { FacturaOffline } from '@/lib/db'

export function OfflineQueue() {
  const isOnline = useOnlineStatus()
  const [queue, setQueue]       = useState<FacturaOffline[]>([])
  const [syncing, setSyncing]   = useState(false)
  const [expanded, setExpanded] = useState(false)

  const loadQueue = async () => {
    const items = await db.facturas_offline.toArray()
    setQueue(items)
  }

  useEffect(() => { void loadQueue() }, [isOnline])

  // Auto-sync when back online
  useEffect(() => {
    if (isOnline && queue.some(q => q.estado === 'pendiente')) {
      void syncAll()
    }
  }, [isOnline])

  const syncAll = async () => {
    setSyncing(true)
    const pendientes = await db.facturas_offline.where('estado').equals('pendiente').toArray()

    for (const item of pendientes) {
      try {
        await db.facturas_offline.update(item.id!, { estado: 'sincronizando' })
        await api.post('/facturas', item.payload)
        await db.facturas_offline.delete(item.id!)
      } catch {
        await db.facturas_offline.update(item.id!, { estado: 'error' })
      }
    }

    await loadQueue()
    queryClient.invalidateQueries({ queryKey: ['facturas'] })
    setSyncing(false)
  }

  const remove = async (id: number) => {
    await db.facturas_offline.delete(id)
    await loadQueue()
  }

  const retry = async (id: number) => {
    await db.facturas_offline.update(id, { estado: 'pendiente' })
    await loadQueue()
  }

  if (queue.length === 0) return null

  const pending = queue.filter(q => q.estado === 'pendiente').length
  const errors  = queue.filter(q => q.estado === 'error').length

  return (
    <div className="fixed bottom-20 right-4 md:bottom-4 z-40">
      {/* Badge */}
      <button
        onClick={() => setExpanded(e => !e)}
        className={`flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium shadow-lg transition-colors
          ${errors > 0 ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white'}`}
      >
        <WifiOff className="h-4 w-4" />
        {queue.length} en cola
      </button>

      {/* Panel */}
      {expanded && (
        <div className="absolute bottom-12 right-0 w-72 rounded-lg border border-slate-700 bg-slate-900 shadow-2xl">
          <div className="flex items-center justify-between px-4 py-3 border-b border-slate-700">
            <p className="text-sm font-medium text-white">Cola offline</p>
            {isOnline && pending > 0 && (
              <button onClick={() => void syncAll()} disabled={syncing}
                className="flex items-center gap-1 text-xs text-blue-400 hover:text-blue-300 disabled:opacity-50">
                <RefreshCw className={`h-3.5 w-3.5 ${syncing ? 'animate-spin' : ''}`} />
                Sincronizar
              </button>
            )}
          </div>

          <div className="max-h-64 overflow-y-auto p-2 space-y-2">
            {queue.map(item => (
              <div key={item.id}
                className={`rounded-lg p-3 text-sm flex items-start justify-between gap-2
                  ${item.estado === 'error' ? 'bg-red-900/20 border border-red-800/40'
                  : item.estado === 'sincronizando' ? 'bg-blue-900/20 border border-blue-800/40'
                  : 'bg-slate-800 border border-slate-700'}`}>
                <div className="flex-1 min-w-0">
                  <p className="text-white text-xs font-medium truncate">
                    {item.payload.codcliente} — {item.payload.items?.length ?? 0} ítems
                  </p>
                  <p className={`text-xs mt-0.5 ${
                    item.estado === 'error'         ? 'text-red-400'    :
                    item.estado === 'sincronizando' ? 'text-blue-400'   :
                                                      'text-slate-400'}`}>
                    {item.estado === 'error'         ? 'Error al sincronizar' :
                     item.estado === 'sincronizando' ? 'Sincronizando…'       :
                                                       'Pendiente'}
                  </p>
                </div>
                <div className="flex items-center gap-1">
                  {item.estado === 'error' && (
                    <button onClick={() => void retry(item.id!)} title="Reintentar"
                      className="text-yellow-400 hover:text-yellow-300 p-0.5">
                      <RefreshCw className="h-3.5 w-3.5" />
                    </button>
                  )}
                  {item.estado !== 'sincronizando' && (
                    <button onClick={() => void remove(item.id!)} title="Eliminar"
                      className="text-red-400 hover:text-red-300 p-0.5">
                      <Trash2 className="h-3.5 w-3.5" />
                    </button>
                  )}
                  {item.estado === 'sincronizando' && (
                    <Upload className="h-3.5 w-3.5 text-blue-400 animate-pulse" />
                  )}
                </div>
              </div>
            ))}
          </div>

          {!isOnline && (
            <div className="px-4 py-3 border-t border-slate-700">
              <p className="text-xs text-yellow-400 flex items-center gap-1.5">
                <WifiOff className="h-3.5 w-3.5" />
                Sin conexión. Las facturas se sincronizarán automáticamente al reconectar.
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
