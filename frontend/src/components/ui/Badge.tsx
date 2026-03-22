import { clsx } from 'clsx'
type Color = 'green' | 'red' | 'yellow' | 'blue' | 'gray'
const colors: Record<Color, string> = {
  green:  'bg-green-900/50 text-green-300 ring-green-800/30',
  red:    'bg-red-900/50   text-red-300   ring-red-800/30',
  yellow: 'bg-yellow-900/50 text-yellow-300 ring-yellow-800/30',
  blue:   'bg-blue-900/50  text-blue-300  ring-blue-800/30',
  gray:   'bg-slate-700    text-slate-300  ring-slate-600/30',
}
export function Badge({ color = 'gray', children }: { color?: Color; children: React.ReactNode }) {
  return <span className={clsx('inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1', colors[color])}>{children}</span>
}
