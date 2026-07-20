# Unit-Tests

Um Unit-Tests lokal auszuführen, siehe die [GitHub Workflows](https://github.com/it-at-m/eappointment/blob/main/.github/workflows/unit-tests.yaml) und führe in deinem lokalen Container Folgendes aus:

## Unit-Tests der PHP-Module

### Mit DDEV

```bash
ddev ssh
```

```bash
cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}
```

```bash
./vendor/bin/phpunit
```

### Mit Podman

```bash
podman exec -it zms-web bash
```

```bash
cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}
```

```bash
./vendor/bin/phpunit
```

Nützliche Flags für `./vendor/bin/phpunit`:

```bash
--display-warnings
--display-deprecations
--display-notices
--display-errors
--display-failures
--debug
```

### Sonderfälle (zmsbackend & zmsclient)

**zmsclient:**

Für `zmsclient` benötigst du das PHP-Basis-Image, das einen lokalen Mock-Server startet. Das JSON in den Mocks muss zur Signatur passen, die die Entität in den Anfragen zurückgibt (das ist üblicherweise die Ursache, wenn Tests in `zmsclient` fehlschlagen).

**Mit Docker:**

```bash
cd zmsclient
docker compose down && docker compose up -d && docker exec zmsclient-test-1 ./vendor/bin/phpunit
```

**Mit Podman:**

```bash
cd zmsclient
./zmsclient-test
./zmsclient-test --filter "testSetKeyBasic"
```

Das Skript `zmsclient-test` erkennt und nutzt automatisch Docker oder Podman, startet die Container für einen sauberen Zustand neu und führt die PHPUnit-Tests aus.

#### Traditionelle Methode (überschreibt lokale DB)

Für **zmsbackend** müssen Testdaten importiert werden. Beachte, dass dabei deine lokale Datenbank überschrieben wird.

**zmsbackend** (einheitliches REST-API- und Datenbankmodul für `/terminvereinbarung/api/2`):

Mit DDEV oder Podman:

```bash
./zmsbackend/zmsbackend-test
```

Oder manuell (in `zms-web` / `ddev ssh`):

```bash
cd zmsbackend && bin/importTestData --commit
cd zmsbackend && bin/configure && ./vendor/bin/phpunit
```

## PHP-Unit-Tests im Container (empfohlen – isolierte Umgebung)

Führe deine Tests in sauberen, wegwerfbaren Containern aus, damit sie weder dein lokales System noch deine Datenbank beeinträchtigen:

```bash
# Web-Container betreten
podman exec -it zms-web bash  # Podman
ddev ssh                      # DDEV

# zmsbackend-Tests ausführen
./zmsbackend/zmsbackend-test                    # alle Tests
./zmsbackend/zmsbackend-test --filter="StatusTest::testBasic"  # spezifischen Test
./zmsbackend/zmsbackend-test --filter="StatusGetTest::testRendering"  # spezifischen Test
```

**Verfügbare PHPUnit-Flags:**

```bash
# Testauswahl (filter ist ein Regex gegen "Namespace\TestClass::testMethod")
--filter="TestClass::testMethod"  # spezifische Testmethode
--filter="TestClass"              # alle Tests einer Klasse
--filter="testMethod"             # alle Tests mit passendem Methodennamen
--filter="pattern"                # Tests nach Regex-Muster

# Ausgabe & Ausführlichkeit
--verbose                         # detailliertere Ausgabe
--debug                           # Debug-Informationen
--stop-on-failure                 # beim ersten Fehler stoppen
--stop-on-error                   # beim ersten Error stoppen
--stop-on-warning                 # bei erster Warnung stoppen

# Coverage & Berichte
--coverage-text                   # Text-Coverage-Bericht
--coverage-html=dir               # HTML-Coverage-Bericht
--coverage-clover=file.xml        # XML-Coverage-Bericht

# Testausführung
--group="groupName"               # Tests einer Gruppe ausführen
--exclude-group="groupName"       # Tests einer Gruppe ausschließen
--testsuite="suiteName"           # spezifische Test-Suite ausführen
```

**Beispiele:**

```bash
# spezifischen Test mit ausführlicher Ausgabe ausführen
./zmsbackend/zmsbackend-test --filter="StatusTest::testBasic" --verbose

./zmsbackend/zmsbackend-test --filter="StatusGetTest" --stop-on-failure

./zmsbackend/zmsbackend-test --coverage-text

./zmsbackend/zmsbackend-test --exclude-group="slow"
```

## Unit-Tests von zmscitizenview

Führe Frontend-Unit-Tests mit dem Vitest-/Jest-Setup des Moduls aus:

```bash
cd zmscitizenview
```

```bash
npm test
```

Nach Testnamen-Muster filtern:

```bash
npm test -- -t "AppointmentView"
```

## zmsautomation

`zmsautomation` ist kein Unit-Test-Modul. Seine API-/UI-Test-Suiten sind in der [zmsautomation-Dokumentation](./zmsautomation.md) beschrieben.
