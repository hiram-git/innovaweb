import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Database, Server, User, Lock, Clock, CheckCircle } from 'lucide-react'
import { api } from '@/lib/axios'
import { Button } from '@/components/ui/Button'

// ─── Timezones (same list as legacy con_register.php) ─────────────────────────

const TIMEZONES = [
  'America/Bogota',
  'America/Caracas',
  'America/Costa_Rica',
  'America/El_Salvador',
  'America/Guatemala',
  'America/Havana',
  'America/La_Paz',
  'America/Lima',
  'America/Mexico_City',
  'America/Montevideo',
  'America/New_York',
  'America/Panama',
  'America/Puerto_Rico',
  'America/Santo_Domingo',
  'America/Santiago',
  'America/Sao_Paulo',
]

// ─── Types ────────────────────────────────────────────────────────────────────

interface SetupForm {
  servidor: string
  dbname:   string
  usuario:  string
  password: string
  timezone: string
}

interface SetupPageProps {
  onComplete: () => void
}

// ─── Component ────────────────────────────────────────────────────────────────

export function SetupPage({ onComplete }: SetupPageProps) {
  const [error, setError]     = useState<string | null>(null)
  const [success, setSuccess] = useState(false)

  const {
    register,
    handleSubmit,
    formState: { isSubmitting, errors },
  } = useForm<SetupForm>({
    defaultValues: { timezone: 'America/Panama' },
  })

  const onSubmit = async (data: SetupForm) => {
    setError(null)
    try {
      await api.post('/setup', data)
      setSuccess(true)
      // Give the server a moment to write .env, then reload
      setTimeout(onComplete, 2500)
    } catch (e: unknown) {
      const msg = (e as { response?: { data?: { message?: string } } })
        ?.response?.data?.message
      setError(msg ?? 'Error al guardar la configuración')
    }
  }

  const INPUT = 'w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white placeholder-slate-500 focus:border-orange-500 focus:outline-none'
  const LABEL = 'mb-1 flex items-center gap-1.5 text-xs text-slate-400'

  // ── Success screen ────────────────────────────────────────────────────────
  if (success) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950">
        <div className="text-center space-y-4 animate-pulse">
          <CheckCircle className="h-16 w-16 text-green-400 mx-auto" />
          <h2 className="text-xl font-bold text-white">¡Configuración exitosa!</h2>
          <p className="text-slate-400 text-sm">Iniciando la aplicación…</p>
        </div>
      </div>
    )
  }

  // ── Form ─────────────────────────────────────────────────────────────────
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-950 p-4">
      <div className="w-full max-w-md space-y-6">

        {/* Header */}
        <div className="text-center space-y-1">
          <h1 className="text-2xl font-bold text-white">Configuración inicial</h1>
          <p className="text-slate-400 text-sm">
            Establece la conexión con la base de datos del ERP Clarion
          </p>
        </div>

        {/* Form card */}
        <form onSubmit={handleSubmit(onSubmit)}
          className="rounded-lg border border-slate-700 bg-slate-900 p-6 space-y-4">

          {/* Error banner */}
          {error && (
            <div className="rounded-lg bg-red-900/30 border border-red-700/60 p-3 text-sm text-red-400">
              {error}
            </div>
          )}

          {/* Servidor */}
          <div>
            <label className={LABEL}>
              <Server className="h-3.5 w-3.5" />
              Servidor de base de datos
            </label>
            <input
              {...register('servidor', { required: 'Campo requerido' })}
              placeholder="DESKTOP-XXXX\SQLEXPRESS"
              className={INPUT}
            />
            {errors.servidor && (
              <p className="mt-1 text-xs text-red-400">{errors.servidor.message}</p>
            )}
          </div>

          {/* DB name */}
          <div>
            <label className={LABEL}>
              <Database className="h-3.5 w-3.5" />
              Nombre de la base de datos
            </label>
            <input
              {...register('dbname', { required: 'Campo requerido' })}
              placeholder="PRUEBAS"
              className={INPUT}
            />
            {errors.dbname && (
              <p className="mt-1 text-xs text-red-400">{errors.dbname.message}</p>
            )}
          </div>

          {/* Usuario */}
          <div>
            <label className={LABEL}>
              <User className="h-3.5 w-3.5" />
              Usuario
            </label>
            <input
              {...register('usuario', { required: 'Campo requerido' })}
              className={INPUT}
            />
            {errors.usuario && (
              <p className="mt-1 text-xs text-red-400">{errors.usuario.message}</p>
            )}
          </div>

          {/* Password */}
          <div>
            <label className={LABEL}>
              <Lock className="h-3.5 w-3.5" />
              Contraseña
            </label>
            <input
              type="password"
              {...register('password', { required: 'Campo requerido' })}
              className={INPUT}
              autoComplete="new-password"
            />
            {errors.password && (
              <p className="mt-1 text-xs text-red-400">{errors.password.message}</p>
            )}
          </div>

          {/* Timezone */}
          <div>
            <label className={LABEL}>
              <Clock className="h-3.5 w-3.5" />
              Zona horaria
            </label>
            <select
              {...register('timezone', { required: 'Campo requerido' })}
              className={INPUT}
            >
              {TIMEZONES.map(tz => (
                <option key={tz} value={tz}>{tz}</option>
              ))}
            </select>
          </div>

          <Button type="submit" loading={isSubmitting} className="w-full mt-2">
            Conectar y guardar
          </Button>
        </form>
      </div>
    </div>
  )
}
