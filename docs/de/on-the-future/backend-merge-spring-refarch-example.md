---
outline: deep
---

# Backend-Merge-Beispiel: von ZMS-PHP-Modulen zur Spring-RefArch

## Einführung

Teil der [produitorientierten RefArch-Roadmap](./product-oriented-refarch-roadmap.md): die geteilten PHP-Backend-Pakete werden durch **zwei Spring-Boot-Services** ersetzt, die dasselbe MySQL-Schema nutzen.

| Ziel-Service        | Ersetzt (heute)                                            |
| ------------------- | ---------------------------------------------------------- |
| `zmsbackend`        | `zmsdb`, `zmsapi`, serverseitige Nutzung von `zmsentities` |
| `zmscitizenbackend` | `zmscitizenapi`                                            |

**`zmsentities` bleibt im Monorepo** als gemeinsame Vertragsschicht: JSON-Schemas, Validierungshilfen und typisierte Objekte. **Frontend-Module** wie `zmsadmin` und `zmsstatistic` hängen weiter daran für API-Antwortformen und clientseitige Validierung.

---

## `zmsbackend`

Fusioniert **`zmsdb`**, **`zmsapi`** und die serverseitige Nutzung von **`zmsentities`** in einen Backend-Service.

### Heute

| Paket         | Rolle                                                                               |
| ------------- | ----------------------------------------------------------------------------------- |
| `zmsentities` | Schema-basierte Domain-Objekte (`Department`, `Scope`, …), Validierung, Collections |
| `zmsdb`       | SQL-Queries, Tabellen-Mappings, Caching, Schreibpfade                               |
| `zmsapi`      | HTTP-Controller, Berechtigungen, JSON-Hülle (`Message`)                             |

### Ziel

Jede Domain erhält einen **vertikalen Schnitt**: Pakete `api/`, `model/`, `repository/`, `service/`, `view/` und `exception/`. Services sind **nach Operation getrennt** (Fetch, Create, Update, Delete) statt einer Klasse pro PHP-Modul.

API-Typen liegen in `view/` und bleiben **kompatibel** zu **`zmsentities`-JSON-Schemas** (z. B. `department.json`) für Frontends — **`zmsbackend` führt aber keine JSON-Schema-Validierung aus**. Validierung erfolgt in Java auf `view/`-Typen (RefArch-Validatoren). JPA-Typen in `model/` mappen auf umbenannte DB-Tabellen (siehe [Datenbank-Refactor](./database-refactor/standardize-database-table-and-field-naming.md)).

### Warum `zmsbackend`?

