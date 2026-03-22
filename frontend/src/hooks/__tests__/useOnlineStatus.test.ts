import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { renderHook, act } from '@testing-library/react'
import { useOnlineStatus } from '../useOnlineStatus'

/**
 * Tests del hook useOnlineStatus
 *
 * El hook escucha los eventos online/offline del window
 * y retorna el estado de conexión actualizado.
 */
describe('useOnlineStatus', () => {
  const originalOnline = Object.getOwnPropertyDescriptor(navigator, 'onLine')

  const setOnline = (value: boolean) => {
    Object.defineProperty(navigator, 'onLine', { value, configurable: true })
  }

  beforeEach(() => {
    setOnline(true)
  })

  afterEach(() => {
    if (originalOnline) {
      Object.defineProperty(navigator, 'onLine', originalOnline)
    }
  })

  it('retorna true cuando navigator.onLine es true', () => {
    setOnline(true)
    const { result } = renderHook(() => useOnlineStatus())
    expect(result.current).toBe(true)
  })

  it('retorna false cuando navigator.onLine es false', () => {
    setOnline(false)
    const { result } = renderHook(() => useOnlineStatus())
    expect(result.current).toBe(false)
  })

  it('actualiza a false cuando dispara evento offline', () => {
    setOnline(true)
    const { result } = renderHook(() => useOnlineStatus())
    expect(result.current).toBe(true)

    act(() => {
      setOnline(false)
      window.dispatchEvent(new Event('offline'))
    })

    expect(result.current).toBe(false)
  })

  it('actualiza a true cuando dispara evento online', () => {
    setOnline(false)
    const { result } = renderHook(() => useOnlineStatus())
    expect(result.current).toBe(false)

    act(() => {
      setOnline(true)
      window.dispatchEvent(new Event('online'))
    })

    expect(result.current).toBe(true)
  })

  it('limpia los event listeners al desmontar', () => {
    const addSpy    = vi.spyOn(window, 'addEventListener')
    const removeSpy = vi.spyOn(window, 'removeEventListener')

    const { unmount } = renderHook(() => useOnlineStatus())

    expect(addSpy).toHaveBeenCalledWith('online',  expect.any(Function))
    expect(addSpy).toHaveBeenCalledWith('offline', expect.any(Function))

    unmount()

    expect(removeSpy).toHaveBeenCalledWith('online',  expect.any(Function))
    expect(removeSpy).toHaveBeenCalledWith('offline', expect.any(Function))

    addSpy.mockRestore()
    removeSpy.mockRestore()
  })

  it('puede alternar múltiples veces entre online y offline', () => {
    setOnline(true)
    const { result } = renderHook(() => useOnlineStatus())

    act(() => { setOnline(false); window.dispatchEvent(new Event('offline')) })
    expect(result.current).toBe(false)

    act(() => { setOnline(true);  window.dispatchEvent(new Event('online'))  })
    expect(result.current).toBe(true)

    act(() => { setOnline(false); window.dispatchEvent(new Event('offline')) })
    expect(result.current).toBe(false)
  })
})
