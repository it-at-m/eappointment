---
outline: deep
---

# Tabellen- und Feld-Benennung standardisieren (inkl. Backend-Mappings)

### Problembeschreibung

<mark>**Hinweis: Mehrere Aufgaben zum Entfernen ungenutzter Tabellen und Spalten aus der Datenbank laufen bereits. SMS-/Benachrichtigungsfunktionen werden in diesen Issues bereinigt.**</mark>

**Das aktuelle Datenbankschema leidet unter uneinheitlichen Benennungskonventionen, die Wartung erschweren und die Codeklarheit verringern.**

<mark>
„Das folgende stammt aus Projekterfahrung und ist kein bloßer Vorschlag von KI. Eine Bereinigung würde die langfristigen Technologie-Schulden deutlich reduzieren. In den ersten 6–9 Monaten fiel es mir schwer, all das ungefähr im Kopf zu mappen.“ @ThomasAFink 
</mark>
<br />
<br />
<mark>
Hier schreiben.
</mark>
<br />
<br />
<mark>
Hier schreiben.
</mark>
<br />
<br />
<mark>
Hier schreiben.
</mark>
<br />
<br />
<mark>
Investition: Schema standardisieren und Abfragen aktualisieren <br />
Rendite:<br />
- Schnelleres Onboarding neuer Entwickler:innen (Monate pro Person)<br />
- Weniger Debug-Zeit (Stunden pro Issue)<br />
- Höhere Entwicklungsgeschwindigkeit (Zeitersparnis bei jedem Backend-Feature)<br />
- Weniger Fehlereinbau (vermeidet kostspielige Produktionsprobleme und Hotfixes)<br />
- Schnelleres Refactoring weiterer Teile später<br />
- Einfachere Test-Erstellung
</mark>
<br />
<br />

Die Themen umfassen:

**1. Sprachmischung (Deutsch ↔ Englisch)**

- Tabellennamen: `oeffnungszeit` (Deutsch) vs. `availability` (englisches Konzept)
- Tabellennamen: `standort` (Deutsch) vs. `scope` (englisches Konzept)
- Tabellennamen: `buerger` (Deutsch) vs. `citizen` (englisches Konzept)
- Tabellennamen: `feiertage` (Deutsch) vs. `holidays` (englisches Konzept)

**2. Inkonsistente Benennungskonventionen**

- Mischung aus camelCase und snake_case: `StandortID` vs. `scope_id`
- Gemischte Konventionen in derselben Tabelle: `contact__name` vs. `contact__email` vs. `StandortID`

**3. Konzeptionelle Inkonsistenzen**

- Dasselbe Konzept mit unterschiedlichen Namen: `availability`/`oeffnungszeit`, `scope`/`standort`
- Query-Klassen nutzen englische Namen, mappen aber auf deutsche Tabellennamen

### Aktuelle Beispiele

**Availability-Query-Klasse:**

```php
const TABLE = 'oeffnungszeit';  // German table name
// But maps to English field names:
'id' => 'availability.OeffnungszeitID',
'scope__id' => 'availability.StandortID',
```

**Scope-Query-Klasse:**

```php
const TABLE = 'standort';  // German table name
// Maps to mixed naming:
'id' => 'scope.StandortID',
'contact__name' => 'scopeprovider.name',
```

### Vorgeschlagene Lösung

**Standardisierungsregeln:**

1. **Sprache**: Alle Tabellen- und Spaltennamen auf Englisch
2. **Datenbankkonvention**: snake_case für alle Tabellen- und Spaltennamen
3. **Mapping-Konvention**: camelCase für alle Mapping-Variablen in Query-Klassen
4. **Konsistenz**: Konzeptnamen im gesamten Schema angleichen

**Migrationsplan:**

1. **Phase 1: Umbenennung von Tabellen**
   - `oeffnungszeit` → `availability`
   - `standort` → `scope`
   - `buerger` → `citizen`
   - `feiertage` → `holidays`
   - `gesamtkalender` → `calendar`

2. **Phase 2: Spalten-Standardisierung**
   - Alle Spaltennamen nach snake_case
   - Fremdschlüssel-Namen vereinheitlichen: `StandortID` → `scope_id`
   - Feldnamen angleichen: `OeffnungszeitID` → `availability_id`

3. **Phase 3: Aktualisierung der Query-Klassen**
   - Alle `Zmsdb/Query/*`-Klassen auf neue Tabellen-/Spaltennamen
   - Mapping-Variablen in camelCase: `scopeId` → `availability.scope_id`
   - Entity-Mapping-Methoden aktualisieren

### Erwartete Ergebnisse

- **Wartbarkeit**: Einheitliche Namen reduzieren kognitive Last
- **Klarheit**: Englische Namen verbessern internationale Zusammenarbeit
- **Angleichung**: Query-Klassen entsprechen dem tatsächlichen Schema
- **Zukunftssicherheit**: Standardkonventionen für neue Entwicklung
- **Testbarkeit**: Tests sind mit konsistenter Benennung einfacher zu schreiben und zu pflegen

### Hinweise zur Umsetzung

- Umfassende Migrationsskripte pro Tabelle erstellen
- Alle betroffenen Query-Klassen in `zmsbackend/src/Zmsbackend/Query/` aktualisieren
- Abwärtskompatibilität während der Übergangsphase sicherstellen
- Dokumentation und API-Referenzen aktualisieren

### Beispiel nach Standardisierung

**Availability-Query-Klasse (nachher):**

```php
const TABLE = 'availability';  // English snake_case table name
// Maps to camelCase variables:
'id' => 'availability.availability_id',
'scopeId' => 'availability.scope_id',
'startDate' => 'availability.start_date',
```

**Scope-Query-Klasse (nachher):**

```php
const TABLE = 'scope';  // English snake_case table name
// Maps to camelCase variables:
'id' => 'scope.scope_id',
'contactName' => 'scopeprovider.name',
```

Damit gilt:

- **Datenbank**: Alles snake_case (übliche SQL-Konvention)
- **Query-Mappings**: Alles camelCase (übliche PHP-Konvention)
- **Konsistenz**: Keine Ausnahmen, einheitliches Muster

# Vier Phasen:

1. Vollständiger Plan zur Tabellenumbenennung – alles Englisch, alles snake_case <mark>einfach</mark>

- 47 Tabellen von Deutsch nach Englisch
- Alle Tabellennamen auf snake_case vereinheitlicht
- Nach Geschäftspriorität sortiert (Kern → Benutzer → System → Technik)
- Klarer Migrationspfad mit Beispielen

2. Vollständiger Plan zur Spaltenumbenennung – alles Englisch, alles snake_case <mark>einfach–mittel</mark>

- Hunderte Spalten von Deutsch nach Englisch
- Alle Spaltennamen auf snake_case
- Fremdschlüssel-Namen tabellenübergreifend vereinheitlicht
- Gemeinsame Muster identifiziert und standardisiert

3. Vollständiger Plan zur Umbenennung der PHP-Variablen-Mappings – alles Englisch, alles camelCase <mark>schwer</mark>

- Alle Query-Klassen-Mappings nach camelCase
- Doppel-Unterstrich-Muster entfernt
- Verschachtelte Objektmuster standardisiert
- Referenz-Mappings aktualisiert

4. Langfristige Schema-Vision (über Umbenennung hinaus) <mark>strategisch</mark>

- Strukturelle Aufteilungen (`buerger`, `queue_number_statistics`, `preferences`, DLDB-`data`-Spalten)
- Tabellen-Disposition (löschen, prüfen, neu gestalten)
- Migrations-Benennung und Asset-Speicherung (S3 vs. `image_data`)

<mark>Die Abschnitte 1–3 fokussieren Namensvereinheitlichung. Abschnitt 4 hält größere Architekturänderungen fest, die parallel oder danach umgesetzt werden können.</mark>

<mark>Die Spaltenzuordnungen in Abschnitt 2 decken alle Tabellen im ZMS-Schema ab (siehe `.resources/zms.sql`). Die PHP-Variablen-Mappings in Abschnitt 3 werden weiterhin tabellenweise ergänzt.</mark>

## 1. Vollständiger Plan zur Tabellenumbenennung – alles Englisch, alles snake_case

### Phase 1: Kerngeschäfts-Tabellen (hohe Priorität)

| Current          | New (snake_case) | Reason                     |
| ---------------- | ---------------- | -------------------------- |
| `oeffnungszeit`  | `availability`   | Opening hours/availability |
| `standort`       | `scope`          | Location/scope             |
| `buerger`        | `citizen`        | Citizen                    |
| `feiertage`      | `holidays`       | Holidays                   |
| `gesamtkalender` | `calendar`       | Calendar                   |
| `behoerde`       | `department`     | Government department      |
| `organisation`   | `organization`   | Organization               |

### Phase 2: Benutzer- & Prozess-Tabellen

| Current              | New (snake_case)   | Reason                                                            |
| -------------------- | ------------------ | ----------------------------------------------------------------- |
| `buergeranliegen`    | `citizen_requests` | Citizen requests/issues                                           |
| `buergerarchiv`      | `citizen_archive`  | Archived citizen data                                             |
| `buergerarchivtoday` | — (delete)         | Redundanter Snapshot; in `citizen_archive` auflösen (Abschnitt 4) |
| `nutzer`             | `user`             | System users                                                      |
| `nutzerzuordnung`    | `user_assignment`  | User assignments                                                  |
| `kunde`              | `jurisdiction`     | Owner/jurisdiction (Entity/API today: `owner`)                    |
| `kundenlinks`        | — (delete)         | Unused; drop table and related code                               |

> **Hinweis:** Die Tabelle `kunde` und die Entity/API `owner` werden zu `jurisdiction` umbenannt (nicht `customer`). Die Permission `jurisdiction` (ZMSKVR-1345) führt diese Benennung im Berechtigungsmodell bereits ein; die Datenbank-Umbenennung folgt in diesem Refactor.
>
> **Hinweis:** Die Tabelle `kundenlinks` (Favoriten-Links) wird nicht mehr genutzt. Geplant sind Löschung der Tabelle sowie des zugehörigen Codes (z. B. `Link`-Entity, DB-Query, Admin-UI „Favoriten“).

### Phase 3: System- & Konfigurations-Tabellen

