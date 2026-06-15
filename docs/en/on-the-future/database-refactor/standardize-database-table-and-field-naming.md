---
outline: deep
---

# Standardize Database Table and Field Naming Conventions With Their Backend Application Mappings

### Problem Description

<mark>**Please Note: Several tasks to prune unused tables and columns from the database are already under way. SMS/Notification features will be scrubbed in those issues.**</mark>

**The current database schema suffers from inconsistent naming conventions that create maintenance challenges and reduce code clarity.**

<mark>
"The following comes from experience in the project and is not a simple suggestion from ai. Cleaning this up would significantly reduce long-term technical debt. I struggled very much the first 6-9 months trying to roughly map all this in my mind." @ThomasAFink 
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
Investment: Standardize schema and update queries <br />
Return:<br />
- Faster developer onboarding (saves months per new dev)<br />
- Reduced debugging time (saves hours per issue)<br />
- Improved development velocity (saves time on every backend feature)<br />
- Reduced bug introduction (prevents costly production issues and hotfixes)<br />
- Faster refactoring of other parts later<br />
- Easier test writing
</mark>
<br />
<br />

The issues include:

**1. Mixed Language Usage (German ↔ English)**

- Table names: `oeffnungszeit` (German) vs `availability` (English concept)
- Table names: `standort` (German) vs `scope` (English concept)
- Table names: `buerger` (German) vs `citizen` (English concept)
- Table names: `feiertage` (German) vs `holidays` (English concept)

**2. Inconsistent Naming Conventions**

- Mix of camelCase and snake_case: `StandortID` vs `scope_id`
- Mixed conventions within same table: `contact__name` vs `contact__email` vs `StandortID`

**3. Conceptual Inconsistencies**

- Same concept with different names: `availability`/`oeffnungszeit`, `scope`/`standort`
- Query classes use English names but map to German table names

### Current Examples

**Availability Query Class:**

```php
const TABLE = 'oeffnungszeit';  // German table name
// But maps to English field names:
'id' => 'availability.OeffnungszeitID',
'scope__id' => 'availability.StandortID',
```

**Scope Query Class:**

```php
const TABLE = 'standort';  // German table name
// Maps to mixed naming:
'id' => 'scope.StandortID',
'contact__name' => 'scopeprovider.name',
```

### Proposed Solution

**Standardization Rules:**

1. **Language**: Convert all table and column names to English
2. **Database Convention**: Use snake_case for all table and column names
3. **Mapping Convention**: Use camelCase for all mapping variables in Query classes
4. **Consistency**: Align concept naming across the entire schema

**Migration Plan:**

1. **Phase 1: Table Renaming**
   - `oeffnungszeit` → `availability`
   - `standort` → `scope`
   - `buerger` → `citizen`
   - `feiertage` → `holidays`
   - `gesamtkalender` → `calendar`

2. **Phase 2: Column Standardization**
   - Convert all column names to snake_case
   - Standardize foreign key naming: `StandortID` → `scope_id`
   - Align field naming: `OeffnungszeitID` → `availability_id`

3. **Phase 3: Query Class Updates**
   - Update all `Zmsdb/Query/*` classes to use new table/column names
   - Ensure variable mapping uses camelCase: `scopeId` → `availability.scope_id`
   - Update entity mapping methods

### Expected Outcomes

- **Maintainability**: Consistent naming reduces cognitive load
- **Clarity**: English names improve international collaboration
- **Alignment**: Query classes match actual database schema
- **Future-proofing**: Standardized conventions for new development
- **Testability**: Easier to write and maintain tests with consistent naming

### Implementation Notes

- Create comprehensive migration scripts for each table
- Update all affected query classes in `zmsdb/src/Zmsdb/Query/`
- Ensure backward compatibility during transition period
- Update documentation and API references

### Example After Standardization

**Availability Query Class (After):**

```php
const TABLE = 'availability';  // English snake_case table name
// Maps to camelCase variables:
'id' => 'availability.availability_id',
'scopeId' => 'availability.scope_id',
'startDate' => 'availability.start_date',
```

**Scope Query Class (After):**

```php
const TABLE = 'scope';  // English snake_case table name
// Maps to camelCase variables:
'id' => 'scope.scope_id',
'contactName' => 'scopeprovider.name',
```

This approach ensures:

- **Database**: All snake_case (standard SQL convention)
- **Query Mappings**: All camelCase (standard PHP convention)
- **Consistency**: No exceptions, everything follows the same pattern

# Four phases:

1. Complete Table Renaming Plan - All English, All snake_case <mark>easy</mark>

- 47 tables converted from German to English
- All table names standardized to snake_case
- Organized by business priority (Core → User → System → Technical)
- Clear migration path with examples

2. Complete Column Renaming Plan - All English, All snake_case <mark>easy-medium</mark>

- Hundreds of columns converted from German to English
- All column names standardized to snake_case
- Foreign key naming standardized across all tables
- Common patterns identified and standardized

3. Complete PHP Variable Mapping Renaming Plan - All English, All camelCase <mark>hard</mark>

- All Query class mappings converted to camelCase
- Double underscore patterns eliminated
- Nested object patterns standardized
- Reference mappings updated

4. Long-Term Schema Vision (Beyond Renaming) <mark>strategic</mark>

- Structural splits (`buerger`, `queue_number_statistics`, `preferences`, DLDB `data` columns)
- Table disposition audit (drop, verify, or redesign)
- Migration naming hygiene and asset storage (S3 vs `image_data`)

<mark>Sections 1–3 focus on naming consistency. Section 4 captures larger architectural changes that may follow or run in parallel once prerequisites are clear.</mark>

<mark>Column mappings in section 2 cover all tables in the ZMS schema (see `.resources/zms.sql`). PHP variable mappings in section 3 are still being expanded table by table.</mark>

## 1. Complete Table Renaming Plan - All English, All snake_case

### Phase 1: Core Business Tables (High Priority)

| Current          | New (snake_case) | Reason                     |
| ---------------- | ---------------- | -------------------------- |
| `oeffnungszeit`  | `availability`   | Opening hours/availability |
| `standort`       | `scope`          | Location/scope             |
| `buerger`        | `citizen`        | Citizen                    |
| `feiertage`      | `holidays`       | Holidays                   |
| `gesamtkalender` | `calendar`       | Calendar                   |
| `behoerde`       | `department`     | Government department      |
| `organisation`   | `organization`   | Organization               |

### Phase 2: User & Process Tables

| Current              | New (snake_case)   | Reason                                                                 |
| -------------------- | ------------------ | ---------------------------------------------------------------------- |
| `buergeranliegen`    | `citizen_requests` | Citizen requests/issues                                                |
| `buergerarchiv`      | `citizen_archive`  | Archived citizen data                                                  |
| `buergerarchivtoday` | — (delete)         | Redundant snapshot table; merge into `citizen_archive` (see section 4) |
| `nutzer`             | `user`             | System users                                                           |
| `nutzerzuordnung`    | `user_assignment`  | User assignments                                                       |
| `kunde`              | `jurisdiction`     | Owner/jurisdiction (entity/API today: `owner`)                         |
| `kundenlinks`        | — (delete)         | Unused; drop table and related code                                    |

> **Note:** The `kunde` table and the `owner` entity/API will be renamed to `jurisdiction` (not `customer`). The `jurisdiction` permission (ZMSKVR-1345) already introduces this naming in the permission model; the database rename is planned as part of this refactor.
>
> **Note:** The `kundenlinks` table (Favoriten/bookmark links) is no longer used. Planned cleanup: drop the table and remove related code (e.g. `Link` entity, DB query, admin “Favoriten” UI).

### Phase 3: System & Configuration Tables

| Current            | New (snake_case)          | Reason                                                          |
| ------------------ | ------------------------- | --------------------------------------------------------------- |
| `abrechnung`       | — (delete)                | Unused; drop table (see section 4)                              |
| `ipausnahmen`      | `ip_exceptions`           | IP exceptions; verify usage (see section 4)                     |
| `kiosk`            | `kiosk`                   | Kiosk (universal term)                                          |
| `wartenrstatistik` | `queue_number_statistics` | Queue statistics; normalize into smaller tables (see section 4) |
| `standortcluster`  | `location_cluster`        | Location clustering                                             |
| `statistik`        | `statistics`              | Statistics                                                      |
| `role`             | `role`                    | RBAC roles (already snake_case)                                 |
| `permission`       | `permission`              | RBAC permissions (already snake_case)                           |
| `role_permission`  | `role_permission`         | Role–permission mapping (already snake_case)                    |
| `user_role`        | `user_role`               | User–role mapping (already snake_case)                          |

### Phase 4: API & Technical Tables

| Current     | New (snake_case) | Reason                                             |
| ----------- | ---------------- | -------------------------------------------------- |
| `apiclient` | `api_client`     | API client                                         |
| `apikey`    | `api_key`        | API key; verify production usage (see section 4)   |
| `apiquota`  | `api_quota`      | API quota; verify production usage (see section 4) |

