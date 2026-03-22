import { WifiOff } from 'lucide-react'
import { useOnlineStatus } from '@/hooks/useOnlineStatus'

export function OfflineBanner() {
  const isOnline = useOnlineStatus()
  if (isOnline) return null
  return (
    <div className="fixed top-0 inset-x-0 z-50 flex items-center justify-center gap-2 bg-yellow-600 px-4 py-2 text-sm font-medium text-white">
      <WifiOff className="h-4 w-4" />
      Sin conexión — Las facturas se guardarán localmente y se enviarán al reconectar
    </div>
  )
}
