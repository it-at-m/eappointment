# Projektgeschichte

## Kontext

Dieses Repository dient dazu, Münchner Anpassungen für eAppointment zu pflegen und zu veröffentlichen.
Die Entwicklung und Dokumentation erfolgt offen, um die Erbringung kommunaler Dienstleistungen zu unterstützen.

## Wesentliche Projektstränge

### ZMS

Das ursprüngliche Projekt zur Ablösung kommerziell-proprietärer Software durch die Open-Source-Berliner-Lösung ZMS, das mit jeder städtischen Behörde/Abteilung in Betrieb genommen wurde. Dieses Grundlagenprojekt hat die Kerninfrastruktur des Terminverwaltungssystems für Münchens kommunale Dienstleistungen geschaffen.

### MPDZBS

Die Entwicklung der PHP-Anwendung zmscitizenapi sowie die Ablösung des ersten Open-Source-Vue2-Frontends ([eappointment-buergeransicht](https://github.com/it-at-m/eappointment-buergeransicht)) durch das Vue3-Frontend zmscitizenview/[refarch](https://refarch.oss.muenchen.de/) für Bürger:innen, dazu der Aufbau des Vue-Patternlabs der Stadt ([muc-patternlab-vue](https://github.com/it-at-m/muc-patternlab-vue)). Dieses Projekt hat die bürgerseitigen Komponenten modernisiert und Standards für das Designsystem etabliert.

### ZMSKVR

Ergänzt weiter benötigte Funktionen und Anforderungen der städtischen Behörden/Abteilungen und behebt Schwächen in der Nutzererfahrung. Dazu zählt die Umsetzung von Funktionen in zmscitizenview, die im Rahmen von MPDZBS nicht abgeschlossen wurden, um eine umfassende Funktionalität für alle kommunalen Dienststellen sicherzustellen.

### MUXDBS

Baut auf MPDZBS gemäß dem Reifegradmodell auf, das als Umsetzungsrahmen für die Konformität mit dem Onlinezugangsgesetz (OZG) dient ([digitale-verwaltung.de](https://www.digitale-verwaltung.de/Webs/DV/DE/onlinezugangsgesetz/ozg-grundlagen/info-reifegradmodell/info-reifegradmodell-node.html)), und ergänzt zmscitizenview um zusätzliche Komponenten, die etwa die Anmeldung mit BundID, BayernID und Elster für reibungslose Online-Bürgertermine ermöglichen. Die Bürger-Authentifizierung übernimmt der Münchner Open-Source [Digitale Bürgerservice (DBS)](https://it-at-m.github.io/dbs/), der die Identitäts-Flows über BundID, BayernID und Elster vermittelt. Dieses Projekt steht für die nächste Reifestufe digitaler Verwaltungsdienstleistungen entlang der föderalen Umsetzungsleitlinien.

## Kontakte

- Münchner Kontakt: `opensource@muenchen.de`
- Software-Übersicht: [https://opensource.muenchen.de/software/zeitmanagementsystem.html](https://opensource.muenchen.de/software/zeitmanagementsystem.html)