### Phase 5: Communication Tables

| Current             | New (snake_case) | Reason                                    |
| ------------------- | ---------------- | ----------------------------------------- |
| `email`             | `email`          | Email (already snake_case)                |
| `sms`               | `sms`            | SMS (already snake_case)                  |
| `mailpart`          | `mail_part`      | Mail part                                 |
| `mailqueue`         | `mail_queue`     | Mail queue                                |
| `mailtemplate`      | `mail_template`  | Mail template                             |
| `notificationqueue` | — (delete)       | Verify unused; drop table (see section 4) |

### Phase 6: Data & Process Tables

| Current             | New (snake_case)            | Reason                                                    |
| ------------------- | --------------------------- | --------------------------------------------------------- |
| `closures`          | `closures`                  | Already snake_case                                        |
| `config`            | `config`                    | Already snake_case                                        |
| `eventlog`          | `event_log`                 | Event log; verify scope of use (see section 4)            |
| `imagedata`         | `image_data`                | Image data; move assets to S3 (see section 4)             |
| `log`               | `log`                       | Split `data` JSON for searchability (see section 4)       |
| `migrations`        | `migrations`                | Already snake_case                                        |
| `preferences`       | `scope_preferences` / split | Scope + system settings; rename and split (see section 4) |
| `process_sequence`  | `process_sequence`          | Already snake_case                                        |
| `sessiondata`       | `session_data`              | Session data                                              |
| `source`            | `source`                    | Already snake_case                                        |
| `overview_calendar` | `overview_calendar`         | Overview calendar (already snake_case)                    |

### Phase 7: Service & Provider Tables

| Current            | New (snake_case)              | Reason                                                           |
| ------------------ | ----------------------------- | ---------------------------------------------------------------- |
| `provider`         | `office` (candidate)          | DLDB location; align with zmscitizenapi `office` (see section 4) |
| `request`          | `service` (candidate)         | DLDB service; align with zmscitizenapi `service` (see section 4) |
| `request_provider` | `office_service` (candidate)  | Office–service relation; split `data` JSON (see section 4)       |
| `request_variant`  | `service_variant` (candidate) | Service variant; review naming with citizen API (see section 4)  |

### Phase 8: Slot System Tables

| Current         | New (snake_case) | Reason             |
| --------------- | ---------------- | ------------------ |
| `slot`          | `slot`           | Already snake_case |
| `slot_hiera`    | `slot_hierarchy` | Slot hierarchy     |
| `slot_process`  | `slot_process`   | Already snake_case |
| `slot_sequence` | `slot_sequence`  | Already snake_case |

### Phase 9: Assignment & Clustering Tables

| Current            | New (snake_case)     | Reason             |
| ------------------ | -------------------- | ------------------ |
| `clusterzuordnung` | `cluster_assignment` | Cluster assignment |

## 2. Complete Column Renaming Plan - All English, All snake_case

### Phase 1: Core Business Tables (High Priority)

#### availability (formerly oeffnungszeit)

| Current Column               | New Column (snake_case)         | Reason              |
| ---------------------------- | ------------------------------- | ------------------- |
| `OeffnungszeitID`            | `availability_id`               | Primary/foreign key |
| `StandortID`                 | `scope_id`                      | Primary/foreign key |
| `Startdatum`                 | `start_date`                    | Standardize naming  |
| `Endedatum`                  | `end_date`                      | Standardize naming  |
| `allexWochen`                | `every_x_weeks`                 | Standardize naming  |
| `jedexteWoche`               | `every_other_week`              | Standardize naming  |
| `Wochentag`                  | `weekday`                       | Standardize naming  |
| `Anfangszeit`                | `start_time`                    | Standardize naming  |
| `Terminanfangszeit`          | `appointment_start_time`        | Standardize naming  |
| `Endzeit`                    | `end_time`                      | Standardize naming  |
| `Terminendzeit`              | `appointment_end_time`          | Standardize naming  |
| `Timeslot`                   | `time_slot`                     | Standardize naming  |
| `Anzahlarbeitsplaetze`       | `workstation_count`             | Standardize naming  |
| `Anzahlterminarbeitsplaetze` | `appointment_workstation_count` | Standardize naming  |
| `kommentar`                  | `comment`                       | Standardize naming  |
| `reduktionTermineImInternet` | `internet_reduction`            | Standardize naming  |
| `erlaubemehrfachslots`       | `multiple_slots_allowed`        | Standardize naming  |
| `reduktionTermineCallcenter` | `callcenter_reduction`          | Standardize naming  |
| `Offen_ab`                   | `open_from_days`                | Standardize naming  |
| `Offen_bis`                  | `open_until_days`               | Standardize naming  |
| `updateTimestamp`            | `updated_at`                    | Timestamp           |

#### scope (formerly standort)

| Current Column                     | New Column (snake_case)        | Reason              |
| ---------------------------------- | ------------------------------ | ------------------- |
| `StandortID`                       | `scope_id`                     | Primary/foreign key |
| `BehoerdenID`                      | `department_id`                | Primary/foreign key |
| `InfoDienstleisterID`              | `info_provider_id`             | Primary/foreign key |
| `Hinweis`                          | `hint`                         | Standardize naming  |
| `Bezeichnung`                      | `name`                         | Standardize naming  |
| `Adresse`                          | `address`                      | Standardize naming  |
| `Stadtplanlink`                    | `city_map_link`                | Standardize naming  |
| `Bearbeitungszeit`                 | `processing_time`              | Standardize naming  |
| `Kennung`                          | `identifier`                   | Standardize naming  |
| `Termine_ab`                       | `appointments_from_days`       | Standardize naming  |
| `Termine_bis`                      | `appointments_until_days`      | Standardize naming  |
| `smswarteschlange`                 | `sms_queue`                    | Standardize naming  |
| `smswmsbestaetigung`               | `sms_wms_confirmation`         | Standardize naming  |
| `smsbenachrichtigungsfrist`        | `sms_notification_deadline`    | Standardize naming  |
| `smsbenachrichtigungstext`         | `sms_notification_text`        | Standardize naming  |
| `smsbestaetigungstext`             | `sms_confirmation_text`        | Standardize naming  |
| `wartenrsperre`                    | `queue_number_locked`          | Standardize naming  |
| `wartenrhinweis`                   | `queue_hint`                   | Standardize naming  |
| `notruffunktion`                   | `emergency_function`           | Standardize naming  |
| `notrufausgeloest`                 | `emergency_triggered`          | Standardize naming  |
| `notrufinitiierung`                | `emergency_initiation`         | Standardize naming  |
| `notrufantwort`                    | `emergency_response`           | Standardize naming  |
| `emailPflichtfeld`                 | `email_required`               | Standardize naming  |
| `anmerkungPflichtfeld`             | `comment_required`             | Standardize naming  |
| `anmerkungLabel`                   | `comment_label`                | Standardize naming  |
| `telefonPflichtfeld`               | `phone_required`               | Standardize naming  |
| `standortinfozeile`                | `location_info_line`           | Standardize naming  |
| `standortkuerzel`                  | `short_name`                   | Standardize naming  |
| `aufrufanzeigetext`                | `display_text`                 | Standardize naming  |
| `reservierungsdauer`               | `reservation_duration`         | Standardize naming  |
| `anzahlwiederaufruf`               | `recall_count`                 | Standardize naming  |
| `startwartenr`                     | `first_queue_number`           | Standardize naming  |
| `endwartenr`                       | `last_queue_number_limit`      | Standardize naming  |
| `letztewartenr`                    | `last_queue_number`            | Standardize naming  |
| `wartenrdatum`                     | `queue_number_date`            | Standardize naming  |
| `mehrfachtermine`                  | `multiple_appointments`        | Standardize naming  |
| `schreibschutz`                    | `write_protection`             | Standardize naming  |
| `ohnestatistik`                    | `without_statistics`           | Standardize naming  |
| `smskioskangebotsfrist`            | `sms_kiosk_offer_deadline`     | Standardize naming  |
| `emailstandortadmin`               | `admin_email`                  | Standardize naming  |
| `wartenummernkontingent`           | `queue_number_contingent`      | Standardize naming  |
| `vergebenewartenummern`            | `assigned_queue_numbers`       | Standardize naming  |
| `kundenbefragung`                  | `customer_survey`              | Standardize naming  |
| `kundenbef_label`                  | `customer_survey_label`        | Standardize naming  |
| `kundenbef_emailtext`              | `customer_survey_email_text`   | Standardize naming  |
| `telefonaktiviert`                 | `phone_enabled`                | Standardize naming  |
| `virtuellesachbearbeiterzahl`      | `virtual_processor_count`      | Standardize naming  |
| `datumvirtuellesachbearbeiterzahl` | `virtual_processor_count_date` | Standardize naming  |
| `smsnachtrag`                      | `sms_addition`                 | Standardize naming  |
| `loeschdauer`                      | `deletion_duration`            | Standardize naming  |
| `updateTimestamp`                  | `updated_at`                   | Timestamp           |
| `source`                           | `source`                       | Already snake_case  |
| `custom_text_field_label`          | `custom_text_field_label`      | Already snake_case  |
| `custom_text_field_active`         | `custom_text_field_active`     | Already snake_case  |
| `custom_text_field_required`       | `custom_text_field_required`   | Already snake_case  |
| `admin_mail_on_appointment`        | `admin_mail_on_appointment`    | Already snake_case  |
| `admin_mail_on_deleted`            | `admin_mail_on_deleted`        | Already snake_case  |
| `admin_mail_on_updated`            | `admin_mail_on_updated`        | Already snake_case  |
| `admin_mail_on_mail_sent`          | `admin_mail_on_mail_sent`      | Already snake_case  |
| `appointments_per_mail`            | `appointments_per_mail`        | Already snake_case  |
| `whitelisted_mails`                | `whitelisted_mails`            | Already snake_case  |
| `slots_per_appointment`            | `slots_per_appointment`        | Already snake_case  |
| `info_for_appointment`             | `info_for_appointment`         | Already snake_case  |
| `aktivierungsdauer`                | `activation_duration`          | Standardize naming  |
| `captcha_activated_required`       | `captcha_activated_required`   | Already snake_case  |
| `email_confirmation_activated`     | `email_confirmation_activated` | Already snake_case  |
| `custom_text_field2_label`         | `custom_text_field2_label`     | Already snake_case  |
| `custom_text_field2_active`        | `custom_text_field2_active`    | Already snake_case  |
| `custom_text_field2_required`      | `custom_text_field2_required`  | Already snake_case  |
| `info_for_all_appointments`        | `info_for_all_appointments`    | Already snake_case  |
| `last_display_number`              | `last_display_number`          | Already snake_case  |
| `max_display_number`               | `max_display_number`           | Already snake_case  |
| `display_number_prefix`            | `display_number_prefix`        | Already snake_case  |

