// ─── Auth ──────────────────────────────────────────────────────────────────
export interface LoginCredentials {
  usuario:  string
  password: string
}

export interface AuthUser {
  id:      number
  codigo:  string
  email:   string
  roles:   string[]
  permisos: string[]
  erp: {
    CODUSER:     string
    VALVENDEDOR: number
    VALDEPOSITO: number
    VALCONTADOR: number
  }
}

export interface AuthState {
  token:     string | null
  user:      AuthUser | null
  expiresAt: string | null
}

// ─── Cliente ───────────────────────────────────────────────────────────────
export interface Cliente {
  CODIGO:        string
  NOMBRE:        string | null
  RIF:           string | null
  NIT:           string | null
  TIPOCLI:       string | null
  TIPOCOMERCIO:  number | null
  DIRECC1:       string | null
  NUMTEL:        string | null
  DIRCORREO:     string | null
  DIASCRE:       number | null
  CONESPECIAL:   number | null
  PORRETIMP:     number | null
  PERCREDITO:    number | null   // 0=no credit, 1=credit with limit
  LIMITECRE:     number | null   // credit limit amount
  PORMAXDESGLO:  number | null   // max global discount %
  PORMAXDESPAR:  number | null   // max per-item discount %
  provincia:     string | null
  distrito:      string | null
  corregimiento: string | null
}

// ─── Inventario ────────────────────────────────────────────────────────────
export interface Produto {
  CODPRO:       string
  DESCRIP1:     string
  EXISTENCIA:   number | null
  CANRESERVADA: number | null
  PRECVEN1:     number | null
  IMPPOR:       number | null
  PROCOMPUESTO: number | null
  TIPINV:       string | null
  UNIDAD:       string | null
}

// Keep alias for backwards compatibility within this file
export type Producto = Produto

// ─── Factura ───────────────────────────────────────────────────────────────
export type TipoFactura = 'CONTADO' | 'CREDITO'
export type TasaITBMS   = 0 | 7 | 10 | 15

export interface ItemFactura {
  codpro:    string
  descrip:   string
  cantidad:  number
  precio:    number
  descuento: number
  imppor:    number
}

export interface FormaPago {
  instrumento:  string
  descripcion?: string
  monto:        number
  referencia?:  string
}

export interface FacturaMaestro {
  CONTROLMAESTRO: string
  NROFAC:         string | null
  NOMCLIENTE:     string | null
  FECHA:          string | null
  TIPTRAN:        string | null
  MONTOTOT:       number | null
  MONTOSAL:       number | null
  MONTOIMP:       number | null
  FE_ESTADO:      string | null
  FE_MENSAJE:     string | null
  CUFE:           string | null
  INTEGRADO:      number | null
}

export interface FacturaDetalle {
  CONTROLDETALLE: string
  CONTROLMAESTRO: string
  CODPRO:         string
  DESCRIP1:       string
  CANTIDAD:       number
  PRECIO:         number
  MONTODESCUENTO: number
  IMPPOR:         number
  MONTOIMP:       number
}

export interface NuevaFacturaPayload {
  codcliente:      string
  tipoFactura:     TipoFactura
  diasVencimiento: number
  descuentoGlobal: number
  observacion:     string
  items:           ItemFactura[]
  formasPago:      FormaPago[]
}

// ─── FE ────────────────────────────────────────────────────────────────────
export interface FEConfig {
  ambiente:   'sandbox' | 'produccion'
  pac:        'TFHKA' | 'DIGIFACT'
  ruc_emisor: string
  dv_emisor:  string
}

export interface FEEstado {
  estado:    0 | 1
  codigo?:   number
  resultado?: string
  mensaje:   string
  cufe?:     string
  qr?:       string
  fechaRecepcionDGI?:          string
  nroProtocoloAutorizacion?:   string
  fechaLimite?:                string
}

// ─── Instrumento de pago ───────────────────────────────────────────────────
export interface Instrumento {
  CODINSTRUMENTO:  string
  DESCRINSTRUMENTO: string
  FUNCION:         number
}

// ─── Orden de Trabajo ──────────────────────────────────────────────────────
export interface OrdenTrabajo {
  CONTROLOT:      string
  CODCLIENTE:     string
  NOMCLIENTE?:    string
  ATENDIDO:       string | null
  FECHAOT:        string | null
  FECHA_ENTREGA:  string | null
  DESCRIPCION:    string | null
  ESTADO:         number | null
  CONTROLPRES?:   string | null
}

// ─── Presupuesto ───────────────────────────────────────────────────────────
export interface Presupuesto {
  CONTROLMAESTRO: string
  NROFAC:         string | null
  NOMCLIENTE:     string | null
  FECHA:          string | null
  MONTOTOT:       number | null
  ITBMS:          number | null
  INTEGRADO:      number | null
}

// ─── Pedido ────────────────────────────────────────────────────────────────
// Columnas tal como las devuelve TRANSACCMAESTRO (CONTROL, NUMREF, NOMBRE…)
export interface Pedido {
  CONTROL:   string
  NUMREF:    string | null
  CODIGO:    string | null
  NOMBRE:    string | null
  FECEMIS:   number | null   // INT YYYYMMDD (formato Clarion)
  MONTOBRU:  number | null
  MONTOIMP:  number | null
  MONTODES:  number | null
  MONTOTOT:  number | null
}

// ─── API Response genérica ─────────────────────────────────────────────────
export interface ApiResponse<T> {
  data:    T
  meta?:   { last_page: number; total: number; per_page: number; current_page: number }
  message?: string
}

export interface ApiError {
  message: string
  error?:  string
  errors?: Record<string, string[]>
}
