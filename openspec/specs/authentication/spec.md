# Authentication Capability Spec

## Overview

User authentication and authorization for the platform.

## Target Groups

| Group                  | Needs                                    |
|------------------------|------------------------------------------|
| Anonymous users        | View published offers                    |
| Authenticated users    | Submit offers, manage own offers         |
| Editors                | Moderate offers                          |
| Admins                 | Manage users and system settings         |

## Features

### F1: User Registration

- Email-based registration
- Email verification (optional)
- No social login (privacy-first)

### F2: User Login

- Email + password authentication
- Session management
- "Remember me" option

### F3: Password Reset

- Forgot password flow
- Email-based reset link
- Token expiration

### F4: Role Management

| Role     | Can Do                                       |
|----------|----------------------------------------------|
| user     | View offers, submit offers, edit own offers  |
| editor   | All user + moderate any offer                |
| admin    | All editor + manage users, system settings   |

### F5: User Profile

- View/edit profile information
- Change password
- View own submitted offers

## Security Considerations

- Password hashing (bcrypt)
- Session tokens (secure, httpOnly cookies)
- CSRF protection
- Rate limiting on auth endpoints

## Implementation Status

| Component | Status |
|-----------|--------|
| User model | ✗ Not implemented |
| Registration | ✗ Not implemented |
| Login | ✗ Not implemented |
| Password reset | ✗ Not implemented |
| Role management | ✗ Not implemented |
| Session handling | ✗ Not implemented |

## Open Decisions

- Use existing auth library or custom?
- Session vs JWT tokens?
- Multi-instance support needed?
- OAuth providers (Google, etc.)?