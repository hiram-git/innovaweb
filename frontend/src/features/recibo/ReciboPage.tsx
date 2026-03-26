/**
 * ReciboPage — post-document preview and print page.
 *
 * Shown after successfully creating a Factura, Presupuesto or Pedido.
 * Fetches all receipt data from the API and:
 *  - Renders a screen preview of the document.
 *  - Provides an "Imprimir" button that renders a print-optimized layout.
 *  - For Facturas with FE active: shows FE status and "Enviar FE" button.
 *  - If FE was accepted and a DGI PDF exists: "Ver PDF DGI" button.
 */
import { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { useParams, useNavigate } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Printer, ChevronLeft, Send, FileText, Plus,
  CheckCircle, AlertTriangle, Clock, XCircle,
} from 'lucide-react'
import { api } from '@/lib/axios'
import { Button } from '@/components/ui/Button'
import { Toast } from '@/components/ui/Toast'

// ─── Types ────────────────────────────────────────────────────────────────────

interface ReciboData {
  empresa: {
    NOMBRE: string
    NUMFISCAL: string
    DIRECC1: string
    DIRECC2: string
    NUMTEL: string
  }
  maestro: {
    CONTROL:     string
    TIPTRAN:     string
    NUMREF:      string
    NOMBRE:      string
    FECEMISS:    string
    TIPOFACTURA: string
    MONTOBRU:    number
    MONTODES:    number
    MONTOIMP:    number
    MONTOTOT:    number
    CAMBIO:      number
  }
  cliente: {
    NOMBRE:   string
    RIF:      string
    DIRECC1:  string
  } | null
  vendedor: { NOMBRE: string } | null
  detalles: Array<{
    CODPRO:          string
    DESCRIP1:        string
    CANTIDAD:        number
    PRECOSUNI:       number
    MONTODESCUENTO:  number
    IMPPOR:          number
    MONTOIMP:        number
  }>
  pagos: Array<{
    CODTAR:           string
    DESCRINSTRUMENTO: string
    MONTOPAG:         number
  }>
  documento: {
    CUFE:                     string
    QR:                       string
    RESULTADO:                string
    NROPROTOCOLOAYTORIZACION: string
    FECHARECEPCIONDGI:        string | null
    NUMDOCFISCAL:             string
    tiene_pdf:                number
  } | null
  config: {
    facelect:     boolean
    tipo_factura: 'PDF' | 'Ticket'
  }
}

type Tipo = 'factura' | 'presupuesto' | 'pedido'

// ─── Helpers ──────────────────────────────────────────────────────────────────

const TIPO_LABELS: Record<Tipo, string> = {
  factura:     'Factura',
  presupuesto: 'Presupuesto',
  pedido:      'Pedido',
}

const TIPO_ENDPOINTS: Record<Tipo, string> = {
  factura:     'facturas',
  presupuesto: 'presupuestos',
  pedido:      'pedidos',
}

const TIPO_NEW_PATHS: Record<Tipo, string> = {
  factura:     '/facturas/nueva',
  presupuesto: '/presupuestos/nuevo',
  pedido:      '/pedidos/nuevo',
}

const TIPO_LIST_PATHS: Record<Tipo, string> = {
  factura:     '/facturas',
  presupuesto: '/presupuestos',
  pedido:      '/pedidos',
}

function fmtDate(ymd: string | number | null): string {
  if (!ymd) return '—'
  const s = String(ymd).replace(/-/g, '').slice(0, 8)
  if (s.length !== 8) return String(ymd)
  return `${s.slice(6, 8)}/${s.slice(4, 6)}/${s.slice(0, 4)}`
}

function fmtMoney(n: number | null | undefined): string {
  return `$${Number(n ?? 0).toFixed(2)}`
}

// ─── FE status badge ──────────────────────────────────────────────────────────

function FEBadge({ resultado }: { resultado: string }) {
  if (!resultado) return null
  const map: Record<string, { label: string; cls: string; Icon: typeof CheckCircle }> = {
    ACEPTADO:  { label: 'Aceptado DGI',  cls: 'bg-green-900/30 text-green-300 border-green-700/40',  Icon: CheckCircle },
    ENVIADO:   { label: 'Enviado',        cls: 'bg-blue-900/30  text-blue-300  border-blue-700/40',   Icon: Clock },
    PENDIENTE: { label: 'Pendiente',      cls: 'bg-yellow-900/30 text-yellow-300 border-yellow-700/40', Icon: Clock },
    RECHAZADO: { label: 'Rechazado DGI', cls: 'bg-red-900/30   text-red-300   border-red-700/40',    Icon: XCircle },
  }
  const cfg = map[resultado] ?? { label: resultado, cls: 'bg-slate-800 text-slate-300 border-slate-700', Icon: AlertTriangle }
  return (
    <span className={`inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded border ${cfg.cls}`}>
      <cfg.Icon className="h-3 w-3" />{cfg.label}
    </span>
  )
}

