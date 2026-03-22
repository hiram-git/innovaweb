import { useEffect, useState } from 'react'
import { CheckCircle, XCircle, AlertCircle, X } from 'lucide-react'
import { clsx } from 'clsx'

type ToastType = 'success' | 'error' | 'warning'
interface ToastProps { type: ToastType; message: string; onClose: () => void }

const icons = { success: CheckCircle, error: XCircle, warning: AlertCircle }
const styles: Record<ToastType, string> = {
  success: 'border-green-700 bg-green-900/80 text-green-100',
  error:   'border-red-700   bg-red-900/80   text-red-100',
  warning: 'border-yellow-700 bg-yellow-900/80 text-yellow-100',
}

export function Toast({ type, message, onClose }: ToastProps) {
  const Icon = icons[type]
  useEffect(() => { const t = setTimeout(onClose, 4000); return () => clearTimeout(t) }, [onClose])
  return (
    <div className={clsx('flex items-center gap-3 rounded-xl border px-4 py-3 shadow-xl animate-slide-in-up', styles[type])}>
      <Icon className="h-5 w-5 shrink-0" />
      <span className="text-sm font-medium">{message}</span>
      <button onClick={onClose} className="ml-auto p-1 hover:opacity-70"><X className="h-4 w-4"/></button>
    </div>
  )
}
