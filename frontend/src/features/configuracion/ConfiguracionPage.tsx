import { useState, useEffect } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Settings, Building2, Zap, Save } from 'lucide-react'
import { useForm, useWatch } from 'react-hook-form'
import { api } from '@/lib/axios'
import { Button } from '@/components/ui/Button'
import { Toast } from '@/components/ui/Toast'
import { Spinner } from '@/components/ui/Spinner'

// ─── Types ────────────────────────────────────────────────────────────────────

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
  FACELECT:               string        // '0' | '1'
  TIPO_FACTURA:           string        // 'PDF' | 'Ticket'
  AMBIENTE:               number | null // 1=Demo, 2=Producción
  PAC:                    number | null // 1=TFHKA, 2=EBI, 3=Digifact
  // The Factory HKA / EBI
  token_empresa:          string
  token_password:         string
  codigo_sucursal:        string
  punto_facturacion:      string
  num_doc_fiscal:         string  // readonly from BASEEMPRESA
  // Digifact
  usuario_digi:           string
  password_digi:          string
  codigo_sucursal_digi:   string
  punto_facturacion_digi: string
  ruc_digi:               string
  dv_digi:                string
  nombre_digi:            string
  email_digi:             string
  tel_digi:               string
  direccion_digi:         string
  coordenadas_digi:       string
  ubicacion_digi:         string
  juridico_digi:          number  // 0 | 1
  // Hidden / computed
  direccion_envio:        string
  tipo_emision:           string
  tipo_sucursal:          string
  naturaleza_operacion:   string
  tipo_operacion:         string
  destino_operacion:      string
  formato_cafe:           string
  entrega_cafe:           string
}

// ─── PAC URL helper ───────────────────────────────────────────────────────────

function getUrlForPacAmbiente(pac: number | null, ambiente: number | null): string {
  const demo = ambiente === 1
  if (pac === 1) {
    return demo
      ? 'https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl'
      : 'https://emision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl'
  }
  if (pac === 2) {
    return demo
      ? 'https://demointegracion.ebi-pac.com/ws/obj/v1.0/Service.svc?wsdl'
      : 'https://emision.ebi-pac.com/ws/obj/v1.0/Service.svc?wsdl'
  }
  // Digifact (3) or unknown
  return demo
    ? 'https://pactest.digifact.com.pa/pa.com.apinuc/api'
    : 'https://apinuc.digifact.com.pa/api'
}

// ─── Shared input class ───────────────────────────────────────────────────────

const INPUT = 'w-full rounded-lg border border-slate-700 bg-slate-800 py-2 px-3 text-sm text-white focus:border-orange-500 focus:outline-none'
const SELECT = INPUT
const LABEL  = 'mb-1 block text-xs text-slate-400'

// ─── Component ────────────────────────────────────────────────────────────────

