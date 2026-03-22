/**
 * Base de datos offline — IndexedDB via Dexie
 *
 * Almacena datos localmente para funcionar sin internet.
 * Se sincroniza con la API cuando hay conexión.
 */
import Dexie, { type EntityTable } from 'dexie'
import type { Cliente, Produto, FacturaMaestro, ItemFactura, FormaPago } from '@/types'

// Factura pendiente de envío (creada offline)
export interface FacturaOffline {
  id?:     number
  tempId:  string
  payload: {
    codcliente:      string
    tipoFactura:     string
    diasVencimiento: number
    descuentoGlobal: number
    observacion:     string
    items:           ItemFactura[]
    formasPago:      FormaPago[]
  }
  creadaEn: Date
  estado:   'pendiente' | 'sincronizando' | 'error'
  errorMsg?: string
}

class InnovaWebDB extends Dexie {
  clientes!:         EntityTable<Cliente,       'CODCLIENTE'>
  productos!:        EntityTable<Produto,        'CODPRO'>
  facturas!:         EntityTable<FacturaMaestro, 'CONTROLMAESTRO'>
  facturas_offline!: EntityTable<FacturaOffline, 'id'>

  constructor() {
    super('InnovaWebDB')
    this.version(1).stores({
      clientes:         'CODCLIENTE, NOMBRE, RIF',
      productos:        'CODPRO, DESCRIP1',
      facturas:         'CONTROLMAESTRO, NROFAC, NOMCLIENTE, FECHA',
      facturas_offline: '++id, tempId, creadaEn, estado',
    })
  }
}

export const db = new InnovaWebDB()
