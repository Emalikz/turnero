## ADDED Requirements

### Requirement: Tenant user can log in

The system SHALL authenticate users against the tenant's own users table. The tenant context SHALL be resolved from the `X-Tenant` header (derived from the URL slug `/t/:slug/login`).

#### Scenario: Successful login

- **WHEN** a user submits valid email and password with a valid `X-Tenant` header
- **THEN** the system returns a Sanctum token, user data (excluding password), and `must_change_password` flag

#### Scenario: Invalid credentials

- **WHEN** a user submits incorrect email or password
- **THEN** the system returns HTTP 422 with error code `invalid_credentials`

#### Scenario: Tenant not found

- **WHEN** a request contains an `X-Tenant` header that does not match any tenant
- **THEN** the system returns HTTP 404 with error code `tenant_not_found`

#### Scenario: User is inactive

- **WHEN** a user's `is_active` flag is false
- **THEN** the system returns HTTP 403 with error code `account_disabled`

### Requirement: Forced password change on first login

The system SHALL force users to change their password when `must_change_password` is true.

#### Scenario: Login with must_change_password

- **WHEN** a user logs in and `must_change_password` is true
- **THEN** the login response includes `must_change_password: true` and the frontend redirects to the change-password page

#### Scenario: Successful password change

- **WHEN** a user submits their current password and a valid new password
- **THEN** the system updates the password, sets `must_change_password` to false, and returns success

#### Scenario: Invalid current password

- **WHEN** a user submits an incorrect current password during forced change
- **THEN** the system returns HTTP 422 with error code `invalid_current_password`

### Requirement: Password recovery

The system SHALL allow users to request a password reset via email.

#### Scenario: Forgot password request

- **WHEN** a user submits their email to the forgot-password endpoint
- **THEN** the system always returns HTTP 200 (regardless of whether the email exists), and if the email exists, stores a hashed reset token with 60-minute expiry

#### Scenario: Reset password with valid token

- **WHEN** a user submits a valid token and matching new password
- **THEN** the system updates the password, clears the reset token, sets `must_change_password` to false, and returns success

#### Scenario: Reset password with expired token

- **WHEN** a user submits an expired reset token
- **THEN** the system returns HTTP 422 with error code `token_expired`

#### Scenario: Reset password with invalid token

- **WHEN** a user submits an invalid reset token
- **THEN** the system returns HTTP 422 with error code `token_invalid`

### Requirement: Tenant user can view their profile

The system SHALL provide a `GET /api/v1/tenant/me` endpoint that returns the authenticated user's data from the tenant's users table.

#### Scenario: Get current user profile

- **WHEN** an authenticated tenant user requests their profile
- **THEN** the system returns the user's id, name, email, role, is_active, must_change_password, and created_at

### Requirement: Token scope

Sanctum tokens SHALL be scoped to individual users. The `X-Tenant` header MUST be present on every authenticated request to resolve the correct schema.

#### Scenario: Request without X-Tenant header

- **WHEN** an authenticated request is made without an `X-Tenant` header
- **THEN** the system resolves the tenant as null and the request proceeds without tenant context (platform-level only)

#### Scenario: Request with invalid X-Tenant header

- **WHEN** an authenticated request contains an `X-Tenant` header that does not match any tenant
- **THEN** the system returns HTTP 404 with error code `tenant_not_found`