// ─── Receipt content (used for both screen and print) ─────────────────────────

function ReciboContent({ d, compact = false }: { d: ReciboData; compact?: boolean }) {
  const isTicket = d.config.tipo_factura === 'Ticket'
  const tipoLabel =
    d.maestro.TIPTRAN === 'FAC' ? 'FACTURA' :
    d.maestro.TIPTRAN === 'PRE' ? 'PRESUPUESTO' : 'PEDIDO'

  const width = isTicket
    ? (compact ? 'w-full max-w-[302px]' : 'w-full max-w-sm')
    : 'w-full max-w-2xl'

  return (
    <div className={`${width} mx-auto font-mono text-xs text-black bg-white`}
         style={{ fontFamily: 'Courier New, monospace' }}>

      {/* Header empresa */}
      <div className="text-center py-2 border-b border-dashed border-gray-400">
        <p className="font-bold text-sm">{d.empresa.NOMBRE}</p>
        <p>RUC: {d.empresa.NUMFISCAL}</p>
        <p>{[d.empresa.DIRECC1, d.empresa.DIRECC2].filter(Boolean).join(' ')}</p>
        {d.empresa.NUMTEL && <p>Tel: {d.empresa.NUMTEL}</p>}
      </div>

      {/* Tipo + número */}
      <div className="text-center py-2 border-b border-dashed border-gray-400">
        <p className="font-bold">{tipoLabel}</p>
        <p>Nro: {d.maestro.NUMREF?.trim()}</p>
        <p>Fecha: {fmtDate(d.maestro.FECEMISS)}</p>
      </div>

      {/* Cliente */}
      <div className="py-2 border-b border-dashed border-gray-400">
        <p>Cliente: {d.cliente?.NOMBRE ?? d.maestro.NOMBRE}</p>
        {d.cliente?.RIF && <p>RUC: {d.cliente.RIF}</p>}
        {d.cliente?.DIRECC1 && <p className="truncate">Dir: {d.cliente.DIRECC1}</p>}
        {d.vendedor?.NOMBRE && <p>Vendedor: {d.vendedor.NOMBRE}</p>}
        {d.maestro.TIPOFACTURA && <p>Tipo: {d.maestro.TIPOFACTURA}</p>}
      </div>

      {/* Detalle */}
      <div className="py-2 border-b border-dashed border-gray-400">
        <div className="flex justify-between font-bold border-b border-gray-300 pb-1 mb-1">
          <span className="flex-1">DESCRIPCIÓN</span>
          <span className="w-10 text-right">CANT</span>
          <span className="w-16 text-right">TOTAL</span>
        </div>
        {d.detalles.map((det, i) => {
          const sub = Number(det.CANTIDAD) * Number(det.PRECOSUNI) - Number(det.MONTODESCUENTO)
          return (
            <div key={i} className="mb-1">
              <div className="flex justify-between">
                <span className="flex-1 truncate pr-1">{det.CODPRO} {det.DESCRIP1}</span>
                <span className="w-10 text-right shrink-0">{Number(det.CANTIDAD).toFixed(2)}</span>
                <span className="w-16 text-right shrink-0">{fmtMoney(sub)}</span>
              </div>
              <div className="pl-2 text-gray-500">
                {Number(det.CANTIDAD).toFixed(2)} x {fmtMoney(det.PRECOSUNI)}
                {Number(det.MONTODESCUENTO) > 0 && ` Dcto: ${fmtMoney(det.MONTODESCUENTO)}`}
                {` ITBMS ${det.IMPPOR}%`}
              </div>
            </div>
          )
        })}
      </div>

      {/* Totales */}
      <div className="py-2 border-b border-dashed border-gray-400 space-y-0.5">
        <div className="flex justify-between">
          <span>Subtotal</span><span>{fmtMoney(d.maestro.MONTOBRU)}</span>
        </div>
        {Number(d.maestro.MONTODES) > 0 && (
          <div className="flex justify-between">
            <span>Descuento</span><span>-{fmtMoney(d.maestro.MONTODES)}</span>
          </div>
        )}
        <div className="flex justify-between">
          <span>ITBMS</span><span>{fmtMoney(d.maestro.MONTOIMP)}</span>
        </div>
        <div className="flex justify-between font-bold text-sm">
          <span>TOTAL</span><span>{fmtMoney(d.maestro.MONTOTOT)}</span>
        </div>
      </div>

      {/* Pagos */}
      {d.pagos.length > 0 && (
        <div className="py-2 border-b border-dashed border-gray-400 space-y-0.5">
          <p className="font-bold">FORMAS DE PAGO</p>
          {d.pagos.map((p, i) => (
            <div key={i} className="flex justify-between">
              <span>{p.DESCRINSTRUMENTO}</span>
              <span>{fmtMoney(p.MONTOPAG)}</span>
            </div>
          ))}
          {Number(d.maestro.CAMBIO) > 0 && (
            <div className="flex justify-between font-bold">
              <span>CAMBIO</span><span>{fmtMoney(d.maestro.CAMBIO)}</span>
            </div>
          )}
        </div>
      )}

      {/* FE info */}
      {d.documento?.CUFE && (
        <div className="py-2 border-b border-dashed border-gray-400 text-center space-y-1">
          <p className="font-bold text-xs">COMPROBANTE AUXILIAR DE FACTURA ELECTRÓNICA</p>
          {d.documento.FECHARECEPCIONDGI && (
            <p>CAFE emitido el: {d.documento.FECHARECEPCIONDGI}</p>
          )}
          <p className="break-all text-xs">Para verificar consulte:</p>
          <p className="text-xs">https://fe.dgi.mef.gob.pa/consulta</p>
          <p className="font-bold break-all">{d.documento.NROPROTOCOLOAYTORIZACION}</p>
          {d.documento.QR && (
            <p className="text-xs text-gray-500 break-all">{d.documento.QR}</p>
          )}
        </div>
      )}

      {/* Footer */}
      <div className="py-2 text-center">
        <p>¡Gracias por su compra!</p>
        <p className="text-gray-500">Copia de cliente</p>
      </div>
    </div>
  )
}