#### process (formerly buerger)

| Current Column                   | New Column (snake_case)       | Reason              |
| -------------------------------- | ----------------------------- | ------------------- |
| `BuergerID`                      | `process_id`                  | Primary/foreign key |
| `StandortID`                     | `scope_id`                    | Primary/foreign key |
| `Datum`                          | `date`                        | Standardize naming  |
| `Uhrzeit`                        | `time`                        | Standardize naming  |
| `Name`                           | `name`                        | Standardize naming  |
| `Anmerkung`                      | `comment`                     | Standardize naming  |
| `Telefonnummer`                  | `phone`                       | Standardize naming  |
| `EMail`                          | `email`                       | Standardize naming  |
| `EMailverschickt`                | `email_sent_count`            | Standardize naming  |
| `Erinnerungszeitpunkt`           | `reminder_timestamp`          | Timestamp           |
| `SMSverschickt`                  | `sms_sent_count`              | Standardize naming  |
| `AnzahlAufrufe`                  | `call_count`                  | Standardize naming  |
| `Timestamp`                      | `timestamp`                   | Timestamp           |
| `IPAdresse`                      | `ip_address`                  | Standardize naming  |
| `IPTimeStamp`                    | `ip_timestamp`                | Timestamp           |
| `NutzerID`                       | `user_id`                     | Primary/foreign key |
| `aufruferfolgreich`              | `call_successful`             | Standardize naming  |
| `wsm_aufnahmezeit`               | `ticket_printer_capture_time` | Standardize naming  |
| `aufrufzeit`                     | `call_time`                   | Standardize naming  |
| `nicht_erschienen`               | `did_not_appear`              | Standardize naming  |
| `Abholer`                        | `pickup_person`               | Standardize naming  |
| `AbholortID`                     | `pickup_scope_id`             | Primary/foreign key |
| `wartenummer`                    | `queue_number`                | Standardize naming  |
| `vorlaeufigeBuchung`             | `provisional_booking`         | Standardize naming  |
| `hatFolgetermine`                | `follow_up_appointment_count` | Standardize naming  |
| `istFolgeterminvon`              | `follow_up_of_process_id`     | Primary/foreign key |
| `zustimmung_kundenbefragung`     | `survey_accepted`             | Standardize naming  |
| `telefonnummer_fuer_rueckfragen` | `callback_phone`              | Standardize naming  |
| `absagecode`                     | `auth_key`                    | Standardize naming  |
| `AnzahlPersonen`                 | `person_count`                | Standardize naming  |
| `updateTimestamp`                | `updated_at`                  | Timestamp           |
| `apiClientID`                    | `api_client_id`               | Primary/foreign key |
| `custom_text_field`              | `custom_text_field`           | Already snake_case  |
| `showUpTime`                     | `show_up_time`                | Standardize naming  |
| `finishTime`                     | `finish_time`                 | Standardize naming  |
| `timeoutTime`                    | `timeout_time`                | Standardize naming  |
| `way_time`                       | `way_time`                    | Already snake_case  |
| `parked`                         | `parked`                      | Already snake_case  |
| `processing_time`                | `processing_time`             | Already snake_case  |
| `bestaetigt`                     | `confirmed`                   | Standardize naming  |
| `waiting_time`                   | `waiting_time`                | Already snake_case  |
| `wasMissed`                      | `was_missed`                  | Standardize naming  |
| `custom_text_field2`             | `custom_text_field2`          | Already snake_case  |
| `status`                         | `status`                      | Already snake_case  |
| `priority`                       | `priority`                    | Already snake_case  |
| `external_user_id`               | `external_user_id`            | Already snake_case  |
| `displayNumber`                  | `display_number`              | Standardize naming  |

##### Dereference payload in `Anmerkung` / custom text fields (technical debt)

When a process is finished or soft-deleted, `Process::writeBlockedEntity()` runs `QUERY_DEREFERENCED` (`zmsdb/src/Zmsdb/Query/Process.php`). That update clears PII and sets `StandortID = 0`, `Name = 'dereferenced'`, and `status = 'blocked'`. Because the row no longer has a usable `scope_id`, the original scope and metadata are **serialized into free-text columns** using PHP `var_export()`:

| Column               | Written by                                | Payload shape                                                       |
| -------------------- | ----------------------------------------- | ------------------------------------------------------------------- |
| `Anmerkung`          | `Process::toDerefencedAmendment()`        | `BuergerID`, `StandortID`, `Anmerkung`, `IPTimeStamp`, `LastChange` |
| `custom_text_field`  | `Process::toDerefencedCustomTextfield()`  | same pattern with `CustomTextfield`                                 |
| `custom_text_field2` | `Process::toDerefencedCustomTextfield2()` | same pattern with `CustomTextfield2`                                |

Example (what remains in `Anmerkung` after dereference):

```
array (
  'BuergerID' => 100000,
  'StandortID' => 1,
  'Anmerkung' => NULL,
  'IPTimeStamp' => 0,
  'LastChange' => '1970-01-01T01:00:00+01:00',
)
```

**Where this payload is read back (string parsing, not typed columns):**

- `CalculateDailyWaitingStatisticByCron::extractScopeFromAnmerkung()` — regex on all three columns when `StandortID = 0` (`zmsdb/src/Zmsdb/Helper/CalculateDailyWaitingStatisticByCron.php`)
- Ad-hoc SQL in maintenance migrations (e.g. `SUBSTRING_INDEX` / `LIKE` on `'StandortID' =>` in `Anmerkung` and custom text fields)
- Any code path that must resolve scope on a dereferenced shell row before the cron deletes it

**Why this is bad practice and must not be copied in new schema:**

- **Wrong column semantics:** `Anmerkung` / custom text fields are user-facing comment fields, not an archive or audit store.
- **Fragile parsing:** Scope and IDs are recovered with regex/`SUBSTRING_INDEX` on `var_export` output; a `NULL` or overwritten `StandortID` in the string breaks downstream jobs (e.g. archive cron inserting `NULL` into `buergerarchivtoday.StandortID`).
- **Duplicated payload:** The same structural array is written to three unrelated columns.
- **No schema enforcement:** Nothing prevents post-finish updates from corrupting the string or flipping `status` while `StandortID` stays `0`.

**Target direction (when refactoring `process` / archive):**

- Persist dereference metadata in **typed columns or a dedicated `process_dereference` / audit table** (`process_id`, `scope_id`, `archived_at`, …).
- Stop writing `var_export` arrays into `comment` / custom text fields.
- Remove regex-based scope recovery from cron and statistics paths once shells expose a real FK or archive link.

#### holidays (formerly feiertage)

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `FeiertagID`      | `holiday_id`            | Primary/foreign key |
| `Datum`           | `date`                  | Standardize naming  |
| `Feiertag`        | `name`                  | Standardize naming  |
| `BehoerdenID`     | `department_id`         | Primary/foreign key |
| `updateTimestamp` | `updated_at`            | Timestamp           |

#### calendar (formerly gesamtkalender)

