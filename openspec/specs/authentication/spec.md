# Authentication Capability Spec

## Overview

User authentication and authorization for the Gärngschee-Mässpäggli platform.

## Benutzerrollen

| Rolle | Beschreibung |
|-------|--------------|
| Besucher | Nicht angemeldete Benutzer |
| Klient | Personen/Familien mit knappen Mitteln |
| Spender | Finanzen Mässpäggli |
| Mitarbeiter | Vereinsmitarbeiter |
| Administrator | Systemverwaltung |

## Features

### F1: Besucher (Anonymous)

- View public information
- Access donation page
- Submit registration

### F2: Klient (Client)

- Manage own registration
- View registration status
- Receive QR code after qualification
- Update personal information

### F3: Spender (Donor)

- Select number of Mässpäggli
- Redirect to Payrexx for payment
- No account required (payment handles identity)

### F4: Mitarbeiter (Staff)

- Manage all registrations
- Manage waitlist
- Verify QR codes
- Confirm pickups
- Manage email templates
- Manage reminder rules

### F5: Administrator

- Manage master data
- System configuration
- User management

## Security Considerations

- Password hashing (bcrypt)
- Session tokens (secure, httpOnly cookies)
- CSRF protection
- Rate limiting on auth endpoints

## Implementation Status

| Component | Status |
|-----------|--------|
| Besucher (public access) | ✗ Not implemented |
| Klient authentication | ✗ Not implemented |
| Spender (Payrexx handles) | ✗ Not implemented |
| Mitarbeiter auth | ✗ Not implemented |
| Admin auth | ✗ Not implemented |

## Open Decisions

- Session vs JWT tokens?
- Fairgate integration for eligibility?
- Existing Fairgate accounts or separate?
