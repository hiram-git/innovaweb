import { useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/axios'
import { SetupPage } from './SetupPage'
import { Spinner } from '@/components/ui/Spinner'

interface SetupStatus {
  needs_setup: boolean
}

interface SetupGuardProps {
  children: React.ReactNode
}

export function SetupGuard({ children }: SetupGuardProps) {
  const qc = useQueryClient()

  const { data, isLoading, isError } = useQuery<SetupStatus>({
    queryKey: ['setup-status'],
    queryFn: () => api.get<SetupStatus>('/setup/status').then(r => r.data),
    retry: 1,
    staleTime: Infinity, // only re-check after explicit invalidation
  })

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950">
        <Spinner />
      </div>
    )
  }

  // If the status check itself fails (API down, etc.), let the app proceed
  if (isError || !data?.needs_setup) {
    return <>{children}</>
  }

  return (
    <SetupPage
      onComplete={() => {
        // Invalidate so next render re-checks status, then reload
        qc.invalidateQueries({ queryKey: ['setup-status'] })
        window.location.reload()
      }}
    />
  )
}
