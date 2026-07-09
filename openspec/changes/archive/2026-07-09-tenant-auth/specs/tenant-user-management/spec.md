## ADDED Requirements

### Requirement: Tenant admin can create users

The system SHALL allow the first user created during provisioning (tenant admin) to create additional users within the tenant.

#### Scenario: Admin creates a new user

- **WHEN** an authenticated tenant admin creates a user with name, email, and role
- **THEN** the system creates the user in the tenant schema with `must_change_password = true`, `is_active = true`, and a randomly generated temporary password

#### Scenario: Duplicate email within tenant

- **WHEN** an admin creates a user with an email that already exists in the tenant schema
- **THEN** the system returns HTTP 422 with error code `email_taken`

#### Scenario: Non-admin cannot create users

- **WHEN** a non-admin tenant user attempts to create a user
- **THEN** the system returns HTTP 403

### Requirement: Tenant admin can list users

The system SHALL allow tenant admins to list all users in the tenant.

#### Scenario: List all users

- **WHEN** a tenant admin requests the user list
- **THEN** the system returns all users in the tenant schema with id, name, email, role, is_active, and created_at

### Requirement: Tenant admin can deactivate users

The system SHALL allow tenant admins to deactivate (soft-disable) users within the tenant.

#### Scenario: Deactivate a user

- **WHEN** a tenant admin deactivates a user
- **THEN** the system sets `is_active = false` for that user and the user can no longer log in

#### Scenario: Admin cannot deactivate themselves

- **WHEN** a tenant admin attempts to deactivate their own account
- **THEN** the system returns HTTP 422 with error code `cannot_deactivate_self`

### Requirement: Default admin user created during provisioning

The `TenantProvisioningService` SHALL create a default admin user inside the tenant schema during tenant creation.

#### Scenario: Tenant creation includes default admin

- **WHEN** the platform admin creates a new tenant with `admin_email` and `admin_name`
- **THEN** the system creates a user in the tenant schema with role `admin`, `must_change_password = true`, and returns the temporary password in the API response (shown once only)

#### Scenario: Tenant creation without admin fields

- **WHEN** the platform admin creates a tenant without `admin_email` or `admin_name`
- **THEN** the system creates the tenant without a default admin user (schema only)
