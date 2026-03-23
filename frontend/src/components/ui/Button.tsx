import { forwardRef, type ButtonHTMLAttributes } from 'react'
import { clsx } from 'clsx'

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger'
type Size    = 'sm' | 'md' | 'lg'

interface Props extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: Variant
  size?: Size
  loading?: boolean
}

const styles: Record<Variant, string> = {
  primary:   'bg-orange-600 hover:bg-orange-700 text-white shadow-sm',
  secondary: 'bg-slate-700 hover:bg-slate-600 text-slate-100',
  ghost:     'hover:bg-slate-800 text-slate-300',
  danger:    'bg-red-600 hover:bg-red-700 text-white',
}
const sizes: Record<Size, string> = {
  sm: 'px-2.5 py-1.5 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-6 py-3 text-base',
}

export const Button = forwardRef<HTMLButtonElement, Props>(
  ({ variant = 'primary', size = 'md', loading, className, children, disabled, ...props }, ref) => (
    <button
      ref={ref}
      disabled={disabled || loading}
      className={clsx(
        'inline-flex items-center justify-center gap-2 rounded-lg font-medium',
        'transition-colors focus-visible:outline focus-visible:outline-2',
        'focus-visible:outline-orange-500 disabled:opacity-50 disabled:cursor-not-allowed',
        styles[variant], sizes[size], className
      )}
      {...props}
    >
      {loading && (
        <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" className="opacity-25"/>
          <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" className="opacity-75"/>
        </svg>
      )}
      {children}
    </button>
  )
)
Button.displayName = 'Button'
