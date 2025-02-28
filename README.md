<div id="top"></div>

<!-- PROJECT SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
<!-- [![EUPL License][license-shield]][license-url] -->

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

[Code Coverage ZMSAPI and ZMSCITIZENAPI Documentation](https://it-at-m.github.io/eappointment/)

## Contact
[Overview](https://opensource.muenchen.de/software/zeitmanagementsystem.html)
BerlinOnline Stadtportal GmbH & Co KG Contact: 

Munich Contact: it@M - opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG and it@M.

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" height="30" align="center"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" height="30" align="center"></td>
    <td><img src="https://muenchen.digital/.resources/lhm-ms-templates-digitalradar/resources/img/logo-lhm.svg" height="30" align="center"></td>
    <td><img src="https://avatars.githubusercontent.com/u/58515289" height="30" align="center"></td>
  </tr>
</table>

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

[Code-Abdeckung ZMSAPI und ZMSCITIZENAPI Dokumentation](https://it-at-m.github.io/eappointment/)

## Kontakt
BerlinOnline Stadtportal GmbH & Co KG Kontakt: 

Munich Kontakt: it@M - opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG und it@M.

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" height="30" align="center"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" height="30" align="center"></td>
    <td><img src="https://muenchen.digital/.resources/lhm-ms-templates-digitalradar/resources/img/logo-lhm.svg" height="30" align="center"></td>
    <td><img src="https://avatars.githubusercontent.com/u/58515289" height="30" align="center"></td>
  </tr>
</table>

----

## Getting Started
- `ddev start`
- `ddev exec ./cli modules loop composer install`
- `ddev exec ./cli modules loop npm install`
- `ddev exec ./cli modules loop npm build`

- `cd zmscitizenview`
- `npm install`
- `npm run build`
- `npm run dev`

## Import Database
- `ddev import-db --file=.resources/zms.sql`
- `ddev exec zmsapi/vendor/bin/migrate --update`

## Dependency Check for PHP Upgrades
Pass the PHP version that you would want to upgrade to and recieve information about dependency changes patch, minor, or major for each module.
e.g.
- `ddev exec ./cli modules check-upgrade 8.1`
- `ddev exec ./cli modules check-upgrade 8.2`
- `ddev exec ./cli modules check-upgrade 8.3`

## Code Quality Checks
We use PHPCS (following PSR-12 standards) and PHPMD to maintain code quality and detect possible issues early. These checks run automatically in our GitHub Actions pipeline but can also be executed locally.

To run Checks locally in your local docker container:

0. Run all at once:
- `ddev exec "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'"`
- `ddev exec "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"`

1. **Enter the container** (if using DDEV or Docker):
- `ddev ssh`

2. **Go to the desired module directory:
- `cd zmsadmin`
3. Run PHPCS (PSR-12 standard):
- `vendor/bin/phpcs --standard=psr12 src/`
- ```
  You can automatically fix many PHPCS formatting issues by running:
  - vendor/bin/phpcbf --standard=psr12 src/
  or
  - phpcs --standard=psr12 --fix src/
  ```
4. Run PHPMD (using the phpmd.rules.xml in the project root):
- `vendor/bin/phpmd src/ text ../phpmd.rules.xml`

## Unit Testing
To run unit tests locally refer to the Github Workflows: https://github.com/it-at-m/eappointment/blob/main/.github/workflows/unit-tests.yaml and in your local docker container run:

- `ddev ssh`
- `cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}`
- `./vendor/bin/phpunit`

For zmsapi and zmsdb you must first import the test data which unfortunately overwrites your local database. For zmsclient you need the php base image.


## Branch Naming Convention
To keep our branch names organized and easily understandable, we follow a specific naming convention for all branches created in this repository. Please adhere to this convention when creating new branches:

1. **type**: The type of work the branch represents. This should be one of the following:
   - `feature`: For new features or enhancements.
   - `bugfix`: For bug fixes.
   - `hotfix`: For urgent fixes that need to be applied quickly.
   - `cleanup`: For code refactoring, or documentation updates.
   - `docs`: For updating documentation such as the README.md CODE_OF_CONDUCT.md LICENSE.md CHANGELOG.md CONTRIBUTING.md. Providing a ticket number or project for docs is optional.
   - `chore`: For maintaining and updating dependencies, libraries, PHP/Node/Twig Versions, or other maintenance work.

2. **project**: The project identifier. This should be:
   - `zms` for the ZMS project.
   - `zmskvr` for the ZMSKVR project.
   - `mpdzbs` for the MPDZBS project.

3. **issue number**: The ticket or issue number related to this branch (use digits only). This helps track the branch to a specific issue in the project management system.

4. **description**: A brief, lowercase description of the branch's purpose, using only lowercase letters, numbers, and hyphens (`-`).

- Always use lowercase letters and hyphens for the description.
- The issue number should be a numeric ID corresponding to the relevant ticket or task.
- Descriptions should be concise and informative, summarizing the branch's purpose.

#### Examples

- **Feature Branch**: `feature-zms-12345-this-is-a-feature-in-the-zms-project`
- **Bugfix Branch**: `bugfix-mpdzbs-67890-fix-crash-on-startup`
- **Hotfix Branch**: `hotfix-zmskvr-98765-critical-fix-for-login`
- **Cleanup Branch**: `cleanup-mpdzbs-11111-remove-unused-code`
- **Chore Branch**: `chore-zms-2964-composer-update`
- **Docs Branch**: `docs-zmskvr-0000-update-readme` `docs-zms-release-40-update-changelog`

#### Regular Expression

The branch name must match the following regular expression:
`^(feature|hotfix|bugfix|cleanup|maintenance|docs)-(zms|zmskvr|mpdzbs)-[0-9]+-[a-z0-9-]+$`

**For further commit rules please refer to https://www.conventionalcommits.org/en/v1.0.0-beta.4/**
- **feat(ZMS-123): commit message**
- **fix(ZMSKVR-123): commit message**
- **clean(ZMS-123): commit message**
- **chore(ZMSKVR-123): commit message**
- **docs(ZMS-123): commit message**

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