| Current            | New (snake_case)          | Reason                                                           |
| ------------------ | ------------------------- | ---------------------------------------------------------------- |
| `abrechnung`       | — (delete)                | Ungenutzt; Tabelle löschen (Abschnitt 4)                         |
| `ipausnahmen`      | `ip_exceptions`           | IP-Ausnahmen; Nutzung prüfen (Abschnitt 4)                       |
| `kiosk`            | `kiosk`                   | Kiosk (universal term)                                           |
| `wartenrstatistik` | `queue_number_statistics` | Wartestatistik; in kleinere Tabellen normalisieren (Abschnitt 4) |
| `standortcluster`  | `location_cluster`        | Location clustering                                              |
| `statistik`        | `statistics`              | Statistics                                                       |
| `role`             | `role`                    | RBAC roles (already snake_case)                                  |
| `permission`       | `permission`              | RBAC permissions (already snake_case)                            |
| `role_permission`  | `role_permission`         | Role–permission mapping (already snake_case)                     |
| `user_role`        | `user_role`               | User–role mapping (already snake_case)                           |

### Phase 4: API- & technische Tabellen

| Current     | New (snake_case) | Reason                                           |
| ----------- | ---------------- | ------------------------------------------------ |
| `apiclient` | `api_client`     | API client                                       |
| `apikey`    | `api_key`        | API-Key; Produktivnutzung prüfen (Abschnitt 4)   |
| `apiquota`  | `api_quota`      | API-Quota; Produktivnutzung prüfen (Abschnitt 4) |

### Phase 5: Kommunikations-Tabellen

| Current             | New (snake_case) | Reason                                        |
| ------------------- | ---------------- | --------------------------------------------- |
| `email`             | `email`          | Email (already snake_case)                    |
| `sms`               | `sms`            | SMS (already snake_case)                      |
| `mailpart`          | `mail_part`      | Mail part                                     |
| `mailqueue`         | `mail_queue`     | Mail queue                                    |
| `mailtemplate`      | `mail_template`  | Mail template                                 |
| `notificationqueue` | — (delete)       | Nutzung prüfen; Tabelle löschen (Abschnitt 4) |

### Phase 6: Daten- & Prozess-Tabellen

| Current             | New (snake_case)            | Reason                                                                 |
| ------------------- | --------------------------- | ---------------------------------------------------------------------- |
| `closures`          | `closures`                  | Already snake_case                                                     |
| `config`            | `config`                    | Already snake_case                                                     |
| `eventlog`          | `event_log`                 | Event-Log; Nutzungsumfang prüfen (Abschnitt 4)                         |
| `imagedata`         | `image_data`                | Bilddaten; Assets nach S3 (Abschnitt 4)                                |
| `log`               | `log`                       | `data`-JSON für Suche aufteilen (Abschnitt 4)                          |
| `migrations`        | `migrations`                | Already snake_case                                                     |
| `preferences`       | `scope_preferences` / split | Scope- und Systemeinstellungen; umbenennen und aufteilen (Abschnitt 4) |
| `process_sequence`  | `process_sequence`          | Already snake_case                                                     |
| `sessiondata`       | `session_data`              | Session data                                                           |
| `source`            | `source`                    | Already snake_case                                                     |
| `overview_calendar` | `overview_calendar`         | Overview calendar (already snake_case)                                 |

### Phase 7: Leistungs- & Anbieter-Tabellen

| Current            | New (snake_case)             | Reason                                                                 |
| ------------------ | ---------------------------- | ---------------------------------------------------------------------- |
| `provider`         | `office` (Kandidat)          | DLDB-Standort; an zmscitizenapi `office` anlehnen (Abschnitt 4)        |
| `request`          | `service` (Kandidat)         | DLDB-Dienstleistung; an zmscitizenapi `service` anlehnen (Abschnitt 4) |
| `request_provider` | `office_service` (Kandidat)  | Standort–Leistung-Verknüpfung; `data`-JSON aufteilen (Abschnitt 4)     |
| `request_variant`  | `service_variant` (Kandidat) | Leistungsvariante; Benennung mit Citizen API abstimmen (Abschnitt 4)   |

### Phase 8: Slot-System-Tabellen

| Current         | New (snake_case) | Reason             |
| --------------- | ---------------- | ------------------ |
| `slot`          | `slot`           | Already snake_case |
| `slot_hiera`    | `slot_hierarchy` | Slot hierarchy     |
| `slot_process`  | `slot_process`   | Already snake_case |
| `slot_sequence` | `slot_sequence`  | Already snake_case |

### Phase 9: Zuweisungs- & Clustering-Tabellen

| Current            | New (snake_case)     | Reason             |
| ------------------ | -------------------- | ------------------ |
| `clusterzuordnung` | `cluster_assignment` | Cluster assignment |

## 2. Vollständiger Plan zur Spaltenumbenennung – alles Englisch, alles snake_case

### Phase 1: Kerngeschäfts-Tabellen (hohe Priorität)

#### availability (formerly oeffnungszeit)

| Aktuelle Spalte              | Neue Spalte (snake_case)        | Grund                  |
| ---------------------------- | ------------------------------- | ---------------------- |
| `OeffnungszeitID`            | `availability_id`               | Primär-/Fremdschlüssel |
| `StandortID`                 | `scope_id`                      | Primär-/Fremdschlüssel |
| `Startdatum`                 | `start_date`                    | Namensstandardisierung |
| `Endedatum`                  | `end_date`                      | Namensstandardisierung |
| `allexWochen`                | `every_x_weeks`                 | Namensstandardisierung |
| `jedexteWoche`               | `every_other_week`              | Namensstandardisierung |
| `Wochentag`                  | `weekday`                       | Namensstandardisierung |
| `Anfangszeit`                | `start_time`                    | Namensstandardisierung |
| `Terminanfangszeit`          | `appointment_start_time`        | Namensstandardisierung |
| `Endzeit`                    | `end_time`                      | Namensstandardisierung |
| `Terminendzeit`              | `appointment_end_time`          | Namensstandardisierung |
| `Timeslot`                   | `time_slot`                     | Namensstandardisierung |
| `Anzahlarbeitsplaetze`       | `workstation_count`             | Namensstandardisierung |
| `Anzahlterminarbeitsplaetze` | `appointment_workstation_count` | Namensstandardisierung |
| `kommentar`                  | `comment`                       | Namensstandardisierung |
| `reduktionTermineImInternet` | `internet_reduction`            | Namensstandardisierung |
| `erlaubemehrfachslots`       | `multiple_slots_allowed`        | Namensstandardisierung |
| `reduktionTermineCallcenter` | `callcenter_reduction`          | Namensstandardisierung |
| `Offen_ab`                   | `open_from_days`                | Namensstandardisierung |
| `Offen_bis`                  | `open_until_days`               | Namensstandardisierung |
| `updateZeitstempel`          | `updated_at`                    | Zeitstempel            |

#### scope (formerly standort)

| Aktuelle Spalte                    | Neue Spalte (snake_case)       | Grund                  |
| ---------------------------------- | ------------------------------ | ---------------------- |
| `StandortID`                       | `scope_id`                     | Primär-/Fremdschlüssel |
| `BehoerdenID`                      | `department_id`                | Primär-/Fremdschlüssel |
| `InfoDienstleisterID`              | `info_provider_id`             | Primär-/Fremdschlüssel |
| `Hinweis`                          | `hint`                         | Namensstandardisierung |
| `Bezeichnung`                      | `name`                         | Namensstandardisierung |
| `Adresse`                          | `address`                      | Namensstandardisierung |
| `Stadtplanlink`                    | `city_map_link`                | Namensstandardisierung |
| `Bearbeitungszeit`                 | `processing_time`              | Namensstandardisierung |
| `Kennung`                          | `identifier`                   | Namensstandardisierung |
| `Termine_ab`                       | `appointments_from_days`       | Namensstandardisierung |
| `Termine_bis`                      | `appointments_until_days`      | Namensstandardisierung |
| `smswarteschlange`                 | `sms_queue`                    | Namensstandardisierung |
| `smswmsbestaetigung`               | `sms_wms_confirmation`         | Namensstandardisierung |
| `smsbenachrichtigungsfrist`        | `sms_notification_deadline`    | Namensstandardisierung |
| `smsbenachrichtigungstext`         | `sms_notification_text`        | Namensstandardisierung |
| `smsbestaetigungstext`             | `sms_confirmation_text`        | Namensstandardisierung |
| `wartenrsperre`                    | `queue_number_locked`          | Namensstandardisierung |
| `wartenrhinweis`                   | `queue_hint`                   | Namensstandardisierung |
| `notruffunktion`                   | `emergency_function`           | Namensstandardisierung |
| `notrufausgeloest`                 | `emergency_triggered`          | Namensstandardisierung |
| `notrufinitiierung`                | `emergency_initiation`         | Namensstandardisierung |
| `notrufantwort`                    | `emergency_response`           | Namensstandardisierung |
| `emailPflichtfeld`                 | `email_required`               | Namensstandardisierung |
| `anmerkungPflichtfeld`             | `comment_required`             | Namensstandardisierung |
| `anmerkungLabel`                   | `comment_label`                | Namensstandardisierung |
| `telefonPflichtfeld`               | `phone_required`               | Namensstandardisierung |
| `standortinfozeile`                | `location_info_line`           | Namensstandardisierung |
| `standortkuerzel`                  | `short_name`                   | Namensstandardisierung |
| `aufrufanzeigetext`                | `display_text`                 | Namensstandardisierung |
| `reservierungsdauer`               | `reservation_duration`         | Namensstandardisierung |
| `anzahlwiederaufruf`               | `recall_count`                 | Namensstandardisierung |
| `startwartenr`                     | `first_queue_number`           | Namensstandardisierung |
| `endwartenr`                       | `last_queue_number_limit`      | Namensstandardisierung |
| `letztewartenr`                    | `last_queue_number`            | Namensstandardisierung |
| `wartenrdatum`                     | `queue_number_date`            | Namensstandardisierung |
| `mehrfachtermine`                  | `multiple_appointments`        | Namensstandardisierung |
| `schreibschutz`                    | `write_protection`             | Namensstandardisierung |
| `ohnestatistik`                    | `without_statistics`           | Namensstandardisierung |
| `smskioskangebotsfrist`            | `sms_kiosk_offer_deadline`     | Namensstandardisierung |
| `emailstandortadmin`               | `admin_email`                  | Namensstandardisierung |
| `wartenummernkontingent`           | `queue_number_contingent`      | Namensstandardisierung |
| `vergebenewartenummern`            | `assigned_queue_numbers`       | Namensstandardisierung |
| `kundenbefragung`                  | `customer_survey`              | Namensstandardisierung |
| `kundenbef_label`                  | `customer_survey_label`        | Namensstandardisierung |
| `kundenbef_emailtext`              | `customer_survey_email_text`   | Namensstandardisierung |
| `telefonaktiviert`                 | `phone_enabled`                | Namensstandardisierung |
| `virtuellesachbearbeiterzahl`      | `virtual_processor_count`      | Namensstandardisierung |
| `datumvirtuellesachbearbeiterzahl` | `virtual_processor_count_date` | Namensstandardisierung |
| `smsnachtrag`                      | `sms_addition`                 | Namensstandardisierung |
| `loeschdauer`                      | `deletion_duration`            | Namensstandardisierung |
| `updateZeitstempel`                | `updated_at`                   | Zeitstempel            |
| `source`                           | `source`                       | Bereits snake_case     |
| `custom_text_field_label`          | `custom_text_field_label`      | Bereits snake_case     |
| `custom_text_field_active`         | `custom_text_field_active`     | Bereits snake_case     |
| `custom_text_field_required`       | `custom_text_field_required`   | Bereits snake_case     |
| `admin_mail_on_appointment`        | `admin_mail_on_appointment`    | Bereits snake_case     |
| `admin_mail_on_deleted`            | `admin_mail_on_deleted`        | Bereits snake_case     |
| `admin_mail_on_updated`            | `admin_mail_on_updated`        | Bereits snake_case     |
| `admin_mail_on_mail_sent`          | `admin_mail_on_mail_sent`      | Bereits snake_case     |
| `appointments_per_mail`            | `appointments_per_mail`        | Bereits snake_case     |
| `whitelisted_mails`                | `whitelisted_mails`            | Bereits snake_case     |
| `slots_per_appointment`            | `slots_per_appointment`        | Bereits snake_case     |
| `info_for_appointment`             | `info_for_appointment`         | Bereits snake_case     |
| `aktivierungsdauer`                | `activation_duration`          | Namensstandardisierung |
| `captcha_activated_required`       | `captcha_activated_required`   | Bereits snake_case     |
| `email_confirmation_activated`     | `email_confirmation_activated` | Bereits snake_case     |
| `custom_text_field2_label`         | `custom_text_field2_label`     | Bereits snake_case     |
| `custom_text_field2_active`        | `custom_text_field2_active`    | Bereits snake_case     |
| `custom_text_field2_required`      | `custom_text_field2_required`  | Bereits snake_case     |
| `info_for_all_appointments`        | `info_for_all_appointments`    | Bereits snake_case     |
| `last_display_number`              | `last_display_number`          | Bereits snake_case     |
| `max_display_number`               | `max_display_number`           | Bereits snake_case     |
| `display_number_prefix`            | `display_number_prefix`        | Bereits snake_case     |

