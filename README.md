# E-Appointment
<!-- <img src="https://it-at-m.github.io/eappointment/zmsapi/public/doc/logo.png" width="150" align="right"> -->

This monorepo contains the Munich-specific adjustments to the original Berlin version. You can explore the original project here: https://gitlab.com/eappointment/eappointment

Public E-Appointment is a software for online booking of appointments and processing of queues such as calling appointment numbers and collecting statistics on services provided.

The software has been used in public administration in the German capital Berlin for more than 20 years and has been redeveloped under a new license since 2016. This allows the software to be re-released under the EUPL, an open source license recognized by OSI.

It is planned to release the software as open source in the course of 2022/2024. This requires a number of adjustments, so that step by step the individual components of the software will be published here.
On the one hand, the documentation of the software is published in this repository, on the other hand, new ideas and further developments are planned here, which apply across the board for the other repositories.

The ZMS system is intended to manage human waiting queues. It has the following features:

* make appointments via a calender and initiate a process to manage an appointment
* import requests (services) and providers (locations) from external sources
* manage scopes for appointments, including a four level hierarchy of owner->organisation->department->scope
* manage opening hours including closed days
* login-system with different access levels
* pickup for documents
* ticketprinter support for customers without appointments (authenticated, lockable, timeable)
* calldisplay support
* collecting statistics like waiting time or served clients per day
* emergency call for employees

[ZMSAPI Documentation](https://it-at-m.github.io/eappointment/zmsapi/public/doc/index.html)
[ZMSCITIZENAPI Documentation](https://it-at-m.github.io/eappointment/zmscitizenapi/public/doc/index.html)

## Contact
[Overview](https://opensource.muenchen.de/software/zeitmanagementsystem.html)
BerlinOnline Stadtportal GmbH & Co KG Contact: 

Munich Contact: it@M - opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG and it@M.

<!-- <table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" height="30" align="center"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" height="30" align="center"></td>
    <td><img src="https://muenchen.digital/.resources/lhm-ms-templates-digitalradar/resources/img/logo-lhm.svg" height="30" align="center"></td>
    <td><img src="https://avatars.githubusercontent.com/u/58515289" height="30" align="center"></td>
  </tr>
</table> -->

---

<!-- <img src="https://it-at-m.github.io/eappointment/zmsapi/public/doc/logo.png" width="150" align="right"> -->

Dieses Monorepo enthält die München-spezifischen Anpassungen der ursprünglichen Berliner Version. Das ursprüngliche Projekt kannst du hier erkunden: https://gitlab.com/eappointment/eappointment

Die Public E-Appointment-Software ist eine Software für die Online-Buchung von Terminen und die Bearbeitung von Warteschlangen, wie das Aufrufen von Terminnummern und das Sammeln von Statistiken über erbrachte Dienstleistungen.

Die Software wird seit mehr als 20 Jahren in der öffentlichen Verwaltung der deutschen Hauptstadt Berlin verwendet und wird seit 2016 unter einer neuen Lizenz weiterentwickelt. Dies ermöglicht eine Wiederveröffentlichung der Software unter der EUPL, einer von der OSI anerkannten Open-Source-Lizenz.

Es ist geplant, die Software im Laufe der Jahre 2022/2024 als Open Source zu veröffentlichen. Dafür sind einige Anpassungen erforderlich, sodass die einzelnen Komponenten der Software schrittweise hier veröffentlicht werden.

Einerseits wird die Dokumentation der Software in diesem Repository veröffentlicht, andererseits sind hier neue Ideen und Weiterentwicklungen geplant, die bereichsübergreifend für die anderen Repositories gelten.

Das ZMS-System dient zur Verwaltung von Warteschlangen für Menschen. Es bietet folgende Funktionen:

* Termine über einen Kalender vereinbaren und einen Prozess zur Verwaltung eines Termins initiieren
* Anfragen (Dienste) und Anbieter (Standorte) aus externen Quellen importieren
* Verwaltung von Terminbereichen, einschließlich einer vierstufigen Hierarchie von Eigentümer->Organisation->Abteilung->Bereich
* Verwaltung von Öffnungszeiten einschließlich geschlossener Tage
* Login-System mit verschiedenen Zugriffsebenen
* Abholung von Dokumenten
* Unterstützung für Ticketdrucker für Kunden ohne Termine (authentifiziert, abschließbar, zeitgesteuert)
* Unterstützung für Anzeigesysteme für Aufrufe
* Sammeln von Statistiken wie Wartezeiten oder bedienten Kunden pro Tag
* Notruf für Mitarbeiter

[ZMSAPI-Dokumentation](https://it-at-m.github.io/eappointment/zmsapi/public/doc/index.html)
[ZMSCITIZENAPI-Dokumentation](https://it-at-m.github.io/eappointment/zmscitizenapi/public/doc/index.html)

## Kontakt
BerlinOnline Stadtportal GmbH & Co KG Kontakt: 

Munich Kontakt: it@M - opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG und it@M.

<!-- <table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" height="30" align="center"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" height="30" align="center"></td>
    <td><img src="https://muenchen.digital/.resources/lhm-ms-templates-digitalradar/resources/img/logo-lhm.svg" height="30" align="center"></td>
    <td><img src="https://avatars.githubusercontent.com/u/58515289" height="30" align="center"></td>
  </tr>
</table> -->

----

## Getting Started
- `ddev start`
- `ddev exec ./cli modules loop composer install`

## Import Database
- `ddev import-db --file=.resources/zms.sql`
- `ddev exec zmsapi/vendor/bin/migrate --update`

## Dependency Check for PHP Upgrades
Pass the PHP version that you would want to upgrade to and recieve information about dependency changes patch, minor, or major for each module.
e.g.
- `ddev exec ./cli modules check-upgrade 8.1`
- `ddev exec ./cli modules check-upgrade 8.2`
- `ddev exec ./cli modules check-upgrade 8.3`

## Unit Testing
To run unit tests locally refer to the Github Workflows: https://github.com/it-at-m/eappointment/blob/main/.github/workflows/unit-tests.yaml and in your local docker container run:

- `ddev ssh`
- `cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}`
- `./vendor/bin/phpunit`

For zmsapi and zmsdb you must first import the test data which unfortunately overwrites your local database. For zmsclient you need the php base image.