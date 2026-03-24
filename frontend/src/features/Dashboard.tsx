import { useQuery } from '@tanstack/react-query'
import { FileText, Users, DollarSign, Zap, Plus } from 'lucide-react'
import { Link } from 'react-router-dom'
import { Card } from '@/components/ui/Card'
import { Spinner } from '@/components/ui/Spinner'
import api from '@/lib/axios'
import { useAuthStore } from '@/stores/authStore'

interface TopCliente {
  CODIGO: string
  NOMBRE: string
  total_facturas: number
  monto_total: number
}

interface DashboardStats {
  kpis: {
    facturas_hoy: number
    total_cobrar: number
    fe_aceptadas_mes: number
    clientes_activos: number
  }
  top_clientes: TopCliente[]
}

const shortcuts = [
  {
    to: '/facturas/nueva',
    label: 'Nueva Factura',
    icon: FileText,
    color: 'bg-orange-700 hover:bg-orange-600',
    permiso: 'ver_factura' as const,
  },
  {
    to: '/cobros',
    label: 'Nuevo Cobro',
    icon: DollarSign,
    color: 'bg-green-700 hover:bg-green-600',
    permiso: 'ver_cobro' as const,
  },
  {
    to: '/clientes',
    label: 'Nuevo Cliente',
    icon: Users,
    color: 'bg-blue-700 hover:bg-blue-600',
    permiso: null,
  },
]

export function Dashboard() {
  const permisos = useAuthStore(s => s.user?.permisos)

  const { data, isLoading, isError } = useQuery<DashboardStats>({
    queryKey: ['dashboard-stats'],
    queryFn: () => api.get<DashboardStats>('/dashboard/stats').then(r => r.data),
    staleTime: 60_000,
  })

  const kpis = data?.kpis
  const topClientes = data?.top_clientes ?? []

  const visibleShortcuts = shortcuts.filter(s =>
    s.permiso === null || !permisos || (permisos[s.permiso] as number) !== 0
  )

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

      {/* Accesos directos */}
      {visibleShortcuts.length > 0 && (
        <div className="grid grid-cols-3 gap-3">
          {visibleShortcuts.map(({ to, label, icon: Icon, color }) => (
            <Link
              key={to}
              to={to}
              className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white transition-colors ${color}`}
            >
              <Plus className="h-4 w-4 shrink-0" />
              <span>{label}</span>
              <Icon className="ml-auto h-4 w-4 shrink-0 opacity-70" />
            </Link>
          ))}
        </div>
      )}

      {/* Top 10 clientes por facturas */}
      <Card title="Clientes con más facturas">
        {isLoading ? (
          <div className="flex justify-center py-8"><Spinner /></div>
        ) : isError ? (
          <p className="py-8 text-center text-sm text-red-400">Error al cargar datos</p>
        ) : topClientes.length === 0 ? (
          <p className="py-8 text-center text-sm text-slate-500">Sin datos disponibles</p>
        ) : (
          <div className="space-y-1.5">
            {topClientes.map((c, idx) => (
              <div
                key={c.CODIGO}
                className="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2.5"
              >
                <div className="flex items-center gap-3 min-w-0 flex-1">
                  <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-700 text-xs font-bold text-slate-300">
                    {idx + 1}
                  </span>
                  <div className="min-w-0">
                    <p className="truncate text-sm text-white">{c.NOMBRE ?? c.CODIGO}</p>
                    <p className="text-xs text-slate-500">{c.CODIGO}</p>
                  </div>
                </div>
                <div className="ml-3 shrink-0 text-right">
                  <p className="text-sm font-semibold text-orange-400">
                    {Number(c.total_facturas)} fact.
                  </p>
                  <p className="text-xs text-slate-400">
                    B/.{Number(c.monto_total ?? 0).toLocaleString('es-PA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                  </p>
                </div>
              </div>
            ))}
          </div>
        )}
        <div className="mt-3 border-t border-slate-700 pt-3">
          <Link
            to="/clientes"
            className="text-xs text-orange-400 hover:text-orange-300 transition-colors"
          >
            Ver todos los clientes →
          </Link>
        </div>
      </Card>
    </div>
  )
}
