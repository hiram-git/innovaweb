import { QueryClient } from '@tanstack/react-query'

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,    // 5 min — datos considerados frescos
      gcTime:    1000 * 60 * 30,   // 30 min — retención en caché
      retry: (failureCount, error: unknown) => {
        // No reintentar errores 4xx (son errores del cliente)
        const status = (error as { response?: { status: number } })?.response?.status
        if (status && status >= 400 && status < 500) return false
        return failureCount < 2
      },
      refetchOnWindowFocus: false,
    },
    mutations: {
      retry: false,
    },
  },
})
