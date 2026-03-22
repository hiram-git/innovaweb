import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest'
import axios from 'axios'

// Importamos el módulo bajo test DESPUÉS de configurar los mocks
// para que los interceptores ya estén registrados cuando se ejecuten.
let api: typeof import('../axios').default

describe('api axios client', () => {
  const ORIGINAL_LOCATION = window.location

  beforeEach(async () => {
    // Limpiar caché de módulo para re-evaluar los interceptores con cada test
    vi.resetModules()
    api = (await import('../axios')).default

    // Mock window.location.href (no se puede asignar directamente en jsdom)
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: { ...ORIGINAL_LOCATION, href: '' },
    })

    localStorage.clear()
  })

  afterEach(() => {
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: ORIGINAL_LOCATION,
    })
    localStorage.clear()
    vi.restoreAllMocks()
  })

  // ─── Request interceptor ────────────────────────────────────────────────────

  it('agrega el header Authorization si hay token en localStorage', async () => {
    localStorage.setItem('innovaweb_token', 'mi-token-secreto')

    // Interceptamos la petición antes de que se envíe
    const interceptedConfig = await new Promise<ReturnType<typeof api.get>>((resolve) => {
      // @ts-expect-error acceder a internals de axios para test
      const requestInterceptor = api.interceptors.request.handlers[0]
      const config = { headers: {} as Record<string, string> }
      const result = requestInterceptor.fulfilled(config)
      resolve(result)
    })

    expect((interceptedConfig as { headers: Record<string, string> }).headers.Authorization).toBe(
      'Bearer mi-token-secreto'
    )
  })

  it('NO agrega Authorization si no hay token en localStorage', async () => {
    localStorage.removeItem('innovaweb_token')

    // @ts-expect-error acceder a internals de axios para test
    const requestInterceptor = api.interceptors.request.handlers[0]
    const config = { headers: {} as Record<string, string> }
    const result = requestInterceptor.fulfilled(config)

    expect((result as { headers: Record<string, string> }).headers.Authorization).toBeUndefined()
  })

  // ─── Response interceptor ───────────────────────────────────────────────────

  it('redirige a /login y limpia localStorage en respuesta 401', async () => {
    localStorage.setItem('innovaweb_token', 'token-caducado')
    localStorage.setItem('innovaweb_user', JSON.stringify({ name: 'Test' }))

    const axiosError = {
      response: { status: 401, data: { message: 'Unauthenticated.' } },
      isAxiosError: true,
    }

    // @ts-expect-error acceder a internals de axios para test
    const responseInterceptor = api.interceptors.response.handlers[0]

    await expect(responseInterceptor.rejected(axiosError)).rejects.toEqual(axiosError)

    expect(localStorage.getItem('innovaweb_token')).toBeNull()
    expect(localStorage.getItem('innovaweb_user')).toBeNull()
    expect(window.location.href).toBe('/login')
  })

  it('propaga errores que NO son 401 sin modificar localStorage', async () => {
    localStorage.setItem('innovaweb_token', 'token-valido')

    const axiosError = {
      response: { status: 500, data: { message: 'Server Error' } },
      isAxiosError: true,
    }

    // @ts-expect-error acceder a internals de axios para test
    const responseInterceptor = api.interceptors.response.handlers[0]

    await expect(responseInterceptor.rejected(axiosError)).rejects.toEqual(axiosError)

    // El token NO debe ser removido
    expect(localStorage.getItem('innovaweb_token')).toBe('token-valido')
  })

  // ─── Base config ────────────────────────────────────────────────────────────

  it('usa /api/v1 como baseURL', () => {
    expect(api.defaults.baseURL).toBe('/api/v1')
  })

  it('establece Content-Type y Accept como application/json', () => {
    expect(api.defaults.headers['Content-Type']).toBe('application/json')
    expect(api.defaults.headers['Accept']).toBe('application/json')
  })

  it('establece timeout de 30 segundos', () => {
    expect(api.defaults.timeout).toBe(30_000)
  })
})
