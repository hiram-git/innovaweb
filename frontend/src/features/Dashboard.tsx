import { useQuery } from '@tanstack/react-query'
import { FileText, Users, DollarSign, Zap, TrendingUp, Clock } from 'lucide-react'
import { Card } from '@/components/ui/Card'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import api from '@/lib/axios'

export function Dashboard() {
  const { data: facturas, isLoading } = useQuery({
    queryKey: ['dashboard-facturas'],
    queryFn: () => api.get('/facturas?per_page=10').then(r => r.data.data),
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-white">Dashboard</h1>
        <p className="text-sm text-slate-400">Resumen de actividad del sistema</p>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
        {[
          { label: 'Facturas hoy',     icon: FileText,   value: '—', color: 'blue'  },
          { label: 'Clientes activos', icon: Users,      value: '—', color: 'green' },
          { label: 'Por cobrar',       icon: DollarSign, value: '—', color: 'yellow'},
          { label: 'FE enviadas',      icon: Zap,        value: '—', color: 'purple'},
        ].map(({ label, icon: Icon, value, color }) => (
          <Card key={label} className="flex flex-col gap-2">
            <div className={`flex h-9 w-9 items-center justify-center rounded-lg bg-${color}-900/50`}>
              <Icon className={`h-5 w-5 text-${color}-400`} />
            </div>
            <p className="text-2xl font-bold text-white">{value}</p>
            <p className="text-xs text-slate-400">{label}</p>
          </Card>
        ))}
      </div>

      {/* Últimas facturas */}
      <Card title="Últimas facturas">
        {isLoading ? (
          <div className="flex justify-center py-8"><Spinner /></div>
        ) : (
          <div className="space-y-2">
            {(facturas ?? []).map((f: Record<string, unknown>) => (
              <div key={String(f.CONTROL)} className="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2.5">
                <div>
                  <p className="text-sm font-medium text-white">#{String(f.NUMREF)}</p>
                  <p className="text-xs text-slate-400 truncate max-w-[180px]">{String(f.NOMBRE)}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm font-semibold text-white">B/. {Number(f.MONTOTOT).toFixed(2)}</p>
                  <Badge color={f.CUFE ? 'green' : 'gray'}>{f.CUFE ? 'FE Enviada' : 'Pendiente'}</Badge>
                </div>
              </div>
            ))}
            {!facturas?.length && (
              <p className="py-8 text-center text-sm text-slate-500">No hay facturas recientes</p>
            )}
          </div>
        )}
      </Card>
    </div>
  )
}
