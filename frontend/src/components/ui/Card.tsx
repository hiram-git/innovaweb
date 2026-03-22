import { clsx } from 'clsx'
interface Props { children: React.ReactNode; className?: string; title?: string }
export function Card({ children, className, title }: Props) {
  return (
    <div className={clsx('rounded-xl border border-slate-700 bg-slate-800/50 p-4', className)}>
      {title && <h3 className="mb-3 text-sm font-semibold text-slate-300 uppercase tracking-wide">{title}</h3>}
      {children}
    </div>
  )
}
