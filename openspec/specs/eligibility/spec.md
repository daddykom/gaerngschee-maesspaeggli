# Eligibility Capability Spec

## Overview

Berechtigungsprüfung über Fairgate-System.

## Features

### F1: Fairgate-Integration

- Übernahme der Berechtigungsinformationen aus Fairgate
- Keine eigene Prüfung durch die Anwendung

### F2: Status-Anzeige

- Anzeige des Berechtigungsstatus
- Mögliche Status: pending, eligible, not_eligible

## Fachliche Regeln

- Berechtigungsprüfung erfolgt ausschliesslich über das Fairgate-System
- Die Anwendung führt keine eigene Prüfung durch

## Implementation Status

| Component | Status |
|-----------|--------|
| Fairgate API integration | ✗ Not implemented |
| Eligibility status display | ✗ Not implemented |
