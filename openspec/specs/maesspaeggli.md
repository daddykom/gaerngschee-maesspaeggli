---
created: 2026-06-28 12:42
updated: 2026-07-06 14:32
---

---
Betreff: Fachkonzept Mässpäggli
---
# **Fachkonzept Gärngschee-Mässpäggli**

## **Zweck dieses Dokuments**

Dieses Dokument beschreibt den fachlichen Funktionsumfang der neuen Gärngschee-Mässpäggli-Plattform.

---

# **1. Benutzerrollen**

## **Besucher**

Nicht angemeldete Benutzer.

### **Funktionsumfang**

- Öffentliche Informationen ansehen.
- Zur Spendenseite wechseln.
- Anmeldung für Mässpäggli erfassen.

### **Fachliche Regeln**

- Besucher besitzen keinen Zugriff auf geschützte Bereiche.

---

## **Klient**

Personen oder Familien mit knappen finanziellen Mitteln.

### **Funktionsumfang**

- Anmeldung erfassen.
- Eigene Anmeldung bearbeiten.
- Status der Anmeldung verfolgen.
- Benachrichtigungen empfangen.
- QR-Code für die Abholung erhalten.

### **Fachliche Regeln**

- Eine Anmeldung wird über das Fairgate-System geprüft.
- Eine Anmeldung kann jederzeit angepasst werden.
- Eine Anmeldung kann mehrere Kinder enthalten.

---

## **Spender**

Personen, welche Mässpäggli finanzieren.

### **Funktionsumfang**

- Anzahl Mässpäggli auswählen.
- Zur Payrexx-Spendenseite wechseln.

### **Fachliche Regeln**

- Die Zahlungsabwicklung erfolgt vollständig über Payrexx.
- Jede erfolgreiche Spende erhöht die Anzahl verfügbarer Mässpäggli.

---

## **Mitarbeiter**

Mitarbeiter des Vereins Gärngschee.

### **Funktionsumfang**

- Anmeldungen verwalten.
- Warteliste verwalten.
- QR-Codes prüfen.
- Abgaben bestätigen.
- E-Mail-Vorlagen verwalten.
- Erinnerungsregeln verwalten.

### **Fachliche Regeln**

- Mitarbeiter besitzen Zugriff auf sämtliche administrativen Funktionen.

---

## **Administrator**

Verwaltet die Anwendung.

### **Funktionsumfang**

- Verwaltung aller Stammdaten.
- Systemkonfiguration.
- Verwaltung der Benutzer.

---

# **2. Spenden**

## **Ziel**

Spender finanzieren Mässpäggli.

### **Funktionsumfang**

- Weiterleitung zu Payrexx.
- Anzahl Mässpäggli auswählen.
- Erfassung erfolgreicher Spenden.

### **Fachliche Regeln**

- Die Anwendung verarbeitet keine Zahlungen selbst.
- Die Zahlungsabwicklung erfolgt ausschliesslich über Payrexx.
- Nur erfolgreich bezahlte Spenden erhöhen die Anzahl verfügbarer Mässpäggli.

---

# **3. Anmeldung**

## **Ziel**

Klienten können Mässpäggli beantragen.

### **Funktionsumfang**

- Anmeldung erfassen.
- Anmeldung bearbeiten.
- Anzahl Kinder erfassen.
- Altersgruppe pro Kind erfassen.
- Zusätzliche Informationen erfassen.

### **Fachliche Regeln**

- Eine Anmeldung kann mehrere Kinder enthalten.
- Für jedes Kind wird genau eine Altersgruppe angegeben.
- Die Altersgruppen sind fest vorgegeben und werden nicht administriert.
- Anmeldungen können jederzeit angepasst werden.

---

# **4. Berechtigungsprüfung**

## **Ziel**

Prüfung, ob eine Anmeldung berechtigt ist.

### **Funktionsumfang**

- Übernahme der Berechtigungsinformationen aus Fairgate.
- Status der Berechtigungsprüfung anzeigen.

### **Fachliche Regeln**

- Die Berechtigungsprüfung erfolgt ausschliesslich über das Fairgate-System.
- Die Anwendung führt keine eigene Prüfung durch.