#### process (formerly buerger)

| Aktuelle Spalte                  | Neue Spalte (snake_case)      | Grund                  |
| -------------------------------- | ----------------------------- | ---------------------- |
| `BuergerID`                      | `process_id`                  | Primär-/Fremdschlüssel |
| `StandortID`                     | `scope_id`                    | Primär-/Fremdschlüssel |
| `Datum`                          | `date`                        | Namensstandardisierung |
| `Uhrzeit`                        | `time`                        | Namensstandardisierung |
| `Name`                           | `name`                        | Namensstandardisierung |
| `Anmerkung`                      | `comment`                     | Namensstandardisierung |
| `Telefonnummer`                  | `phone`                       | Namensstandardisierung |
| `EMail`                          | `email`                       | Namensstandardisierung |
| `EMailverschickt`                | `email_sent_count`            | Namensstandardisierung |
| `Erinnerungszeitpunkt`           | `reminder_timestamp`          | Zeitstempel            |
| `SMSverschickt`                  | `sms_sent_count`              | Namensstandardisierung |
| `AnzahlAufrufe`                  | `call_count`                  | Namensstandardisierung |
| `Zeitstempel`                    | `timestamp`                   | Zeitstempel            |
| `IPAdresse`                      | `ip_address`                  | Namensstandardisierung |
| `IPTimeStamp`                    | `ip_timestamp`                | Zeitstempel            |
| `NutzerID`                       | `user_id`                     | Primär-/Fremdschlüssel |
| `aufruferfolgreich`              | `call_successful`             | Namensstandardisierung |
| `wsm_aufnahmezeit`               | `ticket_printer_capture_time` | Namensstandardisierung |
| `aufrufzeit`                     | `call_time`                   | Namensstandardisierung |
| `nicht_erschienen`               | `did_not_appear`              | Namensstandardisierung |
| `Abholer`                        | `pickup_person`               | Namensstandardisierung |
| `AbholortID`                     | `pickup_scope_id`             | Primär-/Fremdschlüssel |
| `wartenummer`                    | `queue_number`                | Namensstandardisierung |
| `vorlaeufigeBuchung`             | `provisional_booking`         | Namensstandardisierung |
| `hatFolgetermine`                | `follow_up_appointment_count` | Namensstandardisierung |
| `istFolgeterminvon`              | `follow_up_of_process_id`     | Primär-/Fremdschlüssel |
| `zustimmung_kundenbefragung`     | `survey_accepted`             | Namensstandardisierung |
| `telefonnummer_fuer_rueckfragen` | `callback_phone`              | Namensstandardisierung |
| `absagecode`                     | `auth_key`                    | Namensstandardisierung |
| `AnzahlPersonen`                 | `person_count`                | Namensstandardisierung |
| `updateZeitstempel`              | `updated_at`                  | Zeitstempel            |
| `apiClientID`                    | `api_client_id`               | Primär-/Fremdschlüssel |
| `custom_text_field`              | `custom_text_field`           | Bereits snake_case     |
| `showUpTime`                     | `show_up_time`                | Namensstandardisierung |
| `finishTime`                     | `finish_time`                 | Namensstandardisierung |
| `timeoutTime`                    | `timeout_time`                | Namensstandardisierung |
| `way_time`                       | `way_time`                    | Bereits snake_case     |
| `parked`                         | `parked`                      | Bereits snake_case     |
| `processing_time`                | `processing_time`             | Bereits snake_case     |
| `bestaetigt`                     | `confirmed`                   | Namensstandardisierung |
| `waiting_time`                   | `waiting_time`                | Bereits snake_case     |
| `wasMissed`                      | `was_missed`                  | Namensstandardisierung |
| `custom_text_field2`             | `custom_text_field2`          | Bereits snake_case     |
| `status`                         | `status`                      | Bereits snake_case     |
| `priority`                       | `priority`                    | Bereits snake_case     |
| `external_user_id`               | `external_user_id`            | Bereits snake_case     |
| `displayNumber`                  | `display_number`              | Namensstandardisierung |

##### Dereference-Payload in `Anmerkung` / Custom-Text-Feldern (technische Schuld)

Wenn ein Vorgang abgeschlossen oder soft-gelöscht wird, führt `Process::writeBlockedEntity()` `QUERY_DEREFERENCED` aus (`zmsbackend/src/Zmsbackend/Query/Process.php`). Dabei werden PII entfernt und `StandortID = 0`, `Name = 'dereferenced'` sowie `status = 'blocked'` gesetzt. Weil die Zeile danach keine nutzbare `scope_id` mehr hat, werden ursprünglicher Standort und Metadaten **in Freitextspalten per PHP `var_export()` serialisiert**:

| Spalte               | Geschrieben von                           | Payload-Struktur                                                    |
| -------------------- | ----------------------------------------- | ------------------------------------------------------------------- |
| `Anmerkung`          | `Process::toDerefencedAmendment()`        | `BuergerID`, `StandortID`, `Anmerkung`, `IPTimeStamp`, `LastChange` |
| `custom_text_field`  | `Process::toDerefencedCustomTextfield()`  | gleiches Muster mit `CustomTextfield`                               |
| `custom_text_field2` | `Process::toDerefencedCustomTextfield2()` | gleiches Muster mit `CustomTextfield2`                              |

Beispiel (Inhalt von `Anmerkung` nach Dereferenzierung):

```
array (
  'BuergerID' => 100000,
  'StandortID' => 1,
  'Anmerkung' => NULL,
  'IPTimeStamp' => 0,
  'LastChange' => '1970-01-01T01:00:00+01:00',
)
```

**Wo diese Payload wieder ausgelesen wird (String-Parsing, keine typisierten Spalten):**

- `CalculateDailyWaitingStatisticByCron::extractScopeFromAnmerkung()` — Regex über alle drei Spalten, wenn `StandortID = 0` (`zmsbackend/src/Zmsbackend/Helper/CalculateDailyWaitingStatisticByCron.php`)
- Ad-hoc-SQL in Wartungs-Migrationen (z. B. `SUBSTRING_INDEX` / `LIKE` auf `'StandortID' =>` in `Anmerkung` und Custom-Text-Feldern)
- Jeder Code-Pfad, der auf dereferenzierten Shell-Zeilen noch eine Standort-ID braucht, bevor der Cron sie löscht

**Warum das schlechte Praxis ist und im neuen Schema nicht fortgeschrieben werden darf:**

- **Falsche Spaltensemantik:** `Anmerkung` / Custom-Text-Felder sind nutzersichtbare Kommentare, kein Archiv oder Audit-Store.
- **Fragiles Parsing:** Standort und IDs werden per Regex/`SUBSTRING_INDEX` aus `var_export`-Text gewonnen; `NULL` oder überschriebene `StandortID` in der Zeichenkette bricht Folge-Jobs (z. B. Archive-Cron mit `NULL` in `buergerarchivtoday.StandortID`).
- **Dreifach redundante Payload:** dieselbe Array-Struktur in drei unabhängigen Spalten.
- **Keine Schema-Integrität:** Nichts verhindert Updates nach dem Finish, die den String zerstören oder `status` zurückdrehen, während `StandortID` bei `0` bleibt.

**Zielrichtung (beim Refactor von `process` / Archiv):**

- Dereference-Metadaten in **typisierten Spalten oder eigener Tabelle** (`process_dereference` / Audit: `process_id`, `scope_id`, `archived_at`, …).
- Kein `var_export`-Array mehr in `comment` / Custom-Text-Felder schreiben.
- Regex-basierte Standort-Wiederherstellung in Cron- und Statistik-Pfaden entfernen, sobald Shells echte FK- oder Archiv-Verknüpfung haben.

#### holidays (formerly feiertage)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `FeiertagID`        | `holiday_id`             | Primär-/Fremdschlüssel |
| `Datum`             | `date`                   | Namensstandardisierung |
| `Feiertag`          | `name`                   | Namensstandardisierung |
| `BehoerdenID`       | `department_id`          | Primär-/Fremdschlüssel |
| `updateZeitstempel` | `updated_at`             | Zeitstempel            |

#### calendar (formerly gesamtkalender)