| Current Column    | New Column (snake_case) | Reason             |
| ----------------- | ----------------------- | ------------------ |
| `id`              | `id`                    | Already snake_case |
| `scope_id`        | `scope_id`              | Already snake_case |
| `availability_id` | `availability_id`       | Already snake_case |
| `time`            | `time`                  | Already snake_case |
| `seat`            | `seat`                  | Already snake_case |
| `process_id`      | `process_id`            | Already snake_case |
| `slots`           | `slots`                 | Already snake_case |
| `status`          | `status`                | Already snake_case |
| `updated_at`      | `updated_at`            | Already snake_case |

#### department (formerly behoerde)

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `BehoerdenID`     | `department_id`         | Primary/foreign key |
| `OrganisationsID` | `organization_id`       | Primary/foreign key |
| `KundenID`        | `jurisdiction_id`       | Primary/foreign key |
| `Name`            | `name`                  | Standardize naming  |
| `Adresse`         | `address`               | Standardize naming  |
| `Ansprechpartner` | `contact_person`        | Standardize naming  |
| `IPProtectZeit`   | `ip_protection_time`    | Standardize naming  |

#### organization (formerly organisation)

| Current Column        | New Column (snake_case)     | Reason              |
| --------------------- | --------------------------- | ------------------- |
| `OrganisationsID`     | `organization_id`           | Primary/foreign key |
| `InfoBezirkID`        | `info_district_id`          | Primary/foreign key |
| `KundenID`            | `jurisdiction_id`           | Primary/foreign key |
| `Organisationsname`   | `name`                      | Standardize naming  |
| `Anschrift`           | `address`                   | Standardize naming  |
| `kioskpasswortschutz` | `kiosk_password_protection` | Standardize naming  |

### Phase 2: User & Process Tables (Medium Priority)

#### citizen_requests (formerly buergeranliegen)

| Current Column      | New Column (snake_case) | Reason              |
| ------------------- | ----------------------- | ------------------- |
| `BuergeranliegenID` | `citizen_request_id`    | Primary/foreign key |
| `BuergerID`         | `process_id`            | Primary/foreign key |
| `BuergerarchivID`   | `citizen_archive_id`    | Primary/foreign key |
| `AnliegenID`        | `request_id`            | Primary/foreign key |
| `source`            | `source`                | Already snake_case  |

#### citizen_archive (formerly buergerarchiv)

| Current Column     | New Column (snake_case) | Reason              |
| ------------------ | ----------------------- | ------------------- |
| `BuergerarchivID`  | `citizen_archive_id`    | Primary/foreign key |
| `StandortID`       | `scope_id`              | Primary/foreign key |
| `Datum`            | `date`                  | Standardize naming  |
| `mitTermin`        | `with_appointment`      | Standardize naming  |
| `nicht_erschienen` | `did_not_appear`        | Standardize naming  |
| `Timestamp`        | `timestamp`             | Timestamp           |
| `waiting_time`     | `waiting_time`          | Already snake_case  |
| `AnzahlPersonen`   | `person_count`          | Standardize naming  |
| `processing_time`  | `processing_time`       | Already snake_case  |
| `name`             | `name`                  | Already snake_case  |
| `dienstleistungen` | `services`              | Standardize naming  |
| `way_time`         | `way_time`              | Already snake_case  |

#### citizen_archive_today (formerly buergerarchivtoday)

| Current Column     | New Column (snake_case) | Reason              |
| ------------------ | ----------------------- | ------------------- |
| `BuergerarchivID`  | `citizen_archive_id`    | Primary/foreign key |
| `StandortID`       | `scope_id`              | Primary/foreign key |
| `Datum`            | `date`                  | Standardize naming  |
| `mitTermin`        | `with_appointment`      | Standardize naming  |
| `nicht_erschienen` | `did_not_appear`        | Standardize naming  |
| `Timestamp`        | `timestamp`             | Timestamp           |
| `waiting_time`     | `waiting_time`          | Already snake_case  |
| `AnzahlPersonen`   | `person_count`          | Standardize naming  |
| `processing_time`  | `processing_time`       | Already snake_case  |
| `name`             | `name`                  | Already snake_case  |
| `dienstleistungen` | `services`              | Standardize naming  |
| `way_time`         | `way_time`              | Already snake_case  |

#### user (formerly nutzer)

| Current Column      | New Column (snake_case) | Reason              |
| ------------------- | ----------------------- | ------------------- |
| `NutzerID`          | `user_id`               | Primary/foreign key |
| `Name`              | `name`                  | Standardize naming  |
| `Passworthash`      | `password_hash`         | Standardize naming  |
| `Frage`             | `security_question`     | Standardize naming  |
| `Antworthash`       | `answer_hash`           | Standardize naming  |
| `Berechtigung`      | `permission_level`      | Standardize naming  |
| `KundenID`          | `jurisdiction_id`       | Primary/foreign key |
| `BehoerdenID`       | `department_id`         | Primary/foreign key |
| `SessionID`         | `session_id`            | Primary/foreign key |
| `StandortID`        | `scope_id`              | Primary/foreign key |
| `Arbeitsplatznr`    | `workstation_number`    | Standardize naming  |
| `Datum`             | `date`                  | Standardize naming  |
| `Kalenderansicht`   | `calendar_view`         | Standardize naming  |
| `clusteransicht`    | `cluster_view`          | Standardize naming  |
| `notrufinitiierung` | `emergency_initiation`  | Standardize naming  |
| `notrufantwort`     | `emergency_response`    | Standardize naming  |
| `aufrufzusatz`      | `call_suffix`           | Standardize naming  |
| `lastUpdate`        | `last_update`           | Standardize naming  |
| `sessionExpiry`     | `session_expiry`        | Standardize naming  |

#### user_assignment (formerly nutzerzuordnung)

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `nutzerid`     | `user_id`               | Primary/foreign key |
| `behoerdenid`  | `department_id`         | Primary/foreign key |

#### jurisdiction (formerly kunde)

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `KundenID`        | `jurisdiction_id`       | Primary/foreign key |
| `Kundenname`      | `name`                  | Standardize naming  |
| `Anschrift`       | `address`               | Standardize naming  |
| `Module`          | `modules`               | Standardize naming  |
| `Startkennung`    | `start_identifier`      | Standardize naming  |
| `Anzahlkennungen` | `identifier_count`      | Standardize naming  |
| `TerminURL`       | `appointment_url`       | Standardize naming  |

#### customer_links (formerly kundenlinks)

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `linkid`          | `link_id`               | Primary/foreign key |
| `kundenid`        | `jurisdiction_id`       | Primary/foreign key |
| `organisationsid` | `organization_id`       | Primary/foreign key |
| `behoerdenid`     | `department_id`         | Primary/foreign key |
| `beschreibung`    | `description`           | Standardize naming  |
| `link`            | `link`                  | Already snake_case  |
| `oeffentlich`     | `public`                | Standardize naming  |
| `neuerFrame`      | `new_frame`             | Standardize naming  |

### Phase 3: System & Configuration Tables (Lower Priority)

#### billing (formerly abrechnung)

| Current Column  | New Column (snake_case) | Reason              |
| --------------- | ----------------------- | ------------------- |
| `AbrechnungsID` | `billing_id`            | Primary/foreign key |
| `StandortID`    | `scope_id`              | Primary/foreign key |
| `Telefonnummer` | `phone`                 | Standardize naming  |
| `Datum`         | `date`                  | Standardize naming  |
| `gesendet`      | `sent`                  | Standardize naming  |

#### ip_exceptions (formerly ipausnahmen)

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `IPID`         | `ip_exception_id`       | Primary/foreign key |
| `BehoerdenID`  | `department_id`         | Primary/foreign key |
| `IPAdresse`    | `ip_address`            | Standardize naming  |

#### kiosk

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `kioskid`         | `kiosk_id`              | Primary/foreign key |
| `kundenid`        | `jurisdiction_id`       | Primary/foreign key |
| `organisationsid` | `organization_id`       | Primary/foreign key |
| `timestamp`       | `timestamp`             | Already snake_case  |
| `cookiecode`      | `cookie_code`           | Standardize naming  |
| `name`            | `name`                  | Already snake_case  |
| `zugelassen`      | `allowed`               | Standardize naming  |

#### queue_number_statistics (formerly wartenrstatistik)

| Current Column                               | New Column (snake_case)                      | Reason                                                    |
| -------------------------------------------- | -------------------------------------------- | --------------------------------------------------------- |
| `datum`                                      | `date`                                       | Date                                                      |
| `standortid`                                 | `scope_id`                                   | Foreign key to scope                                      |
| `wartenrstatistikid`                         | `queue_number_statistics_id`                 | Primary key                                               |
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

