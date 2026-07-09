## 1. Backend — Tenant User Model & Migration

- [x] 1.1 Create tenant-scoped `users` migration in `database/migrations/tenant/`
- [x] 1.2 Create `TenantUser` model with `HasFactory`, scoped to tenant schema
- [x] 1.3 Create `password_reset_tokens` migration in tenant schema

## 2. Backend — Update Tenant Provisioning

- [x] 2.1 Update `TenantProvisioningService` to create `users` table inside tenant schema
- [x] 2.2 Add logic to create default admin user with `must_change_password = true` and temp password
- [x] 2.3 Update `TenantController@store` to accept `admin_email` and `admin_name` fields
- [x] 2.4 Update `CreateTenantRequest` to validate `admin_email` and `admin_name` (optional)

## 3. Backend — Tenant Auth Endpoints

- [x] 3.1 Create `TenantLoginRequest` form request
- [x] 3.2 Create `TenantAuthController@store` — login against tenant users table
- [x] 3.3 Create `TenantChangePasswordRequest` form request
- [x] 3.4 Create `TenantAuthController@update` — change password (forced + voluntary)
- [x] 3.5 Create `TenantForgotPasswordRequest` form request
- [x] 3.6 Create `TenantAuthController@forgotPassword` — send reset email
- [x] 3.7 Create `TenantResetPasswordRequest` form request
- [x] 3.8 Create `TenantAuthController@resetPassword` — validate token and update password
- [x] 3.9 Create `TenantAuthController@show` — GET /api/v1/tenant/me (current user profile)

## 4. Backend — Tenant Auth Middleware & Routes

- [x] 4.1 Create `EnsureTenantUser` middleware (verify token belongs to user in current tenant schema)
- [x] 4.2 Create `EnsureTenantAdmin` middleware (verify user has role `admin` within tenant)
- [x] 4.3 Add tenant auth routes to `routes/api.php` (login, forgot-password, reset-password as public)
- [x] 4.4 Add tenant protected routes (me, change-password with `auth:sanctum` + `tenant` + `EnsureTenantUser`)
- [x] 4.5 Add tenant admin routes (user CRUD with `auth:sanctum` + `tenant` + `EnsureTenantAdmin`)

## 5. Backend — Tenant User Management

- [x] 5.1 Create `CreateTenantUserRequest` form request
- [x] 5.2 Create `TenantUserController@index` — list users in tenant
- [x] 5.3 Create `TenantUserController@store` — create user with temp password
- [x] 5.4 Create `TenantUserController@destroy` — deactivate user (prevent self-deactivation)

## 6. Backend — Password Reset Email

- [x] 6.1 Create `TenantPasswordResetMail` mailable (or Notification)
- [x] 6.2 Create email template for password reset link

## 7. Frontend — Types & Store

- [x] 7.1 Create `types/tenant.ts` with `TenantUser`, `TenantSession`, `ApiEnvelope` types
- [x] 7.2 Create `stores/tenantAuth.ts` with token, user, tenantSlug, mustChangePassword state
- [x] 7.3 Update `lib/http.ts` to dynamically attach `X-Tenant` header from current route

## 8. Frontend — Tenant Login Page

- [x] 8.1 Create `TenantLoginPage.vue` with email/password form
- [x] 8.2 Handle `must_change_password` redirect in login response

## 9. Frontend — Password Management Pages

- [x] 9.1 Create `TenantChangePasswordPage.vue` for forced password change
- [x] 9.2 Create `TenantForgotPasswordPage.vue` for requesting reset
- [x] 9.3 Create `TenantResetPasswordPage.vue` for setting new password via token

## 10. Frontend — Tenant Dashboard & Navigation

- [x] 10.1 Create `TenantDashboardPage.vue` with welcome message and user info
- [x] 10.2 Add `/t/:slug/*` routes to router with tenant auth guard
- [x] 10.3 Update `App.vue` header to show tenant user info when in tenant context

## 11. Backend — Tests

- [x] 11.1 Write test: tenant login with valid credentials
- [x] 11.2 Write test: tenant login with invalid credentials
- [x] 11.3 Write test: forced password change flow
- [x] 11.4 Write test: forgot password sends email
- [x] 11.5 Write test: reset password with valid/expired/invalid token
- [x] 11.6 Write test: tenant admin creates user
- [x] 11.7 Write test: non-admin cannot create users
- [x] 11.8 Write test: admin cannot deactivate themselves
- [x] 11.9 Write test: provisioning creates default admin user

## 12. Verification

- [x] 12.1 Run `php artisan test` — all tests pass
- [x] 12.2 Run `npm run build` — frontend builds successfully
- [x] 12.3 Manual test: create tenant → login → change password → dashboard
