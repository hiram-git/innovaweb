import { useNavigate } from 'react-router-dom'
import { FileText, ClipboardList, ShoppingCart, DollarSign, X } from 'lucide-react'
import { useFacturaStore } from '@/stores/facturaStore'
import type { Cliente } from '@/types'

interface Props {
  cliente: Cliente
  onClose: () => void
}

const tasks = [
  {
    id: 'presupuesto',
    icon: ClipboardList,
    label: 'Presupuesto',
    description: 'Cotización — no mueve inventario',
    color: 'text-yellow-400',
    bg: 'bg-yellow-900/30 hover:bg-yellow-900/50 border-yellow-800/40',
  },
  {
    id: 'pedido',
    icon: ShoppingCart,
    label: 'Pedido',
    description: 'Reserva mercancía en el almacén',
    color: 'text-blue-400',
    bg: 'bg-blue-900/30 hover:bg-blue-900/50 border-blue-800/40',
  },
  {
    id: 'factura',
    icon: FileText,
    label: 'Nueva Factura',
    description: 'Despacha y registra salida de inventario',
    color: 'text-orange-400',
    bg: 'bg-orange-900/30 hover:bg-orange-900/50 border-orange-800/40',
  },
  {
    id: 'cobro',
    icon: DollarSign,
    label: 'Cobros',
    description: 'Registrar o consultar cobros',
    color: 'text-green-400',
    bg: 'bg-green-900/30 hover:bg-green-900/50 border-green-800/40',
  },
] as const

type TaskId = typeof tasks[number]['id']

export function ClienteTaskModal({ cliente, onClose }: Props) {
  const navigate     = useNavigate()
  const setCliente   = useFacturaStore(s => s.setCliente)

  const handleSelect = (taskId: TaskId) => {
    switch (taskId) {
      case 'factura':
        setCliente(cliente)
        navigate('/facturas/nueva')
        break
      case 'presupuesto':
        navigate('/presupuestos/nuevo', { state: { cliente } })
        break
      case 'pedido':
        navigate('/pedidos/nuevo', { state: { cliente } })
        break
      case 'cobro':
        navigate('/cobros', { state: { cliente } })
        break
    }
    onClose()
  }

  return (
    <>
      {/* Overlay */}
      <div
        className="fixed inset-0 bg-black/60 z-40 animate-fade-in"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
        <div className="w-full max-w-sm rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl pointer-events-auto animate-slide-in-up">
          {/* Header */}
          <div className="flex items-start justify-between px-5 pt-5 pb-4 border-b border-slate-800">
            <div className="flex-1 min-w-0">
              <p className="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Cliente seleccionado</p>
              <p className="font-semibold text-white truncate">{cliente.NOMBRE}</p>
              {cliente.RIF && (
                <p className="text-xs text-slate-500 font-mono mt-0.5">RUC: {cliente.RIF}{cliente.NIT ? ` DV: ${cliente.NIT}` : ''}</p>
              )}
            </div>
            <button
              onClick={onClose}
              className="ml-3 p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
            >
              <X className="h-4 w-4" />
            </button>
          </div>

          {/* Task options */}
          <div className="p-4 space-y-2">
            <p className="text-xs text-slate-500 mb-3">¿Qué deseas hacer?</p>
            {tasks.map(({ id, icon: Icon, label, description, color, bg }) => (
              <button
                key={id}
                onClick={() => handleSelect(id)}
                className={`w-full flex items-center gap-3 rounded-xl border p-3.5 text-left transition-colors ${bg}`}
              >
                <div className={`h-9 w-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 ${color}`}>
                  <Icon className="h-5 w-5" />
                </div>
                <div>
                  <p className="font-medium text-white text-sm">{label}</p>
                  <p className="text-xs text-slate-400 mt-0.5">{description}</p>
                </div>
              </button>
            ))}
          </div>
        </div>
      </div>
    </>
  )
}