| Current Column            | New Column (snake_case) | Reason              |
| ------------------------- | ----------------------- | ------------------- |
| `clusterID`               | `cluster_id`            | Primary/foreign key |
| `name`                    | `name`                  | Already snake_case  |
| `clusterinfozeile1`       | `cluster_info_line_1`   | Standardize naming  |
| `clusterinfozeile2`       | `cluster_info_line_2`   | Standardize naming  |
| `stadtplanlink`           | `city_map_link`         | Standardize naming  |
| `aufrufanzeigetext`       | `display_text`          | Standardize naming  |
| `standortkuerzelanzeigen` | `show_scope_short_name` | Standardize naming  |

#### statistics (formerly statistik)

| Current Column        | New Column (snake_case)   | Reason              |
| --------------------- | ------------------------- | ------------------- |
| `statistikid`         | `statistics_id`           | Primary/foreign key |
| `kundenid`            | `jurisdiction_id`         | Primary/foreign key |
| `organisationsid`     | `organization_id`         | Primary/foreign key |
| `behoerdenid`         | `department_id`           | Primary/foreign key |
| `clusterid`           | `cluster_id`              | Primary/foreign key |
| `standortid`          | `scope_id`                | Primary/foreign key |
| `anliegenid`          | `request_id`              | Primary/foreign key |
| `datum`               | `datum`                   | Already snake_case  |
| `lastbuergerarchivid` | `last_citizen_archive_id` | Primary/foreign key |
| `termin`              | `with_appointment`        | Standardize naming  |
| `info_dl_id`          | `info_provider_id`        | Primary/foreign key |
| `processing_time`     | `processing_time`         | Already snake_case  |

#### api_client (formerly apiclient)

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `apiClientID`     | `api_client_id`         | Primary/foreign key |
| `clientKey`       | `client_key`            | Standardize naming  |
| `shortname`       | `short_name`            | Standardize naming  |
| `accesslevel`     | `access_level`          | Standardize naming  |
| `updateTimestamp` | `updated_at`            | Timestamp           |

#### api_key (formerly apikey)

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `key`          | `key`                   | Already snake_case  |
| `createIP`     | `create_ip`             | Standardize naming  |
| `ts`           | `ts`                    | Already snake_case  |
| `apiClientID`  | `api_client_id`         | Primary/foreign key |

#### api_quota (formerly apiquota)

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `quotaid`      | `quota_id`              | Primary/foreign key |
| `key`          | `key`                   | Already snake_case  |
| `route`        | `route`                 | Already snake_case  |
| `period`       | `period`                | Already snake_case  |
| `requests`     | `requests`              | Already snake_case  |
| `ts`           | `ts`                    | Already snake_case  |

#### role

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `id`           | `id`                    | Already snake_case |
| `name`         | `name`                  | Already snake_case |
| `description`  | `description`           | Already snake_case |

#### permission

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `id`           | `id`                    | Already snake_case |
| `name`         | `name`                  | Already snake_case |
| `description`  | `description`           | Already snake_case |

#### role_permission

| Current Column  | New Column (snake_case) | Reason             |
| --------------- | ----------------------- | ------------------ |
| `role_id`       | `role_id`               | Already snake_case |
| `permission_id` | `permission_id`         | Already snake_case |

#### user_role

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `user_id`      | `user_id`               | Already snake_case |
| `role_id`      | `role_id`               | Already snake_case |

### Phase 4: Communication Tables

#### email

| Current Column                 | New Column (snake_case)        | Reason              |
| ------------------------------ | ------------------------------ | ------------------- |
| `emailID`                      | `email_id`                     | Primary/foreign key |
| `BehoerdenID`                  | `department_id`                | Primary/foreign key |
| `serveradresse`                | `server_address`               | Standardize naming  |
| `authentication`               | `authentication`               | Already snake_case  |
| `username`                     | `username`                     | Already snake_case  |
| `password`                     | `password`                     | Already snake_case  |
| `ssl_coding`                   | `ssl_encoding`                 | Standardize naming  |
| `absenderadresse`              | `sender_address`               | Standardize naming  |
| `send_reminder`                | `send_reminder`                | Already snake_case  |
| `send_reminder_minutes_before` | `send_reminder_minutes_before` | Already snake_case  |

#### sms

| Current Column         | New Column (snake_case) | Reason              |
| ---------------------- | ----------------------- | ------------------- |
| `smsID`                | `sms_id`                | Primary/foreign key |
| `BehoerdenID`          | `department_id`         | Primary/foreign key |
| `enabled`              | `enabled`               | Already snake_case  |
| `Absender`             | `sender`                | Standardize naming  |
| `interneterinnerung`   | `internet_reminder`     | Standardize naming  |
| `internetbestaetigung` | `internet_confirmation` | Standardize naming  |

#### mail_part (formerly mailpart)

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `id`           | `id`                    | Already snake_case  |
| `queueId`      | `queue_id`              | Primary/foreign key |
| `mime`         | `mime`                  | Already snake_case  |
| `content`      | `content`               | Already snake_case  |
| `base64`       | `base64`                | Already snake_case  |

#### mail_queue (formerly mailqueue)

| Current Column     | New Column (snake_case) | Reason              |
| ------------------ | ----------------------- | ------------------- |
| `id`               | `id`                    | Already snake_case  |
| `processID`        | `process_id`            | Primary/foreign key |
| `departmentID`     | `department_id`         | Primary/foreign key |
| `createIP`         | `create_ip`             | Standardize naming  |
| `createTimestamp`  | `created_at`            | Timestamp           |
| `subject`          | `subject`               | Already snake_case  |
| `clientFamilyName` | `client_family_name`    | Standardize naming  |
| `clientEmail`      | `client_email`          | Standardize naming  |

#### mail_template (formerly mailtemplate)

| Current Column    | New Column (snake_case) | Reason             |
| ----------------- | ----------------------- | ------------------ |
| `id`              | `id`                    | Already snake_case |
| `name`            | `name`                  | Already snake_case |
| `value`           | `value`                 | Already snake_case |
| `provider`        | `provider`              | Already snake_case |
| `changeTimestamp` | `changed_at`            | Timestamp          |

#### notification_queue (formerly notificationqueue)

| Current Column     | New Column (snake_case) | Reason              |
| ------------------ | ----------------------- | ------------------- |
| `id`               | `id`                    | Already snake_case  |
| `processID`        | `process_id`            | Primary/foreign key |
| `departmentID`     | `department_id`         | Primary/foreign key |
| `createIP`         | `create_ip`             | Standardize naming  |
| `createTimestamp`  | `created_at`            | Timestamp           |
| `message`          | `message`               | Already snake_case  |
| `clientFamilyName` | `client_family_name`    | Standardize naming  |
| `clientTelephone`  | `client_phone`          | Standardize naming  |
| `scopeID`          | `scope_id`              | Primary/foreign key |

### Phase 5: Data & Process Tables

#### closures

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `id`              | `id`                    | Already snake_case  |
| `year`            | `year`                  | Already snake_case  |
| `month`           | `month`                 | Already snake_case  |
| `day`             | `day`                   | Already snake_case  |
| `StandortID`      | `scope_id`              | Primary/foreign key |
| `updateTimestamp` | `updated_at`            | Timestamp           |

#### config

| Current Column    | New Column (snake_case) | Reason             |
| ----------------- | ----------------------- | ------------------ |
| `name`            | `name`                  | Already snake_case |
| `value`           | `value`                 | Already snake_case |
| `changeTimestamp` | `changed_at`            | Timestamp          |

#### event_log (formerly eventlog)

| Current Column       | New Column (snake_case) | Reason              |
| -------------------- | ----------------------- | ------------------- |
| `eventId`            | `event_id`              | Primary/foreign key |
| `eventName`          | `event_name`            | Standardize naming  |
| `origin`             | `origin`                | Already snake_case  |
| `referenceType`      | `reference_type`        | Standardize naming  |
| `reference`          | `reference`             | Already snake_case  |
| `sessionid`          | `session_id`            | Primary/foreign key |
| `contextjson`        | `context_json`          | Standardize naming  |
| `creationDateTime`   | `created_at`            | Timestamp           |
| `expirationDateTime` | `expires_at`            | Timestamp           |

#### image_data (formerly imagedata)

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `imagename`    | `image_name`            | Standardize naming |
| `imagecontent` | `image_content`         | Standardize naming |
| `ts`           | `ts`                    | Already snake_case |

#### log

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `log_id`       | `log_id`                | Already snake_case |
| `type`         | `type`                  | Already snake_case |
| `reference_id` | `reference_id`          | Already snake_case |
| `ts`           | `ts`                    | Already snake_case |
| `message`      | `message`               | Already snake_case |
| `scope_id`     | `scope_id`              | Already snake_case |
| `data`         | `data`                  | Already snake_case |
| `user_id`      | `user_id`               | Already snake_case |

#### migrations

| Current Column    | New Column (snake_case) | Reason             |
| ----------------- | ----------------------- | ------------------ |
| `filename`        | `filename`              | Already snake_case |
| `changeTimestamp` | `changed_at`            | Timestamp          |

#### preferences

