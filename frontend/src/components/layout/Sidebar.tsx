import { NavLink } from 'react-router-dom'
import {
  LayoutDashboard, Users, Package, FileText, Zap,
  Wrench, DollarSign, ClipboardList, ShoppingCart, Settings, LogOut, Building2,
} from 'lucide-react'
import { clsx } from 'clsx'
import { useAuthStore } from '@/stores/authStore'
import { useOnlineStatus } from '@/hooks/useOnlineStatus'

const navItems = [
  { to: '/',                 icon: LayoutDashboard, label: 'Dashboard'       },
  { to: '/clientes',         icon: Users,           label: 'Clientes'        },
  { to: '/inventario',       icon: Package,         label: 'Inventario'      },
  { to: '/facturas',         icon: FileText,        label: 'Facturas'        },
  { to: '/facturacion-electronica', icon: Zap,      label: 'FE / DGI'        },
  { to: '/ordenes-trabajo',  icon: Wrench,          label: 'Órd. Trabajo'    },
  { to: '/cobros',           icon: DollarSign,      label: 'Cobros'          },
  { to: '/presupuestos',     icon: ClipboardList,   label: 'Presupuestos'    },
  { to: '/pedidos',          icon: ShoppingCart,    label: 'Pedidos'         },
  { to: '/configuracion',    icon: Settings,        label: 'Configuración'   },
]

export function Sidebar() {
  const { user, clearAuth } = useAuthStore()
  const isOnline = useOnlineStatus()

  return (
    <aside className="flex h-full w-64 flex-col border-r border-slate-700 bg-slate-900">
      {/* Logo */}
      <div className="flex items-center gap-2 border-b border-slate-700 px-4 py-5">
        <Building2 className="h-7 w-7 text-orange-400" />
        <div>
          <p className="text-sm font-bold text-white">InnovaWeb</p>
          <p className="text-xs text-slate-400">Facturación Electrónica</p>
        </div>
      </div>

      {/* Estado de conexión */}
      <div className={clsx(
        'mx-3 mt-2 rounded-md px-3 py-1.5 text-xs font-medium flex items-center gap-1.5',
        isOnline ? 'bg-green-900/40 text-green-400' : 'bg-yellow-900/40 text-yellow-400'
      )}>
        <span className={clsx('h-1.5 w-1.5 rounded-full', isOnline ? 'bg-green-400' : 'bg-yellow-400')} />
        {isOnline ? 'Conectado' : 'Sin conexión'}
      </div>

      {/* Nav */}
      <nav className="mt-3 flex-1 space-y-0.5 overflow-y-auto px-2">
        {navItems.map(({ to, icon: Icon, label }) => (
          <NavLink
            key={to}
            to={to}
            end={to === '/'}
            className={({ isActive }) => clsx(
              'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
              isActive
                ? 'bg-orange-700 text-white font-medium'
                : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
            )}
          >
            <Icon className="h-4 w-4 shrink-0" />
            {label}
          </NavLink>
        ))}
      </nav>

      {/* User */}
      <div className="border-t border-slate-700 p-3">
        <div className="flex items-center gap-2 rounded-lg px-2 py-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-orange-700 text-sm font-bold text-white">
            {user?.codigo?.[0] ?? '?'}
          </div>
          <div className="flex-1 min-w-0">
            <p className="truncate text-sm font-medium text-white">{user?.codigo ?? 'Usuario'}</p>
            <p className="truncate text-xs text-slate-400">{user?.roles?.[0] ?? 'Sin rol'}</p>
          </div>
          <button
            onClick={clearAuth}
            title="Cerrar sesión"
            className="p-1 text-slate-400 hover:text-red-400 transition-colors"
          >
            <LogOut className="h-4 w-4" />
          </button>
        </div>
      </div>
    </aside>
  )
}