| Aktuelle Spalte   | Neue Spalte (snake_case) | Grund              |
| ----------------- | ------------------------ | ------------------ |
| `id`              | `id`                     | Bereits snake_case |
| `scope_id`        | `scope_id`               | Bereits snake_case |
| `availability_id` | `availability_id`        | Bereits snake_case |
| `time`            | `time`                   | Bereits snake_case |
| `seat`            | `seat`                   | Bereits snake_case |
| `process_id`      | `process_id`             | Bereits snake_case |
| `slots`           | `slots`                  | Bereits snake_case |
| `status`          | `status`                 | Bereits snake_case |
| `updated_at`      | `updated_at`             | Bereits snake_case |

#### department (formerly behoerde)

| Aktuelle Spalte   | Neue Spalte (snake_case) | Grund                  |
| ----------------- | ------------------------ | ---------------------- |
| `BehoerdenID`     | `department_id`          | Primär-/Fremdschlüssel |
| `OrganisationsID` | `organization_id`        | Primär-/Fremdschlüssel |
| `KundenID`        | `jurisdiction_id`        | Primär-/Fremdschlüssel |
| `Name`            | `name`                   | Namensstandardisierung |
| `Adresse`         | `address`                | Namensstandardisierung |
| `Ansprechpartner` | `contact_person`         | Namensstandardisierung |
| `IPProtectZeit`   | `ip_protection_time`     | Namensstandardisierung |

#### organization (formerly organisation)

| Aktuelle Spalte       | Neue Spalte (snake_case)    | Grund                  |
| --------------------- | --------------------------- | ---------------------- |
| `OrganisationsID`     | `organization_id`           | Primär-/Fremdschlüssel |
| `InfoBezirkID`        | `info_district_id`          | Primär-/Fremdschlüssel |
| `KundenID`            | `jurisdiction_id`           | Primär-/Fremdschlüssel |
| `Organisationsname`   | `name`                      | Namensstandardisierung |
| `Anschrift`           | `address`                   | Namensstandardisierung |
| `kioskpasswortschutz` | `kiosk_password_protection` | Namensstandardisierung |

### Phase 2: Benutzer- & Prozess-Tabellen (mittlere Priorität)

#### citizen_requests (formerly buergeranliegen)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `BuergeranliegenID` | `citizen_request_id`     | Primär-/Fremdschlüssel |
| `BuergerID`         | `process_id`             | Primär-/Fremdschlüssel |
| `BuergerarchivID`   | `citizen_archive_id`     | Primär-/Fremdschlüssel |
| `AnliegenID`        | `request_id`             | Primär-/Fremdschlüssel |
| `source`            | `source`                 | Bereits snake_case     |

#### citizen_archive (formerly buergerarchiv)

| Aktuelle Spalte    | Neue Spalte (snake_case) | Grund                  |
| ------------------ | ------------------------ | ---------------------- |
| `BuergerarchivID`  | `citizen_archive_id`     | Primär-/Fremdschlüssel |
| `StandortID`       | `scope_id`               | Primär-/Fremdschlüssel |
| `Datum`            | `date`                   | Namensstandardisierung |
| `mitTermin`        | `with_appointment`       | Namensstandardisierung |
| `nicht_erschienen` | `did_not_appear`         | Namensstandardisierung |
| `Zeitstempel`      | `timestamp`              | Zeitstempel            |
| `waiting_time`     | `waiting_time`           | Bereits snake_case     |
| `AnzahlPersonen`   | `person_count`           | Namensstandardisierung |
| `processing_time`  | `processing_time`        | Bereits snake_case     |
| `name`             | `name`                   | Bereits snake_case     |
| `dienstleistungen` | `services`               | Namensstandardisierung |
| `way_time`         | `way_time`               | Bereits snake_case     |

#### citizen_archive_today (formerly buergerarchivtoday)

| Aktuelle Spalte    | Neue Spalte (snake_case) | Grund                  |
| ------------------ | ------------------------ | ---------------------- |
| `BuergerarchivID`  | `citizen_archive_id`     | Primär-/Fremdschlüssel |
| `StandortID`       | `scope_id`               | Primär-/Fremdschlüssel |
| `Datum`            | `date`                   | Namensstandardisierung |
| `mitTermin`        | `with_appointment`       | Namensstandardisierung |
| `nicht_erschienen` | `did_not_appear`         | Namensstandardisierung |
| `Zeitstempel`      | `timestamp`              | Zeitstempel            |
| `waiting_time`     | `waiting_time`           | Bereits snake_case     |
| `AnzahlPersonen`   | `person_count`           | Namensstandardisierung |
| `processing_time`  | `processing_time`        | Bereits snake_case     |
| `name`             | `name`                   | Bereits snake_case     |
| `dienstleistungen` | `services`               | Namensstandardisierung |
| `way_time`         | `way_time`               | Bereits snake_case     |

#### user (formerly nutzer)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `NutzerID`          | `user_id`                | Primär-/Fremdschlüssel |
| `Name`              | `name`                   | Namensstandardisierung |
| `Passworthash`      | `password_hash`          | Namensstandardisierung |
| `Frage`             | `security_question`      | Namensstandardisierung |
| `Antworthash`       | `answer_hash`            | Namensstandardisierung |
| `Berechtigung`      | `permission_level`       | Namensstandardisierung |
| `KundenID`          | `jurisdiction_id`        | Primär-/Fremdschlüssel |
| `BehoerdenID`       | `department_id`          | Primär-/Fremdschlüssel |
| `SessionID`         | `session_id`             | Primär-/Fremdschlüssel |
| `StandortID`        | `scope_id`               | Primär-/Fremdschlüssel |
| `Arbeitsplatznr`    | `workstation_number`     | Namensstandardisierung |
| `Datum`             | `date`                   | Namensstandardisierung |
| `Kalenderansicht`   | `calendar_view`          | Namensstandardisierung |
| `clusteransicht`    | `cluster_view`           | Namensstandardisierung |
| `notrufinitiierung` | `emergency_initiation`   | Namensstandardisierung |
| `notrufantwort`     | `emergency_response`     | Namensstandardisierung |
| `aufrufzusatz`      | `call_suffix`            | Namensstandardisierung |
| `lastUpdate`        | `last_update`            | Namensstandardisierung |
| `sessionExpiry`     | `session_expiry`         | Namensstandardisierung |

#### user_assignment (formerly nutzerzuordnung)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `nutzerid`      | `user_id`                | Primär-/Fremdschlüssel |
| `behoerdenid`   | `department_id`          | Primär-/Fremdschlüssel |

#### jurisdiction (formerly kunde)

| Aktuelle Spalte   | Neue Spalte (snake_case) | Grund                  |
| ----------------- | ------------------------ | ---------------------- |
| `KundenID`        | `jurisdiction_id`        | Primär-/Fremdschlüssel |
| `Kundenname`      | `name`                   | Namensstandardisierung |
| `Anschrift`       | `address`                | Namensstandardisierung |
| `Module`          | `modules`                | Namensstandardisierung |
| `Startkennung`    | `start_identifier`       | Namensstandardisierung |
| `Anzahlkennungen` | `identifier_count`       | Namensstandardisierung |
| `TerminURL`       | `appointment_url`        | Namensstandardisierung |

#### customer_links (formerly kundenlinks)

| Aktuelle Spalte   | Neue Spalte (snake_case) | Grund                  |
| ----------------- | ------------------------ | ---------------------- |
| `linkid`          | `link_id`                | Primär-/Fremdschlüssel |
| `kundenid`        | `jurisdiction_id`        | Primär-/Fremdschlüssel |
| `organisationsid` | `organization_id`        | Primär-/Fremdschlüssel |
| `behoerdenid`     | `department_id`          | Primär-/Fremdschlüssel |
| `beschreibung`    | `description`            | Namensstandardisierung |
| `link`            | `link`                   | Bereits snake_case     |
| `oeffentlich`     | `public`                 | Namensstandardisierung |
| `neuerFrame`      | `new_frame`              | Namensstandardisierung |

### Phase 3: System- & Konfigurations-Tabellen (niedrigere Priorität)

#### billing (formerly abrechnung)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `AbrechnungsID` | `billing_id`             | Primär-/Fremdschlüssel |
| `StandortID`    | `scope_id`               | Primär-/Fremdschlüssel |
| `Telefonnummer` | `phone`                  | Namensstandardisierung |
| `Datum`         | `date`                   | Namensstandardisierung |
| `gesendet`      | `sent`                   | Namensstandardisierung |

#### ip_exceptions (formerly ipausnahmen)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `IPID`          | `ip_exception_id`        | Primär-/Fremdschlüssel |
| `BehoerdenID`   | `department_id`          | Primär-/Fremdschlüssel |
| `IPAdresse`     | `ip_address`             | Namensstandardisierung |

#### kiosk

| Aktuelle Spalte   | Neue Spalte (snake_case) | Grund                  |
| ----------------- | ------------------------ | ---------------------- |
| `kioskid`         | `kiosk_id`               | Primär-/Fremdschlüssel |
| `kundenid`        | `jurisdiction_id`        | Primär-/Fremdschlüssel |
| `organisationsid` | `organization_id`        | Primär-/Fremdschlüssel |
| `timestamp`       | `timestamp`              | Bereits snake_case     |
| `cookiecode`      | `cookie_code`            | Namensstandardisierung |
| `name`            | `name`                   | Bereits snake_case     |
| `zugelassen`      | `allowed`                | Namensstandardisierung |

#### queue_number_statistics (formerly wartenrstatistik)

| Aktuelle Spalte                              | Neue Spalte (snake_case)                     | Grund                                                     |
| -------------------------------------------- | -------------------------------------------- | --------------------------------------------------------- |
| `datum`                                      | `date`                                       | Datum                                                     |
| `standortid`                                 | `scope_id`                                   | Fremdschlüssel zu scope                                   |
| `wartenrstatistikid`                         | `queue_number_statistics_id`                 | Primärschlüssel                                           |
| `hour_##_waiting_time_spontaneous`           | `hour_##_waiting_time_spontaneous`           | Per-hour actual waiting time (spontaneous); ## = 00–23    |
| `hour_##_waiting_time_appointment`           | `hour_##_waiting_time_appointment`           | Per-hour actual waiting time (appointment); ## = 00–23    |
| `hour_##_way_time_spontaneous`               | `hour_##_way_time_spontaneous`               | Per-hour way time (spontaneous); ## = 00–23               |
| `hour_##_way_time_appointment`               | `hour_##_way_time_appointment`               | Per-hour way time (appointment); ## = 00–23               |
| `hour_##_estimated_waiting_time_spontaneous` | `hour_##_estimated_waiting_time_spontaneous` | Per-hour estimated waiting time (spontaneous); ## = 00–23 |
| `hour_##_estimated_waiting_time_appointment` | `hour_##_estimated_waiting_time_appointment` | Per-hour estimated waiting time (appointment); ## = 00–23 |
| `hour_##_waiting_count_spontaneous`          | `hour_##_waiting_count_spontaneous`          | Per-hour waiting count (spontaneous); ## = 00–23          |
| `hour_##_waiting_count_appointment`          | `hour_##_waiting_count_appointment`          | Per-hour waiting count (appointment); ## = 00–23          |