| Current Column    | New Column (snake_case) | Reason             |
| ----------------- | ----------------------- | ------------------ |
| `entity`          | `entity`                | Already snake_case |
| `id`              | `id`                    | Already snake_case |
| `groupName`       | `group_name`            | Standardize naming |
| `name`            | `name`                  | Already snake_case |
| `value`           | `value`                 | Already snake_case |
| `updateTimestamp` | `updated_at`            | Timestamp          |

#### process_sequence

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `processId`    | `process_id`            | Primary/foreign key |

#### session_data (formerly sessiondata)

| Current Column   | New Column (snake_case) | Reason              |
| ---------------- | ----------------------- | ------------------- |
| `sessionid`      | `session_id`            | Primary/foreign key |
| `sessionname`    | `session_name`          | Standardize naming  |
| `sessioncontent` | `session_content`       | Standardize naming  |
| `ts`             | `ts`                    | Already snake_case  |

#### source

| Current Column   | New Column (snake_case) | Reason             |
| ---------------- | ----------------------- | ------------------ |
| `source`         | `source`                | Already snake_case |
| `label`          | `label`                 | Already snake_case |
| `editable`       | `editable`              | Already snake_case |
| `contact__name`  | `contact__name`         | Already snake_case |
| `contact__email` | `contact__email`        | Already snake_case |
| `lastChange`     | `last_change`           | Standardize naming |

#### overview_calendar

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `id`           | `id`                    | Already snake_case |
| `scope_id`     | `scope_id`              | Already snake_case |
| `process_id`   | `process_id`            | Already snake_case |
| `status`       | `status`                | Already snake_case |
| `starts_at`    | `starts_at`             | Already snake_case |
| `ends_at`      | `ends_at`               | Already snake_case |
| `updated_at`   | `updated_at`            | Already snake_case |

### Phase 6: Service & Provider Tables

#### provider

| Current Column          | New Column (snake_case) | Reason             |
| ----------------------- | ----------------------- | ------------------ |
| `source`                | `source`                | Already snake_case |
| `id`                    | `id`                    | Already snake_case |
| `name`                  | `name`                  | Already snake_case |
| `contact__city`         | `contact__city`         | Already snake_case |
| `contact__country`      | `contact__country`      | Already snake_case |
| `contact__lat`          | `contact__lat`          | Already snake_case |
| `contact__lon`          | `contact__lon`          | Already snake_case |
| `contact__postalCode`   | `contact__postalCode`   | Already snake_case |
| `contact__region`       | `contact__region`       | Already snake_case |
| `contact__street`       | `contact__street`       | Already snake_case |
| `contact__streetNumber` | `contact__streetNumber` | Already snake_case |
| `link`                  | `link`                  | Already snake_case |
| `data`                  | `data`                  | Already snake_case |
| `display_name`          | `display_name`          | Already snake_case |
| `parent_id`             | `parent_id`             | Already snake_case |

#### request

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `source`       | `source`                | Already snake_case |
| `id`           | `id`                    | Already snake_case |
| `name`         | `name`                  | Already snake_case |
| `link`         | `link`                  | Already snake_case |
| `group`        | `group`                 | Already snake_case |
| `data`         | `data`                  | Already snake_case |
| `parent_id`    | `parent_id`             | Already snake_case |
| `variant_id`   | `variant_id`            | Already snake_case |

#### request_provider

| Current Column      | New Column (snake_case) | Reason             |
| ------------------- | ----------------------- | ------------------ |
| `source`            | `source`                | Already snake_case |
| `request__id`       | `request__id`           | Already snake_case |
| `provider__id`      | `provider__id`          | Already snake_case |
| `slots`             | `slots`                 | Already snake_case |
| `bookable`          | `bookable`              | Already snake_case |
| `max_quantity`      | `max_quantity`          | Already snake_case |
| `public_visibility` | `public_visibility`     | Already snake_case |

#### request_variant

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `id`           | `id`                    | Already snake_case |
| `name`         | `name`                  | Already snake_case |

### Phase 7: Slot System Tables

#### slot

| Current Column      | New Column (snake_case) | Reason              |
| ------------------- | ----------------------- | ------------------- |
| `slotID`            | `slot_id`               | Primary/foreign key |
| `scopeID`           | `scope_id`              | Primary/foreign key |
| `year`              | `year`                  | Already snake_case  |
| `month`             | `month`                 | Already snake_case  |
| `day`               | `day`                   | Already snake_case  |
| `time`              | `time`                  | Already snake_case  |
| `availabilityID`    | `availability_id`       | Primary/foreign key |
| `public`            | `public`                | Already snake_case  |
| `callcenter`        | `callcenter`            | Already snake_case  |
| `intern`            | `intern`                | Already snake_case  |
| `status`            | `status`                | Already snake_case  |
| `slotTimeInMinutes` | `slot_time_in_minutes`  | Standardize naming  |
| `createTimestamp`   | `created_at`            | Timestamp           |
| `updateTimestamp`   | `updated_at`            | Timestamp           |

#### slot_hierarchy (formerly slot_hiera)

| Current Column  | New Column (snake_case) | Reason              |
| --------------- | ----------------------- | ------------------- |
| `slothieraID`   | `slot_hierarchy_id`     | Primary/foreign key |
| `slotID`        | `slot_id`               | Primary/foreign key |
| `ancestorID`    | `ancestor_id`           | Primary/foreign key |
| `ancestorLevel` | `ancestor_level`        | Standardize naming  |

#### slot_process

| Current Column    | New Column (snake_case) | Reason              |
| ----------------- | ----------------------- | ------------------- |
| `slotID`          | `slot_id`               | Primary/foreign key |
| `processID`       | `process_id`            | Primary/foreign key |
| `updateTimestamp` | `updated_at`            | Timestamp           |

#### slot_sequence

| Current Column | New Column (snake_case) | Reason             |
| -------------- | ----------------------- | ------------------ |
| `slotsequence` | `slot_sequence`         | Standardize naming |

### Phase 8: Assignment & Clustering Tables

#### cluster_assignment (formerly clusterzuordnung)

| Current Column | New Column (snake_case) | Reason              |
| -------------- | ----------------------- | ------------------- |
| `clusterID`    | `cluster_id`            | Primary/foreign key |
| `standortID`   | `scope_id`              | Primary/foreign key |

### Phase 9: Foreign Key Standardization

| Current Pattern   | New Pattern       | Example                                     |
| ----------------- | ----------------- | ------------------------------------------- |
| `StandortID`      | `scope_id`        | Foreign key to scope table                  |
| `BehoerdenID`     | `department_id`   | Foreign key to department table             |
| `BuergerID`       | `process_id`      | Foreign key to process table (`buerger`)    |
| `NutzerID`        | `user_id`         | Foreign key to user table                   |
| `KundenID`        | `jurisdiction_id` | Foreign key to jurisdiction table (`kunde`) |
| `OrganisationsID` | `organization_id` | Foreign key to organization table           |
| `OeffnungszeitID` | `availability_id` | Foreign key to availability table           |
| `apiClientID`     | `api_client_id`   | Foreign key to API client table             |
| `clusterID`       | `cluster_id`      | Foreign key to location cluster table       |

> Column mappings are generated from `.resources/zms.sql` and recent migrations. Some columns (for example `provider.contact__*`) are JSON-path keys stored in relational columns and are listed as-is until the PHP mapping refactor (section 3) moves them to camelCase entity fields.

## 3. Complete PHP Variable Mapping Renaming Plan - All English, All camelCase

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

### Phase 1: Core Entity Mappings (High Priority)

Availability Query Class
Current Mapping | New Mapping (camelCase) | Database Column (snake_case)
-- | -- | --
'id' | 'id' | availability.availability_id
'scope**id' | 'scopeId' | availability.scope_id
'bookable**startInDays' | 'bookableStartInDays' | availability.open_from_days
'bookable**endInDays' | 'bookableEndInDays' | availability.open_until_days
'description' | 'description' | availability.comment
'startDate' | 'startDate' | availability.start_date
'startTime' | 'startTime' | availability.start_time
'endDate' | 'endDate' | availability.end_date
'endTime' | 'endTime' | availability.end_time
'lastChange' | 'lastChange' | availability.updated_at
'multipleSlotsAllowed' | 'multipleSlotsAllowed' | availability.multiple_slots_allowed
'repeat**afterWeeks' | 'repeatAfterWeeks' | availability.every_x_weeks
'repeat**weekOfMonth' | 'repeatWeekOfMonth' | availability.every_other_week
'slotTimeInMinutes' | 'slotTimeInMinutes' | availability.time_slot
'type' | 'type' | availability.type
'weekday**monday' | 'weekdayMonday' | availability.weekday
'weekday**tuesday' | 'weekdayTuesday' | availability.weekday
'weekday**wednesday' | 'weekdayWednesday' | availability.weekday
'weekday**thursday' | 'weekdayThursday' | availability.weekday
'weekday**friday' | 'weekdayFriday' | availability.weekday
'weekday**saturday' | 'weekdaySaturday' | availability.weekday
'weekday**sunday' | 'weekdaySunday' | availability.weekday
'workstationCount**callcenter' | 'workstationCountCallcenter' | availability.workstation_count_callcenter
'workstationCount**intern' | 'workstationCountIntern' | availability.workstation_count_intern
'workstationCount\_\_public' | 'workstationCountPublic' | availability.workstation_count_public

