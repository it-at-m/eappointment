---
outline:
  level: [2, 3]
---

# Current Cucumber Tests in zmsautomation

This page is generated automatically from `zmsautomation/src/test/resources/features`.
When a `.feature` file is added, changed, or removed, this documentation is updated automatically.

## Recommended Feature Pattern

Use ticket tags consistently. Always include a Jira tag like `@ZMSKVR-123` on new scenarios/features.

```gherkin
@rest @zmsapi @ZMSKVR-123 @smoke
Feature: Example feature with required ticket tag
  Scenario: Example scenario
    Given the API is available
    When I call the endpoint
    Then the response status code should be 200
```

## REST

### zmsapi

#### `status.feature`

Source: [status.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/rest/zmsapi/status.feature)

```gherkin
@rest @zmsapi @smoke
Feature: ZMS API Status Endpoint
  As a client application
  I want to check the status of the ZMS API
  So that I can verify the API is available and operational

  Background:
    Given the ZMS API is available

  Scenario: GET /status/ returns 200 and JSON body
    When I request the status endpoint
    Then the response status code should be 200
    And the response should contain status information
```

### zmscitizenapi

#### `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature`

Source: [zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/rest/zmscitizenapi/zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature)

```gherkin
@rest @zmscitizenapi @ZMSKVR-1124
Feature: ZMSKVR-1124 Ruppertstraße booking — Citizen API (10502 / 10489 / 10492, jump-in allow-mix)
  As a citizen
  I want to complete a full appointment booking from offices-and-services through confirm
  So that the API behaviour matches the citizen frontend (offices → days → slots → reserve → preconfirm → confirm via mail)

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    And the response should contain offices and services

  @passCalendar
  Scenario: Personalausweis at Bürgerbüro Ruppertstraße (10502) – lands at 10502
    When I request available days for office 10502 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht

  @mainCalendar
  Scenario: Personalausweis at Pass Ruppertstraße (10489) – lands at 10489
    When I request available days for office 10489 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht

  @pickupCalendar
  Scenario: Abholung at 10492 (Bürgerbüro Ruppertstraße KVR-II/211)
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht

  @passCalendar @jumpin
  Scenario: JumpIn 10489 with Personalausweis 1063441 – effective office may be 10502
    When I request available days for office 10502 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht
```

## UI

### buergeransicht (deprecated)

> Deprecated: These scenarios target the legacy buergeransicht frontend from `it-at-m/eappointment-buergeransicht` and are not used for `zmscitizenview`.

#### `ZMS-1540.feature`

