# eAppointment-Dokumentation

Dieses Handbuch ist die Hauptanlaufstelle auf [GitHub Pages](https://it-at-m.github.io/eappointment/). Es ist mit dem Repository (`main`) versioniert.

- **GitHub-Repository** (Handbuch): [https://github.com/it-at-m/eappointment/](https://github.com/it-at-m/eappointment/)

**Coverage- und API-HTML** aus der CI werden auf demselben Host veröffentlicht; siehe [Unit-Tests in ZMS](./testing-and-automation/testing-unit.md), [Unit-Test-Abdeckung](./testing-and-automation/testing-coverage.md) und [API-Referenz](./operations/api-reference.md).

## Schnellzugriffe

- [Projektgeschichte](./overview/project-history.md)
- [DDEV und Devcontainer](./setup-and-development/getting-started/ddev-and-devcontainer.md)
- [Lokale Datenbank- und Cache-Operationen](./setup-and-development/local-database-and-cache-operations.md)
- [Abhängigkeits-Aktualisierungsprüfung](./setup-and-development/dependency-upgrade-check.md)
- [PHP-Basis-Images](./setup-and-development/php-base-images.md)
- [Unit-Tests in ZMS](./testing-and-automation/testing-unit.md)
- [Unit-Test-Abdeckung](./testing-and-automation/testing-coverage.md)
- [API-Referenz](./operations/api-reference.md) — ReDoc und Diagramme
- [Modul-READMEs](./reference/module-readmes.md)
- [DLDB-Schnittstellendokumentation](./operations/dldb-interface-documentation.md)
- [Monolog-Logging](./operations/monolog-logging.md) — `App::$log`, `DEBUGLEVEL`
- [Monitoring und Status](./operations/monitoring-and-status.md) — Grafana, `GET /status/`

## Repository-Umfang

<img width="200" align="right" alt="Project logo" src="../img/logo.png" />

Dieses Monorepo enthält Münchner Anpassungen der ursprünglichen Berliner eAppointment-Software:
[https://gitlab.com/eappointment/eappointment](https://gitlab.com/eappointment/eappointment)

Public E-Appointment ist eine Software für die Online-Buchung von Terminen und die Verarbeitung von Warteschlangen, etwa das Aufrufen von Terminnummern und das Erfassen von Statistiken zu erbrachten Leistungen.

Die Software wird seit über 20 Jahren in der öffentlichen Verwaltung der deutschen Hauptstadt Berlin eingesetzt und wird seit 2016 unter einer neuen Lizenz neu entwickelt. Damit kann die Software unter der EUPL, einer von OSI anerkannten Open-Source-Lizenz, neu veröffentlicht werden.

Eine Veröffentlichung der Software als Open Source ist im Laufe der Jahre 2022/2024 vorgesehen. Dafür sind eine Reihe von Anpassungen erforderlich, sodass die einzelnen Komponenten der Software hier schrittweise veröffentlicht werden. Zum einen wird die Dokumentation der Software in diesem Repository veröffentlicht, zum anderen werden hier neue Ideen und Weiterentwicklungen geplant, die übergreifend für die anderen Repositories gelten.

Das ZMS-System dient der Verwaltung menschlicher Warteschlangen. Es bietet folgende Funktionen:

- Termine über einen Kalender vereinbaren und einen Vorgang zur Verwaltung eines Termins anstoßen
- Anliegen (Dienstleistungen) und Anbieter (Standorte) aus externen Quellen importieren
- Bereiche (Scopes) für Termine verwalten, einschließlich einer vierstufigen Hierarchie aus Mandant → Organisation → Abteilung → Bereich
- Öffnungszeiten inklusive geschlossener Tage verwalten
- Anmeldesystem für Sachbearbeitende mit unterschiedlichen Berechtigungsstufen
- Ticketdrucker-Unterstützung für Kund:innen ohne Termin (authentifiziert, sperrbar, zeitlich steuerbar)
- Aufrufanzeige (calldisplay) wird unterstützt
- Erfassen von Statistiken wie Wartezeit oder bediente Kund:innen pro Tag
- Notruf für Mitarbeitende
- Bürger-Buchungssystem inklusive Bürgeranmeldung

## Kontakt

[Übersicht](https://opensource.muenchen.de/software/zeitmanagementsystem.html)

Münchner Kontakt: it@M – opensource@muenchen.de

BerlinOnline Stadtportal GmbH & Co KG und it@M.

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="https://service.berlin.de/i9f/r1/images/logo_berlin_m_srgb.svg" align="center" style="height: 30px; width: auto; object-fit: contain;"></td>
    <td style="padding-right: 30px;"><img src="https://gitlab.com/eappointment/zmsstatistic/-/raw/main/public/_css/images/bo_logo.svg?ref_type=heads" align="center" style="height: 30px; width: auto; object-fit: contain;"></td>
    <td><img src="https://assets.muenchen.de/logos/lhm/logo-lhm-muenchen.svg" align="center" style="height: 30px; width: auto; object-fit: contain;"></td>
    <td><img src="https://assets.muenchen.de/logos/itm/itM_Basislogo_gelb_schwarz-500.png" align="center" style="height: 30px; width: auto; object-fit: contain;"></td>
  </tr>
</table>

## Screenshot

<img alt="Screenshot von zmsadmin, zmsstatistic, zmscalldisplay und zmsticketprinter" src="../img/screenshot_1.png" />

<img alt="Screenshot von zmscitizenview" src="../img/screenshot_2.jpg" />