> Legacy columns `echte_zeit_ab_##_*`, `wegezeit_ab_##_*`, `zeit_ab_##_*`, and `wartende_ab_##_*` map to the `hour_##_*` names above (see migration `91775568666-rename-waiting-way-processing-columns.sql`).

#### location_cluster (formerly standortcluster)

| Aktuelle Spalte           | Neue Spalte (snake_case) | Grund                  |
| ------------------------- | ------------------------ | ---------------------- |
| `clusterID`               | `cluster_id`             | Primär-/Fremdschlüssel |
| `name`                    | `name`                   | Bereits snake_case     |
| `clusterinfozeile1`       | `cluster_info_line_1`    | Namensstandardisierung |
| `clusterinfozeile2`       | `cluster_info_line_2`    | Namensstandardisierung |
| `stadtplanlink`           | `city_map_link`          | Namensstandardisierung |
| `aufrufanzeigetext`       | `display_text`           | Namensstandardisierung |
| `standortkuerzelanzeigen` | `show_scope_short_name`  | Namensstandardisierung |

#### statistics (formerly statistik)

| Aktuelle Spalte       | Neue Spalte (snake_case)  | Grund                  |
| --------------------- | ------------------------- | ---------------------- |
| `statistikid`         | `statistics_id`           | Primär-/Fremdschlüssel |
| `kundenid`            | `jurisdiction_id`         | Primär-/Fremdschlüssel |
| `organisationsid`     | `organization_id`         | Primär-/Fremdschlüssel |
| `behoerdenid`         | `department_id`           | Primär-/Fremdschlüssel |
| `clusterid`           | `cluster_id`              | Primär-/Fremdschlüssel |
| `standortid`          | `scope_id`                | Primär-/Fremdschlüssel |
| `anliegenid`          | `request_id`              | Primär-/Fremdschlüssel |
| `datum`               | `datum`                   | Bereits snake_case     |
| `lastbuergerarchivid` | `last_citizen_archive_id` | Primär-/Fremdschlüssel |
| `termin`              | `with_appointment`        | Namensstandardisierung |
| `info_dl_id`          | `info_provider_id`        | Primär-/Fremdschlüssel |
| `processing_time`     | `processing_time`         | Bereits snake_case     |

#### api_client (formerly apiclient)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `apiClientID`       | `api_client_id`          | Primär-/Fremdschlüssel |
| `clientKey`         | `client_key`             | Namensstandardisierung |
| `shortname`         | `short_name`             | Namensstandardisierung |
| `accesslevel`       | `access_level`           | Namensstandardisierung |
| `updateZeitstempel` | `updated_at`             | Zeitstempel            |

#### api_key (formerly apikey)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `key`           | `key`                    | Bereits snake_case     |
| `createIP`      | `create_ip`              | Namensstandardisierung |
| `ts`            | `ts`                     | Bereits snake_case     |
| `apiClientID`   | `api_client_id`          | Primär-/Fremdschlüssel |

#### api_quota (formerly apiquota)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `quotaid`       | `quota_id`               | Primär-/Fremdschlüssel |
| `key`           | `key`                    | Bereits snake_case     |
| `route`         | `route`                  | Bereits snake_case     |
| `period`        | `period`                 | Bereits snake_case     |
| `requests`      | `requests`               | Bereits snake_case     |
| `ts`            | `ts`                     | Bereits snake_case     |

#### role

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `id`            | `id`                     | Bereits snake_case |
| `name`          | `name`                   | Bereits snake_case |
| `description`   | `description`            | Bereits snake_case |

#### permission

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `id`            | `id`                     | Bereits snake_case |
| `name`          | `name`                   | Bereits snake_case |
| `description`   | `description`            | Bereits snake_case |

#### role_permission

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `role_id`       | `role_id`                | Bereits snake_case |
| `permission_id` | `permission_id`          | Bereits snake_case |

#### user_role

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `user_id`       | `user_id`                | Bereits snake_case |
| `role_id`       | `role_id`                | Bereits snake_case |

### Phase 4: Kommunikations-Tabellen

#### email

| Aktuelle Spalte                | Neue Spalte (snake_case)       | Grund                  |
| ------------------------------ | ------------------------------ | ---------------------- |
| `emailID`                      | `email_id`                     | Primär-/Fremdschlüssel |
| `BehoerdenID`                  | `department_id`                | Primär-/Fremdschlüssel |
| `serveradresse`                | `server_address`               | Namensstandardisierung |
| `authentication`               | `authentication`               | Bereits snake_case     |
| `username`                     | `username`                     | Bereits snake_case     |
| `password`                     | `password`                     | Bereits snake_case     |
| `ssl_coding`                   | `ssl_encoding`                 | Namensstandardisierung |
| `absenderadresse`              | `sender_address`               | Namensstandardisierung |
| `send_reminder`                | `send_reminder`                | Bereits snake_case     |
| `send_reminder_minutes_before` | `send_reminder_minutes_before` | Bereits snake_case     |

#### sms

| Aktuelle Spalte        | Neue Spalte (snake_case) | Grund                  |
| ---------------------- | ------------------------ | ---------------------- |
| `smsID`                | `sms_id`                 | Primär-/Fremdschlüssel |
| `BehoerdenID`          | `department_id`          | Primär-/Fremdschlüssel |
| `enabled`              | `enabled`                | Bereits snake_case     |
| `Absender`             | `sender`                 | Namensstandardisierung |
| `interneterinnerung`   | `internet_reminder`      | Namensstandardisierung |
| `internetbestaetigung` | `internet_confirmation`  | Namensstandardisierung |

#### mail_part (formerly mailpart)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `id`            | `id`                     | Bereits snake_case     |
| `queueId`       | `queue_id`               | Primär-/Fremdschlüssel |
| `mime`          | `mime`                   | Bereits snake_case     |
| `content`       | `content`                | Bereits snake_case     |
| `base64`        | `base64`                 | Bereits snake_case     |

#### mail_queue (formerly mailqueue)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `id`                | `id`                     | Bereits snake_case     |
| `processID`         | `process_id`             | Primär-/Fremdschlüssel |
| `departmentID`      | `department_id`          | Primär-/Fremdschlüssel |
| `createIP`          | `create_ip`              | Namensstandardisierung |
| `createZeitstempel` | `created_at`             | Zeitstempel            |
| `subject`           | `subject`                | Bereits snake_case     |
| `clientFamilyName`  | `client_family_name`     | Namensstandardisierung |
| `clientEmail`       | `client_email`           | Namensstandardisierung |

#### mail_template (formerly mailtemplate)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund              |
| ------------------- | ------------------------ | ------------------ |
| `id`                | `id`                     | Bereits snake_case |
| `name`              | `name`                   | Bereits snake_case |
| `value`             | `value`                  | Bereits snake_case |
| `provider`          | `provider`               | Bereits snake_case |
| `changeZeitstempel` | `changed_at`             | Zeitstempel        |

#### notification_queue (formerly notificationqueue)

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `id`                | `id`                     | Bereits snake_case     |
| `processID`         | `process_id`             | Primär-/Fremdschlüssel |
| `departmentID`      | `department_id`          | Primär-/Fremdschlüssel |
| `createIP`          | `create_ip`              | Namensstandardisierung |
| `createZeitstempel` | `created_at`             | Zeitstempel            |
| `message`           | `message`                | Bereits snake_case     |
| `clientFamilyName`  | `client_family_name`     | Namensstandardisierung |
| `clientTelephone`   | `client_phone`           | Namensstandardisierung |
| `scopeID`           | `scope_id`               | Primär-/Fremdschlüssel |

### Phase 5: Datumn- & Prozess-Tabellen

#### closures

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `id`                | `id`                     | Bereits snake_case     |
| `year`              | `year`                   | Bereits snake_case     |
| `month`             | `month`                  | Bereits snake_case     |
| `day`               | `day`                    | Bereits snake_case     |
| `StandortID`        | `scope_id`               | Primär-/Fremdschlüssel |
| `updateZeitstempel` | `updated_at`             | Zeitstempel            |

#### config

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund              |
| ------------------- | ------------------------ | ------------------ |
| `name`              | `name`                   | Bereits snake_case |
| `value`             | `value`                  | Bereits snake_case |
| `changeZeitstempel` | `changed_at`             | Zeitstempel        |

#### event_log (formerly eventlog)

| Aktuelle Spalte       | Neue Spalte (snake_case) | Grund                  |
| --------------------- | ------------------------ | ---------------------- |
| `eventId`             | `event_id`               | Primär-/Fremdschlüssel |
| `eventName`           | `event_name`             | Namensstandardisierung |
| `origin`              | `origin`                 | Bereits snake_case     |
| `referenceType`       | `reference_type`         | Namensstandardisierung |
| `reference`           | `reference`              | Bereits snake_case     |
| `sessionid`           | `session_id`             | Primär-/Fremdschlüssel |
| `contextjson`         | `context_json`           | Namensstandardisierung |
| `creationDatumTime`   | `created_at`             | Zeitstempel            |
| `expirationDatumTime` | `expires_at`             | Zeitstempel            |

#### image_data (formerly imagedata)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `imagename`     | `image_name`             | Namensstandardisierung |
| `imagecontent`  | `image_content`          | Namensstandardisierung |
| `ts`            | `ts`                     | Bereits snake_case     |

#### log

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `log_id`        | `log_id`                 | Bereits snake_case |
| `type`          | `type`                   | Bereits snake_case |
| `reference_id`  | `reference_id`           | Bereits snake_case |
| `ts`            | `ts`                     | Bereits snake_case |
| `message`       | `message`                | Bereits snake_case |
| `scope_id`      | `scope_id`               | Bereits snake_case |
| `data`          | `data`                   | Bereits snake_case |
| `user_id`       | `user_id`                | Bereits snake_case |

#### migrations

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund              |
| ------------------- | ------------------------ | ------------------ |
| `filename`          | `filename`               | Bereits snake_case |
| `changeZeitstempel` | `changed_at`             | Zeitstempel        |

