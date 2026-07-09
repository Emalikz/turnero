export type AdminUser = {
  id: number
  name: string
  email: string
  is_platform_admin: boolean
}

export type AdminSession = {
  token: string
  user: AdminUser
}

export type Tenant = {
  id: number
  name: string
  slug: string
  schema: string
  primary_domain: string
  settings: Record<string, unknown> | null
  created_at: string | null
}

export type ApiEnvelope<T> = {
  data: T
  meta: {
    timestamp: string
  }
  error: null | {
    code: string
    message: string
    details?: Record<string, string[]>
  }
}
