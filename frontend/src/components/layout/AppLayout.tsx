import { Outlet, NavLink } from 'react-router-dom'
import { Sidebar } from './Sidebar'
import { OfflineBanner } from '@/components/ui/OfflineBanner'
import { OfflineQueue } from '@/components/ui/OfflineQueue'
import { InstallPWA } from '@/components/ui/InstallPWA'
import { useOnlineStatus } from '@/hooks/useOnlineStatus'
import { FileText, Users, Package, DollarSign, LayoutDashboard } from 'lucide-react'
import { clsx } from 'clsx'

const mobileNav = [
  { to: '/',          icon: LayoutDashboard, label: 'Inicio'     },
  { to: '/facturas',  icon: FileText,        label: 'Facturas'   },
  { to: '/clientes',  icon: Users,           label: 'Clientes'   },
  { to: '/inventario',icon: Package,         label: 'Inventario' },
  { to: '/cobros',    icon: DollarSign,      label: 'Cobros'     },
]

export function AppLayout() {
  const isOnline = useOnlineStatus()
  return (
    <div className="flex h-dvh overflow-hidden bg-slate-950">
      <OfflineBanner />
      {/* Sidebar — md+ */}
      <div className={clsx('hidden md:flex md:flex-col', !isOnline && 'mt-8')}>
        <Sidebar />
      </div>
      {/* Contenido principal */}
      <main className={clsx('flex flex-1 flex-col overflow-hidden', !isOnline && 'mt-8')}>
        <div className="flex-1 overflow-y-auto p-4 pb-20 md:p-6 md:pb-6">
          <Outlet />
        </div>
      </main>
      {/* Offline queue floating button */}
      <OfflineQueue />
      {/* PWA install prompt */}
      <InstallPWA />
      {/* Bottom nav — mobile only */}
      <nav className="fixed bottom-0 inset-x-0 border-t border-slate-700 bg-slate-900 flex md:hidden z-30">
        {mobileNav.map(({ to, icon: Icon, label }) => (
          <NavLink key={to} to={to} end={to === '/'} className={({ isActive }) =>
            clsx('flex flex-1 flex-col items-center py-2 text-xs gap-1 transition-colors',
              isActive ? 'text-blue-400' : 'text-slate-400 hover:text-slate-200')}>
            <Icon className="h-5 w-5"/>
            {label}
          </NavLink>
        ))}
      </nav>
    </div>
  )
}