Scope Query Class
Current Mapping | New Mapping (camelCase) | Database Column (snake_case)
-- | -- | --
'hint' | 'hint' | scope.hint
'id' | 'id' | scope.scope_id
'contact**name' | 'contactName' | scopeprovider.name
'contact**street' | 'contactStreet' | scope.address
'contact**email' | 'contactEmail' | scope.admin_email
'contact**country' | 'contactCountry' | "Germany"
'lastChange' | 'lastChange' | scope.updated_at
'preferences**appointment**deallocationDuration' | 'preferencesAppointmentDeallocationDuration' | scope.deletion_duration
'preferences**appointment**infoForAppointment' | 'preferencesAppointmentInfoForAppointment' | scope.info_for_appointment
'preferences**appointment**infoForAllAppointments' | 'preferencesAppointmentInfoForAllAppointments' | scope.info_for_all_appointments
'preferences**appointment**endInDaysDefault' | 'preferencesAppointmentEndInDaysDefault' | scope.appointments_until_days
'preferences**appointment**multipleSlotsEnabled' | 'preferencesAppointmentMultipleSlotsEnabled' | scope.multiple_appointments
'preferences**appointment**reservationDuration' | 'preferencesAppointmentReservationDuration' | scope.reservation_duration
'preferences**appointment**activationDuration' | 'preferencesAppointmentActivationDuration' | scope.activation_duration
'preferences**appointment**startInDaysDefault' | 'preferencesAppointmentStartInDaysDefault' | scope.appointments_from_days
'preferences**appointment**notificationConfirmationEnabled' | 'preferencesAppointmentNotificationConfirmationEnabled' | scope.sms_confirmation_enabled
'preferences**appointment**notificationHeadsUpEnabled' | 'preferencesAppointmentNotificationHeadsUpEnabled' | scope.sms_notification_enabled
'preferences**client**alternateAppointmentUrl' | 'preferencesClientAlternateAppointmentUrl' | scope.qtv_url
'preferences**client**amendmentActivated' | 'preferencesClientAmendmentActivated' | scope.comment_required
'preferences**client**amendmentLabel' | 'preferencesClientAmendmentLabel' | scope.comment_label
'preferences**client**emailFrom' | 'preferencesClientEmailFrom' | scopemail.sender_address
'preferences**client**emailRequired' | 'preferencesClientEmailRequired' | scope.email_required
'preferences**client**emailConfirmationActivated' | 'preferencesClientEmailConfirmationActivated' | scope.email_confirmation_activated
'preferences**client**telephoneActivated' | 'preferencesClientTelephoneActivated' | scope.phone_enabled
'preferences**client**telephoneRequired' | 'preferencesClientTelephoneRequired' | scope.phone_required
'preferences**client**appointmentsPerMail' | 'preferencesClientAppointmentsPerMail' | scope.appointments_per_mail
'preferences**client**slotsPerAppointment' | 'preferencesClientSlotsPerAppointment' | scope.slots_per_appointment
'preferences**client**whitelistedMails' | 'preferencesClientWhitelistedMails' | scope.whitelisted_mails
'preferences**client**customTextfieldActivated' | 'preferencesClientCustomTextfieldActivated' | scope.custom_text_field_active
'preferences**client**customTextfieldRequired' | 'preferencesClientCustomTextfieldRequired' | scope.custom_text_field_required
'preferences**client**customTextfieldLabel' | 'preferencesClientCustomTextfieldLabel' | scope.custom_text_field_label
'preferences**client**customTextfield2Activated' | 'preferencesClientCustomTextfield2Activated' | scope.custom_text_field2_active
'preferences**client**customTextfield2Required' | 'preferencesClientCustomTextfield2Required' | scope.custom_text_field2_required
'preferences**client**customTextfield2Label' | 'preferencesClientCustomTextfield2Label' | scope.custom_text_field2_label
'preferences**client**captchaActivatedRequired' | 'preferencesClientCaptchaActivatedRequired' | scope.captcha_activated_required
'preferences**client**adminMailOnAppointment' | 'preferencesClientAdminMailOnAppointment' | scope.admin_mail_on_appointment
'preferences**client**adminMailOnDeleted' | 'preferencesClientAdminMailOnDeleted' | scope.admin_mail_on_deleted
'preferences**client**adminMailOnUpdated' | 'preferencesClientAdminMailOnUpdated' | scope.admin_mail_on_updated
'preferences**client**adminMailOnMailSent' | 'preferencesClientAdminMailOnMailSent' | scope.admin_mail_on_mail_sent
'preferences**notifications**confirmationContent' | 'preferencesNotificationsConfirmationContent' | scope.sms_confirmation_text
'preferences**notifications**headsUpContent' | 'preferencesNotificationsHeadsUpContent' | scope.sms_notification_text
'preferences**notifications**headsUpTime' | 'preferencesNotificationsHeadsUpTime' | scope.sms_notification_deadline
'preferences**pickup**alternateName' | 'preferencesPickupAlternateName' | scope.pickup_counter_name
'preferences**pickup**isDefault' | 'preferencesPickupIsDefault' | scope.default_pickup_location
'preferences**queue**callCountMax' | 'preferencesQueueCallCountMax' | scope.recall_count
'preferences**queue**callDisplayText' | 'preferencesQueueCallDisplayText' | scope.display_text
'preferences**queue**firstNumber' | 'preferencesQueueFirstNumber' | scope.first_queue_number
'preferences**queue**lastNumber' | 'preferencesQueueLastNumber' | scope.last_queue_number
'preferences**queue**maxNumberContingent' | 'preferencesQueueMaxNumberContingent' | scope.queue_number_contingent
'preferences**queue**processingTimeAverage' | 'preferencesQueueProcessingTimeAverage' | scope.processing_time
'preferences**queue**publishWaitingTimeEnabled' | 'preferencesQueuePublishWaitingTimeEnabled' | scope.publish_waiting_time
'preferences**queue**statisticsEnabled' | 'preferencesQueueStatisticsEnabled' | scope.statistics_enabled
'preferences**survey**emailContent' | 'preferencesSurveyEmailContent' | scope.customer_survey_email_text
'preferences**survey**enabled' | 'preferencesSurveyEnabled' | scope.customer_survey
'preferences**survey**label' | 'preferencesSurveyLabel' | scope.customer_survey_label
'preferences**ticketprinter**buttonName' | 'preferencesTicketprinterButtonName' | scope.location_info_line
'preferences**ticketprinter**confirmationEnabled' | 'preferencesTicketprinterConfirmationEnabled' | scope.sms_wms_confirmation
'preferences**ticketprinter**deactivatedText' | 'preferencesTicketprinterDeactivatedText' | scope.queue_hint
'preferences**ticketprinter**notificationsAmendmentEnabled' | 'preferencesTicketprinterNotificationsAmendmentEnabled' | scope.sms_addition
'preferences**ticketprinter**notificationsEnabled' | 'preferencesTicketprinterNotificationsEnabled' | scope.sms_queue
'preferences**ticketprinter**notificationsDelay' | 'preferencesTicketprinterNotificationsDelay' | scope.sms_kiosk_offer_deadline
'preferences**workstation**emergencyEnabled' | 'preferencesWorkstationEmergencyEnabled' | scope.emergency_function
'preferences**workstation**emergencyRefreshInterval' | 'preferencesWorkstationEmergencyRefreshInterval' | scope.emergency_refresh_interval
'shortName' | 'shortName' | scope.short_name
'status**emergency**acceptedByWorkstation' | 'statusEmergencyAcceptedByWorkstation' | scope.emergency_response
'status**emergency**activated' | 'statusEmergencyActivated' | scope.emergency_triggered
'status**emergency**calledByWorkstation' | 'statusEmergencyCalledByWorkstation' | scope.emergency_initiation
'status**queue**ghostWorkstationCount' | 'statusQueueGhostWorkstationCount' | scope.virtual_processor_count
'status**queue**givenNumberCount' | 'statusQueueGivenNumberCount' | scope.assigned_queue_numbers
'status**queue**lastGivenNumber' | 'statusQueueLastGivenNumber' | scope.last_queue_number
'status**queue**lastGivenNumberTimestamp' | 'statusQueueLastGivenNumberTimestamp' | scope.queue_number_date

### Phase 2: Process & Citizen Mappings (Medium Priority)

Process Query Class
Current Mapping | New Mapping (camelCase) | Database Column (snake_case)
-- | -- | --
'amendment' | 'amendment' | process.comment
'id' | 'id' | process.citizen_id
'appointments**0**date' | 'appointments0Date' | process.appointment_datetime
'scope**id' | 'scopeId' | process.scope_id
'appointments**0**scope**id' | 'appointments0ScopeId' | process.scope_id