// ─── Print portal ─────────────────────────────────────────────────────────────

function PrintPortal({ d }: { d: ReciboData }) {
  useEffect(() => {
    const style = document.createElement('style')
    style.id = 'recibo-print-style'
    style.textContent = `
      @media print {
        #root { display: none !important; }
        #recibo-print-portal { display: block !important; padding: 8px; }
      }
    `
    document.head.appendChild(style)
    return () => { document.getElementById('recibo-print-style')?.remove() }
  }, [])

  return createPortal(
    <div id="recibo-print-portal" style={{ display: 'none' }}>
      <ReciboContent d={d} compact />
    </div>,
    document.body
  )
}

// ─── Main page ────────────────────────────────────────────────────────────────

interface ReciboPageProps {
  tipo: Tipo
}

export function ReciboPage({ tipo }: ReciboPageProps) {
  const { id }      = useParams<{ id: string }>()
  const navigate    = useNavigate()
  const qc          = useQueryClient()
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const printRef    = useRef<HTMLDivElement>(null)

  const endpoint = `/${TIPO_ENDPOINTS[tipo]}/${id}/recibo`
  const label    = TIPO_LABELS[tipo]

  const { data, isLoading, isError, refetch } = useQuery({
    queryKey: ['recibo', tipo, id],
    queryFn:  () => api.get<{ data: ReciboData }>(endpoint).then(r => r.data.data),
    staleTime: 0,
    retry: false,
  })

  // FE mutation (only for facturas)
  const feMutation = useMutation({
    mutationFn: () =>
      api.post<{ estado: number; mensaje: string; cufe?: string }>(
        `/facturacion-electronica/enviar/${id}`
      ).then(r => r.data),
    onSuccess: (res) => {
      if (res.estado === 1 || res.cufe) {
        setToast({ type: 'success', message: `FE enviada exitosamente. CUFE: ${res.cufe ?? '—'}` })
        void refetch()
        qc.invalidateQueries({ queryKey: ['facturas'] })
      } else {
        setToast({ type: 'error', message: res.mensaje ?? 'Error al enviar FE' })
      }
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Error al enviar FE'
      setToast({ type: 'error', message: msg })
    },
  })

  if (isLoading) {
    return (
      <div className="flex justify-center py-20">
        <div className="h-8 w-8 animate-spin rounded-full border-2 border-slate-600 border-t-orange-500" />
      </div>
    )
  }

  if (isError || !data) {
    return (
      <div className="rounded-lg border border-red-700 bg-red-900/20 p-6 text-center text-red-300">
        <AlertTriangle className="mx-auto h-8 w-8 mb-2" />
        <p>No se pudo cargar el recibo.</p>
        <Button variant="secondary" className="mt-4" onClick={() => navigate(TIPO_LIST_PATHS[tipo])}>
          Ir a la lista
        </Button>
      </div>
    )
  }

  const hasCufe   = Boolean(data.documento?.CUFE)
  const hasDgiPdf = Boolean(data.documento?.tiene_pdf)
  const feEstado  = data.documento?.RESULTADO ?? ''
  const showFEBtn = tipo === 'factura' && data.config.facelect && !hasCufe

  const handlePrint = () => window.print()

  const handleVerPdf = async () => {
    try {
      const res = await api.get<{ pdf: string; numdocfiscal: string }>(`/facturas/${id}/pdf`)
      if (res.data.pdf) {
        const byteCharacters = atob(res.data.pdf)
        const byteNumbers = Array.from(byteCharacters).map(c => c.charCodeAt(0))
        const blob = new Blob([new Uint8Array(byteNumbers)], { type: 'application/pdf' })
        const url = URL.createObjectURL(blob)
        window.open(url, '_blank')
      }
    } catch {
      setToast({ type: 'error', message: 'No se pudo obtener el PDF de la DGI.' })
    }
  }

  return (
    <div className="max-w-3xl space-y-5">
      {toast && <Toast type={toast.type} message={toast.message} onClose={() => setToast(null)} />}

      {/* Header */}
      <div className="flex items-center gap-3">
        <button
          onClick={() => navigate(TIPO_LIST_PATHS[tipo])}
          className="text-slate-400 hover:text-white transition-colors"
        >
          <ChevronLeft className="h-5 w-5" />
        </button>
        <div className="flex-1">
          <h1 className="text-xl font-bold text-white">{label} Creado</h1>
          <p className="text-sm text-slate-400">Nro. {data.maestro.NUMREF?.trim()}</p>
        </div>
        <CheckCircle className="h-6 w-6 text-green-400" />
      </div>

      {/* FE status banner */}
      {data.config.facelect && (
        <div className={`rounded-lg border px-4 py-3 flex items-center gap-3
          ${hasCufe ? 'border-green-700/40 bg-green-900/20' : 'border-yellow-700/40 bg-yellow-900/20'}`}>
          <div className="flex-1 text-sm">
            <p className="font-medium text-white">Facturación Electrónica</p>
            <div className="flex items-center gap-2 mt-0.5">
              <FEBadge resultado={feEstado} />
              {hasCufe && (
                <span className="text-xs text-slate-400 font-mono truncate max-w-[200px]">
                  {data.documento?.CUFE}
                </span>
              )}
              {!hasCufe && (
                <span className="text-xs text-yellow-300">Pendiente de envío a DGI</span>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Summary card */}
      <div className="rounded-lg border border-slate-700 bg-slate-900 p-4 grid grid-cols-2 gap-3 sm:grid-cols-4 text-sm">
        <div>
          <p className="text-xs text-slate-400">Cliente</p>
          <p className="text-white font-medium truncate">{data.maestro.NOMBRE}</p>
        </div>
        <div>
          <p className="text-xs text-slate-400">Fecha</p>
          <p className="text-white">{fmtDate(data.maestro.FECEMISS)}</p>
        </div>
        <div>
          <p className="text-xs text-slate-400">Items</p>
          <p className="text-white">{data.detalles.length}</p>
        </div>
        <div>
          <p className="text-xs text-slate-400">Total</p>
          <p className="text-orange-400 font-bold font-mono">{fmtMoney(data.maestro.MONTOTOT)}</p>
        </div>
      </div>

      {/* Receipt preview (screen) */}
      <div
        ref={printRef}
        className="rounded-lg border border-slate-700 bg-white overflow-auto p-4"
        style={{ maxHeight: '60vh' }}
      >
        <ReciboContent d={data} />
      </div>

      {/* Action buttons */}
      <div className="flex flex-wrap gap-3">
        <Button onClick={handlePrint}>
          <Printer className="h-4 w-4 mr-1.5" />
          Imprimir
        </Button>

        {showFEBtn && (
          <Button
            variant="secondary"
            loading={feMutation.isPending}
            onClick={() => feMutation.mutate()}
          >
            <Send className="h-4 w-4 mr-1.5" />
            Enviar a FE
          </Button>
        )}

        {tipo === 'factura' && hasDgiPdf && (
          <Button variant="secondary" onClick={handleVerPdf}>
            <FileText className="h-4 w-4 mr-1.5" />
            Ver PDF DGI
          </Button>
        )}

        <Button variant="ghost" onClick={() => navigate(TIPO_NEW_PATHS[tipo])}>
          <Plus className="h-4 w-4 mr-1.5" />
          Nuevo {label}
        </Button>

        <Button variant="ghost" onClick={() => navigate(TIPO_LIST_PATHS[tipo])}>
          Ir a la lista
        </Button>
      </div>

      {/* Print portal */}
      <PrintPortal d={data} />
    </div>
  )
}
