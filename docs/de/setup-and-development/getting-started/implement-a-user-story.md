# Wie setze ich eine User Story in ZMS um?

In jedem Stack — auch in ZMS — beginnst du am **unteren Ende des Stacks** und arbeitest dich nach oben. In ZMS ist das die Backend-Schicht **`zmsbackend`**.

Bevor du Bürgeransicht, Admin-UI oder reine Durchreich-APIs anfasst, stelle die Frage, ob die Story überhaupt eine Backend-Änderung braucht. Wenn ja, setze so nah wie möglich an der **Datenbankschicht** an.

## Entscheidungsbaum

```mermaid
flowchart TD
  start([Start der User Story]) --> backend{"Müssen wir etwas im<br/>Backend ändern<br/><code>zmsbackend</code>?"}
  backend -->|Nein| frontendFork
  backend -->|Ja| dbLayer

  subgraph databaseLayer ["Datenbankschicht — am nächsten an den Daten"]
    direction TB
    dbLayer[So nah wie möglich an der<br/>Datenbankschicht starten]
    dbLayer --> migrations{"Brauchen wir<br/>Migrationen?"}
    migrations -->|Nein| repos
    migrations -->|Ja| migTypes[Alltägliche Migrationstypen]
    migTypes --> schema{"Datenbankstruktur ändern?<br/>neue Spalten / Tabellen"}
    migTypes --> data{"Daten in bestehende<br/>Strukturen einfügen?"}
    schema -->|Ja| expandContract[Schema-Migration<br/>Expand / Contract]
    data -->|Ja| dataMig[Daten-Migration<br/>im bestehenden Schema]
    expandContract --> bothNote[Eine Story kann beides brauchen]
    dataMig --> bothNote
    bothNote --> repos
    repos[Als Nächstes: Repository-Conditions<br/><code>Repository</code>-Ordner in <code>zmsbackend</code>]
    repos --> conditions{"Brauche ich neue Conditions<br/>im Repository, damit ich<br/>Queries in der Service-Schicht<br/>ändern oder bauen kann?"}
    conditions -->|Struktur-Migration| alwaysCond[Immer ja — Conditions anlegen oder anpassen]
    conditions -->|Nur Daten-Migration| maybeDataCond[Ja oder nein]
    conditions -->|Keine Migration| maybeNoMigCond[Ja oder nein]
    alwaysCond --> serviceLayer[Dann Queries in<br/><code>Service</code>-Ordnern zusammensetzen]
    maybeDataCond --> serviceLayer
    maybeNoMigCond --> serviceLayer
  end

  serviceLayer --> entitiesQ

  subgraph entitiesLayer ["Entities und API — Schema, dann Controller"]
    direction TB
    entitiesQ{"Muss ich das JSON-Schema / Modell<br/>in <code>zmsentities</code> ändern oder<br/>ergänzen, damit ich die Änderung<br/>in der API-Controller-Response<br/>propagieren kann?"}
    entitiesQ -->|Ja| schemaModel[Schema in <code>zmsentities</code> anpassen<br/>wird später zum Modell]
    entitiesQ -->|Nein| maybeController[Controller kann sich auch ohne<br/>Schema-Änderung ändern]
    schemaModel --> apiController[In API-Controller propagieren<br/><code>Api</code>-Ordner in <code>zmsbackend</code>]
    maybeController --> apiController
    apiController --> apiElse{"Muss ich sonst noch etwas<br/>in der <code>zmsbackend</code>-<br/>API-Schicht ändern?"}
    apiElse -->|Ja| apiMore[Neuer Controller → in<br/><code>zmsbackend/routing.php</code> eintragen<br/>plus Request-Handling, Statuscodes, …]
    apiElse -->|Nein| apiDone[API-Schicht für diese Story fertig]
    apiMore --> apiDone
  end

  apiDone --> frontendFork

  subgraph clientsLayer ["Oberhalb der API — welcher Pfad?"]
    direction TB
    frontendFork{"Legacy-Frontend-Module<br/>oder Citizen-Stack?"}
    frontendFork -->|Legacy| legacyMods["zmsadmin · zmsstatistic<br/>zmsticketprinter · zmscalldisplay<br/>zmsmessaging"]
    frontendFork -->|Citizen| citizenApiQ
  end

  legacyMods --> laterLegacy[Legacy-Modul-Details<br/>— folgt in späteren Schritten]

  subgraph citizenApiLayer ["Citizen-API — zmscitizenapi, weiterhin ein Backend"]
    direction TB
    citizenApiQ{"Müssen wir etwas in<br/><code>zmscitizenapi</code> ändern?"}
    citizenApiQ -->|Nein| citizenViewLater
    citizenApiQ -->|Ja| buildsOn["Ruft <code>zmsbackend</code> auf<br/>über ZmsApiClientService<br/>kein direkter Datenbankzugriff"]
    buildsOn --> citizenSchema{"Neues oder geändertes<br/>Citizen-Schema und Modell?"}
    citizenSchema -->|Ja| schemaFiles["Schema in<br/><code>zmsentities/schema/citizenapi/</code><br/>Modell in <code>Models/</code>"]
    citizenSchema -->|Nein| servicesQ
    schemaFiles --> servicesQ{"Backend-Aufruf, Mapping<br/>oder Validierung ändern?"}
    servicesQ -->|Ja| services["Client, Facade, Mapper,<br/>Domain-Service, Validierung"]
    servicesQ -->|Nein| errorsQ
    services --> errorsQ{"Fehler, den die UI<br/>als Callout zeigen muss?"}
    errorsQ -->|Ja| errorCatalog["<code>ErrorMessages</code>:<br/>errorCode, Meldung,<br/>Status, errorType"]
    errorsQ -->|Nein| controllers
    errorCatalog --> controllers["Controller liefert das Modell<br/>oder ein errors-Array"]
    controllers --> routeQ{"Neuer Endpunkt?"}
    routeQ -->|Ja| routing["Eintragen in<br/><code>zmscitizenapi/routing.php</code>"]
    routeQ -->|Nein| citizenDone
    routing --> citizenDone["Citizen-API bereit für<br/><code>zmscitizenview</code>"]
  end

  citizenDone --> citizenViewLater["<code>zmscitizenview</code><br/>— folgt in einem späteren Schritt"]

  style databaseLayer fill:#e3f2fd,stroke:#0277bd,stroke-width:2px,color:#01579b
  style entitiesLayer fill:#e0f2f1,stroke:#00897b,stroke-width:2px,color:#00695c
  style clientsLayer fill:#fff3e0,stroke:#ef6c00,stroke-width:2px,color:#e65100
  style citizenApiLayer fill:#ede7f6,stroke:#5e35b1,stroke-width:2px,color:#311b92
  classDef dbNode fill:#bbdefb,stroke:#0277bd,stroke-width:1px,color:#0d47a1
  classDef entitiesNode fill:#b2dfdb,stroke:#00897b,stroke-width:1px,color:#004d40
  classDef clientsNode fill:#ffe0b2,stroke:#ef6c00,stroke-width:1px,color:#e65100
  classDef citizenApiNode fill:#d1c4e9,stroke:#5e35b1,stroke-width:1px,color:#311b92
  class dbLayer,migrations,migTypes,schema,data,expandContract,dataMig,bothNote,repos,conditions,alwaysCond,maybeDataCond,maybeNoMigCond,serviceLayer dbNode
  class entitiesQ,schemaModel,maybeController,apiController,apiElse,apiMore,apiDone entitiesNode
  class frontendFork,legacyMods,laterLegacy clientsNode
  class citizenApiQ,buildsOn,citizenSchema,schemaFiles,servicesQ,services,errorsQ,errorCatalog,controllers,routeQ,routing,citizenDone citizenApiNode
```

