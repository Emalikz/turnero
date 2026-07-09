## Context

Turnero is a SaaS agenda management system for professionals (psychologists, barbers, etc.). Currently only platform admin auth exists (global `users` table with `is_platform_admin`). Tenant schemas are created empty — no users table, no tenant-specific authentication.

The product requires per-tenant login so professionals can access their own agenda. Each tenant's users are isolated in their own PostgreSQL schema.

## Goals / Non-Goals

**Goals:**

- Enable tenant-scoped authentication (login, password management)
- Force password change on first login for provisioning-created users
- Allow tenant admins to invite/create additional users
- Provide password recovery flow

**Non-Goals:**

- Role-based access control beyond MVP (admin vs professional — deferred)
- Email verification flow (deferred)
- Multi-factor authentication (deferred)
- Invitations via email (admin shares temp passwords manually for now)

## Decisions

### D1: Tenant users table inside tenant schema

**Decision**: Each tenant schema has its own `users` table created during provisioning.

**Rationale**: Aligns with ADR 0002 (schema-based multitenancy). Provides strong data isolation. The existing `ResolveTenant` middleware already handles schema switching via `search_path`.

**Alternatives considered**:

- Global users table with `tenant_id`: simpler but weakens isolation, contradicts ADR 0002.
- Separate database per tenant: too expensive for this scale.

### D2: Login resolves tenant from URL, not from credential

**Decision**: The tenant slug comes from the URL path (`/t/:slug/login`). The `X-Tenant` header is derived from the route parameter.

**Rationale**: Eliminates the chicken-and-egg problem. The user knows which workspace they belong to. Path-based is simpler than subdomains for development and Docker.

**Alternatives considered**:

- Subdomain-based (`acme.turnero.com/login`): cleaner UX but needs wildcard DNS, more complex Docker setup.
- Selection screen (pick tenant then log in): extra step, worse UX.

### D3: Sanctum token per tenant user

**Decision**: Sanctum tokens are used for tenant users, same as platform admins. Token stores user ID only. Tenant context comes from `X-Tenant` header on every request.

**Rationale**: No need for custom JWT. Sanctum is already installed. The `ResolveTenant` middleware sets the schema, then the controller queries the tenant's users table by the token's user ID.

**Trade-off**: Every request needs `X-Tenant` header. This is acceptable because the frontend always knows the current tenant from the URL/store.

### D4: Forced password change via `must_change_password` flag

**Decision**: New users created by the tenant admin get `must_change_password = true`. Login response includes this flag. Frontend redirects to `/t/:slug/change-password`.

**Rationale**: Simple, explicit, no time-based expiry complexity. The flag is cleared when the user successfully changes their password.

### D5: Password reset uses token-based flow

**Decision**: Standard forgot/reset flow. Store hashed token + expiry in `password_reset_tokens` table inside tenant schema. Send email via Laravel Mail.

**Rationale**: Battle-tested pattern. No external dependencies needed.

### D6: Tenant admin creates users, not self-registration

**Decision**: Only tenant admins (the first user created during provisioning) can create additional users. No public registration.

**Rationale**: Tenants are invited businesses, not open sign-ups. The admin controls who joins their workspace.

## Risks / Trade-offs

- **[Risk] Tenant schema users table missing on first login** → Mitigated by provisioning service creating the table + default admin during tenant creation. Verified in tests.
- **[Risk] Forgetting X-Tenant header on requests** → Mitigated by frontend HTTP client automatically attaching it from the current route context.
- **[Trade-off] No email-based invitations** → Admin must share temp passwords manually. Acceptable for MVP; email invitations can be added later.
- **[Trade-off] No role system yet** → All users have same permissions within tenant. Admin role is implicit (first user). Full RBAC deferred.
