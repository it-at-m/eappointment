<div id="top"></div>

<!-- PROJECT SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
<!-- [![EUPL License][license-shield]][license-url] -->

---

# E-Appointment

## Documentation

- Repository docs (source of truth): [docs/index.md](docs/index.md)
- Published developer handbook (GitHub Pages root): [https://it-at-m.github.io/eappointment/](https://it-at-m.github.io/eappointment/)


## About eAppointment

<img width="200" align="right" alt="web-app-manifest-512x512" src="https://github.com/user-attachments/assets/5dc7c4db-cc17-47a4-ad11-25e096b4e7e8" />

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
* ticketprinter support for customers without appointments (authenticated, lockable, timeable)
* calldisplay support
* collecting statistics like waiting time or served clients per day
* emergency call for employees

[Code Coverage ZMSAPI and ZMSCITIZENAPI Documentation](https://it-at-m.github.io/eappointment/)


## Projects

### ZMS
The original project to replace commercial proprietary software with the open source Berlin solution ZMS and went live with each city agency/department. This foundational project established the core appointment management system infrastructure for Munich's municipal services.

### MPDZBS
The creation of the PHP zmscitizenapi and the replacement of the first open source Vue2 frontend ([eappointment-buergeransicht](https://github.com/it-at-m/eappointment-buergeransicht)) with the Vue3 zmscitizenview/[refarch](https://refarch.oss.muenchen.de/) citizen frontend, plus the creation of the city's Vue patternlab ([muc-patternlab-vue](https://github.com/it-at-m/muc-patternlab-vue)). This project modernized the citizen-facing components and established design system standards.

### ZMSKVR
To add still needed features and requirements for the city's agencies/departments and improve weaknesses in user experiences. This includes implementing features in zmscitizenview that were not completed by MPDZBS, ensuring comprehensive functionality for all municipal departments.

### MUXDBS
Builds on MPDZBS following the Reifegradmodell (Maturity Level Model) as an implementation framework for Onlinezugangsgesetz (OZG - Online Access Act) compliance ([digitale-verwaltung.de](https://www.digitale-verwaltung.de/Webs/DV/DE/onlinezugangsgesetz/ozg-grundlagen/info-reifegradmodell/info-reifegradmodell-node.html)) and adds additional components to zmscitizenview which will allow things such as login with BundID, BayernID and Elster for seamless online citizen appointments. This project represents the next maturity level of digital government services following federal implementation guidelines.

## Contact
[Overview](https://opensource.muenchen.de/software/zeitmanagementsystem.html)
BerlinOnline Stadtportal GmbH & Co KG Contact: 

Munich Contact: it@M - opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG and it@M.

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" height="30" align="center"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" height="30" align="center"></td>
    <td><img src="https://assets.muenchen.de/logos/lhm/logo-lhm-muenchen.svg" height="30" align="center"></td>
    <td><img src="https://avatars.githubusercontent.com/u/58515289" height="30" align="center"></td>
  </tr>
</table>

---

## Über eAppointment

<img width="200" align="right" alt="web-app-manifest-512x512" src="https://github.com/user-attachments/assets/5dc7c4db-cc17-47a4-ad11-25e096b4e7e8" />

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

[Code-Abdeckung ZMSAPI und ZMSCITIZENAPI Dokumentation](https://it-at-m.github.io/eappointment/)

## Projekte

### ZMS
Das ursprüngliche Projekt zur Ersetzung kommerzieller proprietärer Software durch die Open-Source-Berlin-Lösung ZMS und ging mit jeder städtischen Behörde/Abteilung live. Dieses grundlegende Projekt etablierte die Kerninfrastruktur des Terminverwaltungssystems für Münchens kommunale Dienstleistungen.

### MPDZBS
Die Erstellung der PHP zmscitizenapi und der Ersatz des ersten Open-Source Vue2-Frontends ([eappointment-buergeransicht](https://github.com/it-at-m/eappointment-buergeransicht)) durch das Vue3 zmscitizenview/[refarch](https://refarch.oss.muenchen.de/) Bürger-Frontend, plus die Erstellung des städtischen Vue-Patternlabs ([muc-patternlab-vue](https://github.com/it-at-m/muc-patternlab-vue)). Dieses Projekt modernisierte die bürgerseitigen Komponenten und etablierte Design-System-Standards.

### ZMSKVR
Um noch benötigte Funktionen und Anforderungen für die städtischen Behörden/Abteilungen hinzuzufügen und Schwächen in der Benutzererfahrung zu verbessern. Dies umfasst die Implementierung von Funktionen in zmscitizenview, die von MPDZBS nicht abgeschlossen wurden, um umfassende Funktionalität für alle kommunalen Abteilungen sicherzustellen.

### MUXDBS
Baut auf MPDZBS auf und folgt dem Reifegradmodell (Reifegradmodell) als Implementierungsrahmen für Onlinezugangsgesetz (OZG) Compliance ([digitale-verwaltung.de](https://www.digitale-verwaltung.de/Webs/DV/DE/onlinezugangsgesetz/ozg-grundlagen/info-reifegradmodell/info-reifegradmodell-node.html)) und fügt zusätzliche Komponenten zu zmscitizenview hinzu, die Dinge wie Login mit BundID, BayernID und Elster für nahtlose Online-Bürgertermine ermöglichen werden. Dieses Projekt repräsentiert das nächste Reifegradniveau digitaler Regierungsdienstleistungen entsprechend den bundesweiten Implementierungsrichtlinien.

## Kontakt
BerlinOnline Stadtportal GmbH & Co KG Kontakt: 

Munich Kontakt: it@M - opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG und it@M.

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" height="30" align="center"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" height="30" align="center"></td>
    <td><img src="https://assets.muenchen.de/logos/lhm/logo-lhm-muenchen.svg" height="30" align="center"></td>
    <td><img src="https://avatars.githubusercontent.com/u/58515289" height="30" align="center"></td>
  </tr>
</table>

## Dokumentation (Deutsch)

- Dokumentation im Repository (maßgebliche Quelle): [docs/index.md](docs/index.md)
- Veröffentlichtes Entwicklerhandbuch (GitHub Pages, Startseite): [https://it-at-m.github.io/eappointment/](https://it-at-m.github.io/eappointment/)

## Screenshot
![screenshot](https://github.com/user-attachments/assets/54d360e9-c47b-4f3c-b849-5966a8766af9)
![combined_image](https://github.com/user-attachments/assets/87902e60-fe90-48a0-bf60-c5edec210dc9)


<p align="right">(<a href="#top">back to top</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/it-at-m/eappointment.svg?style=for-the-badge
[contributors-url]: https://github.com/it-at-m/eappointment/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/it-at-m/eappointment.svg?style=for-the-badge
[forks-url]: https://github.com/it-at-m/eappointment/network/members
[stars-shield]: https://img.shields.io/github/stars/it-at-m/eappointment.svg?style=for-the-badge
[stars-url]: https://github.com/it-at-m/eappointment/stargazers
[issues-shield]: https://img.shields.io/github/issues/it-at-m/eappointment.svg?style=for-the-badge
[issues-url]: https://github.com/it-at-m/eappointment/issues
[license-shield]: https://img.shields.io/github/license/it-at-m/eappointment.svg?style=for-the-badge
[license-url]: https://github.com/it-at-m/eappointment/blob/main/LICENSE
[product-screenshot]: images/screenshot.png