Citizen Query Class
Current Mapping | New Mapping (camelCase) | Database Column (snake_case)
-- | -- | --
'id' | 'id' | citizen.citizen_id
'scopeId' | 'scopeId' | citizen.scope_id
'pickupLocationId' | 'pickupLocationId' | citizen.pickup_location_id
'userId' | 'userId' | citizen.user_id
'name' | 'name' | citizen.name
'email' | 'email' | citizen.email
'phone' | 'phone' | citizen.phone
'comment' | 'comment' | citizen.comment
'provisionalBooking' | 'provisionalBooking' | citizen.provisional_booking
'confirmed' | 'confirmed' | citizen.confirmed
'callSuccessful' | 'callSuccessful' | citizen.call_successful
'callTime' | 'callTime' | citizen.call_time
'pickupPerson' | 'pickupPerson' | citizen.pickup_person
'queueNumber' | 'queueNumber' | citizen.queue_number
'queueNumberDate' | 'queueNumberDate' | citizen.queue_number_date
'waitingTime' | 'waitingTime' | citizen.waiting_time
'processingTime' | 'processingTime' | citizen.processing_time
'parked' | 'parked' | citizen.parked
'wasMissed' | 'wasMissed' | citizen.was_missed
'apiClientId' | 'apiClientId' | citizen.api_client_id
'source' | 'source' | citizen.source
'lastChange' | 'lastChange' | citizen.updated_at

### Phase 3: Provider & Request Mappings (Lower Priority)

Provider Query Class
Current Mapping | New Mapping (camelCase) | Database Column (snake_case)
-- | -- | --
'contact**city' | 'contactCity' | provider.contact_city
'contact**country' | 'contactCountry' | provider.contact_country
'contact**name' | 'contactName' | provider.name
'contact**postalCode' | 'contactPostalCode' | provider.contact_postal_code
'contact**region' | 'contactRegion' | provider.contact_region
'contact**street' | 'contactStreet' | provider.contact_street
'contact\_\_streetNumber' | 'contactStreetNumber' | provider.contact_street_number
'id' | 'id' | provider.id
'link' | 'link' | provider.link
'name' | 'name' | provider.name
'displayName' | 'displayName' | provider.display_name
'source' | 'source' | provider.source
'data' | 'data' | provider.data

## 4. Long-Term Schema Vision (Beyond Renaming)

Sections 1–3 standardize **names**. This section records **structural** changes under consideration for a healthier long-term schema. These are not committed timelines; they inform design discussions and ticket breakdown.

### 4.1 Major structural refactors

#### Split `buerger` into citizen and process (or citizen and appointment)

Today the `buerger` table is the physical store for the `process` entity. It mixes concerns that evolved together over years:

- Client / citizen PII (`Name`, `EMail`, `Telefonnummer`, custom fields)
- Appointment scheduling (`Datum`, `Uhrzeit`, scope references, slot linkage)
- Queue runtime state (`wartenummer`, `status`, `waiting_time`, call metadata)
- Archival / statistics inputs

**Candidate target models:**

| Option | Tables                    | Fits when                                                                             |
| ------ | ------------------------- | ------------------------------------------------------------------------------------- |
| A      | `citizen` + `process`     | Queue-first workflows; process is the unit of work; citizen is reusable across visits |
| B      | `citizen` + `appointment` | Appointment-first workflows; clearer split between booking and queue handling         |

Near-term renaming in section 1 may still map `buerger` → `process` for minimal disruption. The split in this section is a **later** migration once API, statistics, and archive paths are untangled.

#### Drop `buergerarchivtoday` / `citizen_archive_today`

`buergerarchivtoday` is a daily snapshot of `buergerarchiv`. It duplicates data and adds sync/cron complexity. Prefer:

- Query `citizen_archive` with a date predicate and proper indexes, or
- A database view / materialized view if performance requires it

Remove the table once query plans and dashboards are validated without it.

#### Normalize `queue_number_statistics` (`wartenrstatistik`)

The table is extremely wide: per-hour columns for estimated waiting time, actual waiting time, way time, and waiting counts, each split by spontaneous vs appointment (96+ columns per family). Interim renames exist in migration `91775568666-rename-waiting-way-processing-columns.sql`.

**Long-term direction:** fact-style tables, for example:

- `queue_statistics_hourly` — `(scope_id, date, hour, metric, channel, value)` where `channel` is `spontaneous` | `appointment` and `metric` is `waiting_time` | `way_time` | `waiting_count` | `estimated_waiting_time`
- Or separate narrow tables per metric family if query patterns differ

Benefits: simpler migrations, easier aggregation, room for new metrics without `ALTER TABLE` on hundreds of columns.

#### Normalize `log.data` (JSON)

The `log` table stores a JSON `data` blob alongside indexed columns (`type`, `scope_id`, `user_id`, `reference_id`). Searching inside JSON is slow and awkward.

**Direction:** promote frequently filtered fields to typed columns; keep `data` only for optional debug payload or drop it once structured columns cover admin search needs.

#### Split and rename `preferences`

`preferences` is keyed by `(entity, id, groupName, name)` but usage is mixed:

- **Scope settings** — migrated from `standort` columns; entity `scope`
- **System / admin config** — edited via zmsadmin config area; not truly “scope preferences”

**Direction:**

- `scope_preference` (or `scope_setting`) for per-scope values
- `system_setting` (or keep `config` for key-value and migrate overlapping rows)
- Avoid the generic name `preferences` for scope-only data

#### DLDB tables: `provider`, `request`, `request_provider`, `request_variant`

These tables are DLDB-synced. Several store a `data` JSON column with nested contact and metadata. `zmscitizenapi` already speaks in terms of **offices** and **services** (`officeId`, `serviceId`, `OfficeServiceRelation`).

**Naming candidates:**

| Current            | Citizen API term    | Candidate table name |
| ------------------ | ------------------- | -------------------- |
| `provider`         | office              | `office`             |
| `request`          | service             | `service`            |
| `request_provider` | office–service link | `office_service`     |
| `request_variant`  | service variant     | `service_variant`    |

**Structural direction:** parse `data` into relational columns where queried; keep JSON only for rare DLDB fields or use a `metadata` column with a documented schema version.

### 4.2 Table disposition audit

| Table (current)      | Planned disposition      | Notes                                                                                                                                                                                                                                                                  |
| -------------------- | ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `abrechnung`         | **Drop**                 | Billing/SMS accounting; no active use. Migration `91772633097-drop-abrechnung.sql` already drops the table.                                                                                                                                                            |
| `ipausnahmen`        | **Verify → likely drop** | No references in PHP codebase at time of writing; confirm no external dependency before removal.                                                                                                                                                                       |
| `apikey`             | **Verify**               | Routes exist in `zmsapi`; confirm whether any deployment still issues or validates API keys.                                                                                                                                                                           |
| `apiquota`           | **Verify**               | Tied to `apikey`; same audit as above.                                                                                                                                                                                                                                 |
| `notificationqueue`  | **Drop**                 | Part of SMS/notification removal. Migration `91772633137-drop-notifcationqueue.sql` already drops the table.                                                                                                                                                           |
| `eventlog`           | **Verify**               | Still used (e.g. `ProcessListSummaryMail`); clarify whether to keep, replace with `log`, or consolidate.                                                                                                                                                               |
| `imagedata`          | **Redesign**             | Store object URL in DB; binary in RefArch S3 bucket via zmsadmin. Calldisplay/cluster image upload paths exist, but the **admin header logo** is static at `terminvereinbarung/admin/_css/images/muc_logo_head2.png` — DB logo upload may already be unused or broken. |
| `kundenlinks`        | **Drop**                 | Already marked unused in section 1.                                                                                                                                                                                                                                    |
| `buergerarchivtoday` | **Drop**                 | See §4.1; redundant with `buergerarchiv`.                                                                                                                                                                                                                              |

### 4.3 Assets, migrations, and operations

#### `image_data` → object storage

- Upload logos and calldisplay images through zmsadmin into a **RefArch S3 bucket** (or compatible object store).
- Persist only `bucket`, `object_key`, and/or HTTPS URL in `image_data` (or a renamed `asset` table).
- Remove `imagecontent` BLOB/TEXT columns after migration.
- Audit all consumers: `FileUploader`, calldisplay routes, cluster scope images.

#### Migration file naming

Many migration files use a leading `917…` numeric prefix (legacy ticket/timestamp encoding), which makes chronological ordering and code review harder.

**Target convention (proposal):**

```
{YYYYMMDD}-{HHMMSS}-{ticket-or-short-description}.sql
```

Examples: `20260302-143000-ZMS-1234-split-buerger.sql`. Apply to **new** migrations; optionally rename historical files only when the cost is justified.

#### `migrations` table

The `migrations` table itself is fine as snake_case. The improvement target is the **filename convention** on disk, not the table name.