#### preferences

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `entity`            | `entity`                 | Bereits snake_case     |
| `id`                | `id`                     | Bereits snake_case     |
| `groupName`         | `group_name`             | Namensstandardisierung |
| `name`              | `name`                   | Bereits snake_case     |
| `value`             | `value`                  | Bereits snake_case     |
| `updateZeitstempel` | `updated_at`             | Zeitstempel            |

#### process_sequence

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `processId`     | `process_id`             | Primär-/Fremdschlüssel |

#### session_data (formerly sessiondata)

| Aktuelle Spalte  | Neue Spalte (snake_case) | Grund                  |
| ---------------- | ------------------------ | ---------------------- |
| `sessionid`      | `session_id`             | Primär-/Fremdschlüssel |
| `sessionname`    | `session_name`           | Namensstandardisierung |
| `sessioncontent` | `session_content`        | Namensstandardisierung |
| `ts`             | `ts`                     | Bereits snake_case     |

#### source

| Aktuelle Spalte  | Neue Spalte (snake_case) | Grund                  |
| ---------------- | ------------------------ | ---------------------- |
| `source`         | `source`                 | Bereits snake_case     |
| `label`          | `label`                  | Bereits snake_case     |
| `editable`       | `editable`               | Bereits snake_case     |
| `contact__name`  | `contact__name`          | Bereits snake_case     |
| `contact__email` | `contact__email`         | Bereits snake_case     |
| `lastChange`     | `last_change`            | Namensstandardisierung |

#### overview_calendar

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `id`            | `id`                     | Bereits snake_case |
| `scope_id`      | `scope_id`               | Bereits snake_case |
| `process_id`    | `process_id`             | Bereits snake_case |
| `status`        | `status`                 | Bereits snake_case |
| `starts_at`     | `starts_at`              | Bereits snake_case |
| `ends_at`       | `ends_at`                | Bereits snake_case |
| `updated_at`    | `updated_at`             | Bereits snake_case |

### Phase 6: Leistungs- & Anbieter-Tabellen

#### provider

| Aktuelle Spalte         | Neue Spalte (snake_case) | Grund              |
| ----------------------- | ------------------------ | ------------------ |
| `source`                | `source`                 | Bereits snake_case |
| `id`                    | `id`                     | Bereits snake_case |
| `name`                  | `name`                   | Bereits snake_case |
| `contact__city`         | `contact__city`          | Bereits snake_case |
| `contact__country`      | `contact__country`       | Bereits snake_case |
| `contact__lat`          | `contact__lat`           | Bereits snake_case |
| `contact__lon`          | `contact__lon`           | Bereits snake_case |
| `contact__postalCode`   | `contact__postalCode`    | Bereits snake_case |
| `contact__region`       | `contact__region`        | Bereits snake_case |
| `contact__street`       | `contact__street`        | Bereits snake_case |
| `contact__streetNumber` | `contact__streetNumber`  | Bereits snake_case |
| `link`                  | `link`                   | Bereits snake_case |
| `data`                  | `data`                   | Bereits snake_case |
| `display_name`          | `display_name`           | Bereits snake_case |
| `parent_id`             | `parent_id`              | Bereits snake_case |

#### request

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `source`        | `source`                 | Bereits snake_case |
| `id`            | `id`                     | Bereits snake_case |
| `name`          | `name`                   | Bereits snake_case |
| `link`          | `link`                   | Bereits snake_case |
| `group`         | `group`                  | Bereits snake_case |
| `data`          | `data`                   | Bereits snake_case |
| `parent_id`     | `parent_id`              | Bereits snake_case |
| `variant_id`    | `variant_id`             | Bereits snake_case |

#### request_provider

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund              |
| ------------------- | ------------------------ | ------------------ |
| `source`            | `source`                 | Bereits snake_case |
| `request__id`       | `request__id`            | Bereits snake_case |
| `provider__id`      | `provider__id`           | Bereits snake_case |
| `slots`             | `slots`                  | Bereits snake_case |
| `bookable`          | `bookable`               | Bereits snake_case |
| `max_quantity`      | `max_quantity`           | Bereits snake_case |
| `public_visibility` | `public_visibility`      | Bereits snake_case |

#### request_variant

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund              |
| --------------- | ------------------------ | ------------------ |
| `id`            | `id`                     | Bereits snake_case |
| `name`          | `name`                   | Bereits snake_case |

### Phase 7: Slot-System-Tabellen

#### slot

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `slotID`            | `slot_id`                | Primär-/Fremdschlüssel |
| `scopeID`           | `scope_id`               | Primär-/Fremdschlüssel |
| `year`              | `year`                   | Bereits snake_case     |
| `month`             | `month`                  | Bereits snake_case     |
| `day`               | `day`                    | Bereits snake_case     |
| `time`              | `time`                   | Bereits snake_case     |
| `availabilityID`    | `availability_id`        | Primär-/Fremdschlüssel |
| `public`            | `public`                 | Bereits snake_case     |
| `callcenter`        | `callcenter`             | Bereits snake_case     |
| `intern`            | `intern`                 | Bereits snake_case     |
| `status`            | `status`                 | Bereits snake_case     |
| `slotTimeInMinutes` | `slot_time_in_minutes`   | Namensstandardisierung |
| `createZeitstempel` | `created_at`             | Zeitstempel            |
| `updateZeitstempel` | `updated_at`             | Zeitstempel            |

#### slot_hierarchy (formerly slot_hiera)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `slothieraID`   | `slot_hierarchy_id`      | Primär-/Fremdschlüssel |
| `slotID`        | `slot_id`                | Primär-/Fremdschlüssel |
| `ancestorID`    | `ancestor_id`            | Primär-/Fremdschlüssel |
| `ancestorLevel` | `ancestor_level`         | Namensstandardisierung |

#### slot_process

| Aktuelle Spalte     | Neue Spalte (snake_case) | Grund                  |
| ------------------- | ------------------------ | ---------------------- |
| `slotID`            | `slot_id`                | Primär-/Fremdschlüssel |
| `processID`         | `process_id`             | Primär-/Fremdschlüssel |
| `updateZeitstempel` | `updated_at`             | Zeitstempel            |

#### slot_sequence

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `slotsequence`  | `slot_sequence`          | Namensstandardisierung |

### Phase 8: Zuweisungs- & Clustering-Tabellen

#### cluster_assignment (formerly clusterzuordnung)

| Aktuelle Spalte | Neue Spalte (snake_case) | Grund                  |
| --------------- | ------------------------ | ---------------------- |
| `clusterID`     | `cluster_id`             | Primär-/Fremdschlüssel |
| `standortID`    | `scope_id`               | Primär-/Fremdschlüssel |

### Phase 9: Fremdschlüssel-Standardisierung

| Aktuelles Muster  | Neues Muster      | Beispiel                                          |
| ----------------- | ----------------- | ------------------------------------------------- |
| `StandortID`      | `scope_id`        | Fremdschlüssel zur scope-Tabelle                  |
| `BehoerdenID`     | `department_id`   | Fremdschlüssel zur department-Tabelle             |
| `BuergerID`       | `process_id`      | Fremdschlüssel zur process-Tabelle (`buerger`)    |
| `NutzerID`        | `user_id`         | Fremdschlüssel zur user-Tabelle                   |
| `KundenID`        | `jurisdiction_id` | Fremdschlüssel zur jurisdiction-Tabelle (`kunde`) |
| `OrganisationsID` | `organization_id` | Fremdschlüssel zur organization-Tabelle           |
| `OeffnungszeitID` | `availability_id` | Fremdschlüssel zur availability-Tabelle           |
| `apiClientID`     | `api_client_id`   | Fremdschlüssel zur api_client-Tabelle             |
| `clusterID`       | `cluster_id`      | Fremdschlüssel zur location_cluster-Tabelle       |

> Die Spaltenzuordnungen in Abschnitt 2 basieren auf `.resources/zms.sql` und aktuellen Migrationen. Einige Spalten (z. B. `provider.contact__*`) sind JSON-Pfad-Schlüssel in relationalen Spalten und bleiben vorerst unverändert, bis der PHP-Mapping-Refactor (Abschnitt 3) sie in camelCase-Entity-Felder überführt.

## 3. Vollständiger Plan zur Umbenennung der PHP-Variablen-Mappings – alles Englisch, alles camelCase

**Step 1: Update Entity Mappings**
Replace all double underscore patterns with camelCase
Update all German variable names to English
Ensure consistent naming across all Query classes

**Step 2: Update Method Names**
Update method names that reference old mappings
Ensure parameter names match new mapping keys
Update any hardcoded references

**Step 3: Update Tests**
Update all test cases to use new mapping keys
Ensure test data matches new naming conventions
Update any mock data or fixtures

**Step 4: Update Documentation**
Update API documentation with new field names
Update any external references to old field names
Create migration guide for API consumers
This comprehensive plan ensures:
Complete camelCase conversion for all PHP variable mappings
Elimination of double underscores in favor of camelCase
Consistent English naming throughout the codebase
Maintainable structure with clear patterns

### Phase 1: Kern-Entity-Mappings (hohe Priorität)

Availability Query Class

| Current Mapping                  | New Mapping (camelCase)      | Database Column (snake_case)              |
| -------------------------------- | ---------------------------- | ----------------------------------------- |
| 'id'                             | 'id'                         | availability.availability_id              |
| 'scope\*\*id'                    | 'scopeId'                    | availability.scope_id                     |
| 'bookable\*\*startInDays'        | 'bookableStartInDays'        | availability.open_from_days               |
| 'bookable\*\*endInDays'          | 'bookableEndInDays'          | availability.open_until_days              |
| 'description'                    | 'description'                | availability.comment                      |
| 'startDate'                      | 'startDate'                  | availability.start_date                   |
| 'startTime'                      | 'startTime'                  | availability.start_time                   |
| 'endDate'                        | 'endDate'                    | availability.end_date                     |
| 'endTime'                        | 'endTime'                    | availability.end_time                     |
| 'lastChange'                     | 'lastChange'                 | availability.updated_at                   |
| 'multipleSlotsAllowed'           | 'multipleSlotsAllowed'       | availability.multiple_slots_allowed       |
| 'repeat\*\*afterWeeks'           | 'repeatAfterWeeks'           | availability.every_x_weeks                |
| 'repeat\*\*weekOfMonth'          | 'repeatWeekOfMonth'          | availability.every_other_week             |
| 'slotTimeInMinutes'              | 'slotTimeInMinutes'          | availability.time_slot                    |
| 'type'                           | 'type'                       | availability.type                         |
| 'weekday\*\*monday'              | 'weekdayMonday'              | availability.weekday                      |
| 'weekday\*\*tuesday'             | 'weekdayTuesday'             | availability.weekday                      |
| 'weekday\*\*wednesday'           | 'weekdayWednesday'           | availability.weekday                      |
| 'weekday\*\*thursday'            | 'weekdayThursday'            | availability.weekday                      |
| 'weekday\*\*friday'              | 'weekdayFriday'              | availability.weekday                      |
| 'weekday\*\*saturday'            | 'weekdaySaturday'            | availability.weekday                      |
| 'weekday\*\*sunday'              | 'weekdaySunday'              | availability.weekday                      |
| 'workstationCount\*\*callcenter' | 'workstationCountCallcenter' | availability.workstation_count_callcenter |
| 'workstationCount\*\*intern'     | 'workstationCountIntern'     | availability.workstation_count_intern     |
| 'workstationCount\_\_public'     | 'workstationCountPublic'     | availability.workstation_count_public     |