---

# **5. Warteliste**

## **Ziel**

Verwaltung von Anmeldungen, wenn nicht genügend Mässpäggli verfügbar sind.

### **Funktionsumfang**

- Warteliste führen.
- Wartelistenstatus anzeigen.
- Nachträgliche Qualifikation.

### **Fachliche Regeln**

- Neue Anmeldungen können automatisch auf die Warteliste gesetzt werden.
- Wartelisten-Anmeldungen können nachträglich qualifiziert werden, sobald genügend Mässpäggli verfügbar sind.

---

# **6. Qualifikation**

## **Ziel**

Qualifizierte Klienten erhalten eine Zusage.

### **Funktionsumfang**

- Qualifikation anzeigen.
- Zusage versenden.
- QR-Code erzeugen.

### **Fachliche Regeln**

- Eine Anmeldung kann erst qualifiziert werden, wenn genügend Mässpäggli verfügbar sind.
- Nach der Qualifikation erhält der Klient automatisch eine Informations-E-Mail mit seinem persönlichen QR-Code.

---

# **7. Abgabe**

## **Ziel**

Ausgabe der Mässpäggli an die Klienten.

### **Funktionsumfang**

- QR-Code prüfen.
- Anmeldung anzeigen.
- Abgabe bestätigen.
- Bereits abgegebene Mässpäggli anzeigen.

### **Fachliche Regeln**

- Ein QR-Code identifiziert genau eine Anmeldung.
- Eine Anmeldung kann mehrere Mässpäggli enthalten.
- Eine teilweise Abgabe ist möglich.
- Bereits abgegebene Mässpäggli werden entsprechend gekennzeichnet.
- Eine bestätigte Abgabe kann durch berechtigte Mitarbeitende rückgängig gemacht werden.

---

# **8. Benachrichtigungen**

## **Ziel**

Die Anwendung informiert Klienten automatisch über wichtige Ereignisse.

### **Funktionsumfang**

- Versand von Bestätigungs-E-Mails.
- Versand von Informations-E-Mails.
- Versand von Erinnerungs-E-Mails.

### **Fachliche Regeln**

- Nach der Anmeldung wird automatisch eine Bestätigungs-E-Mail versendet.
- Wartelisten-Klienten erhalten eine entsprechende Information.
- Nach erfolgreicher Qualifikation erhält der Klient eine Informations-E-Mail mit seinem QR-Code.
- Erinnerungs-E-Mails können bis zur Abholung versendet werden.
- Nach vollständiger Abgabe werden keine weiteren Erinnerungs-E-Mails versendet.

---

# **9. E-Mail-Verwaltung**

## **Ziel**

Mitarbeitende können sämtliche E-Mail-Vorlagen und deren Versandverhalten selbst verwalten.

### **Funktionsumfang**

- Verwaltung aller E-Mail-Vorlagen.
- Bearbeitung des Betreffs.
- Bearbeitung des E-Mail-Inhalts.
- Verwendung von Platzhaltern.
- Konfiguration der Erinnerungs-E-Mails.

### **Fachliche Regeln**

- Alle E-Mail-Typen werden online verwaltet.
- Änderungen an E-Mail-Vorlagen gelten für zukünftige E-Mails.
- Die Anzahl der Erinnerungs-E-Mails kann global konfiguriert werden.
- Die zeitlichen Abstände zwischen Erinnerungs-E-Mails können global konfiguriert werden.
- Platzhalter werden beim Versand automatisch durch die entsprechenden Daten ersetzt.

---

# **10. Administration**

## **Ziel**

Verwaltung sämtlicher fachlicher Daten.

### **Funktionsumfang**

- Anmeldungen verwalten.
- Spendenübersicht anzeigen.
- Warteliste verwalten.
- Status verwalten.

### **Fachliche Regeln**

- Änderungen werden protokolliert.
- Die Anzahl verfügbarer Mässpäggli ist jederzeit ersichtlich.
- Die Anzahl qualifizierter und wartender Anmeldungen ist jederzeit ersichtlich.

