# Beitrag zum Projekt
Vielen Dank, dass Sie einen Beitrag zu diesem Projekt leisten möchten! Wir schätzen jede Hilfe, sei es durch Code, Dokumentation, Bug-Reports oder Feedback.

---

## Wie Sie beitragen können:
### 1. Klonen Sie das Project:
```bash
git clone https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework.git <Ziel-Pfad>
```
### 2. Erstellen Sie einen neuen Branch:
```bash
cd <Ziel-Pfad>
git branch <Branch-Name>
git switch <Branch-Name>
```
### 3. Nehmen Sie Ihre Änderungen vor.
### 4. Stellen Sie sicher, dass alle Tests erfolgreich durchlaufen sind und Ihr build erfolgreich war.
### 5. Erstellen Sie einen [merge request](https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework/-/merge_requests/new).

---

## Code-Richtlinien
- Benennen Sie Variablen und Funktionen klar und prägnant auf Englisch.
- Bitte schreiben Sie zu allen Klassen und public Konstruktoren / Methoden / Konstanten / Attributen das JavaDoc auf Englisch. Weitere Kommentare sind erwünscht, wo notwendig.

### Code-Formatierung mit Maven Spotless
Dieses Projekt verwendet das Maven-Plugin Spotless, um die automatische Code-Formatierung und Einhaltung eines einheitlichen Code-Stils sicherzustellen.

#### Warum Spotless?
- Einheitliche Code-Formatierung für alle Entwickler
- Verbesserte Lesbarkeit und Codequalität
- Automatische Prüfung im CI-Prozess

#### Nutzung
Code formatieren (lokal):
```bash
mvn spotless:apply
```
> Formatiert den gesamten Code nach dem definierten Style (z. B. Google Java Format).

Code-Style prüfen (lokal oder im CI):
```bash
mvn spotless:check
```
> Prüft, ob der aktuelle Code dem definierten Style entspricht. Bei Abweichungen schlägt der Check fehl.

#### Konfiguration
Die Konfiguration des Code-Styles befindet sich in der Datei ```pom.xml``` unter dem Plugin-Abschnitt für Spotless. Änderungen am Style können dort zentral angepasst werden. Nähere Information zum verwendeten Code-Style finden Sie unter [itm-java-codeformat](https://github.com/it-at-m/itm-java-codeformat). 

#### Hinweise
- Bitte vor jedem Commit den Code mit mvn spotless:apply formatieren, um unnötige Formatierungs-Diskussionen im Review zu vermeiden.
- Die Spotless-Prüfung ist auch Teil des CI-Prozesses und kann dazu führen, dass Builds fehlschlagen, wenn der Style nicht eingehalten wird.

---

## Tests
- Bitte schreiben Sie zu dem von Ihnen erstellten Code Unit-Tests. Regel hier lautet zu jeder public Methode mindestens einen Test!
- Legen Sie die Tests im jeweiligen `src/test` Pfad ab und committen diese mit Ihren Änderungen.
- Vor dem Erstellen eines MRs sollten alle Tests erfolgreich durchgelaufen sein.

So führen Sie die Tests lokal aus:
```bash
mvn clean test
```

---

## Bug Reports und Feature Requests
Wenn Sie einen Bug finden oder eine Funktion vorschlagen möchten, überprüfen Sie zuerst, ob es bereits einen ähnlichen [Vorgang](https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework/-/integrations/jira/issues) gibt. Wenn nicht, erstellen Sie einen [neuen Vorgang](https://jira.muenchen.de/secure/CreateIssue!default.jspa?atlOrigin=eyJpIjoiYjM0MTA4MzUyYTYxNDVkY2IwMzVjOGQ3ZWQ3NzMwM2QiLCJwIjoianN3LWdpdGxhYlNNLWludCJ9) mit einer klaren Beschreibung.