Scope Query Class

| Current Mapping                                             | New Mapping (camelCase)                                 | Database Column (snake_case)       |
| ----------------------------------------------------------- | ------------------------------------------------------- | ---------------------------------- |
| 'hint'                                                      | 'hint'                                                  | scope.hint                         |
| 'id'                                                        | 'id'                                                    | scope.scope_id                     |
| 'contact\*\*name'                                           | 'contactName'                                           | scopeprovider.name                 |
| 'contact\*\*street'                                         | 'contactStreet'                                         | scope.address                      |
| 'contact\*\*email'                                          | 'contactEmail'                                          | scope.admin_email                  |
| 'contact\*\*country'                                        | 'contactCountry'                                        | "Germany"                          |
| 'lastChange'                                                | 'lastChange'                                            | scope.updated_at                   |
| 'preferences**appointment**deallocationDuration'            | 'preferencesAppointmentDeallocationDuration'            | scope.deletion_duration            |
| 'preferences**appointment**infoForAppointment'              | 'preferencesAppointmentInfoForAppointment'              | scope.info_for_appointment         |
| 'preferences**appointment**infoForAllAppointments'          | 'preferencesAppointmentInfoForAllAppointments'          | scope.info_for_all_appointments    |
| 'preferences**appointment**endInDaysDefault'                | 'preferencesAppointmentEndInDaysDefault'                | scope.appointments_until_days      |
| 'preferences**appointment**multipleSlotsEnabled'            | 'preferencesAppointmentMultipleSlotsEnabled'            | scope.multiple_appointments        |
| 'preferences**appointment**reservationDuration'             | 'preferencesAppointmentReservationDuration'             | scope.reservation_duration         |
| 'preferences**appointment**activationDuration'              | 'preferencesAppointmentActivationDuration'              | scope.activation_duration          |
| 'preferences**appointment**startInDaysDefault'              | 'preferencesAppointmentStartInDaysDefault'              | scope.appointments_from_days       |
| 'preferences**appointment**notificationConfirmationEnabled' | 'preferencesAppointmentNotificationConfirmationEnabled' | scope.sms_confirmation_enabled     |
| 'preferences**appointment**notificationHeadsUpEnabled'      | 'preferencesAppointmentNotificationHeadsUpEnabled'      | scope.sms_notification_enabled     |
| 'preferences**client**alternateAppointmentUrl'              | 'preferencesClientAlternateAppointmentUrl'              | scope.qtv_url                      |
| 'preferences**client**amendmentActivated'                   | 'preferencesClientAmendmentActivated'                   | scope.comment_required             |
| 'preferences**client**amendmentLabel'                       | 'preferencesClientAmendmentLabel'                       | scope.comment_label                |
| 'preferences**client**emailFrom'                            | 'preferencesClientEmailFrom'                            | scopemail.sender_address           |
| 'preferences**client**emailRequired'                        | 'preferencesClientEmailRequired'                        | scope.email_required               |
| 'preferences**client**emailConfirmationActivated'           | 'preferencesClientEmailConfirmationActivated'           | scope.email_confirmation_activated |
| 'preferences**client**telephoneActivated'                   | 'preferencesClientTelephoneActivated'                   | scope.phone_enabled                |
| 'preferences**client**telephoneRequired'                    | 'preferencesClientTelephoneRequired'                    | scope.phone_required               |
| 'preferences**client**appointmentsPerMail'                  | 'preferencesClientAppointmentsPerMail'                  | scope.appointments_per_mail        |
| 'preferences**client**slotsPerAppointment'                  | 'preferencesClientSlotsPerAppointment'                  | scope.slots_per_appointment        |
| 'preferences**client**whitelistedMails'                     | 'preferencesClientWhitelistedMails'                     | scope.whitelisted_mails            |
| 'preferences**client**customTextfieldActivated'             | 'preferencesClientCustomTextfieldActivated'             | scope.custom_text_field_active     |
| 'preferences**client**customTextfieldRequired'              | 'preferencesClientCustomTextfieldRequired'              | scope.custom_text_field_required   |
| 'preferences**client**customTextfieldLabel'                 | 'preferencesClientCustomTextfieldLabel'                 | scope.custom_text_field_label      |
| 'preferences**client**customTextfield2Activated'            | 'preferencesClientCustomTextfield2Activated'            | scope.custom_text_field2_active    |
| 'preferences**client**customTextfield2Required'             | 'preferencesClientCustomTextfield2Required'             | scope.custom_text_field2_required  |
| 'preferences**client**customTextfield2Label'                | 'preferencesClientCustomTextfield2Label'                | scope.custom_text_field2_label     |
| 'preferences**client**captchaActivatedRequired'             | 'preferencesClientCaptchaActivatedRequired'             | scope.captcha_activated_required   |
| 'preferences**client**adminMailOnAppointment'               | 'preferencesClientAdminMailOnAppointment'               | scope.admin_mail_on_appointment    |
| 'preferences**client**adminMailOnDeleted'                   | 'preferencesClientAdminMailOnDeleted'                   | scope.admin_mail_on_deleted        |
| 'preferences**client**adminMailOnUpdated'                   | 'preferencesClientAdminMailOnUpdated'                   | scope.admin_mail_on_updated        |
| 'preferences**client**adminMailOnMailSent'                  | 'preferencesClientAdminMailOnMailSent'                  | scope.admin_mail_on_mail_sent      |
| 'preferences**notifications**confirmationContent'           | 'preferencesNotificationsConfirmationContent'           | scope.sms_confirmation_text        |
| 'preferences**notifications**headsUpContent'                | 'preferencesNotificationsHeadsUpContent'                | scope.sms_notification_text        |
| 'preferences**notifications**headsUpTime'                   | 'preferencesNotificationsHeadsUpTime'                   | scope.sms_notification_deadline    |
| 'preferences**pickup**alternateName'                        | 'preferencesPickupAlternateName'                        | scope.pickup_counter_name          |
| 'preferences**pickup**isDefault'                            | 'preferencesPickupIsDefault'                            | scope.default_pickup_location      |
| 'preferences**queue**callCountMax'                          | 'preferencesQueueCallCountMax'                          | scope.recall_count                 |
| 'preferences**queue**callDisplayText'                       | 'preferencesQueueCallDisplayText'                       | scope.display_text                 |
| 'preferences**queue**firstNumber'                           | 'preferencesQueueFirstNumber'                           | scope.first_queue_number           |
| 'preferences**queue**lastNumber'                            | 'preferencesQueueLastNumber'                            | scope.last_queue_number            |
| 'preferences**queue**maxNumberContingent'                   | 'preferencesQueueMaxNumberContingent'                   | scope.queue_number_contingent      |
| 'preferences**queue**processingTimeAverage'                 | 'preferencesQueueProcessingTimeAverage'                 | scope.processing_time              |
| 'preferences**queue**publishWaitingTimeEnabled'             | 'preferencesQueuePublishWaitingTimeEnabled'             | scope.publish_waiting_time         |
| 'preferences**queue**statisticsEnabled'                     | 'preferencesQueueStatisticsEnabled'                     | scope.statistics_enabled           |
| 'preferences**survey**emailContent'                         | 'preferencesSurveyEmailContent'                         | scope.customer_survey_email_text   |
| 'preferences**survey**enabled'                              | 'preferencesSurveyEnabled'                              | scope.customer_survey              |
| 'preferences**survey**label'                                | 'preferencesSurveyLabel'                                | scope.customer_survey_label        |
| 'preferences**ticketprinter**buttonName'                    | 'preferencesTicketprinterButtonName'                    | scope.location_info_line           |
| 'preferences**ticketprinter**confirmationEnabled'           | 'preferencesTicketprinterConfirmationEnabled'           | scope.sms_wms_confirmation         |
| 'preferences**ticketprinter**deactivatedText'               | 'preferencesTicketprinterDeactivatedText'               | scope.queue_hint                   |
| 'preferences**ticketprinter**notificationsAmendmentEnabled' | 'preferencesTicketprinterNotificationsAmendmentEnabled' | scope.sms_addition                 |
| 'preferences**ticketprinter**notificationsEnabled'          | 'preferencesTicketprinterNotificationsEnabled'          | scope.sms_queue                    |
| 'preferences**ticketprinter**notificationsDelay'            | 'preferencesTicketprinterNotificationsDelay'            | scope.sms_kiosk_offer_deadline     |
| 'preferences**workstation**emergencyEnabled'                | 'preferencesWorkstationEmergencyEnabled'                | scope.emergency_function           |
| 'preferences**workstation**emergencyRefreshInterval'        | 'preferencesWorkstationEmergencyRefreshInterval'        | scope.emergency_refresh_interval   |
| 'shortName'                                                 | 'shortName'                                             | scope.short_name                   |
| 'status**emergency**acceptedByWorkstation'                  | 'statusEmergencyAcceptedByWorkstation'                  | scope.emergency_response           |
| 'status**emergency**activated'                              | 'statusEmergencyActivated'                              | scope.emergency_triggered          |
| 'status**emergency**calledByWorkstation'                    | 'statusEmergencyCalledByWorkstation'                    | scope.emergency_initiation         |
| 'status**queue**ghostWorkstationCount'                      | 'statusQueueGhostWorkstationCount'                      | scope.virtual_processor_count      |
| 'status**queue**givenNumberCount'                           | 'statusQueueGivenNumberCount'                           | scope.assigned_queue_numbers       |
| 'status**queue**lastGivenNumber'                            | 'statusQueueLastGivenNumber'                            | scope.last_queue_number            |
| 'status**queue**lastGivenNumberTimestamp'                   | 'statusQueueLastGivenNumberTimestamp'                   | scope.queue_number_date            |