<div class="story-layer story-layer--database">

## Datenbankschicht

Dieser Abschnitt deckt alles an oder nahe den Daten ab: Migrationen, Repository-Conditions und die Service-Methoden, die diese Conditions zu Queries zusammensetzen. Höhere Schichten folgen als Nächstes auf dieser Seite.

### Zuerst das Backend

**Frage 1: Müssen wir etwas im Backend ändern?**

- **Nein** — hier verlässt du `zmsbackend`. Der Backend-Ja/Nein-Pfad **schließt**, und du gehst weiter unter [Oberhalb der API](#oberhalb-der-api).
- **Ja** — bleib in `zmsbackend` und starte nahe an den Daten: Schema und Persistenz vor Services, APIs oder Frontends, die davon abhängen.

### Als Nächstes Migrationen

**Frage 2: Brauchen wir Migrationen?**

Wenn die Story ändert, wie Daten gespeichert, strukturiert oder initial befüllt werden, beginne mit Datenbank-Migration(en), bevor du abhängigen Code schreibst.

Wie du Migrationen lokal ausführst, steht unter [Datenbank-Migrationen](/de/setup-and-development/database-migrations).

#### Alltägliche Migrationstypen

Für den Alltag gibt es zwei Arten von Migrationen. Eine Story kann **eine oder beide** brauchen.

1. **Strukturänderungen** — Tabellen und Spalten anlegen oder ändern (neue Tabellen, neue Spalten, Umbenennungen, Drops und Ähnliches). Das sind die Schema-Migrationen, die du bei rolloutsicheren Änderungen in **Expand** und **Contract** aufteilst. Details: [Expand und Contract](/de/setup-and-development/database-migrations#expand-und-contract).

2. **Daten in bestehenden Strukturen** — Zeilen in bereits vorhandenen Tabellen einfügen oder aktualisieren (Stammdaten, Flags, Seed-Zeilen, Backfills ohne neue Spalte). Das Schema bleibt gleich; nur der Inhalt ändert sich.

Stelle bei jeder Story mit Migrationen beide Fragen: _Ändern wir die Struktur?_ und _Fügen wir Daten in bestehende Strukturen ein oder ändern wir sie?_ Schreibe danach die passenden Migrationsdatei(en), bevor du im Stack nach oben gehst.

### Dann Repositories, dann Services

Repositories sind die **Bausteine**: kleine wiederverwendbare Teile (vor allem `addCondition…`-Methoden, Mappings und Joins), die wissen, wie man mit Tabellen spricht. Services liegen eine Schicht darüber in den zugehörigen `Service`-Ordnern und **setzen** diese Bausteine zu den Queries zusammen, die die Story braucht.

Typisches Layout:

- `zmsbackend/src/Zmsbackend/.../Repository/` — Conditions und Mapping-Bausteine
- `zmsbackend/src/Zmsbackend/.../Service/` — baut oder ändert Queries, indem Repository-Conditions verkettet werden

**Frage 3: Brauche ich neue Conditions im Repository, damit ich Queries in der Service-Schicht ändern oder neu bauen kann?**

| Was die Story an der DB gemacht hat                                   | Neue / geänderte Repository-Conditions?                                                                                     |
| --------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| Struktur-Migration (neue/geänderte Spalten oder Tabellen)             | **Immer ja** — der Service kann die neue Form nicht selektieren, filtern oder schreiben ohne passende Repository-Bausteine. |
| Nur Daten-Migration (neue/geänderte Zeilen in bestehenden Strukturen) | **Ja oder nein** — ja, wenn der Service diese Daten anders filtern oder joinen muss; sonst reichen vorhandene Conditions.   |
| Gar keine Migration                                                   | **Ja oder nein** — die Story kann trotzdem einen neuen Filter, Join oder Lesepfad brauchen, den der Service zusammensetzt.  |

Lege bei Bedarf zuerst Repository-Conditions an oder passe sie an, und ändere oder ergänze danach die Service-Methoden, die die Query aus diesen Conditions bauen. Weiter mit Entities und der API.

</div>

<div class="story-layer story-layer--entities">

## Entities und API

Nachdem die Datenbankschicht die Daten laden und formen kann, entscheide, ob der **gemeinsame Contract** geändert werden muss, damit Controller ihn ausliefern können. In ZMS liegt dieser Contract in **`zmsentities`** als JSON Schema (und dem typisierten Modell, das daraus entsteht). Controller in `zmsbackend` setzen diese Form dann in die API-Response.

Typisches Layout:

- `zmsentities/schema/` — JSON Schema für Entities (und zugehörige Citizen-API-Schemas)
- `zmsentities` PHP-Entity-Klassen — die Modelle zu diesen Schemas
- `zmsbackend/src/Zmsbackend/.../Api/` — API-Controller, die diese Entities in Responses zurückgeben
- `zmsbackend/routing.php` — Slim-Routen, die URL-Pfade an diese Controller binden

**Frage 4: Muss ich das JSON-Schema / Modell in `zmsentities` ändern oder ergänzen, damit ich die Änderung in der API-Controller-Response propagieren kann?**

- **Ja** — zuerst Schema in `zmsentities` anpassen oder anlegen (daraus wird später das Modell), danach den API-Controller so anpassen, dass die Response die neuen oder geänderten Felder trägt.
- **Nein** — du kannst trotzdem einen API-Controller anfassen (Routing, Statuscodes, anderer Service-Aufruf), ohne das Entity-Schema zu ändern.

**Frage 5: Muss ich sonst noch etwas in der `zmsbackend`-API-Schicht ändern?**

Wenn die Response-Form stimmt, mache einen letzten Check der API-Oberfläche in `zmsbackend` (typisch unter `.../Api/`):

- **neuer Controller** → du **musst** ihn in [`zmsbackend/routing.php`](https://github.com/it-at-m/eappointment/blob/main/zmsbackend/routing.php) registrieren (Slim-Route → Controller-Klasse); die Controller-Datei allein reicht nicht
- neue oder geänderte Routen / Endpunkte für bestehende Controller (ebenfalls in `routing.php`)
- Request-Parsing oder Validierung
- welcher Service-Aufruf im Controller
- HTTP-Statuscodes, Fehler oder Auth-/Rechteprüfungen
- verwandte Controller, die denselben Contract einhalten müssen

- **Ja** — schließe diese API-Schicht-Änderungen ab (inkl. `routing.php`, wenn du einen Controller hinzugefügt hast), bevor du `zmsbackend` verlässt.
- **Nein** — die API-Schicht ist für diese Story fertig.

Beide Antworten **schließen** den Backend-Pfad für diese Story. Weiter unter [Oberhalb der API](#oberhalb-der-api).

</div>

<div class="story-layer story-layer--clients">

## Oberhalb der API

Hier treffen sich Frage 1 (**Backend ja/nein**) und Frage 5 (**API fertig**). Ab hier wählst du, welchen Pfad die Story braucht. Ob etwas in `zmsbackend` geändert werden muss, ist hier abgeschlossen.

**Frage 6: Legacy-Frontend-Module oder Citizen-Stack?**

| Pfad                 | Module                                                                           | Wann                                                                                                                            |
| -------------------- | -------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| **Legacy-Frontends** | `zmsadmin`, `zmsstatistic`, `zmsticketprinter`, `zmscalldisplay`, `zmsmessaging` | Mitarbeiter-/Betriebs-UIs und zugehörige Legacy-PHP-Frontends, die mit `zmsbackend` sprechen                                    |
| **Citizen-Stack**    | `zmscitizenapi` → `zmscitizenview`                                               | Öffentlicher Buchungsflow. Der nächste Schritt ist weiterhin ein Backend: [`zmscitizenapi`](#citizen-api), danach die Bürger-UI |

Eine Story kann einen Pfad, beide oder keinen (nur Backend) betreffen. Details zu den Legacy-Modulen folgen in einem späteren Schritt. Der Citizen-Pfad geht unten weiter.

</div>

<div class="story-layer story-layer--citizenapi">

## Citizen-API

`zmscitizenapi` sitzt über der ZMS-API und ist weiterhin ein Backend. Es liest und schreibt die Datenbank nicht. Es ruft `zmsbackend` auf, mappt die vollen Entities auf einen kleineren Citizen-Vertrag, und genau diesen Vertrag rendert `zmscitizenview` — einschließlich der Fehler-Payload hinter den Callout-Boxen.

### Baut auf dem Backend auf

**Frage 7: Müssen wir etwas in `zmscitizenapi` ändern?**

- **Nein** — dieses Modul bleibt unverändert. Weiter mit `zmscitizenview`, sobald dieser Schritt dokumentiert ist, oder hier aufhören, wenn die Story schon fertig ist.
- **Ja** — hier bleiben. Das untere Ende dieses Moduls ist der HTTP-Aufruf nach `zmsbackend`, keine Migration.

So ist der Aufruf aufgebaut:

- `ZmsApiClientService` führt die HTTP-Aufrufe aus und bekommt volle `zmsentities` zurück, zum Beispiel `Process`, `Scope`, `Provider` und `Calendar`.
- `ExceptionService` übersetzt Backend-Exceptions (`ProcessNotFound`, `EmailRequired` und die weiteren, die es mappt) in Citizen-Fehlercodes. Die UI sieht den Namen der Backend-Exception nicht.
- `ZmsApiFacadeService` orchestriert diese Aufrufe, cached Stammdaten (Standorte, Dienstleistungen, Scopes) und lässt den Mapper Citizen-Modelle bauen.
- `MapperService` ist die Übersetzung. `processToThinnedProcess` (und die passenden Schreiber in der Gegenrichtung) macht aus einem vollen Process einen `ThinnedProcess`, der nur enthält, was eine Bürgerin oder ein Bürger sehen darf.

Typische Ablage:

- `zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiClientService.php` — HTTP-Client zu `zmsbackend`
- `zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiFacadeService.php` — Orchestrierung und Cache
- `zmscitizenapi/src/Zmscitizenapi/Services/Core/MapperService.php` — Backend-Entity ↔ Citizen-Modell
- `zmscitizenapi/src/Zmscitizenapi/Services/…` — Domain-Services, die Controller aufrufen (`Appointment`, `Office`, `Availability`, `Captcha`)

### Eigene Schemas und Modelle

Das sind andere Schemas als die internen Entity-Schemas aus Frage 4. Die Citizen-Response hat ihr eigenes JSON-Schema und ihre eigenen PHP-Modelle.

**Frage 8: Muss ich ein Citizen-JSON-Schema und ein Modell ändern oder ergänzen?**

- **Ja** — zuerst das Schema in `zmsentities/schema/citizenapi/` anlegen oder anpassen, danach das PHP-Modell in `zmscitizenapi/src/Zmscitizenapi/Models/`. Jedes Modell erweitert `BO\Zmsentities\Schema\Entity`, setzt `public static $schema` auf diese Datei (zum Beispiel `citizenapi/thinnedProcess.json`) und verweigert die Konstruktion, wenn `testValid()` fehlschlägt.
- **Nein** — Services und Controller können sich ändern, während die Response-Form gleich bleibt.

Ein Feld, das Frage 4 an einer Backend-Entity ergänzt, erscheint in der Citizen-Response erst, wenn der Mapper es auf ein Citizen-Modell kopiert, dessen Schema das Feld erlaubt.

### Services, dann Controller

**Frage 9: Muss ich ändern, wie wir das Backend aufrufen, das Ergebnis mappen oder den Request validieren?**

Domain-Services (zum Beispiel `AppointmentByIdService`) sind das, was Controller aufrufen. Sie validieren die Eingabe über `ValidationService`, rufen die Facade auf und liefern entweder ein Citizen-Modell oder `['errors' => […]]`.

| Was die Story braucht                                         | Wo                                                                                |
| ------------------------------------------------------------- | --------------------------------------------------------------------------------- |
| Neuer oder geänderter Aufruf nach `zmsbackend`                | `ZmsApiClientService`, danach die Facade-Methode, die ihn benutzt                 |
| Andere Felder in der Citizen-Response                         | `MapperService`, nach Schema und Modell aus Frage 8                               |
| Neues Buchungs-, Standort-, Kalender- oder Captcha-Verhalten  | Domain-Service unter `Services/`                                                  |
| Ungültige Eingabe ablehnen, bevor das Backend aufgerufen wird | `ValidationService` — ein errors-Array zurückgeben und das Backend nicht aufrufen |

**Frage 10: Kann das so fehlschlagen, dass die Bürger-UI es als Callout zeigen muss?**

`zmscitizenview` liest den ersten Eintrag des `errors`-Arrays und rendert ein Callout (`muc-callout`). Der Katalog liegt in `zmscitizenapi/src/Zmscitizenapi/Utils/ErrorMessages.php`. Jeder Eintrag hat vier Felder:

| Feld           | Rolle                                                                                                                               |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `errorCode`    | Stabile Id. Die View mappt sie auf übersetzte Überschrift und Text (`apiError…Header` / `apiError…Text` in `zmscitizenview`).       |
| `errorMessage` | Englischer Text am Code. Den Satz, den Bürgerinnen und Bürger lesen, liefert die View-Übersetzung, nachgeschlagen über `errorCode`. |
| `statusCode`   | HTTP-Status. Der Controller antwortet mit dem höchsten Status im Array.                                                             |
| `errorType`    | Callout-Art: `error`, `warning` oder `info`. Die View gibt das an das Callout weiter.                                               |

`BaseController::createJsonResponse` füllt einen zurückgegebenen Fehler aus diesem Katalog. Kommt der Fehler aus `zmsbackend`, ergänze oder passe das Mapping in `ExceptionService` an, damit derselbe Katalogeintrag verwendet wird.

```json
{
  "errors": [
    {
      "errorCode": "appointmentNotFound",
      "errorMessage": "Maybe you have already canceled your appointment? Otherwise, please check that you have used the correct link.",
      "statusCode": 404,
      "errorType": "error"
    }
  ]
}
```

Ein neuer `errorCode` wird erst dann ein konkretes Callout, wenn `zmscitizenview` den Code kennt (Error-State-Map und Übersetzungsschlüssel). Diese Verdrahtung gehört zum Schritt Bürgeransicht. Lege Code, Meldung, Status und `errorType` zuerst hier fest, damit die View einen Vertrag hat, an den sie sich binden kann.

**Frage 11: Brauche ich einen neuen oder geänderten Controller?**

Controller unter `zmscitizenapi/src/Zmscitizenapi/Controllers/` erweitern `BaseController`. Sie validieren den HTTP-Request, rufen einen Domain-Service auf und liefern entweder das Modell oder das errors-Array.

- **neuer Controller** → in [`zmscitizenapi/routing.php`](https://github.com/it-at-m/eappointment/blob/main/zmscitizenapi/routing.php) eintragen (Slim-Route → Controller-Klasse, mit dem OpenAPI-Block über der Route). Eine Controller-Datei allein reicht nicht.
- geänderte Route, Request-Auswertung oder Status-Behandlung an einem bestehenden Controller → diesen Controller anpassen und, wenn sich URL oder dokumentierte Response ändert, den passenden Block in `routing.php`.

So oder so **schließt** das die Citizen-API für diese Story. `zmscitizenapi` liefert jetzt, was `zmscitizenview` braucht. Als Nächstes kommt der Schritt Bürger-UI.

</div>
