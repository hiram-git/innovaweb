import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Building2, Lock, User } from 'lucide-react'
import api from '@/lib/axios'
import { useAuthStore } from '@/stores/authStore'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import type { AuthUser } from '@/types'

const schema = z.object({
  usuario:  z.string().min(1, 'Ingresa tu usuario'),
  password: z.string().min(1, 'Ingresa tu contraseña'),
})
type FormData = z.infer<typeof schema>

export function LoginPage() {
  const navigate = useNavigate()
  const { setAuth } = useAuthStore()
  const [apiError, setApiError] = useState<string | null>(null)

  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<FormData>({
    resolver: zodResolver(schema),
  })

  const onSubmit = async (data: FormData) => {
    setApiError(null)
    try {
      const res = await api.post<{ token: string; expires_at: string; usuario: AuthUser }>('/login', data)
      setAuth(res.data.token, res.data.usuario as unknown as AuthUser, res.data.expires_at)
      navigate('/', { replace: true })
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
      const msgs = err.response?.data?.errors
      if (msgs) {
        setApiError(Object.values(msgs).flat().join(' '))
      } else {
        setApiError(err.response?.data?.message ?? 'Error al iniciar sesión')
      }
    }
  }

  return (
    <div className="flex min-h-dvh items-center justify-center bg-slate-950 px-4">
      <div className="w-full max-w-sm">
        {/* Logo */}
        <div className="mb-8 flex flex-col items-center gap-3">
          <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-700 shadow-lg">
            <Building2 className="h-8 w-8 text-white" />
          </div>
          <div className="text-center">
            <h1 className="text-2xl font-bold text-white">InnovaWeb</h1>
            <p className="text-sm text-slate-400">Facturación Electrónica · Panamá</p>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <div className="rounded-xl border border-slate-700 bg-slate-800/60 p-6 shadow-xl">
            <h2 className="mb-5 text-base font-semibold text-slate-200">Iniciar sesión</h2>

            <div className="space-y-4">
              <div className="relative">
                <User className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input
                  {...register('usuario')}
                  placeholder="Usuario (ej: ADMIN)"
                  autoComplete="username"
                  className="w-full rounded-lg border border-slate-600 bg-slate-900 pl-10 pr-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.usuario && <p className="mt-1 text-xs text-red-400">{errors.usuario.message}</p>}
              </div>

              <div className="relative">
                <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input
                  {...register('password')}
                  type="password"
                  placeholder="Contraseña"
                  autoComplete="current-password"
                  className="w-full rounded-lg border border-slate-600 bg-slate-900 pl-10 pr-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.password && <p className="mt-1 text-xs text-red-400">{errors.password.message}</p>}
              </div>
            </div>

            {apiError && (
              <div className="mt-4 rounded-lg bg-red-900/50 border border-red-700 p-3 text-sm text-red-300">
                {apiError}
              </div>
            )}

            <Button type="submit" loading={isSubmitting} className="mt-5 w-full" size="lg">
              Ingresar
            </Button>
          </div>
        </form>

        <p className="mt-6 text-center text-xs text-slate-500">
          InnovaWeb v2.0 · DGI Panamá Compliance
        </p>
      </div>
    </div>
  )
}
