export type TenantUser = {
  id: number
  name: string
  email: string
  role: string
  is_active: boolean
  must_change_password: boolean
  created_at: string | null
}

export type TenantSession = {
  token: string
  user: TenantUser
  tenant: {
    slug: string
    name: string
  }
}

export type TenantInfo = {
  slug: string
  name: string
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
