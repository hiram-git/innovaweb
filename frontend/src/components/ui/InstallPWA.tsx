import { useState, useEffect } from 'react'
import { Download, X } from 'lucide-react'

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>
}

/**
 * Muestra un banner de instalación cuando el navegador dispara el evento
 * `beforeinstallprompt`. Respeta la decisión del usuario: si descarta el
 * banner, no vuelve a mostrarse en la misma sesión.
 */
export function InstallPWA() {
  const [prompt,    setPrompt]    = useState<BeforeInstallPromptEvent | null>(null)
  const [dismissed, setDismissed] = useState(false)
  const [installed, setInstalled] = useState(false)

  useEffect(() => {
    const handler = (e: Event) => {
      e.preventDefault()
      setPrompt(e as BeforeInstallPromptEvent)
    }
    window.addEventListener('beforeinstallprompt', handler)

    // Si ya está instalada como PWA standalone no mostramos el banner
    const mq = window.matchMedia('(display-mode: standalone)')
    if (mq.matches) setInstalled(true)
    const mqHandler = (e: MediaQueryListEvent) => { if (e.matches) setInstalled(true) }
    mq.addEventListener('change', mqHandler)

    window.addEventListener('appinstalled', () => {
      setInstalled(true)
      setPrompt(null)
    })

    return () => {
      window.removeEventListener('beforeinstallprompt', handler)
      mq.removeEventListener('change', mqHandler)
    }
  }, [])

  const handleInstall = async () => {
    if (!prompt) return
    await prompt.prompt()
    const { outcome } = await prompt.userChoice
    if (outcome === 'accepted') {
      setInstalled(true)
    }
    setPrompt(null)
  }

  const handleDismiss = () => {
    setDismissed(true)
    setPrompt(null)
  }

  if (!prompt || dismissed || installed) return null

  return (
    <div className="fixed bottom-20 left-4 right-4 md:left-auto md:right-4 md:w-80 z-50
                    rounded-xl border border-blue-700 bg-slate-900 shadow-2xl p-4">
      <div className="flex items-start gap-3">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-700">
          <Download className="h-5 w-5 text-white" />
        </div>
        <div className="flex-1 min-w-0">
          <p className="text-sm font-semibold text-white">Instalar InnovaWeb</p>
          <p className="mt-0.5 text-xs text-slate-400 leading-relaxed">
            Instala la app para acceder más rápido y trabajar sin conexión.
          </p>
        </div>
        <button
          onClick={handleDismiss}
          className="shrink-0 rounded-md p-1 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
          aria-label="Cerrar"
        >
          <X className="h-4 w-4" />
        </button>
      </div>
      <div className="mt-3 flex gap-2">
        <button
          onClick={handleInstall}
          className="flex-1 rounded-lg bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-500 transition-colors"
        >
          Instalar
        </button>
        <button
          onClick={handleDismiss}
          className="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-400 hover:text-white hover:border-slate-600 transition-colors"
        >
          Ahora no
        </button>
      </div>
    </div>
  )
}
