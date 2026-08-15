# Notifications Capability Spec

## Overview

Automatische Benachrichtigungen per E-Mail.

## Features

### F1: Bestätigungs-E-Mail

- Nach Eingang einer Anmeldung
- Bestätigt den Empfang

### F2: Wartelisten-Information

- Für Klienten auf der Warteliste
- Information über Status

### F3: Informations-E-Mail

- Nach erfolgreicher Qualifikation
- Enthält QR-Code

### F4: Erinnerungs-E-Mails

- Konfigurierbare Anzahl
- Bis zur Abholung
- Keine Erinnerung nach vollständiger Abgabe

## E-Mail-Typen

| Type | Trigger | Content |
|------|---------|---------|
| confirmation | After registration | Acknowledgement |
| waitlist | After waitlist placement | Waitlist information |
| qualification | After qualification | QR code + info |
| reminder | Configurable | Reminder to pick up |

## Fachliche Regeln

- Nach Anmeldung wird automatisch Bestätigungs-E-Mail versendet
- Wartelisten-Klienten erhalten entsprechende Information
- Nach Qualifikation erhält Klient Informations-E-Mail mit QR-Code
- Erinnerungs-E-Mails können bis zur Abholung versendet werden
- Nach vollständiger Abgabe werden keine Erinnerungs-E-Mails versendet

## Implementation Status

| Component | Status |
|-----------|--------|
| Confirmation email | ✗ Not implemented |
| Waitlist email | ✗ Not implemented |
| Qualification email | ✗ Not implemented |
| Reminder emails | ✗ Not implemented |
| Scheduling logic | ✗ Not implemented |
