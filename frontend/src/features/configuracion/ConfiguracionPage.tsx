import { useState } from 'react'
import { useQuery, useMutation } from '@tanstack/react-query'
import { Settings, Building2, Zap, Save } from 'lucide-react'
import { useForm } from 'react-hook-form'
import { api } from '@/lib/axios'
import { Button } from '@/components/ui/Button'
import { Toast } from '@/components/ui/Toast'
import { Spinner } from '@/components/ui/Spinner'

interface EmpresaConfig {
  RAZONSOCIAL: string
  RUC:         string
  DV:          string
  DIRECCION:   string
  TEL:         string
  EMAIL:       string
  LOGO_URL:    string | null
}

interface FEConfig {
  ambiente:    'sandbox' | 'produccion'
  pac:         'TFHKA' | 'DIGIFACT'
  ruc_emisor:  string
  dv_emisor:   string
}

export function ConfiguracionPage() {
  const [tab, setTab]       = useState<'empresa' | 'fe'>('empresa')
  const [toast, setToast]   = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  const { data: empresa, isLoading: loadEmpresa } = useQuery({
    queryKey: ['config-empresa'],
    queryFn: () => api.get<EmpresaConfig>('/configuracion/empresa').then(r => r.data),
  })

  const { data: feConfig, isLoading: loadFE } = useQuery({
    queryKey: ['config-fe'],
    queryFn: () => api.get<FEConfig>('/configuracion/fe').then(r => r.data),
  })

  const empresaForm = useForm<EmpresaConfig>({ values: empresa })
  const feForm      = useForm<FEConfig>({ values: feConfig })

  const saveEmpresa = useMutation({
    mutationFn: (d: EmpresaConfig) => api.put('/configuracion/empresa', d).then(r => r.data),
    onSuccess: () => setToast({ type: 'success', message: 'Configuración de empresa guardada' }),
    onError: () => setToast({ type: 'error', message: 'Error al guardar' }),
  })

  const saveFE = useMutation({
    mutationFn: (d: FEConfig) => api.put('/configuracion/fe', d).then(r => r.data),
    onSuccess: () => setToast({ type: 'success', message: 'Configuración FE guardada' }),
    onError: () => setToast({ type: 'error', message: 'Error al guardar' }),
  })

  return (
    <div className="max-w-2xl space-y-6">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      <div className="flex items-center gap-3">
        <Settings className="h-6 w-6 text-slate-400" />
        <h1 className="text-xl font-bold text-white">Configuración</h1>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 rounded-lg bg-slate-800 p-1 w-fit">
        {([['empresa', Building2, 'Empresa'], ['fe', Zap, 'FE / DGI']] as const).map(([key, Icon, label]) => (
          <button key={key} onClick={() => setTab(key)}
            className={`flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors
              ${tab === key ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white'}`}>
            <Icon className="h-4 w-4" />{label}
          </button>
        ))}
      </div>

      {/* Empresa */}
      {tab === 'empresa' && (
        loadEmpresa ? <div className="flex justify-center py-12"><Spinner /></div> : (
          <form onSubmit={empresaForm.handleSubmit(d => saveEmpresa.mutate(d))}
            className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
            <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Datos de la empresa</h2>
            {([
              ['RAZONSOCIAL', 'Razón Social'],
              ['RUC',         'RUC'],
              ['DV',          'DV'],
              ['DIRECCION',   'Dirección'],
              ['TEL',         'Teléfono'],
              ['EMAIL',       'Email'],
            ] as const).map(([field, label]) => (
              <div key={field}>
                <label className="mb-1 block text-xs text-slate-400">{label}</label>
                <input {...empresaForm.register(field)}
                  className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none" />
              </div>
            ))}
            <div className="flex justify-end">
              <Button type="submit" loading={saveEmpresa.isPending}>
                <Save className="h-4 w-4 mr-1" /> Guardar
              </Button>
            </div>
          </form>
        )
      )}

      {/* FE */}
      {tab === 'fe' && (
        loadFE ? <div className="flex justify-center py-12"><Spinner /></div> : (
          <form onSubmit={feForm.handleSubmit(d => saveFE.mutate(d))}
            className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-4">
            <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">Facturación Electrónica DGI</h2>

            <div>
              <label className="mb-1 block text-xs text-slate-400">Ambiente</label>
              <select {...feForm.register('ambiente')}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none">
                <option value="sandbox">Pruebas (Sandbox)</option>
                <option value="produccion">Producción</option>
              </select>
            </div>

            <div>
              <label className="mb-1 block text-xs text-slate-400">Proveedor PAC</label>
              <select {...feForm.register('pac')}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none">
                <option value="TFHKA">The Factory HKA</option>
                <option value="DIGIFACT">Digifact</option>
              </select>
            </div>

            <div>
              <label className="mb-1 block text-xs text-slate-400">RUC Emisor</label>
              <input {...feForm.register('ruc_emisor')}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none" />
            </div>

            <div>
              <label className="mb-1 block text-xs text-slate-400">DV Emisor</label>
              <input {...feForm.register('dv_emisor')} maxLength={2}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-blue-500 focus:outline-none" />
            </div>

            <div className="rounded-lg bg-blue-900/20 border border-blue-800/40 p-3 text-xs text-blue-400">
              Las credenciales WSDL/API del PAC se configuran en el archivo <code className="font-mono">.env</code> del servidor.
            </div>

            <div className="flex justify-end">
              <Button type="submit" loading={saveFE.isPending}>
                <Save className="h-4 w-4 mr-1" /> Guardar
              </Button>
            </div>
          </form>
        )
      )}
    </div>
  )
}
