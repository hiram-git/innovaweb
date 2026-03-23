import { Component, type ReactNode, type ErrorInfo } from 'react'

interface Props {
  children: ReactNode
  fallback?: ReactNode
}

interface State {
  hasError: boolean
  error:    Error | null
}

export class ErrorBoundary extends Component<Props, State> {
  state: State = { hasError: false, error: null }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error }
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('[ErrorBoundary]', error, info.componentStack)
  }

  reset = () => this.setState({ hasError: false, error: null })

  render() {
    if (this.state.hasError) {
      if (this.props.fallback) return this.props.fallback

      return (
        <div className="flex min-h-screen items-center justify-center bg-slate-950 p-6">
          <div className="w-full max-w-md rounded-xl border border-red-700 bg-red-900/20 p-8 text-center">
            <h1 className="mb-2 text-xl font-bold text-red-300">Algo salió mal</h1>
            <p className="mb-1 text-sm text-slate-400">Se produjo un error inesperado en la aplicación.</p>
            {this.state.error && (
              <pre className="mt-3 overflow-auto rounded bg-slate-900 p-3 text-left text-xs text-slate-300">
                {this.state.error.message}
              </pre>
            )}
            <button
              onClick={this.reset}
              className="mt-6 rounded-lg bg-orange-600 px-5 py-2 text-sm font-medium text-white hover:bg-orange-500 transition-colors"
            >
              Intentar de nuevo
            </button>
          </div>
        </div>
      )
    }

    return this.props.children
  }
}
