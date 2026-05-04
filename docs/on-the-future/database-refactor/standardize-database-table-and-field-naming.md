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

# Three phases:

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

<mark>All tables columns and mappings may not be listed here. This list may be incomplete. It is however a good start. A good overview can be found in the [local ddev developer environment](https://zms.ddev.site:8037/index.php?route=/database/structure&db=db).</mark>

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

| Current           | New (snake_case)   | Reason                  |
| ----------------- | ------------------ | ----------------------- |
| `buergeranliegen` | `citizen_requests` | Citizen requests/issues |
| `buergerarchiv`   | `citizen_archive`  | Archived citizen data   |
| `nutzer`          | `user`             | System users            |
| `nutzerzuordnung` | `user_assignment`  | User assignments        |
| `kunde`           | `customer`         | Customer                |
| `kundenlinks`     | `customer_links`   | Customer links          |

### Phase 3: System & Configuration Tables

| Current            | New (snake_case)          | Reason                  |
| ------------------ | ------------------------- | ----------------------- |
| `abrechnung`       | `billing`                 | Billing/accounting      |
| `ipausnahmen`      | `ip_exceptions`           | IP exceptions           |
| `kiosk`            | `kiosk`                   | Kiosk (universal term)  |
| `wartenrstatistik` | `queue_number_statistics` | Queue number statistics |
| `standortcluster`  | `location_cluster`        | Location clustering     |
| `statistik`        | `statistics`              | Statistics              |

### Phase 4: API & Technical Tables

| Current     | New (snake_case) | Reason     |
| ----------- | ---------------- | ---------- |
| `apiclient` | `api_client`     | API client |
| `apikey`    | `api_key`        | API key    |
| `apiquota`  | `api_quota`      | API quota  |

### Phase 5: Communication Tables

| Current             | New (snake_case)     | Reason                     |
| ------------------- | -------------------- | -------------------------- |
| `email`             | `email`              | Email (already snake_case) |
| `sms`               | `sms`                | SMS (already snake_case)   |
| `mailpart`          | `mail_part`          | Mail part                  |
| `mailqueue`         | `mail_queue`         | Mail queue                 |
| `mailtemplate`      | `mail_template`      | Mail template              |
| `notificationqueue` | `notification_queue` | Notification queue         |

### Phase 6: Data & Process Tables

| Current            | New (snake_case)   | Reason             |
| ------------------ | ------------------ | ------------------ |
| `closures`         | `closures`         | Already snake_case |
| `config`           | `config`           | Already snake_case |
| `eventlog`         | `event_log`        | Event log          |
| `imagedata`        | `image_data`       | Image data         |
| `log`              | `log`              | Already snake_case |
| `migrations`       | `migrations`       | Already snake_case |
| `preferences`      | `preferences`      | Already snake_case |
| `process_sequence` | `process_sequence` | Already snake_case |
| `sessiondata`      | `session_data`     | Session data       |
| `source`           | `source`           | Already snake_case |

### Phase 7: Service & Provider Tables

| Current            | New (snake_case)   | Reason             |
| ------------------ | ------------------ | ------------------ |
| `provider`         | `provider`         | Already snake_case |
| `request`          | `request`          | Already snake_case |
| `request_provider` | `request_provider` | Already snake_case |
| `request_variant`  | `request_variant`  | Already snake_case |

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

availability (formerly oeffnungszeit)
Current Column | New Column (snake_case) | Reason
-- | -- | --
OeffnungszeitID | availability_id | Primary key
StandortID | scope_id | Foreign key to scope
BehoerdenID | department_id | Foreign key to department
Startdatum | start_date | Start date
Endedatum | end_date | End date
Anfangszeit | start_time | Start time
Endzeit | end_time | End time
Terminanfangszeit | appointment_start_time | Appointment start time
Terminendzeit | appointment_end_time | Appointment end time
Wochentag | weekday | Weekday
Timeslot | time_slot | Time slot
kommentar | comment | Comment
Offen_ab | open_from_days | Open from days
Offen_bis | open_until_days | Open until days
Anzahlterminarbeitsplaetze | appointment_workstation_count | Appointment workstation count
reduktionTermineImInternet | internet_reduction | Internet reduction
reduktionTermineCallcenter | callcenter_reduction | Call center reduction
erlaubemehrfachslots | multiple_slots_allowed | Multiple slots allowed
allexWochen | every_x_weeks | Every X weeks
jedexteWoche | every_other_week | Every other week
updateTimestamp | updated_at | Update timestamp

scope (formerly standort)

| Current Column               | New Column (snake_case)      | Reason                       |
| ---------------------------- | ---------------------------- | ---------------------------- |
| StandortID                   | scope_id                     | Primary key                  |
| BehoerdenID                  | department_id                | Foreign key to department    |
| Bezeichnung                  | name                         | Name/designation             |
| standortkuerzel              | short_name                   | Short name                   |
| Adresse                      | address                      | Address                      |
| emailstandortadmin           | admin_email                  | Admin email                  |
| InfoDienstleisterID          | info_provider_id             | Info provider ID             |
| source                       | source                       | Source (already snake_case)  |
| Termine_ab                   | appointments_from_days       | Appointments from days       |
| Termine_bis                  | appointments_until_days      | Appointments until days      |
| loeschdauer                  | deletion_duration            | Deletion duration            |
| reservierungsdauer           | reservation_duration         | Reservation duration         |
| aktivierungsdauer            | activation_duration          | Activation duration          |
| mehrfachtermine              | multiple_appointments        | Multiple appointments        |
| wartenummernkontingent       | queue_number_contingent      | Queue number contingent      |
| vergebenewartenummern        | assigned_queue_numbers       | Assigned queue numbers       |
| letztewartenr                | last_queue_number            | Last queue number            |
| startwartenr                 | first_queue_number           | First queue number           |
| endwartenr                   | last_queue_number            | Last queue number            |
| anzahlwiederaufruf           | recall_count                 | Recall count                 |
| aufrufanzeigetext            | display_text                 | Display text                 |
| wartenrhinweis               | queue_hint                   | Queue hint                   |
| standortinfozeile            | location_info_line           | Location info line           |
| ausgabeschaltername          | pickup_counter_name          | Pickup counter name          |
| defaultabholerstandort       | default_pickup_location      | Default pickup location      |
| wartezeitveroeffentlichen    | publish_waiting_time         | Publish waiting time         |
| ohnestatistik                | without_statistics           | Without statistics           |
| notruffunktion               | emergency_function           | Emergency function           |
| notrufausgeloest             | emergency_triggered          | Emergency triggered          |
| notrufantwort                | emergency_response           | Emergency response           |
| notrufinitiierung            | emergency_initiation         | Emergency initiation         |
| virtuellesachbearbeiterzahl  | virtual_processor_count      | Virtual processor count      |
| wartenrdatum                 | queue_number_date            | Queue number date            |
| Bearbeitungszeit             | processing_time              | Processing time              |
| emailPflichtfeld             | email_required               | Email required               |
| telefonPflichtfeld           | phone_required               | Phone required               |
| telefonaktiviert             | phone_enabled                | Phone enabled                |
| anmerkungPflichtfeld         | comment_required             | Comment required             |
| anmerkungLabel               | comment_label                | Comment label                |
| qtv_url                      | qtv_url                      | QTV URL (already snake_case) |
| email_confirmation_activated | email_confirmation_activated | Already snake_case           |
| appointments_per_mail        | appointments_per_mail        | Already snake_case           |
| slots_per_appointment        | slots_per_appointment        | Already snake_case           |
| whitelisted_mails            | whitelisted_mails            | Already snake_case           |
| custom_text_field_active     | custom_text_field_active     | Already snake_case           |
| custom_text_field_required   | custom_text_field_required   | Already snake_case           |
| custom_text_field_label      | custom_text_field_label      | Already snake_case           |
| custom_text_field2_active    | custom_text_field2_active    | Already snake_case           |
| custom_text_field2_required  | custom_text_field2_required  | Already snake_case           |
| custom_text_field2_label     | custom_text_field2_label     | Already snake_case           |
| captcha_activated_required   | captcha_activated_required   | Already snake_case           |
| admin_mail_on_appointment    | admin_mail_on_appointment    | Already snake_case           |
| admin_mail_on_deleted        | admin_mail_on_deleted        | Already snake_case           |
| admin_mail_on_updated        | admin_mail_on_updated        | Already snake_case           |
| admin_mail_on_mail_sent      | admin_mail_on_mail_sent      | Already snake_case           |
| smsbestaetigungstext         | sms_confirmation_text        | SMS confirmation text        |
| smsbenachrichtigungstext     | sms_notification_text        | SMS notification text        |
| smsbenachrichtigungsfrist    | sms_notification_deadline    | SMS notification deadline    |
| smswmsbestaetigung           | sms_wms_confirmation         | SMS WMS confirmation         |
| smswarteschlange             | sms_queue                    | SMS queue                    |
| smskioskangebotsfrist        | sms_kiosk_offer_deadline     | SMS kiosk offer deadline     |
| smsnachtrag                  | sms_addition                 | SMS addition                 |
| kundenbef_emailtext          | customer_survey_email_text   | Customer survey email text   |
| kundenbefragung              | customer_survey              | Customer survey              |
| kundenbef_label              | customer_survey_label        | Customer survey label        |
| info_for_appointment         | info_for_appointment         | Already snake_case           |
| info_for_all_appointments    | info_for_all_appointments    | Already snake_case           |
| updateTimestamp              | updated_at                   | Update timestamp             |

citizen (formerly buerger)

| Current Column     | New Column (snake_case) | Reason                      |
| ------------------ | ----------------------- | --------------------------- |
| BuergerID          | citizen_id              | Primary key                 |
| StandortID         | scope_id                | Foreign key to scope        |
| AbholortID         | pickup_location_id      | Pickup location ID          |
| NutzerID           | user_id                 | User ID                     |
| Name               | name                    | Name                        |
| Email              | email                   | Email                       |
| Telefon            | phone                   | Phone                       |
| Anmerkung          | comment                 | Comment                     |
| vorlaeufigeBuchung | provisional_booking     | Provisional booking         |
| bestaetigt         | confirmed               | Confirmed                   |
| aufruferfolgreich  | call_successful         | Call successful             |
| aufrufzeit         | call_time               | Call time                   |
| Abholer            | pickup_person           | Pickup person               |
| wartenr            | queue_number            | Queue number                |
| wartenrdatum       | queue_number_date       | Queue number date           |
| wartezeit          | waiting_time            | Waiting time                |
| bearbeitungszeit   | processing_time         | Processing time             |
| parked             | parked                  | Already snake_case          |
| wasMissed          | was_missed              | Was missed                  |
| apiClientID        | api_client_id           | API client ID               |
| source             | source                  | Source (already snake_case) |
| updateTimestamp    | updated_at              | Update timestamp            |

### Phase 2: User & Process Tables (Medium Priority)

citizen_requests (formerly buergeranliegen)
Current Column | New Column (snake_case) | Reason
-- | -- | --
BuergeranliegenID | citizen_request_id | Primary key
BuergerID | citizen_id | Foreign key to citizen
StandortID | scope_id | Foreign key to scope
Anliegen | request | Request/concern
source | source | Source (already snake_case)
updateTimestamp | updated_at | Update timestamp

user (formerly nutzer)
Current Column | New Column (snake_case) | Reason
-- | -- | --
NutzerID | user_id | Primary key
BehoerdenID | department_id | Foreign key to department
Name | name | Name
Email | email | Email
Passwort | password | Password
updateTimestamp | updated_at | Update timestamp

### Phase 3: System & Configuration Tables (Lower Priority)

api_client (formerly apiclient)
Current Column | New Column (snake_case) | Reason
-- | -- | --
apiClientID | api_client_id | Primary key
clientKey | client_key | Client key
shortname | short_name | Short name
accesslevel | access_level | Access level
updateTimestamp | updated_at | Update timestamp

api_key (formerly apikey)
Current Column | New Column (snake_case) | Reason
-- | -- | --
apiKeyID | api_key_id | Primary key
apiClientID | api_client_id | Foreign key to api_client
key | key | Key (already snake_case)
updateTimestamp | updated_at | Update timestamp

### Phase 4: Slot System Tables

slot
Current Column | New Column (snake_case) | Reason
-- | -- | --
slotID | slot_id | Primary key
scopeID | scope_id | Foreign key to scope
availabilityID | availability_id | Foreign key to availability
year | year | Year (already snake_case)
month | month | Month (already snake_case)
day | day | Day (already snake_case)
time | time | Time (already snake_case)
public | public | Public (already snake_case)
callcenter | callcenter | Call center (already snake_case)
intern | intern | Internal (already snake_case)
status | status | Status (already snake_case)
slotTimeInMinutes | slot_time_in_minutes | Slot time in minutes
createTimestamp | created_at | Create timestamp
updateTimestamp | updated_at | Update timestamp

### Phase 4: Foreign Key Standardization

| Current Pattern | New Pattern     | Example                           |
| --------------- | --------------- | --------------------------------- |
| StandortID      | scope_id        | Foreign key to scope table        |
| BehoerdenID     | department_id   | Foreign key to department table   |
| BuergerID       | citizen_id      | Foreign key to citizen table      |
| NutzerID        | user_id         | Foreign key to user table         |
| OeffnungszeitID | availability_id | Foreign key to availability table |

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