### Phase 2: Prozess- & Bürger-Mappings (mittlere Priorität)

Process Query Class

| Current Mapping                | New Mapping (camelCase) | Database Column (snake_case) |
| ------------------------------ | ----------------------- | ---------------------------- |
| 'amendment'                    | 'amendment'             | process.comment              |
| 'id'                           | 'id'                    | process.citizen_id           |
| 'appointments**0**date'        | 'appointments0Date'     | process.appointment_datetime |
| 'scope\*\*id'                  | 'scopeId'               | process.scope_id             |
| 'appointments**0**scope\*\*id' | 'appointments0ScopeId'  | process.scope_id             |

Citizen Query Class

| Current Mapping      | New Mapping (camelCase) | Database Column (snake_case) |
| -------------------- | ----------------------- | ---------------------------- |
| 'id'                 | 'id'                    | citizen.citizen_id           |
| 'scopeId'            | 'scopeId'               | citizen.scope_id             |
| 'pickupLocationId'   | 'pickupLocationId'      | citizen.pickup_location_id   |
| 'userId'             | 'userId'                | citizen.user_id              |
| 'name'               | 'name'                  | citizen.name                 |
| 'email'              | 'email'                 | citizen.email                |
| 'phone'              | 'phone'                 | citizen.phone                |
| 'comment'            | 'comment'               | citizen.comment              |
| 'provisionalBooking' | 'provisionalBooking'    | citizen.provisional_booking  |
| 'confirmed'          | 'confirmed'             | citizen.confirmed            |
| 'callSuccessful'     | 'callSuccessful'        | citizen.call_successful      |
| 'callTime'           | 'callTime'              | citizen.call_time            |
| 'pickupPerson'       | 'pickupPerson'          | citizen.pickup_person        |
| 'queueNumber'        | 'queueNumber'           | citizen.queue_number         |
| 'queueNumberDate'    | 'queueNumberDate'       | citizen.queue_number_date    |
| 'waitingTime'        | 'waitingTime'           | citizen.waiting_time         |
| 'processingTime'     | 'processingTime'        | citizen.processing_time      |
| 'parked'             | 'parked'                | citizen.parked               |
| 'wasMissed'          | 'wasMissed'             | citizen.was_missed           |
| 'apiClientId'        | 'apiClientId'           | citizen.api_client_id        |
| 'source'             | 'source'                | citizen.source               |
| 'lastChange'         | 'lastChange'            | citizen.updated_at           |

### Phase 3: Anbieter- & Request-Mappings (niedrigere Priorität)

Provider Query Class

| Current Mapping           | New Mapping (camelCase) | Database Column (snake_case)   |
| ------------------------- | ----------------------- | ------------------------------ |
| 'contact\*\*city'         | 'contactCity'           | provider.contact_city          |
| 'contact\*\*country'      | 'contactCountry'        | provider.contact_country       |
| 'contact\*\*name'         | 'contactName'           | provider.name                  |
| 'contact\*\*postalCode'   | 'contactPostalCode'     | provider.contact_postal_code   |
| 'contact\*\*region'       | 'contactRegion'         | provider.contact_region        |
| 'contact\*\*street'       | 'contactStreet'         | provider.contact_street        |
| 'contact\_\_streetNumber' | 'contactStreetNumber'   | provider.contact_street_number |
| 'id'                      | 'id'                    | provider.id                    |
| 'link'                    | 'link'                  | provider.link                  |
| 'name'                    | 'name'                  | provider.name                  |
| 'displayName'             | 'displayName'           | provider.display_name          |
| 'source'                  | 'source'                | provider.source                |
| 'data'                    | 'data'                  | provider.data                  |

## 4. Langfristige Schema-Vision (über Umbenennung hinaus)

Die Abschnitte 1–3 standardisieren **Namen**. Dieser Abschnitt dokumentiert **strukturelle** Änderungen für ein gesünderes Langzeit-Schema. Es handelt sich um Planungsnotizen, keine festen Zeitpläne.

### 4.1 Größere strukturelle Refactorings

#### Aufteilung von `buerger` in citizen und process (oder citizen und appointment)

Heute ist `buerger` die physische Tabelle der `process`-Entity. Sie vermischt gewachsene Concerns:

- Bürger-/Kunden-PII (`Name`, `EMail`, `Telefonnummer`, Custom Fields)
- Terminplanung (`Datum`, `Uhrzeit`, Standortbezug, Slot-Verknüpfung)
- Warteschlangen-Laufzeit (`wartenummer`, `status`, `waiting_time`, Aufruf-Metadaten)
- Archiv- und Statistik-Eingaben

**Zielmodelle (Kandidaten):**

| Option | Tabellen                  | Passt wenn                                                 |
| ------ | ------------------------- | ---------------------------------------------------------- |
| A      | `citizen` + `process`     | Warteschlangen-zentriert; Process als Arbeitseinheit       |
| B      | `citizen` + `appointment` | Termin-zentriert; klare Trennung Buchung vs. Warteschlange |

Die kurzfristige Umbenennung in Abschnitt 1 kann `buerger` → `process` vorsehen. Die Aufteilung hier ist eine **spätere** Migration, sobald API, Statistik und Archiv-Pfade entflochten sind.

#### Entfernen von `buergerarchivtoday` / `citizen_archive_today`

`buergerarchivtoday` ist ein Tages-Snapshot von `buergerarchiv` — redundant und wartungsintensiv. Stattdessen:

- `citizen_archive` mit Datumsfilter und passenden Indizes, oder
- View / materialisierte View bei Performance-Bedarf

Tabelle entfernen, sobald Abfragen und Dashboards ohne sie auskommen.

#### Normalisierung von `queue_number_statistics` (`wartenrstatistik`)

Die Tabelle ist extrem breit: stündliche Spalten für geschätzte Wartezeit, echte Wartezeit, Wegezeit und Wartende — jeweils Spontan vs. Termin (96+ Spalten pro Metrik-Familie). Zwischen-Umbenennungen: Migration `91775568666-rename-waiting-way-processing-columns.sql`.

**Langfristig:** Fact-Tabellen, z. B. `queue_statistics_hourly` mit `(scope_id, date, hour, metric, channel, value)` — `channel`: `spontaneous` | `appointment`.

#### Normalisierung von `log.data` (JSON)

Neben indexierten Spalten (`type`, `scope_id`, `user_id`, `reference_id`) liegt ein JSON-Blob `data`. Suche darin ist langsam.

**Richtung:** häufig gefilterte Felder als typisierte Spalten; `data` nur noch für Debug oder entfernen.

#### Aufteilen und umbenennen von `preferences`

`preferences` nutzt `(entity, id, groupName, name)`, mischt aber:

- **Scope-Einstellungen** (entity `scope`, aus `standort` migriert)
- **System-/Admin-Konfiguration** (zmsadmin Config-Bereich)

**Richtung:** `scope_preference` / `scope_setting` plus `system_setting` (oder Abgrenzung zu `config`); generischen Namen `preferences` für Scope-Daten vermeiden.

#### DLDB-Tabellen: `provider`, `request`, `request_provider`, `request_variant`

DLDB-synchronisiert; mehrere Tabellen mit `data`-JSON. `zmscitizenapi` spricht von **offices** und **services** (`officeId`, `serviceId`, `OfficeServiceRelation`).

| Aktuell            | Citizen-API       | Kandidat Tabellenname |
| ------------------ | ----------------- | --------------------- |
| `provider`         | office            | `office`              |
| `request`          | service           | `service`             |
| `request_provider` | Standort–Leistung | `office_service`      |
| `request_variant`  | Leistungsvariante | `service_variant`     |

**Struktur:** `data` in relationale Spalten überführen, wo abgefragt; JSON nur für seltene DLDB-Felder.

### 4.2 Tabellen-Disposition

| Tabelle (aktuell)    | Geplante Disposition            | Hinweise                                                                                                                                                                                   |
| -------------------- | ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `abrechnung`         | **Löschen**                     | Ungenutzt. Migration `91772633097-drop-abrechnung.sql` löscht die Tabelle bereits.                                                                                                         |
| `ipausnahmen`        | **Prüfen → vermutlich löschen** | Keine PHP-Referenzen zum Zeitpunkt der Dokumentation.                                                                                                                                      |
| `apikey`             | **Prüfen**                      | Routen in `zmsbackend`; klären ob Produktion noch API-Keys nutzt.                                                                                                                          |
| `apiquota`           | **Prüfen**                      | An `apikey` gekoppelt.                                                                                                                                                                     |
| `notificationqueue`  | **Löschen**                     | SMS-/Notification-Abbau. Migration `91772633137-drop-notifcationqueue.sql`.                                                                                                                |
| `eventlog`           | **Prüfen**                      | Noch in Nutzung (z. B. `ProcessListSummaryMail`); ggf. mit `log` zusammenführen.                                                                                                           |
| `imagedata`          | **Neu gestalten**               | Nur URL in DB; Binärdaten in RefArch-S3 via zmsadmin. Admin-Logo aktuell statisch unter `terminvereinbarung/admin/_css/images/muc_logo_head2.png` — DB-Upload vermutlich ungenutzt/defekt. |
| `kundenlinks`        | **Löschen**                     | Bereits in Abschnitt 1 als ungenutzt markiert.                                                                                                                                             |
| `buergerarchivtoday` | **Löschen**                     | Siehe §4.1; redundant zu `buergerarchiv`.                                                                                                                                                  |

### 4.3 Assets, Migrationen und Betrieb

#### `image_data` → Object Storage

- Logos und Calldisplay-Bilder über zmsadmin in **RefArch-S3** (oder kompatiblen Store) hochladen.
- In der DB nur `bucket`, `object_key` und/oder HTTPS-URL speichern.
- `imagecontent`-BLOB/TEXT nach Migration entfernen.
- Alle Consumer prüfen: `FileUploader`, Calldisplay-Routen, Cluster/Scope-Bilder.

#### Migrations-Dateinamen

Viele Dateien beginnen mit `917…` — chronologische Sortierung und Review erschwert.

**Vorschlag für neue Migrationen:**

```
{YYYYMMDD}-{HHMMSS}-{ticket-oder-kurzbeschreibung}.sql
```

Beispiel: `20260302-143000-ZMS-1234-split-buerger.sql`. Historische Dateien nur umbenennen, wenn der Aufwand gerechtfertigt ist.

#### Tabelle `migrations`

Die Tabelle selbst ist in Ordnung; Ziel ist die **Dateikonvention** im Dateisystem, nicht ein Tabellenumbenennung.
