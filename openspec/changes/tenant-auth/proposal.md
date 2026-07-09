## Why

Turnero needs per-tenant authentication so that professionals (psychologists, barbers, etc.) can log in and manage their own agenda. Currently only platform-level admin auth exists — there is no way for tenant users to authenticate, and tenant schemas have no users table.

## What Changes

- Add `users` table inside each tenant schema (created during provisioning)
- Add tenant login endpoint (`POST /api/v1/auth/tenant/login`) that authenticates against the tenant's users table
- Add `TenantUser` model scoped to the active tenant schema
- Add forced password change on first login (`must_change_password` flag)
- Add password recovery flow (forgot + reset)
- Add tenant user CRUD (admin within tenant can create/list/deactivate users)
- Add Sanctum tokens scoped to tenant users
- Add frontend login page, password change/flow pages, and tenant auth store
- Add route guards for `/t/:slug/*` tenant routes
- Update `TenantProvisioningService` to create users table + default admin user during tenant creation

## Capabilities

### New Capabilities

- `tenant-auth`: Login, logout, forced password change, forgot/reset password for tenant users
- `tenant-user-management`: CRUD operations for tenant users (admin role within tenant)

### Modified Capabilities

(none — no existing specs to modify)

## Impact

- **Backend**: New controller (`TenantAuthController`, `TenantUserController`), new middleware (`EnsureTenantUser`, `EnsureTenantAdmin`), new model (`TenantUser`), updated provisioning service, new routes
- **Frontend**: New pages (login, change-password, forgot-password, reset-password, dashboard), new store (`tenantAuth`), updated router with `/t/:slug/*` routes, updated HTTP client for dynamic `X-Tenant` header
- **Database**: New migration inside tenant schemas, `password_reset_tokens` table per tenant schema
- **Dependencies**: None new — uses existing Sanctum, Laravel Mail/Notifications
