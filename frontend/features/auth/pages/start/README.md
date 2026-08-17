# Mässpäggli Start – Angular Material

Standalone Angular-Komponente für die Startseite der Mässpäggli-Anforderung.

Verwendete Angular-Material-Bausteine:

- MatCard
- MatFormField
- MatInput
- MatButton
- MatIcon
- ReactiveFormsModule

Die Komponente verwendet Material-System-CSS-Variablen wie `--mat-sys-primary`,
damit sie sich möglichst gut an das aktive Angular-Material-Theme anpasst.

Hinweis:
Die Icons `mail`, `laptop`, `info`, `lock` und `shield` setzen eine konfigurierte
Material-Icons-Font voraus. Falls das Projekt SVG-Icons über `MatIconRegistry`
verwendet, können diese Namen entsprechend ersetzt werden.

Der `submit()`-Handler enthält absichtlich nur einen Platzhalter. Dort sollte
der vorhandene NgRx-/Service-Flow angeschlossen werden.
