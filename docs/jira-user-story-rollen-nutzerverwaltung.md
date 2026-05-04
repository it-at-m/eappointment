# Jira User Story

## Titel

Rollen-Verwaltung und gezielte Zuordnung zu Nutzer*innen

## Value Statement

Technische Admins können Rollen gezielt definieren und prüfen; Superuser können diese Rollen Nutzer*innen zuweisen – ohne dass alle automatisch dieselbe Standard-Sachbearbeitungs-Rolle erhalten.

## Hintergrundinformationen

Wir stellen das System schrittweise von den **klassischen Berechtigungsstufen** auf **explizite Rollen** um. Bisher war es schwer, genau nachzuvollziehen, welche konkreten Rechte eine Person wirklich braucht – und in der Übergangsphase landen viele Nutzer*innen faktisch bei **ähnlichen Standard-Rollen** (z. B. Sachbearbeitung Standard), was **Audits und technische Kontrolle** erschwert.

Mit **selbst definierten, fein abgestuften Rollen** können wir **gezielt** festlegen, wer was darf; das erleichtert uns technischen Admins die **Überprüfung** und vermeidet den Effekt, dass pauschal dieselbe breite Standard-Rolle vergeben wird. **Fachliche Nutzeradministration** soll dabei **nicht sofort umlernen müssen** – sie legt Nutzer*innen vorerst weiter über die **bekannten Berechtigungen** an; die technische Seite kann Rollen und Zuordnung dort nachziehen, wo es nötig ist.

## Akzeptanzkriterien

- Rollen sind über eine **eigene Verwaltungsseite** vollständig bearbeitbar (inkl. Berechtigungen).
- **Superuser** können Nutzer*innen **explizit Rollen zuweisen**; **Nicht-Superuser** in der Nutzerverwaltung legen Nutzer*innen wie gewohnt über **Berechtigungen** an (Rollenableitung unverändert).
