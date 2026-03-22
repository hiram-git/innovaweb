import { clsx } from 'clsx'
export function Spinner({ className }: { className?: string }) {
  return (
    <svg className={clsx('animate-spin text-blue-500', className ?? 'h-6 w-6')} viewBox="0 0 24 24" fill="none">
      <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" className="opacity-25"/>
      <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" className="opacity-75"/>
    </svg>
  )
}
