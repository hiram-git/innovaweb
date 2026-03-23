import { useQuery } from '@tanstack/react-query'
import { FileText, Users, DollarSign, Zap, CheckCircle, Send, Clock } from 'lucide-react'
import { Link } from 'react-router-dom'
import { Card } from '@/components/ui/Card'
import { Badge } from '@/components/ui/Badge'
import { Spinner } from '@/components/ui/Spinner'
import api from '@/lib/axios'
import type { FacturaMaestro } from '@/types'

interface DashboardStats {
  kpis: {
    facturas_hoy: number
    total_cobrar: number
    fe_aceptadas_mes: number
    clientes_activos: number
  }
  ultimas_facturas: FacturaMaestro[]
}

function feIcon(estado: string | null) {
  switch (estado) {
    case 'ACEPTADO':  return <CheckCircle className="h-3 w-3 text-green-400" />
    case 'ENVIADO':   return <Send        className="h-3 w-3 text-orange-400"  />
    case 'PENDIENTE': return <Clock       className="h-3 w-3 text-yellow-400"/>
    default:          return <FileText    className="h-3 w-3 text-slate-500" />
  }
}

export function Dashboard() {
  const { data, isLoading, isError } = useQuery<DashboardStats>({
    queryKey: ['dashboard-stats'],
    queryFn: () => api.get<DashboardStats>('/dashboard/stats').then(r => r.data),
    staleTime: 60_000, // Re-fetch each minute
  })

  const kpis = data?.kpis
  const facturas = data?.ultimas_facturas ?? []

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-white">Dashboard</h1>
        <p className="text-sm text-slate-400">Resumen de actividad del sistema</p>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
        <Card className="flex flex-col gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-900/50">
            <FileText className="h-5 w-5 text-orange-400" />
          </div>
          {isLoading ? (
            <div className="h-8 w-16 animate-pulse rounded bg-slate-700" />
          ) : (
            <p className="text-2xl font-bold text-white">{kpis?.facturas_hoy ?? 0}</p>
          )}
          <p className="text-xs text-slate-400">Facturas hoy</p>
        </Card>

        <Card className="flex flex-col gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-green-900/50">
            <Users className="h-5 w-5 text-green-400" />
          </div>
          {isLoading ? (
            <div className="h-8 w-16 animate-pulse rounded bg-slate-700" />
          ) : (
            <p className="text-2xl font-bold text-white">{kpis?.clientes_activos ?? 0}</p>
          )}
          <p className="text-xs text-slate-400">Clientes activos</p>
        </Card>

        <Card className="flex flex-col gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-yellow-900/50">
            <DollarSign className="h-5 w-5 text-yellow-400" />
          </div>
          {isLoading ? (
            <div className="h-8 w-24 animate-pulse rounded bg-slate-700" />
          ) : (
            <p className="text-2xl font-bold text-white">
              B/.{(kpis?.total_cobrar ?? 0).toLocaleString('es-PA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </p>
          )}
          <p className="text-xs text-slate-400">Por cobrar</p>
        </Card>

        <Card className="flex flex-col gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-900/50">
            <Zap className="h-5 w-5 text-purple-400" />
          </div>
          {isLoading ? (
            <div className="h-8 w-16 animate-pulse rounded bg-slate-700" />
          ) : (
            <p className="text-2xl font-bold text-white">{kpis?.fe_aceptadas_mes ?? 0}</p>
          )}
          <p className="text-xs text-slate-400">FE aceptadas (mes)</p>
        </Card>
      </div>

      {/* Últimas facturas */}
      <Card title="Últimas facturas">
        {isLoading ? (
          <div className="flex justify-center py-8"><Spinner /></div>
        ) : isError ? (
          <p className="py-8 text-center text-sm text-red-400">Error al cargar datos</p>
        ) : facturas.length === 0 ? (
          <p className="py-8 text-center text-sm text-slate-500">No hay facturas recientes</p>
        ) : (
          <div className="space-y-1.5">
            {facturas.map((f) => (
              <div
                key={f.CONTROLMAESTRO}
                className="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2.5"
              >
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2">
                    <span className="font-mono text-sm text-orange-400">{f.NROFAC ?? '—'}</span>
                    {feIcon(f.FE_ESTADO ?? null)}
                  </div>
                  <p className="truncate text-xs text-slate-400 max-w-[200px]">{f.NOMCLIENTE ?? '—'}</p>
                </div>
                <div className="ml-3 shrink-0 text-right">
                  <p className="text-sm font-semibold text-white">
                    B/.{Number(f.MONTOTOT ?? 0).toFixed(2)}
                  </p>
                  <Badge color={f.TIPTRAN === 'CONTADO' ? 'blue' : 'yellow'}>
                    {f.TIPTRAN === 'CONTADO' ? 'Contado' : 'Crédito'}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        )}
        <div className="mt-3 border-t border-slate-700 pt-3">
          <Link
            to="/facturas"
            className="text-xs text-orange-400 hover:text-orange-300 transition-colors"
          >
            Ver todas las facturas →
          </Link>
        </div>
      </Card>
    </div>
  )
}