export function ConfiguracionPage() {
  const qc = useQueryClient()
  const [tab, setTab]     = useState<'empresa' | 'fe'>('empresa')
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // ── Empresa ────────────────────────────────────────────────────────────────
  const { data: empresa, isLoading: loadEmpresa } = useQuery({
    queryKey: ['config-empresa'],
    queryFn:  () => api.get<EmpresaConfig>('/configuracion/empresa').then(r => r.data),
  })

  const empresaForm  = useForm<EmpresaConfig>({ values: empresa })
  const saveEmpresa  = useMutation({
    mutationFn: (d: EmpresaConfig) => api.put('/configuracion/empresa', d).then(r => r.data),
    onSuccess:  () => setToast({ type: 'success', message: 'Configuración de empresa guardada' }),
    onError:    () => setToast({ type: 'error',   message: 'Error al guardar' }),
  })

  // ── FE ─────────────────────────────────────────────────────────────────────
  const { data: feConfig, isLoading: loadFE } = useQuery({
    queryKey: ['config-fe'],
    queryFn:  () => api.get<FEConfig>('/configuracion/fe').then(r => r.data),
  })

  const feForm = useForm<FEConfig>({ values: feConfig ?? undefined })

  // Watch PAC + ambiente to auto-compute direccion_envio and toggle sections
  const pacVal     = useWatch({ control: feForm.control, name: 'PAC' })
  const ambienteVal = useWatch({ control: feForm.control, name: 'AMBIENTE' })

  useEffect(() => {
    feForm.setValue('direccion_envio', getUrlForPacAmbiente(Number(pacVal), Number(ambienteVal)))
  }, [pacVal, ambienteVal]) // eslint-disable-line react-hooks/exhaustive-deps

  const isDigifact  = Number(pacVal) === 3
  const isTfhkaEbi  = Number(pacVal) === 1 || Number(pacVal) === 2

  const saveFE = useMutation({
    mutationFn: (d: FEConfig) => api.put('/configuracion/fe', {
      facelect:               d.FACELECT === '1' || (d.FACELECT as unknown as boolean) === true,
      tipo_factura:           d.TIPO_FACTURA,
      pac:                    Number(d.PAC),
      ambiente:               Number(d.AMBIENTE),
      token_empresa:          d.token_empresa,
      token_password:         d.token_password,
      codigo_sucursal:        d.codigo_sucursal,
      punto_facturacion:      d.punto_facturacion,
      direccion_envio:        getUrlForPacAmbiente(Number(d.PAC), Number(d.AMBIENTE)),
      tipo_emision:           d.tipo_emision  || '01',
      tipo_sucursal:          d.tipo_sucursal || '',
      naturaleza_operacion:   d.naturaleza_operacion  || '01',
      tipo_operacion:         d.tipo_operacion  || '1',
      destino_operacion:      d.destino_operacion  || '1',
      formato_cafe:           d.formato_cafe  || '1',
      entrega_cafe:           d.entrega_cafe  || '1',
      usuario_digi:           d.usuario_digi,
      password_digi:          d.password_digi,
      codigo_sucursal_digi:   d.codigo_sucursal_digi,
      punto_facturacion_digi: d.punto_facturacion_digi,
      ruc_digi:               d.ruc_digi,
      dv_digi:                d.dv_digi,
      nombre_digi:            d.nombre_digi,
      email_digi:             d.email_digi,
      tel_digi:               d.tel_digi,
      direccion_digi:         d.direccion_digi,
      coordenadas_digi:       d.coordenadas_digi,
      ubicacion_digi:         d.ubicacion_digi,
      juridico_digi:          d.juridico_digi ? true : false,
    }).then(r => r.data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['config-fe'] })
      setToast({ type: 'success', message: 'Configuración FE guardada' })
    },
    onError: () => setToast({ type: 'error', message: 'Error al guardar' }),
  })

  // ── Render ─────────────────────────────────────────────────────────────────
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

      {/* ── Empresa ── */}
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
                <label className={LABEL}>{label}</label>
                <input {...empresaForm.register(field)} className={INPUT} />
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

      {/* ── FE ── */}
      {tab === 'fe' && (
        loadFE ? <div className="flex justify-center py-12"><Spinner /></div> : (
          <form onSubmit={feForm.handleSubmit(d => saveFE.mutate(d))}
            className="rounded-lg border border-slate-700 bg-slate-900 p-5 space-y-5">

            <h2 className="text-sm font-semibold text-slate-300 uppercase tracking-wide">
              Facturación Electrónica DGI
            </h2>

            {/* ─ Activación + Formato ─ */}
            <div className="flex flex-wrap gap-6 pb-4 border-b border-slate-700">
              <label className="flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                  {...feForm.register('FACELECT')}
                  checked={feForm.watch('FACELECT') === '1' || (feForm.watch('FACELECT') as unknown as boolean) === true}
                  onChange={e => feForm.setValue('FACELECT', e.target.checked ? '1' : '0')}
                  className="rounded border-slate-600 bg-slate-800 text-orange-500 focus:ring-orange-500" />
                <span className="text-sm text-slate-300">Facturación Electrónica activa</span>
              </label>

              <div>
                <span className="text-xs text-slate-400 mr-3">Formato</span>
                {(['PDF', 'Ticket'] as const).map(v => (
                  <label key={v} className="inline-flex items-center gap-1.5 mr-4 cursor-pointer">
                    <input type="radio" value={v}
                      {...feForm.register('TIPO_FACTURA', { required: true })}
                      className="text-orange-500 focus:ring-orange-500" />
                    <span className="text-sm text-slate-300">{v}</span>
                  </label>
                ))}
              </div>
            </div>

            {/* ─ PAC + Ambiente ─ */}
            <div className="grid grid-cols-2 gap-4 pb-4 border-b border-slate-700">
              <div>
                <label className={LABEL}>PAC</label>
                <select {...feForm.register('PAC', { required: true, valueAsNumber: true })} className={SELECT}>
                  <option value="">Seleccione…</option>
                  <option value={1}>The Factory HKA</option>
                  <option value={2}>EBI</option>
                  <option value={3}>Digifact</option>
                </select>
              </div>
              <div>
                <label className={LABEL}>Ambiente</label>
                <select {...feForm.register('AMBIENTE', { required: true, valueAsNumber: true })} className={SELECT}>
                  <option value="">Seleccione…</option>
                  <option value={1}>Demo / Pruebas</option>
                  <option value={2}>Producción</option>
                </select>
              </div>
            </div>

            {/* ─ Num. Doc. Fiscal (readonly, from BASEEMPRESA) ─ */}
            {feConfig?.num_doc_fiscal && (
              <div>
                <label className={LABEL}>Número de Documento Fiscal inicial</label>
                <input readOnly value={feConfig.num_doc_fiscal} className={INPUT + ' opacity-60 cursor-not-allowed'} />
              </div>
            )}

            {/* ─ The Factory HKA / EBI section ─ */}
            {isTfhkaEbi && (
              <div className="space-y-4 pb-4 border-b border-slate-700">
                <h3 className="text-xs font-semibold text-slate-400 uppercase tracking-wide">
                  {Number(pacVal) === 1 ? 'The Factory HKA' : 'EBI'}
                </h3>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Token Empresa</label>
                    <input {...feForm.register('token_empresa')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Token Password</label>
                    <input type="password" {...feForm.register('token_password')}
                      placeholder="(sin cambios)" className={INPUT} autoComplete="new-password" />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Código de Sucursal</label>
                    <input {...feForm.register('codigo_sucursal')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Punto de Facturación Fiscal</label>
                    <input {...feForm.register('punto_facturacion')} className={INPUT} />
                  </div>
                </div>
              </div>
            )}

            {/* ─ Digifact section ─ */}
            {isDigifact && (
              <div className="space-y-4 pb-4 border-b border-slate-700">
                <h3 className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Digifact</h3>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Usuario</label>
                    <input {...feForm.register('usuario_digi')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Password</label>
                    <input type="password" {...feForm.register('password_digi')}
                      placeholder="(sin cambios)" className={INPUT} autoComplete="new-password" />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Código de Sucursal</label>
                    <input {...feForm.register('codigo_sucursal_digi')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Punto de Facturación Fiscal</label>
                    <input {...feForm.register('punto_facturacion_digi')} className={INPUT} />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>RUC</label>
                    <input {...feForm.register('ruc_digi')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>DV</label>
                    <input {...feForm.register('dv_digi')} maxLength={5} className={INPUT} />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Nombre Fiscal</label>
                    <input {...feForm.register('nombre_digi')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Email</label>
                    <input type="email" {...feForm.register('email_digi')} className={INPUT} />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Teléfono</label>
                    <input {...feForm.register('tel_digi')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Dirección</label>
                    <input {...feForm.register('direccion_digi')} className={INPUT} />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className={LABEL}>Coordenadas</label>
                    <input {...feForm.register('coordenadas_digi')} className={INPUT} />
                  </div>
                  <div>
                    <label className={LABEL}>Ubicación</label>
                    <input {...feForm.register('ubicacion_digi')} className={INPUT} />
                  </div>
                </div>

                <label className="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox"
                    checked={Number(feForm.watch('juridico_digi')) === 1}
                    onChange={e => feForm.setValue('juridico_digi', e.target.checked ? 1 : 0)}
                    className="rounded border-slate-600 bg-slate-800 text-orange-500 focus:ring-orange-500" />
                  <span className="text-sm text-slate-300">Es persona jurídica</span>
                </label>
              </div>
            )}

            {!pacVal && (
              <p className="text-xs text-slate-500 italic">Seleccione un PAC para ver su configuración.</p>
            )}

            <div className="flex justify-end">
              <Button type="submit" loading={saveFE.isPending}
                disabled={!feForm.watch('PAC') || !feForm.watch('AMBIENTE') || !feForm.watch('TIPO_FACTURA')}>
                <Save className="h-4 w-4 mr-1" /> Guardar
              </Button>
            </div>
          </form>
        )
      )}
    </div>
  )
}
