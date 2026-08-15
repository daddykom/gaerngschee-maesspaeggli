# Donations Capability Spec

## Overview

Spendenverwaltung für Mässpäggli.

## Features

### F1: Anzahl auswählen

- Spender wählt Anzahl Mässpäggli
- Anzeige des Gesamtbetrags

### F2: Payrexx-Integration

- Weiterleitung zu Payrexx
- Payment-Session erstellen
- Status-Updates via Webhook

### F3: Spenden-Erfassung

- Erfassung erfolgreicher Spenden
- Erhöhung der verfügbaren Mässpäggli
- Spenden-ID und Betrag speichern

## Data Model

### Donation

```typescript
interface Donation {
  id: string;
  amount: number;
  maspaeggliCount: number;
  status: 'pending' | 'completed' | 'refunded';
  payrexxPaymentId: string;
  createdAt: string;
}
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/donations/session` | Create Payrexx payment session |
| POST | `/api/donations/webhook` | Payrexx webhook (status update) |
| GET | `/api/donations` | List donations (staff only) |

## Fachliche Regeln

- Die Anwendung verarbeitet keine Zahlungen selbst
- Zahlungsabwicklung erfolgt ausschliesslich über Payrexx
- Nur erfolgreich bezahlte Spenden erhöhen die Anzahl verfügbarer Mässpäggli

## Implementation Status

| Component | Status |
|-----------|--------|
| Payrexx session creation | ✗ Not implemented |
| Webhook handling | ✗ Not implemented |
| Donation recording | ✗ Not implemented |
| Available count update | ✗ Not implemented |