Vorteile der Zusammenführung von `zmsdb`, `zmsapi` und der serverseitigen Nutzung von `zmsentities` in einem Spring-Boot-Service auf dem [RefArch](https://refarch.oss.muenchen.de/)-Stack:

1. **Validierung an einem Ort, weniger Verwirrung** — Heute liegen Regeln verteilt in Controllern, Mellon-Request-Parsing, Opis-JSON-Schema-Dateien, `Entity::testValid()` und Ad-hoc-Checks. Schemas sind zu großen, schwer wartbaren JSON-Dateien mit `oneOf`-Zahl/String-Vereinigungen und `$ref`-Ketten gewachsen, die kaum noch jemand vollständig durchschaut. In `zmsbackend` validiert jede Domain in **`validation/`** gegen ihre **`view/`**-Typen — ein klarer Ort zum Nachschlagen und Ändern.

2. **Ein Dependency-Baum statt drei Composer-Projekte** — `zmsdb`, `zmsapi` und gemeinsame PHP-Bibliotheken bedeuten **mehrere `composer.json`**, einen großen transitiven Abhängigkeitsgraphen und ständige Dependabot/Renovate-PRs über Pakete hinweg. `zmsbackend` ist **ein Maven-`pom.xml`**, ein Backend-Artefakt, ein Upgrade-Pfad.

3. **Auf RefArch aufbauen statt Eigenbau** — [refarch-templates](https://github.com/it-at-m/refarch-templates) für CI/CD, Container-Builds, Keycloak-Anbindung und Gateway-Muster nutzen, statt eigene GitHub Actions zu pflegen, die häufig brechen. Spring-Boot-Images lassen sich typisch **deutlich schneller bauen** als große PHP-Basis-Images (`zmsbase`). In München gibt es **viele Java-Entwickler**; PHP-Backend-Know-how ist rar und teuer in der Pflege.

4. **Ein Service, ein Mentalmodell** — Kein „liegt das in `zmsdb` oder `zmsapi`?“ mehr, kein Durchreichen von Entities durch drei Pakete für einen HTTP-Call. Fachlogik sitzt im **vertikalen Schnitt** (`api/` → `service/` → `repository/` → `model/`).

5. **Stärkere Typen, weniger Laufzeitüberraschungen** — Java-`model/`- und `view/`-Typen ersetzen schema-basierte PHP-`ArrayObject`-Entities. Refactorings und API-Änderungen fallen dem Compiler und der IDE auf, nicht erst zur Laufzeit oder in Integrationstests.

6. **Standard-Persistenz und Migrationen** — JPA-Repositories und RefArch-Standard für DB-Migrationen ersetzen handgeschriebene Query-Klassen und verstreutes SQL. Passt zum [Datenbank-Refactor](./database-refactor/standardize-database-table-and-field-naming.md) (klare Tabellen- und Spaltennamen im Code).

7. **Betrieb und Sicherheit out of the box** — Spring Boot Actuator, Micrometer-Metriken, strukturiertes Logging und RefArch-Sicherheitsmuster (Keycloak, API-Gateway) entsprechen dem Betrieb anderer Münchner IT-Produkte — kein Einzelstück-PHP-Stack.

8. **Schnellere, sicherere Auslieferung** — JUnit und Spring Boot Test für Unit-/Integrationstests; ATAF und REST Assured in `zmsautomation` bereits im Einsatz. Kleinere, reproduzierbare Container-Images verkürzen Deploy-Zeiten und reduzieren „läuft nur in meinem DDEV“-Drift.

9. **Einfachere Wiederverwendung und Einarbeitung** — Gleicher Stack wie bürgerorientierte RefArch-Komponenten (`refarch-gateway`, `zmscitizenview`). Neue Teammitglieder und andere Städte folgen dokumentierten RefArch-Konventionen statt ZMS-spezifischer PHP-Paketgrenzen.

10. **RefArch-API-Gateway mit Keycloak-SSO für interne Frontends** — Das RefArch-Spring-**API-Gateway** bringt **Keycloak-Login** mit. Sobald `zmsadmin` und `zmsstatistic` auf Vue/RefArch-Frontends umgestellt sind, authentifizieren sie sich über dieses Gateway — wie `zmscitizenview` es bereits tut — statt einen eigenen **`zmsclient`**-OAuth/Keycloak-Flow und Login-Controller in jedem PHP-Frontend-Modul zu pflegen.

### Beispiel: `Department` (`behoerde` → `department`)

Illustratives Spring-Boot-Layout. Tabellenumbenennung: `behoerde` → `department`.

#### Ordnerstruktur

```
src/main/java/de/muenchen/zms/department/
├── api/                    # heute: zmsapi-Controller + routing.php
├── exception/
├── model/                  # heute: zmsdb-Tabellen-Mappings (behoerde → department)
├── repository/             # heute: zmsdb Query\* + readByDepartmentId-Helfer
├── service/                # ein Service pro Operation (nicht eine Klasse pro PHP-Modul)
├── validation/             # RefArch: imperative Validatoren auf view/-Typen
└── view/                   # API-Payloads (Form kompatibel zu zmsentities-Schemas)
```

#### PHP heute → Java Ziel (vollständiger `Department`-Slice)

| PHP (heute)                                                               | Java (Ziel)                                          |
| ------------------------------------------------------------------------- | ---------------------------------------------------- |
| `zmsapi/routing.php` (`/department/*`, …)                                 | `api/DepartmentRouteRegistry` + Controller           |
| `zmsapi\DepartmentGet`                                                    | `api/DepartmentController.getDepartment`             |
| `zmsapi\DepartmentList`                                                   | `api/DepartmentController.listDepartments`           |
| `zmsapi\DepartmentUpdate`                                                 | `api/DepartmentController.updateDepartment`          |
| `zmsapi\DepartmentDelete`                                                 | `api/DepartmentController.deleteDepartment`          |
| `zmsapi\DepartmentAddScope`                                               | `api/DepartmentController.addScope`                  |
| `zmsapi\DepartmentAddCluster`                                             | `api/DepartmentController.addCluster`                |
| `zmsapi\OrganisationByDepartment`                                         | `api/DepartmentController.getOrganisation`           |
| `zmsapi\DepartmentWorkstationList`                                        | `api/DepartmentController.listWorkstations`          |
| `zmsapi\OrganisationAddDepartment`                                        | `api/OrganisationDepartmentController.addDepartment` |
| `zmsapi\DepartmentByScopeId`                                              | `api/ScopeDepartmentController.getDepartmentByScope` |
| `zmsapi\UseraccountListByDepartments`                                     | `api/DepartmentUseraccountController`                |
| `zmsapi\UseraccountListByRoleAndDepartments`                              | `api/DepartmentUseraccountController`                |
| `zmsdb\Department` + `Query\Department`                                   | `model/`, `repository/DepartmentRepository`          |
| `zmsdb\Link`, `DayOff`, `Scope`, `Cluster`, `Workstation`, `Organisation` | passende `repository/Department*`                    |
| `zmsdb\Useraccount` (Department-Queries)                                  | `repository/DepartmentUseraccountRepository`         |
| `zmsentities\Department` + `department.json`                              | `view/DepartmentView`                                |
| `zmsentities\Schema\Validator` + `Department::testValid()`                | `validation/ValidateDepartment`                      |
| —                                                                         | `validation/DepartmentValidationService`             |

#### Heute — PHP-Stack durchklicken

Den **vollständigen Department-Slice** im Monorepo: **`zmsentities`**-Schema und -Entity, alle **`zmsdb`**-Query-Klassen für CRUD und Resolved References, alle **`zmsapi`**-Controller und den **`routing.php`**-Auszug mit jedem Department-Endpoint.

<DepartmentCodeExplorerToday />

#### Ziel — Spring-Boot-Modul in `zmsbackend`

Das **vollständig übersetzte Modul** durchklicken: **`api/`**-Controller für jeden Endpoint, **`repository/`**-Klassen für jede Query-Schicht, **`model/`**-JPA-Entities, **`view/`**-API-Typen, **`service/`** (ein Service pro Operation) und **`validation/`**-Java-Validatoren (kein JSON Schema auf dem Server).

<DepartmentCodeExplorerTarget />

Explorer-Daten nach PHP- oder Java-Änderungen neu erzeugen: `npm run docs:department-explorers` in `docs/`.

Frontends wie `zmsadmin` und `zmsstatistic` behandeln API-Payloads weiter als `zmsentities`-Typen; nur der erzeugende Backend-Service ändert sich.

---

## `zmscitizenbackend`

Eigenes **bürgerorientiertes** Backend (heute: PHP-Modul **`zmscitizenapi`**).

### Heute

| Paket                 | Rolle                                                                                                  |
| --------------------- | ------------------------------------------------------------------------------------------------------ |
| `zmscitizenapi`       | Controller, Services, Citizen-Modelle (`Office`, `Service`, `ThinnedScope`, …)                         |
| `ZmsApiClientService` | HTTP-Client zu **`zmsapi`** — lädt volle **`zmsentities`**-Graphen (`Provider`, `Scope`, `Process`, …) |
| `ZmsApiFacadeService` | Orchestriert mehrere **`zmsapi`**-Aufrufe, merged Listen, Second-Level-Cache                           |
| `MapperService`       | Mappt große **`zmsentities`**-Payloads auf schlanke Citizen-Modelle                                    |
| `zmsentities`         | Teilweise gemeinsame Typen; Citizen-Modelle überwiegend separat                                        |

Kerndaten werden **über HTTP-Aufrufe an `zmsapi`** geladen, nicht über eine eigene Query-Schicht. Typische Flows holen **überdimensionierte Admin-Entitäten**, dann projiziert **`MapperService`** sie in **`Office`**, **`ThinnedScope`**, **`Service`** und ähnliche Citizen-DTOs — oft nach **mehreren Roundtrips** über **`ZmsApiFacadeService`** (~900 Zeilen heute).

### Ziel

`zmscitizenbackend` behält **eigene bürgerorientierte Modelle** (`Office`, `Service`, `ThinnedScope`, …) in `model/` und `view/`. Es **ruft `zmsbackend` (früher `zmsapi`) nicht per HTTP auf**, sondern nutzt eine **eigene Repository-Schicht** — dasselbe Prinzip wie `zmsdb` im PHP-Stack heute: SQL- (bzw. JPA-) Queries im Besitz des Citizen-Backends, gegen das **gemeinsame MySQL-Schema**.

Gleiches Vertical-Slice-Layout wie `zmsbackend`: `api/`, `model/`, `repository/`, `service/`, `view/`, `exception/` pro Domain (z. B. `office/`, `appointment/`).

Citizen-Modelle bleiben **schlank und API-spezifisch**. Sie müssen `zmsentities`-Schemas nicht eins zu eins abbilden; `zmsbackend` liefert bei Bedarf vollständigere, schema-kompatible Payloads.

### Warum `zmscitizenbackend`?

Vorteile, **`zmscitizenapi`** und den **`zmsapi`**-Client-Stack durch einen Spring-Boot-Service mit eigener Persistenz zu ersetzen:

1. **Kein Mapping mehr: kleine Citizen-Modelle aus riesigen Admin-Entitäten** — Heute durchläuft **`MapperService`** (~640 Zeilen) volle **`zmsentities`**-Graphen (`Provider`, `Scope`, `Process`, `Request`, …) und kopiert Felder manuell in **`Office`**, **`ThinnedScope`**, **`ThinnedProcess`** usw. In **`zmscitizenbackend`** laden Repositories und **`view/`**-Typen **nur das, was die Citizen-API exponiert** — Mapping ist Query-Design, keine wartungsintensive Übersetzungsschicht.

2. **Weg mit dem zweiten API-Hop** — Jeder Citizen-Lese-/Schreibzugriff läuft heute **`zmscitizenapi` → HTTP → `zmsapi` → `zmsdb` → MySQL**, mit JSON en-/decodieren auf beiden Seiten. **`zmscitizenbackend`** spricht **direkt** mit der Datenbank (`service/` → `repository/` → `model/`). Weniger Netzwerk-Hops, weniger Serialisierung, geringere Latenz auf Buchungs- und Verfügbarkeits-Hotpaths.

3. **Maßgeschneiderte Queries statt „alles laden, in PHP filtern“** — **`ZmsApiFacadeService`** holt oft **komplette Provider- und Scope-Listen** von **`zmsapi`**, merged sie im Speicher und cached das Ergebnis. Citizen-Backends können **fokussiertes JPA/SQL** (Joins, Projektionen, Pagination) für Offices-by-Service, verfügbare Tage und Slots nutzen — **weniger Daten, weniger CPU fürs DTO-Formen**.

4. **Weniger Klebe-Code** — **`ZmsApiClientService`**, **`ZmsApiFacadeService`** und **`MapperService`** hängen eng an **`zmsapi`**-Routen und **`zmsentities`**-Formen. Schema- oder Endpoint-Änderungen in der Admin-API wirken in Citizen-Mapping und Cache-Keys nach. Eigene Repositories **entkoppeln** den öffentlichen Citizen-Vertrag von der internen Admin-API-Evolution.

5. **Einfachere Cache-Strategie** — Second-Level-Caches (`processed_offices`, `processed_scopes`, …) amortisieren vor allem **HTTP + Mapping**. Direkte Reads machen Caching **optional und gezielt** (z. B. heiße Office-Listen) statt Pflicht für akzeptable Antwortzeiten.

6. **Weniger Fehlerquellen für Bürger:innen** — Citizen-Buchung hängt nicht mehr davon ab, dass **`zmsapi`** unter Admin-Last (Reports, Massenbearbeitung, Statistik) schnell und verfügbar bleibt. **`zmscitizenbackend`** skaliert und fällt auf seinen Lese-/Schreibpfaden unabhängig aus.

7. **Gleicher RefArch-Stack wie `zmsbackend` und `refarch-gateway`** — Ein Maven-Projekt, JUnit/Spring Boot Test, Actuator-Metriken, gemeinsame Münchner CI/Container-Muster — kein separates PHP-Modul plus HTTP-Client-Konfiguration (`ZMS_API_URL`, **`zmsclient`**-artige Plumbing).

8. **Klare Verantwortung für die Citizen-Domain** — Vertical Slices (`office/`, `appointment/`, `availability/`) ersetzen eine monolithische Fassade. Jedes Feature besitzt **API, Service, Repository und View** statt neue Zweige in gemeinsamen Mapper-/Facade-Klassen.

9. **Hotpaths gezielt optimierbar** — Verfügbarkeits- und Reservierungsflows können **eigene Read-Modelle und Indizes** bekommen, ohne neue **`zmsapi`**-Endpoints oder aufgeblähte Admin-Entitäten, die Frontends nie sehen.

10. **Einfacher testbar** — Repository- und Service-Tests gegen das gemeinsame Schema ersetzen aufwändiges Mocken von **`ZmsApiClientService`**-HTTP-Antworten und Mapper-Randfällen; ATAF/REST Assured können einen Citizen-Spring-Service End-to-End ansprechen.

---

## Migrationshinweise

1. **Domain für Domain** — zuerst `Department*`-Endpoints auf `zmsbackend` migrieren; PHP-Controller bis Parity-Tests behalten.
2. **Eine Datenbank** — beide Backends lesen dasselbe Schema; Tabellenumbenennungen laufen über Migrationen (siehe Datenbank-Refactor-Dokument).
3. **`zmsentities`-Schemas für Frontends behalten** — `zmsbackend` validiert mit Java auf `view/`-Typen, kein JSON Schema in Spring.
4. **Paket-Layout** — pro Domain auf beiden Backends: `api/` → `service/` → `repository/` → `model/`, API-Typen in `view/`.

Verwandt: [ZMS-Architektur modernisieren (3–5-Jahresplan)](./product-oriented-refarch-roadmap.md) · [Datenbanktabellen- und Feldbenennung standardisieren](./database-refactor/standardize-database-table-and-field-naming.md)