Source: [ZMS-1540.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1540.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Bürger bucht einen Termin, bestätigt ihn. SB öffnet seinen Arbeitsplatz und prüft die anstehende Termine. Bürger nimmt den Termin wahr, die Aufgabe ist beim SB als erledigt markiert und fließt in die Statistik. 
   	@ignore @web @buergeransicht @ZMS-1540 @ZMS-1538 @ZMS-2819 @ZMS-1754 @E2E @automatisiert #@executeLocally
   	 Szenario: [AUT] Bürger bucht über das Internet [zms-dev]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Ummeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite mit Webadresse "https://www.mailinator.com/" und Titel "Home - Mailinator" navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Wochenkalender" Link klicken.
	   	 Dann öffnet sich der Wochenkalender.
	   	 Und werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Sachbearbeiterplatz" Link klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	#Ein Aufruf ist aufgrund der Datenlage, zu wenig freie Termine, auf der DEV nicht möglich.
	   	#Wenn Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer "<TestData.appointment_number>" aufrufen.
	   	#Und Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
	   	#Dann sollten die Kundeninformationen angezeigt werden.
	   	#Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
```

#### `ZMS-1541.feature`

Source: [ZMS-1541.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1541.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Das Terminvereinbarungssystem ermöglicht es den Kunden, die Ihren Termin über das Internet vereinbart haben, dass diese Ihren Termin selbständig ändern oder löschen können.
   	@ignore @web @buergeransicht @ZMS-1541 @ZMS-1538 @ZMS-2819 @ZMS-1754 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Termin buchen, ändern und löschen [zms-dev]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Ummeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Und Sie auf der Bürgeransicht das "<TestData.office>" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<beliebig>" auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Und Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
```

#### `ZMS-1542.feature`

Source: [ZMS-1542.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1542.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Kund*in ruft an und bittet um Terminverschiebung oder ein Terminschreiben kommt unzustellbar zurück. Der Sachbearbeiter ändert im Terminvereinbarungssystem den Terminzeitpunkt für den Kunden.
   	@ignore @web @buergeransicht @ZMS-1542 @ZMS-1538 @ZMS-2228 @ZMS-1825 @ZMS-2290 @ZMS-2202 @ZMS-2385 @E2E @automatisiert @executeLocally
   	 Szenario: Terminverschiebung über den Servicetelefon
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	#Telefonnummer nicht auf Demo konfiguriert
#		Und Sie auf der Bürgeransicht ins Textfeld Telefon "089123456789" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie nach Anruf des Bürgers bzw. Bürgerin den Termin mit der Nummer "<TestData.appointment_number>" auf die Zeit "<nächste>" anpassen.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Termin ändern" klicken.
	   	 Und Sie im Zeitmanagementsystem den Termin mit der Nummer "<TestData.appointment_number>" löschen.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Und Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
```

#### `ZMS-1543.feature`

Source: [ZMS-1543.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1543.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Bürger bucht einen Termin, bestätigt ihn. SB öffnet seinen Arbeitsplatz und prüft die anstehende Termine. Die Sachbearbeitung sieht weiteren Terminbedarf beim Kunden und eröffnet deshalb für diesen Intern einen Termin im Terminvereinbarungssystem.
   	@ignore @web @buergeransicht @ZMS-1543 @ZMS-1538 @ZMS-2228 @ZMS-2290 @ZMS-2202 @ZMS-2385 @E2E @automatisiert @executeLocally
   	 Szenario: Terminbedarfsfeststellung vom SB
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Telefon "1234567890" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Wochenkalender" Link klicken.
	   	 Dann öffnet sich der Wochenkalender.
	   	 Und werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Sachbearbeiterplatz" Link klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer "<TestData.appointment_number>" aufrufen.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
	   	 Dann sollten die Kundeninformationen angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen das Datum "<heute+7_tage>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen die Zeit "<beliebig>" auswählen.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen den Namen "<TestData.customer_name>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen die Telefonnummer "<TestData.customer_phone_number>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen die E-mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen die Anmerkung "Folgetermin wie durch Kunden gewünscht." eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen die Dienstleistung "<TestData.service>" auswählen.
	   	 Und Sie im Zeitmanagementsystem unter Termin erstellen auf die Schaltfläche "Termin buchen" klicken.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Vorgangsnummer drucken" klicken.
	   	 Dann kann die Terminbestätigung gedruckt werden.
```

#### `ZMS-1760.feature`

Source: [ZMS-1760.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1760.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Roave SecurityAdvisories wieder einbauen
   
   	#Bürger bucht einen Termin, bestätigt ihn. SB öffnet seinen Arbeitsplatz und prüft die anstehende Termine. Bürger nimmt den Termin wahr, die Aufgabe ist beim SB als erledigt markiert und fließt in die Statistik. 
   	@ignore @web @buergeransicht @ZMS-1760 @ZMS-1538 @ZMS-2820 @ZMS-1754 @ZMS-1825 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Bürger bucht über das Internet [zms-demo]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht das Jahr "<heute_jahr>" auswählen.
	   	 Und Sie auf der Bürgeransicht den Monat "<heute_monat>" auswählen.
	   	 Und Sie auf der Bürgeransicht den Tag "<heute_tag>" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite mit Webadresse "https://www.mailinator.com/" und Titel "Home - Mailinator" navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Wochenkalender" Link klicken.
	   	 Dann öffnet sich der Wochenkalender.
	   	 Und werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Sachbearbeiterplatz" Link klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer "<TestData.appointment_number>" aufrufen.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
	   	 Dann sollten die Kundeninformationen angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
```

#### `ZMS-1761.feature`

Source: [ZMS-1761.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1761.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Roave SecurityAdvisories wieder einbauen
   
   	#Das Terminvereinbarungssystem ermöglicht es den Kunden, die Ihren Termin über das Internet vereinbart haben, dass diese Ihren Termin selbständig ändern oder löschen können.
   	@ignore @web @buergeransicht @ZMS-1761 @ZMS-1538 @ZMS-2820 @ZMS-1825 @ZMS-1754 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Termin buchen, ändern und löschen [zms-demo]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Und Sie auf der Bürgeransicht das "<TestData.office>" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<beliebig>" auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Und Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
```

#### `ZMS-1799.feature`

Source: [ZMS-1799.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1799.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Das Terminvereinbarungssystem ermöglicht es den Kunden, die Ihren Termin über das Internet vereinbart haben, dass diese Ihren Termin selbständig ändern oder löschen können.
   	@ignore @web @buergeransicht @ZMS-1799 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Termin buchen, ändern und löschen [zms-test]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Und Sie auf der Bürgeransicht das "<TestData.office>" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<beliebig>" auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie die Terminumbuchung bestätigen.
	   	 Und Sie die Bürgeransicht schließen.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
```

#### `ZMS-1800.feature`

Source: [ZMS-1800.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-1800.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Bürger bucht einen Termin, bestätigt ihn. SB öffnet seinen Arbeitsplatz und prüft die anstehende Termine. Bürger nimmt den Termin wahr, die Aufgabe ist beim SB als erledigt markiert und fließt in die Statistik. 
   	#
   	#{color:#de350b}*HINWEIS:*{color}
   	#
   	#{color:#172b4d}*Die Schritte 8,9 und 11 aus dem manuellen Testfall sind nach Rücksprache mit [~*********] aktuell nicht im automatisierten Testfall enthalten!*{color}
   	#
   	# 
   	@ignore @web @buergeransicht @ZMS-1800 @ZMS-1538 @ZMS-2228 @ZMS-2818 @ZMS-1754 @ZMS-2385 @ZMS-2202 @ZMS-2479 @ZMS-2561 @ZMS-2290 @ZMS-2869 @ZMS-2709 @ZMS-3127 @ZMS-3162 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Bürger bucht über das Internet [zms-test]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite mit Webadresse "https://www.mailinator.com/" und Titel "Home - Mailinator" navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem in der Navigationsleite auf die Schaltfläche "Tresen" klicken.
	   	 Dann wird die Seite Tresen geöffnet.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Wochenkalender" Link klicken.
	   	 Dann öffnet sich der Wochenkalender.
	   	 Und werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Sachbearbeiterplatz" Link klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer "<TestData.appointment_number>" aufrufen.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
	   	 Dann sollten die Kundeninformationen angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
```

#### `ZMS-2576.feature`

Source: [ZMS-2576.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-2576.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	@ignore @web @buergeransicht @ZMS-2576 @automatisiert @executeLocally
   	 Szenario: [AUT] Test Begrenzung der Anzahl an kombinierbaren Dienstleistungen ist in den Standorteinstellungen möglich
	   	# admin: Slots Anzahl einschränken
#		Wenn Sie zur Webseite der Administration navigieren.
#		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
#		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" die Anzahl an maximal buchbaren Slots pro Termin auf "1" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" ist die maximale Anzahl buchbarer Slots pro Termin auf "1" begrenzt.
#		Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" die Anzahl an maximal buchbaren Slots pro Termin auf "1" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" ist die maximale Anzahl buchbarer Slots pro Termin auf "1" begrenzt.
#		Und Sie "11" minuten bis die Änderungen übernommen werden warten.
	   	#Bürger
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
	   	 Dann erscheinen die kombinierbaren Dienstleistungen für die Dienstleistung "Mietberatung".
	   	 Wenn für den Service "Mietberatung" und den Standort-ID "101731" ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots "1" ausgewählt wird.
	   	 Dann sollte die Warnung "Der Termin ist zu lang. Bitte wählen Sie weniger Dienstleistungen" erscheinen.
	   	#admin: Slots Anzahl Einschränkung aufheben
#		Wenn Sie zur Webseite der Administration navigieren.
#		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" die Anzahl an maximal buchbaren Slots pro Termin auf "" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" ist die maximale Anzahl buchbarer Slots pro Termin auf "" begrenzt.
#		Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" die Anzahl an maximal buchbaren Slots pro Termin auf "" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" ist die maximale Anzahl buchbarer Slots pro Termin auf "" begrenzt.
#		Und Sie "11" minuten bis die Änderungen übernommen werden warten.
	   	#Bürger
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
	   	 Dann erscheinen die kombinierbaren Dienstleistungen für die Dienstleistung "Mietberatung".
	   	 Wenn für den Service "Mietberatung" und den Standort-ID "101731" ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots "1" ausgewählt wird.
	   	 Und für den Service "Mietberatung" und den Standort-ID "101731" ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots "1" ausgewählt wird.
	   	 Dann sollte keine Warnung erscheinen.
```

#### `ZMS-2659.feature`

Source: [ZMS-2659.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-2659.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Bürger bucht einen Termin, bestätigt ihn. SB öffnet seinen Arbeitsplatz und prüft die anstehende Termine. Bürger nimmt den Termin wahr, die Aufgabe ist beim SB als erledigt markiert und fließt in die Statistik. 
   	@ignore @web @buergeransicht @ZMS-2659 @ZMS-2819 @ZMS-2561 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Bürger bucht über das Internet, inkl. Freitextfeld [zms-dev]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Ummeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite mit Webadresse "https://www.mailinator.com/" und Titel "Home - Mailinator" navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Wochenkalender" Link klicken.
	   	 Dann öffnet sich der Wochenkalender.
	   	 Und werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Sachbearbeiterplatz" Link klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	#Ein Aufruf ist aufgrund der Datenlage, zu wenig freie Termine, auf der DEV nicht möglich.
	   	#Wenn Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer "<TestData.appointment_number>" aufrufen.
	   	#Und Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
	   	#Dann sollten die Kundeninformationen angezeigt werden.
	   	#Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
```

#### `ZMS-2661.feature`

Source: [ZMS-2661.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-2661.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Das Terminvereinbarungssystem ermöglicht es den Kunden, die Ihren Termin über das Internet vereinbart haben, dass diese Ihren Termin selbständig ändern oder löschen können.
   	@ignore @web @buergeransicht @ZMS-2661 @ZMS-1538 @ZMS-2819 @ZMS-2561 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Termin buchen, ändern und löschen, inkl. Freitextfeld [zms-dev]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Ummeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Und Sie auf der Bürgeransicht das "<TestData.office>" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<beliebig>" auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Und Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.
```

#### `ZMS-2700.feature`

Source: [ZMS-2700.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-2700.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Frontend Design UX/UI anpassen
   
   	#Tests {panel:title=Problembeschreibung|borderStyle=solid|borderColor=#a01441|titleBGColor=#f092ad|bgColor=#ffffff}
   	#*Ist:* Beim Umbuchen eines Termins in einer bestimmten Behörde (z. B. BB Pasing) wird auf der Bürgeransicht nicht auf diese Behörde verlinkt, sondern auf den Anfang der Behördenliste. 
   	#
   	#*Soll:* Hier sollte die Verlinkung auf die im Vorfeld ausgewählte Behörde erfolgen. 
   
   	@ignore @web @buergeransicht @ZMS-2700 @ZMS-815 @KVR @executeLocally
   	 Szenario: [AUT] Bei der Umbuchung eines Termins wird die vorherige ausgewählte Behörde nicht automatisch ausgewählt
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Kirchenaustritt" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht den Standort "Standesamt München-Pasing" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
#Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite mit Webadresse "https://www.mailinator.com/" und Titel "Home - Mailinator" navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann ist auf der Bürgeransicht der Standort "Standesamt München-Pasing" vorausgewählt.
```

#### `ZMS-2701.feature`

Source: [ZMS-2701.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-2701.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Beschränkung der Buchung auf eine Mailadresse
   
     @ignore @web @buergeransicht @ZMS-2701 @automatisiert @executeLocally
      Szenario: [AUT] Beschränkung der Buchung auf eine Mailadresse [zms-test]
       # Anzahl Termin pro Email-adresse begrenzen
#    Wenn Sie zur Webseite der Administration navigieren.
#    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
#    Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#    Und Sie für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" setzen.
#    Dann Für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" ist die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" begrenzt.
#    Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#    Und Sie für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" setzen.
#    Dann Für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" ist die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" begrenzt.
#    Und Sie "11" minuten bis die Änderungen übernommen werden warten.
   
       # Hier wird zur der Webseite navigiert, aber im selben Browser-Tab
       # Erster Tab hat den Index (1)
        Wenn Sie zur Webseite der Bürgeransicht navigieren.
       # Hier wird ein neuer Tab aufgemacht und dann navigiert
       # dieser Tab hat den Index (2)
        Und Sie in einem Fenster zur Webseite der Bürgeransicht navigieren.
   
       # '1'
        Wenn Sie zum geöffneten Bürgeransicht Browsertab 1 wechseln.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie den Wert "<TestData.time>" für Parameter mit Namen "time_first" notieren.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
        Und Sie zur Webseite von Mailinator navigieren.
        Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
        Und Sie auf Mailinator.com auf den Button "GO" klicken.
        Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
        Wenn Sie nun die Nachricht öffnen.
        Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
   
       # '2'
        Wenn Sie zum geöffneten Bürgeransicht Browsertab 2 wechseln.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<TestData.customer_name>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Dann Die Warnung mit der Überschrift "Zu viele Termine mit gleicher E-Mail-Adresse." sollte sichtbar sein.
        Dann sollte die Warnung "Bitte stornieren Sie gegebenenfalls bereits gebuchte Termine, damit eine neue Reservierung möglich ist." erscheinen.
   
       # '1' Termin absagen
       # Da die Uhrzeit im zweiten Fenster die ursprüngliche gespeicherte Uhrzeit überschreibt, überschreiben wir die nochmal mit dem alten Wert.
        Und Sie den Wert "<TestData.time_first>" für Parameter mit Namen "time" notieren.
        Wenn Sie auf den Aktivierungslink klicken.
        Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
        Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
        Und Sie das Fenster der Bürgeransicht mit Index 3 schließen.
        Wenn Sie zur Webseite der Bürgeransicht navigieren.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie den Wert "<TestData.time>" für Parameter mit Namen "time_first" notieren.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<TestData.customer_name>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
        Und Sie zur Webseite von Mailinator navigieren.
        Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
        Und Sie auf Mailinator.com auf den Button "GO" klicken.
        Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
        Wenn Sie nun die Nachricht öffnen.
        Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
        Wenn Sie auf den Aktivierungslink klicken.
        Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
        Und Sie das Fenster der Bürgeransicht mit Index 3 schließen.
        Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
   
       # '2'
        Wenn Sie zum geöffneten Bürgeransicht Browsertab 2 wechseln.
        Und Sie die Seite neu laden.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<TestData.customer_name>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Dann Die Warnung mit der Überschrift "Zu viele Termine mit gleicher E-Mail-Adresse." sollte sichtbar sein.
        Dann sollte die Warnung "Bitte stornieren Sie gegebenenfalls bereits gebuchte Termine, damit eine neue Reservierung möglich ist." erscheinen.
```

#### `ZMS-2849.feature`

Source: [ZMS-2849.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-2849.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
	   
   	@ignore @web @buergeransicht @ZMS-2849 @executeLocally
   	 Szenario: [AUT] Bürgerfrontend "Erfolgreiche" Infobox
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminabsage angezeigt bekommen.
```

#### `ZMS-3177.feature`

Source: [ZMS-3177.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/buergeransicht/ZMS-3177.feature)

```gherkin
#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
	   
   	@ignore @web @buergeransicht @ZMS-3177 @ZMS-3162 @automatisiert @executeLocally
   	 Szenario: [AUT] Test zu Info zu Terminbuchung im Bürgerfrontend
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
	   	 Und Sie unter Behörden und Standorte auf den Standort "Gewerbeamt (KVR-III/21) Gewerbemeldungen" klicken.
	   	 Und Sie für den Standort ins Textfeld Information zu Terminbuchung im Bürgerfrontend "<b style='background-color:Red;'>Information zur Terminbuchung</b>" eingeben.
	   	 Und Sie die Änderungen an der Standortkonfiguration speichern.
	   	 Dann ist Für den Standort "Gewerbeamt (KVR-III/21) Gewerbemeldungen" der Text "<b style='background-color:Red;'>Information zur Terminbuchung</b>" als Info für Terminbuchung vorhanden.
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Ummeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Dann erscheinen der Kalender und die Slots für die Terminauswahl.
	   	 Und Informationen zur Terminbuchung sind für den Kunden sichtbar.
	   	 Wenn Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Dann Informationen zur Terminbuchung sind für den Kunden sichtbar.
	   	 Wenn Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Dann Informationen zur Terminbuchung sind für den Kunden sichtbar.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Dann Informationen zur Terminbuchung sind für den Kunden sichtbar.
```

### zmsadmin

#### `ZMS-1546.feature`

Source: [ZMS-1546.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-1546.feature)

```gherkin
#language: de
Funktionalität: Default

	#Ein Sachbearbeiter signalisiert seine Bereitschaft und das Terminvereinbarungssystem findet die nächste Wartenummer (in diesem Fall die für den  fälligen Terminkunden) und zeigt diese auf der Aufrufanlage an.
	@web @zmsadmin @ZMS-1546 @ZMS-1545 @E2E @automatisiert @executeLocally
	Szenario: Ein Sachbearbeiter signalisiert seine Bereitschaft und das Terminvereinbarungssystem findet die nächste Wartenummer
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Leonrodstraße (KVR-II/232 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "12" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann werden die eingegebene Arbeitsplatzinformationen im Seitenkopf angezeigt.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Dann wird der wartende Kunde aufgerufen.
```

#### `ZMS-1548.feature`

Source: [ZMS-1548.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-1548.feature)

```gherkin
#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-1548 @ZMS-1547 @E2E @automatisiert @executeLocally
	Szenario: Tresen Übersicht der aktuellen Warteschlange
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Dann öffnet sich die Standort auswählen Seite.
		Wenn Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Meldungen" auswählen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Tresen geöffnet.
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Dann öffnet sich die Standort auswählen Seite.
		Wenn Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Meldungen" auswählen.
		Wenn Sie in Feld "Platz-Nr. oder Tresen" den Text "1" eingeben.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Wenn Sie im Zeitmanagementsystem in der Navigationsleite auf die Schaltfläche "Tresen" klicken.
		Dann wird die Seite Tresen geöffnet.
```

#### `ZMS-1549.feature`

Source: [ZMS-1549.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-1549.feature)

```gherkin
#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-1549 @ZMS-1547 @E2E @automatisiert @executeLocally
	Szenario: Test-Tresen-Kund*in hinzufügen
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Meldungen" auswählen.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Tresen geöffnet.
		Wenn Sie einen Spontankunden für die Dienstleistung "<beliebig>" buchen.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn sie einen Terminkunden mit ausgewählter Dienstleistung, Uhrzeit, name und gültige E-Mail-Adresse buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn sie einen Terminkunden mit ausgewählter Dienstleistung und Uhrzeit buchen.
		Dann erscheinen zwei Fehlermeldungen die bei Name und E-Mail-Adresse rot hinterlegt sind.
```

#### `ZMS-2389.feature`

Source: [ZMS-2389.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2389.feature)

```gherkin
#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-2389 @ZMS-1738 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Kundenstatistik -Dateninitialisierung
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
#Kunde a Zulassung Taxi oder Mietwagen, Taxi oder Mietwagen – Unterlagen nachreichen
		Wenn sie einen Terminkunden mit der Dienstleistung "Zulassung Taxi oder Mietwagen, Taxi oder Mietwagen – Unterlagen nachreichen", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "Kundenstatistik1" buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn Der Sachbearbeiter den Terminkunden mit der Anmerkung "Kundenstatistik1" aufruft.
		Dann wird der wartende Kunde aufgerufen.
		Dann sollte der Kunde erschienen sein und der Termin fertiggestellt.
#Kunde b Güterkraftverkehr – Erlaubnis und Lizenz
		Wenn Sie einen Spontankunden für die Dienstleistung "Güterkraftverkehr – Erlaubnis und Lizenz" buchen.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn Der Sachbearbeiter den wartenden Kunden aufruft.
		Dann sollte der Kunde erschienen sein und der Termin fertiggestellt.
#Kunde d Güterkraftverkehr – Erlaubnis und Lizenz
		Wenn Sie einen Spontankunden für die Dienstleistung "Güterkraftverkehr – Erlaubnis und Lizenz" buchen.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn Der Sachbearbeiter den wartenden Kunden aufruft.
		Dann sollte der Kunde nicht erschienen sein.
#Kunde c Zulassung Taxi oder Mietwagen
		Wenn sie einen Terminkunden mit der Dienstleistung "Zulassung Taxi oder Mietwagen", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "Kundenstatistik2" buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn Der Sachbearbeiter den Terminkunden mit der Anmerkung "Kundenstatistik2" aufruft.
		Dann wird der wartende Kunde aufgerufen.
		Dann sollte der Kunde nicht erschienen sein.
```

#### `ZMS-2577.feature`

Source: [ZMS-2577.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2577.feature)

```gherkin
#language: de
Funktionalität: Default

	@web @zmsadmin @ZMS-2577 @automatisiert @executeLocally
		Szenario: [AUT] Test zu "Alle Clusterstandorte" auch für Sachbearbeitung ermöglichen
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.

		# Wiederholungsaufrufe je Standort setzen
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie für den Standort "Bürgerbüro Ruppertstraße (KVR-II/22) WB04" die Wiederholungsaufrufe auf "0" setzen.
		Dann sind Für den Standort "Bürgerbüro Ruppertstraße (KVR-II/22) WB04" Wiederholungsaufrufe auf "0" begrenzt.
		Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie für den Standort "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" die Wiederholungsaufrufe auf "3" setzen.
		Dann sind Für den Standort "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" Wiederholungsaufrufe auf "3" begrenzt.
		# Und Sie "1" Minute bis die Änderungen übernommen werden warten.

		# WB04: zwei Spontankunden anlegen
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/22) WB04" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
		| Dienstleistung                   | Termin name | Kunde      |
		| Ausweisdokumente – Familie      | Termin_SG11 | kunde_SG11 |
		| Beglaubigung von Unterschriften | Termin_SG12 | kunde_SG12 |

		# WB04 Pass: zwei Spontankunden anlegen
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Dann öffnet sich die Standort auswählen Seite.
		Und  Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
		| Dienstleistung        | Termin name | Kunde      |
		| Reisepass             | Termin_SG41 | kunde_SG41 |
		| Vorläufiger Reisepass | Termin_SG42 | kunde_SG42 |

		# Clusteransicht aktivieren und Kürzel prüfen
		Wenn Sie in der Menüzeile der Standorttabellen "Alle Clusterstandorte anzeigen" im Dropdown Clusterstandort auswählen.
		Dann wird die Clusteransicht aktiviert.
		Und In der Warteschlange sind die Kürzeln für folgende Standorten des Clusters zu sehen:
		| WB04      |
		| WB04 Pass |

		# RUNDE 1 (auf WB04 Pass): NUR die ersten zwei (SG11, SG12) aufrufen -> "Nicht erschienen" -> bleiben in der Warteliste
		Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_SG11>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin_SG11>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.

		Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_SG12>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin_SG12>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.

		# Verifizieren: SG11 & SG12 sind weiterhin in der Warteliste
		Dann Sollte der Kunde "<TestData.Termin_SG11>" in der Warteliste erscheinen.
		Und Sollte der Kunde "<TestData.Termin_SG12>" in der Warteliste erscheinen.

		# RUNDE 2 (auf WB04): auf WB04 umschalten, Cluster aktiv lassen; NUR die zweiten zwei (SG41, SG42) aufrufen -> "Nicht erschienen" -> unter Verpasste
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Dann öffnet sich die Standort auswählen Seite.
		Und  Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/22) WB04" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Wenn Sie in der Menüzeile der Standorttabellen "Alle Clusterstandorte anzeigen" im Dropdown Clusterstandort auswählen.
		Dann wird die Clusteransicht aktiviert.

		Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_SG41>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin_SG41>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
		Dann Sollte der Kunde "<TestData.Termin_SG41>" unter verpasste Termine erscheinen.

		Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_SG42>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin_SG42>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
		Dann Sollte der Kunde "<TestData.Termin_SG42>" unter verpasste Termine erscheinen.

		# Optional: Clusteransicht gezielt deaktivieren/umschalten am Ende
		# Wenn Sie in der Menüzeile der Standorttabellen "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" im Dropdown Clusterstandort auswählen.
		# Dann wird die Clusteransicht deaktiviert und die Ansicht für "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" wird aktiviert.
```

#### `ZMS-2578.feature`

Source: [ZMS-2578.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2578.feature)

```gherkin
#language: de
Funktionalität: Default

	@web @zmsadmin @ZMS-2578 @automatisiert @executeLocally
	Szenario: [AUT] Test Parken aufgerufener Termine
		#überprüfen, ob bereits für den Standort und den Monat dienstleistungen gebucht wurden.
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung       						| Termin name    |	Kunde	|
			| Abholung Personalausweis, Reisepass oder eID-Karte 	| Termin1        |	Kunde1	|
			| Abholung Personalausweis, Reisepass oder eID-Karte 	| Termin2        |	Kunde2	|
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Wenn Sie für "120000" Millisekunden warten.
		Und  Sie den Termin parken.
		Dann erscheint der Termin "<TestData.Termin1>" unter geparkte Termine.
		Wenn  Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Und   Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunden jetzt aufrufen" klicken.
		Dann wird der wartende Kunde "<TestData.Termin2>" aufgerufen.
		Wenn  Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie für "60000" Millisekunden warten.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann Sollte der Kunde "<TestData.Kunde2>" unter abgeschlossene Termine erscheinen.
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus den geparkten Terminen aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn  Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie für "30000" Millisekunden warten.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
		Angenommen Die fertige Termintabelle angezeigt.
		Dann Die Wartezeit-H:mm:ss für "<TestData.Kunde1>" sollte zwischen "00:00:01" und "00:01:00" liegen.
		Dann Die Wartezeit-H:mm:ss für "<TestData.Kunde2>" sollte zwischen "00:02:00" und "00:03:00" liegen.
		Dann Die Bearbeitungszeit-H:mm:ss für "<TestData.Kunde1>" sollte zwischen "00:00:01" und "00:01:10" liegen.
		Dann Die Bearbeitungszeit-H:mm:ss für "<TestData.Kunde2>" sollte zwischen "00:00:01" und "00:01:10" liegen.
```

#### `ZMS-2702.feature`

Source: [ZMS-2702.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2702.feature)

```gherkin
#language: de
Funktionalität: ZMS Admin GUI Optimierung 

	#Termin-Weiterleitung

	@web @zmsadmin @ZMS-2702 @ZMS-1808 @executeLocally
	Szenario: [AUT] Termin-Weiterleitung [zms-test]
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234)" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Terminkunden für die Dienstleistung buchen:
			| Dienstleistung    | Termin name    |	Kunde	|
			| Personalausweis 	| Termin1        |	Kunde1	|
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie den Termin zu "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" mit der Anmerkung "Weiterleitung" weiterleiten.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Und Sollte der Kunde "<TestData.Termin1>" in der Warteliste erscheinen.
```

#### `ZMS-2850.feature`

Source: [ZMS-2850.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2850.feature)

```gherkin
#language: de
Funktionalität: ZMS Admin GUI Optimierung 

	
	@web @zmsadmin @ZMS-2850 @ZMS-1566 @executeLocally
	Szenario: [AUT] Aufrufhinweis bei 0 wartenden Kunden
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Erstaufnahmeeinrichtung S-III-U" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Für den Standort sind keine Termine in der Warteschlange vorhanden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Dann erscheint die Meldung, dass keine wartenden Kunden vorhanden sind.
		Wenn Sie im Zeitmanagementsystem unter Termin erstellen auf die Schaltfläche "Spontankunden hinzufügen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Schließen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nächster Kunde bitte" klicken.
		Dann erscheint die Meldung, dass keine wartenden Kunden vorhanden sind.
```

#### `ZMS-2851.feature`

Source: [ZMS-2851.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2851.feature)

```gherkin
#language: de
Funktionalität: ZMS Admin GUI Optimierung


  @web @zmsadmin @ZMS-2851 @ZMS-1795 @executeLocally
  Szenario: [AUT] Kunde nach Aufruf nicht erschienen - Button umbenennen
    Wenn Sie zur Webseite der Administration navigieren.
    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
    Und Sie für den Standort "Standesamt München (KVR-II/112) Geburtenbüro" die Wiederholungsaufrufe auf "0" setzen.
    Dann sind Für den Standort "Standesamt München (KVR-II/112) Geburtenbüro" Wiederholungsaufrufe auf "0" begrenzt.
    Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
    Und Sie für den Standort "Standesamt München (KVR-II/1141) Urkundenstelle" die Wiederholungsaufrufe auf "1" setzen.
    Dann sind Für den Standort "Standesamt München (KVR-II/1141) Urkundenstelle" Wiederholungsaufrufe auf "1" begrenzt.
    Und Sie "1" Minute bis die Änderungen übernommen werden warten.
#Standort: Standesamt München (KVR-II/112) Geburtenbüro, Wiederholungsaufrufe: 0
    Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
    Und  Sie für "Standort" den Wert "Standesamt München (KVR-II/112) Geburtenbüro" auswählen.
    Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
      | Dienstleistung  | Termin name   | Kunde        |
      | Urkundenabholung       | Termin_lang_1 | kunde_lang_1 |
      | Vaterschaftsanerkennung ohne Sorgerechtserklärung vor Geburt/Geburtsbeurkundung des Kindes | Termin_lang_2 | kunde_lang_2 |
    Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_lang_1>" aus der Warteliste aufruft.
    Dann wird der wartende Kunde "<TestData.Termin_lang_1>" aufgerufen.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
    Dann Sollte der Kunde "<TestData.Termin_lang_1>" unter verpasste Termine erscheinen.
    Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_lang_2>" aus der Warteliste aufruft.
    Dann wird der wartende Kunde "<TestData.Termin_lang_2>" aufgerufen.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
    # Todo: Bug Fix ZMSKVR-1102 -> Abbrechen Button appointment gets stuck in Aufgerufene Termine and no longer returns to the queue Warteschlange
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Abbrechen" klicken.
    #Dann Sollte der Kunde "<TestData.Termin_lang_2>" in der Warteliste erscheinen.
#Standort: Standesamt München (KVR-II/1141) Urkundenstelle, Wiederholungsaufrufe: 1
    # Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
    # Und  Sie für "Standort" den Wert "Standesamt München (KVR-II/1141) Urkundenstelle" auswählen.
    # Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
    # Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    # Dann wird die Seite Sachbearbeiterplatz angezeigt.
    # Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
    #  | Dienstleistung  | Termin name     | Kunde          |
    #  | Erklärung zur Reihenfolge der Vornamen       | Termin_mittel_1 | kunde_mittel_1 |
    #  | Anpassung des Geschlechtseintrags und Vornamens (Selbstbestimmungsgesetz) | Termin_mittel_2 | kunde_mittel_2 |
    # Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_mittel_1>" aus der Warteliste aufruft.
    # Dann wird der wartende Kunde "<TestData.Termin_mittel_1>" aufgerufen.
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
    # Dann Sollte der Kunde "<TestData.Termin_mittel_1>" in der Warteliste erscheinen.
    # Und  Im Namensfeld der Warteschlange vom "<TestData.Termin_mittel_1>" steht, wie lange es noch dauert, bis der Kunde "<TestData.kunde_mittel_1>" nochmals aufgerufen werden kann.
    # Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_mittel_2>" aus der Warteliste aufruft.
    # Dann wird der wartende Kunde "<TestData.Termin_mittel_2>" aufgerufen.
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Abbrechen" klicken.
    # Dann Sollte der Kunde "<TestData.Termin_mittel_2>" in der Warteliste erscheinen.
    # Und  Im Namensfeld der Warteschlange vom "<TestData.Termin_mittel_2>" steht, wie lange es noch dauert, bis der Kunde "<TestData.kunde_mittel_2>" nochmals aufgerufen werden kann.
```

#### `ZMS-2853.feature`

Source: [ZMS-2853.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-2853.feature)

```gherkin
#language: de
Funktionalität: ZMS Admin GUI Optimierung 

	
	@web @zmsadmin @ZMS-2853 @ZMS-1499 @ZMS-3162 @executeLocally
	Szenario: [AUT] Kundeninformation direkt nach Aufruf anzeigen
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie im Zeitmanagementsystem unter Termin erstellen die Dienstleistung "Meldebescheinigung" auswählen.
		Und Sie im Zeitmanagementsystem unter Termin erstellen den Namen "<zufällig>" eingeben.
		Und Sie im Zeitmanagementsystem unter Termin erstellen die E-mail-Adresse "<mailinator>" eingeben.
		Und Sie im Zeitmanagementsystem unter Termin erstellen die Telefonnummer "+491234567890" eingeben.
		Und Sie im Zeitmanagementsystem unter Termin erstellen die Anmerkung "Spontankunde" eingeben.
		Und Sie im Zeitmanagementsystem unter Termin erstellen auf die Schaltfläche "Spontankunden hinzufügen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Schließen" klicken.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn Der Sachbearbeiter "<TestData.new_waiting_number>" aus der Warteliste aufruft.
		Und wird der Kundennamen "<TestData.new_appointment_customer_name>" unter Kundeninformation angezeigt.
		Und wird die Wartenummer "<TestData.new_waiting_number>" unter Kundeninformation angezeigt.
		Und wird die Dienstleistung "Meldebescheinigung" unter Kundeninformation angezeigt.
		Und wird die Anmerkung "Spontankunde" unter Kundeninformation angezeigt.
		Und wird die Telefinnummer "<TestData.new_appointment_customer_phone_number>" unter Kundeninformation angezeigt.
		Und wird die E-Mail "<TestData.customer_email>" unter Kundeninformation angezeigt.
		Und wird die Wartezeit unter Kundeninformation angezeigt.
		Und wird die Zeit seit Kundenaufruf unter Kundeninformation angezeigt.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und wird der Kundennamen "<TestData.new_appointment_customer_name>" unter Kundeninformation angezeigt.
		Und wird die Wartenummer "<TestData.new_waiting_number>" unter Kundeninformation angezeigt.
		Und wird die Dienstleistung "Meldebescheinigung" unter Kundeninformation angezeigt.
		Und wird die Anmerkung "Spontankunde" unter Kundeninformation angezeigt.
		Und wird die Telefinnummer "<TestData.new_appointment_customer_phone_number>" unter Kundeninformation angezeigt.
		Und wird die E-Mail "<TestData.customer_email>" unter Kundeninformation angezeigt.
		Und wird die Wartezeit unter Kundeninformation angezeigt.
```

#### `ZMS-3160.feature`

Source: [ZMS-3160.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-3160.feature)

```gherkin
#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-3160 @ZMS-3162 @automatisiert @executeLocally
	Szenario: [AUT] Löschen von Behörden und Standorte mit Bestätigung hinterlegen
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Standort "Gewerbeamt (KVR-III/21) Meldungen" klicken.
		Und Sie unter der Standortkonfiguration auf die Schaltfläche "löschen" klicken.
		Dann erscheint ein Pop-Up-Fenster "Der Standort wird gelöscht. Soll der Standort wirklich gelöscht werden?" um den Standort zu löschen.
```

#### `ZMS-3171.feature`

Source: [ZMS-3171.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-3171.feature)

```gherkin
#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-3171 @ZMS-3162 @automatisiert @executeLocally
	Szenario: [AUT] Vorbelegung von "Mit E-Mail Bestätigung" ist konfigurierbar
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Meldungen" auswählen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Standort "Gewerbeamt (KVR-III/21) Meldungen" klicken.
		Und Sie für den Standort den Wert für die E-Mail-Bestätigung auf true setzen.
		Und Sie die Änderungen an der Standortkonfiguration speichern.
		Dann Für den Standort "Gewerbeamt (KVR-III/21) Meldungen" ist der Standardwert für die E-Mail-Bestätigung auf true gesetzt.
		Wenn Sie im Zeitmanagementsystem in der Navigationsleite auf die Schaltfläche "Tresen" klicken.
		Und Sie im Zeitmanagementsystem unter Termin erstellen die Zeit "<beliebig>" auswählen.
    	# ausgewählt / nicht ausgewählt
		Dann ist die Checkbox Mit E-Mail Bestätigung "ausgewählt".
```

#### `ZMS-878.feature`

Source: [ZMS-878.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsadmin/ZMS-878.feature)

```gherkin
#language: de
Funktionalität: Aufbau ZMS-Testautomatisierung,Kernsystem 

	#Testschwerpunkt: Der Terminadministrator kann Arbeitszeiten und deren Gültigkeitszeiträume frei definieren
	#
	# 
	@web @zmsadmin @openingHours @ZMS-878 @ZMS-811 @ZMS-1910 @ZMS-2228 @ZMS-2561 @ZMS-2385 @ZMS-2479 @ZMS-2290 @ZMS-2202 @automatisiert @executeLocally
	Szenario: [AUT] Arbeitszeiten konfigurierbar
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie die Öffnungszeit-Accordion "Neue Öffnungszeit" öffnen.
		Und Sie für "Öffnungszeiten Anmerkung" den Wert "Anmerkung" auswählen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Terminkunden" auswählen.
		Und Sie für "Serie" den Wert "jede Woche" auswählen.
		Und Sie "Montag" unter Wochentage selektieren.
		Und Sie "Dienstag" unter Wochentage selektieren.
		Und Sie "Mittwoch" unter Wochentage selektieren.
		Und Sie "Donnerstag" unter Wochentage selektieren.
		Und Sie "Freitag" unter Wochentage selektieren.
		Und Sie in Feld "Datum bis" den Text "<heute+14_tage>" eingeben.
		Und Sie in Feld "Uhrzeit von" den Text "08:00" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "17:00" eingeben.
		Und Sie für Terminarbeitsplätze unter "Insgesamt" die Anzahl 1 auswählen.
		Und Sie für Terminarbeitsplätze unter "Internet" die Anzahl 1 auswählen.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Dann sollte die aktivierte Öffnungszeit mit der Anmerkung "<TestData.Anmerkung>" löschbar sein.
```

### zmscitizenview

#### `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature`

Source: [zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmscitizenview/zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature)

```gherkin
#language: en
@web @zmscitizenview @ZMSKVR-1124 @executeLocally
Feature: ZMSKVR-1124 Ruppertstraße booking — zmscitizenview (Passkalender 10502, Hauptkalender 10489, Abholung 10492, jump-in)
  As a citizen
  I want to book via the citizen view UI
  So that jump-in links route to the correct office (Passkalender 10502, Hauptkalender 10489, Abholung 10492)
  And allowDisabledServicesMix jump-ins stay valid; Pass-only still books on the Passkalender (10502)

  # Flow: calendar + slot → Ausgewählter Termin callout → Weiter (= reserve) → Kontakt form → Weiter (= update) →
  # preconfirm (summary + privacy) → Weiter → activation callout (“Aktivieren Sie Ihren Termin.”)
  # Slot selection is split into steps (wait for slots → Später if available → scroll/highlight timeslot → click slot → Weiter/reserve)
  # so Cucumber reports and per-step @AfterStep screenshots show the time slot grid (highlight step) before the click.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  # --- Invalid jump-in: Passkalender 10502 only offers Pass family services (three Pass services). Hauptkalender 10489 also supports a Non-Pass service (e.g. 1063475 Wohnsitzanmeldung) in combination with Pass. Jumping in with this Non-Pass alone on 10502 has no relation → invalid jump-in callout. ---
  @jumpin @passCalendar
  Scenario: Non-Pass service jump-in with Passkalender 10502 is rejected
    Given I open zmscitizenview with jump-in service "1063475" and location "10502"
    Then the invalid jump-in callout should be visible in the citizen view

  # --- allowDisabledServicesMix (jump-in): Pass service 1063441 with location 10489 (Hauptkalender) is valid. The Pass-only combination must still list and book to Passkalender 10502; confirms allowDisabledServicesMix behaviour across locations. ---
  @jumpin @allowDisabledServicesMix @passCalendar
  Scenario: Pass jump-in with location 10489 is valid; Pass-only books to provider 10502
    Given I open zmscitizenview with jump-in service "1063441" and location "10489"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10502 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    And the estimated duration in the booking summary should be 15 minutes in the citizen view
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    # Second mail fetch: confirmation mail (with appointment view link) exists only after opening the confirm link above
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10502 in the citizen view
    And the estimated duration in the confirmation view should be 15 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Passkalender 10502 (jump-in): direct Reisepass jump-in must show only Pass-family combination services and book consistently to provider 10502 in Ort, Ausgewählter Termin, booking summary, and confirmation views. ---
  @jumpin @passCalendar
  Scenario: Reisepass jump-in Passkalender 10502 books to provider 10502 with correct summaries
    Given I open zmscitizenview with jump-in service "1063453" and location "10502"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    And only Pass calendar services should be offered on the combination step
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10492 should not appear in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10502 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    And the estimated duration in the booking summary should be 15 minutes in the citizen view
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10502 in the citizen view
    And the estimated duration in the confirmation view should be 15 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Hauptkalender 10489 (jump-in, allowDisabledServicesMix): Pass jump-in lands on the combination step where Pass is combinable with Wohnsitzanmeldung. Booking must stay on Hauptkalender provider 10489, with duration 15 → 30 minutes after adding Wohnsitzanmeldung. ---
  @jumpin @mainCalendar
  Scenario: Non-Pass jump-in Hauptkalender 10489 shows Pass combinable and books to provider 10489
    Given I open zmscitizenview with jump-in service "1063453" and location "10489"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    When I add subservice "Wohnsitzanmeldung" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 30 minutes
    And I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the estimated duration in the booking summary should be 30 minutes in the citizen view
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10489 in the citizen view
    And the estimated duration in the confirmation view should be 30 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Abholung 10295182 (jump-in): pick-up service is only offered at provider 10492 (KVR-II/211). Jump-in must show only 10492 on Ort, and the booking must stay on 10492 in all summary views. ---
  @jumpin @pickupCalendar
  Scenario: Abholung jump-in only Abholstandort 10492 and books to provider 10492
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 10 minutes
    When I continue from the service combination step
    Then provider checkbox 10492 should be visible in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10502 should not appear in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    And the estimated duration in the booking summary should be 10 minutes in the citizen view
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10492 in the citizen view
    And the estimated duration in the confirmation view should be 10 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Full entry (no jump-in): Service Finder path for Personalausweis without preselected office. Passkalender 10502 scenario — only Pass-family services on the combination step and booking tied to provider 10502 throughout. ---
  @serviceFinder @passCalendar
  Scenario: Personalausweis full entry Passkalender 10502
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    And I keep only providers "10502" checked in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10502 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    And the estimated duration in the booking summary should be 15 minutes in the citizen view
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    # Second mail fetch: confirmation mail (with appointment view link) exists only after opening the confirm link above
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10502 in the citizen view
    And the estimated duration in the confirmation view should be 15 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Full entry (no jump-in): Service Finder path for Personalausweis with Hauptkalender 10489. Pass is combinable with Wohnsitzanmeldung on the combination step; adding Wohnsitzanmeldung changes duration 15 → 30 minutes and booking must be tied to provider 10489 in all summaries. ---
  @serviceFinder @mainCalendar
  Scenario: Personalausweis full entry Hauptkalender 10489
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    When I add subservice "Wohnsitzanmeldung" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 30 minutes
    And I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And I keep only providers "10489" checked in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the estimated duration in the booking summary should be 30 minutes in the citizen view
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10489 in the citizen view
    And the estimated duration in the confirmation view should be 30 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
```

### zmsstatistic

#### `ZMS-1558.feature`

Source: [ZMS-1558.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsstatistic/ZMS-1558.feature)

```gherkin
#language: de
Funktionalität: Aufbau ZMS-Testautomatisierung

	
	@web @zmsstatistic @ZMS-1558 @ZMS-1738 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Kundenstatistik
		Wenn Sie zur Webseite der Statistik navigieren.
		Und  Sie in der Statistik auf die Schaltfläche "Anmelden" klicken.
		Und  Sie in der Statistik für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Übersichtsseite der Statistik angezeigt.
		Wenn Sie in der Statistik in der Seitenleiste auf die Schaltfläche "Kundenstatistik" klicken.
		Dann wird die Statistik-Seite "Kundenstatistik" angezeigt.
		Und  Sie in der Statistik im Filter für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik im Zeitraum von 14 Tagen vor heute bis heute filtern.
		Und die folgenden Daten sollten für den vorherigen Tag angezeigt werden:
			| Spaltenname                      | Erwarteter Wert |
			| Erschienene Kunden               | 2               |
			| Nicht erschienene Kunden         | 2               |
			| Erschienene Termin-Kunden        | 1               |
			| Nicht erschienene Termin-Kunden  | 1               |
			| Erschienene Spontan-Kunden       | 1               |
			| Nicht erschienene Spontan-Kunden | 1               |
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann wird die Kundenstatistik heruntergeladen.
```

#### `ZMS-1559.feature`

Source: [ZMS-1559.feature](https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features/ui/zmsstatistic/ZMS-1559.feature)

```gherkin
#language: de
Funktionalität: Default

	
	@web @zmsstatistic @ZMS-1559 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Dienstleistungsstatistik
		Wenn Sie zur Webseite der Statistik navigieren.
		Und  Sie in der Statistik auf die Schaltfläche "Anmelden" klicken.
		Und  Sie in der Statistik für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Übersichtsseite der Statistik angezeigt.
		Wenn Sie in der Statistik in der Seitenleiste auf die Schaltfläche "Dienstleistungsstatistik" klicken.
		Dann wird die Statistik-Seite "Dienstleistungsstatistik" angezeigt.
		Und  Sie in der Statistik im Filter für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik im Zeitraum von 14 Tagen vor heute bis heute filtern.
		Und  die folgenden Dienstleistungen sollten in der Dienstleistungsstatistik angezeigt werden:
			| dienstleistung                              |
			| Güterkraftverkehr – Erlaubnis und Lizenz   |
			| Taxi oder Mietwagen – Unterlagen nachreichen |
			| Zulassung Taxi oder Mietwagen              |
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann wird die Dienstleistungsstatistik heruntergeladen.
```
