import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClientProvider } from '@tanstack/react-query'
import { queryClient } from '@/lib/queryClient'
import { ProtectedRoute } from '@/features/auth/ProtectedRoute'
import { AppLayout } from '@/components/layout/AppLayout'
import { LoginPage } from '@/features/auth/LoginPage'
import { Dashboard } from '@/features/Dashboard'
import { ClientesPage } from '@/features/clientes/ClientesPage'
import { InventarioPage } from '@/features/inventario/InventarioPage'
import { FacturasPage } from '@/features/facturas/FacturasPage'
import { NuevaFacturaPage } from '@/features/facturas/NuevaFacturaPage'
import { FEPage } from '@/features/fe/FEPage'
import { OTPage } from '@/features/ordenes-trabajo/OTPage'
import { CobrosPage } from '@/features/cobros/CobrosPage'
import { PresupuestosPage } from '@/features/presupuestos/PresupuestosPage'
import { PedidosPage } from '@/features/pedidos/PedidosPage'
import { NuevaSolicitudPage } from '@/features/solicitudes/NuevaSolicitudPage'
import { ConfiguracionPage } from '@/features/configuracion/ConfiguracionPage'

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route element={<ProtectedRoute />}>
            <Route element={<AppLayout />}>
              <Route index element={<Dashboard />} />
              <Route path="clientes" element={<ClientesPage />} />
              <Route path="inventario" element={<InventarioPage />} />
              <Route path="facturas" element={<FacturasPage />} />
              <Route path="facturas/nueva" element={<NuevaFacturaPage />} />
              <Route path="facturacion-electronica" element={<FEPage />} />
              <Route path="ordenes-trabajo" element={<OTPage />} />
              <Route path="cobros" element={<CobrosPage />} />
              <Route path="presupuestos" element={<PresupuestosPage />} />
              <Route path="presupuestos/nuevo" element={<NuevaSolicitudPage tipo="presupuesto" />} />
              <Route path="pedidos" element={<PedidosPage />} />
              <Route path="pedidos/nuevo" element={<NuevaSolicitudPage tipo="pedido" />} />
              <Route path="configuracion" element={<ConfiguracionPage />} />
            </Route>
          </Route>
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}
