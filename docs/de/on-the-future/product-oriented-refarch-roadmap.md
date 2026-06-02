---
outline: deep
---

# Produktorientierte RefArch-Roadmap: ZMS-Architektur modernisieren (3–5-Jahresplan)

## Einführung

ZMS entwickelt sich von einem projektgetriebenen PHP-Stack zu einer langfristigen, produktorientierten Plattform. Für mehrjährige Wartbarkeit, schnellere Auslieferung und klarere Ownership schlagen wir eine Referenzarchitektur ([RefArch](https://refarch.oss.muenchen.de/)) vor, die Technologieentscheidungen, Laufzeit-Topologie und Integrationsmuster über alle Module hinweg standardisiert.

https://refarch.oss.muenchen.de/

https://github.com/it-at-m/refarch
https://github.com/it-at-m/refarch-templates

Diese Roadmap verbindet Geschäftsergebnisse mit technischer Umsetzung: Admin-Kernfähigkeiten in **`zmsbackend`**, die Bürger-API in **`zmscitizenbackend`** (jeweils eigener Spring-Boot-Dienst mit Repository-Schicht auf dem gemeinsamen MySQL-Schema), API-Gateways für klare Eingangsgrenzen, Modernisierung aller Frontends auf Vue.js sowie Isolation von EAI-Themen (Messaging, externe Datenflüsse) als eigene Dienste. Der Zielzustand reduziert kognitive Last, verbessert Sicherheit und Betrieb und ermöglicht Teams, Arbeit unabhängig zu skalieren ohne fragile Querkopplung.

Wichtige Treiber:

- Produktorientierung und Domain-Ownership
- Weniger Technologie-Schulden und konsistente Developer Experience
- Einheitliche UX über interne und bürgerorientierte Apps hinweg
- Stärkere Sicherheitsposition an Gateway-Grenzen
- Observability by default (Logs, Metriken, Traces, SLOs)
- Vorhersehbare Releases durch automatisierte Tests und CI/CD
- Klare Zuständigkeit (Kern vs. EAI-Integrationen)
- Langfristige Tragfähigkeit für 3–5 Jahre mit inkrementellen Migrationspfaden
- Wiederverwendbarkeit durch andere Städte und Behörden

## Aktueller Abhängigkeitsgraph

`zmscitizenview` und `refarch-gateway` setzen auf `zmscitizenapi` auf, ziehen aber keine direkten Abhängigkeiten von dort. Ebenso sendet `zmscitizenapi` Anfragen an `zmsapi`, doch `zmsapi` ist keine direkte Abhängigkeit von `zmscitizenapi`.

`zmsadmin` und `zmsstatistic` teilen eingebettete Layout-Assets in `zmslayout` (npm-`file:`-Abhängigkeiten). `zmscalldisplay` und `zmsticketprinter` nutzen eigene PHP/Twig-Stacks und hängen heute nicht von `zmslayout` ab. Ein Refactoring der internen PHP-Frontends auf Vue/Vuetify (Zielarchitektur unten) ersetzt `zmslayout` durch RefArch-UI-Muster, statt die Legacy-SCSS/JS-Bibliothek auszubauen.

```mermaid
%%{init: {"flowchart": {"defaultRenderer": "elk"}} }%%
graph TD;
    %% Main ZMS module dependencies
    zmsapi --> zmsslim & zmsclient & zmsdldb & zmsdb & zmsentities;
    zmsadmin --> mellon & zmsclient & zmsslim & zmsentities;
    zmscalldisplay --> mellon & zmsclient & zmsentities & zmsslim;
    zmsstatistic --> mellon & zmsentities & zmsslim & zmsclient;
    zmsmessaging --> mellon & zmsclient & zmsentities & zmsslim;
    zmsticketprinter --> mellon & zmsclient & zmsentities & zmsslim;

    zmsdb --> zmsentities & zmsdldb & mellon;
    zmsclient --> zmsentities & zmsslim & mellon;
    zmsentities --> mellon;
    zmsslim --> mellon;

    %% zmscitizenapi dependencies
    zmscitizenapi --> mellon & zmsslim & zmsclient & zmsentities;

    %% npm-file:-Abhängigkeiten (gestrichelt)
    zmsadmin -.-> zmslayout;
    zmsstatistic -.-> zmslayout;

    %% Build dependencies (dashed lines)
    zmscitizenapi -.-> zmsapi;
    refarch-gateway -.-> zmscitizenapi;
    zmscitizenview -.-> refarch-gateway;

    %% Group refarch-gateway and zmscitizenview into a subgraph
    subgraph refarch [refarch]
        style refarch stroke-dasharray: 5
        refarch-gateway
        zmscitizenview
    end

    %% Group remaining modules into dashed PHP-style subgraph
    subgraph zms_modules [ZMS PHP Modules]
        style zms_modules stroke-dasharray: 5, 5, 1, 5
        zmsapi
        zmsadmin
        zmscalldisplay
        zmsstatistic
        zmsmessaging
        zmsticketprinter
        zmsdb
        zmsclient
        zmsentities
        zmsslim
        zmsdldb
        mellon
        zmscitizenapi
    end

    subgraph shared_frontend [Gemeinsame Layout-Assets]
        style shared_frontend stroke-dasharray: 5, 2, 1, 2
        zmslayout["zmslayout<br/>eingebettetes SCSS/JS"]
    end

    %% Styling for the three modules
    classDef citizenapi fill:#e1f5fe,stroke:#01579b,stroke-width:2px;
    classDef gateway fill:#f3e5f5,stroke:#4a148c,stroke-width:2px;
    classDef citizenview fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px;
    classDef layout fill:#fff8e1,stroke:#f9a825,stroke-width:2px;

    class zmscitizenapi citizenapi;
    class refarch-gateway gateway;
    class zmscitizenview citizenview;
    class zmslayout layout;
```

## Zukünftige Architektur (3–5 Jahre)

Das folgende Diagramm zeigt die geplante Zielarchitektur nach Refactoring gemäß RefArch-Standards:

```mermaid
%%{init: {"flowchart": {"defaultRenderer": "elk"}} }%%
graph TD;
    %% Frontend Applications (Vue.js)
    zmsadmin_vue[zmsadmin<br/>Vue.js Frontend]
    zmsstatistic_vue[zmsstatistic<br/>Vue.js Frontend]
    zmscalldisplay_vue[zmscalldisplay<br/>Vue.js Frontend]
    zmsticketprinter_vue[zmsticketprinter<br/>Vue.js Frontend]
    zmscallcenter_vue[zmscallcenter<br/>Vue.js Frontend]
    zmscitizenview_vue[zmscitizenview<br/>Vue.js Frontend]

    %% API Gateways
    internal_gateway[Internal API Gateway<br/>SSO/Keycloak<br/>for Workers]
    citizen_gateway[External Citizen API Gateway<br/>SSO/Keycloak<br/>for BundID/BayernID]

    %% Backend Services
    zmsbackend[zmsbackend<br/>Spring Boot]
    zmscitizenbackend[zmscitizenbackend<br/>Spring Boot]
    zmsmessaging[zmsmessaging<br/>Spring Boot EAI]
    zmsdldb[zmsdldb<br/>Spring Boot EAI]
    other_eai[Other EAI<br/>Spring Boot EAI]

    %% Shared database
    mysql[(Shared MySQL<br/>Schema)]

    %% External Systems
    stadt_muenchen[Stadt München<br/>Services/Locations]
    external_apis[Other External APIs<br/>and Services<br/>e.g Online Payments, Online Business Transactions]
    messaging_eai[e.g. Email Server,<br/>SMS Server,<br/>even Mobile App Push Notification Server]

    %% Internal Frontend Dependencies
    zmsadmin_vue --> internal_gateway;
    zmsstatistic_vue --> internal_gateway;
    zmscalldisplay_vue --> internal_gateway;
    zmsticketprinter_vue --> internal_gateway;
    zmscallcenter_vue --> internal_gateway;

    %% Citizen Frontend Dependencies
    zmscitizenview_vue --> citizen_gateway;

    %% API Gateway Dependencies
    internal_gateway --> zmsbackend;
    citizen_gateway --> zmscitizenbackend;

    %% Backend Service Dependencies
    zmsbackend --> mysql;
    zmscitizenbackend --> mysql;
    zmsmessaging --> zmsbackend;
    zmsdldb --> stadt_muenchen;
    zmsmessaging --> messaging_eai;
    other_eai --> external_apis;

    %% EAI Integration
    zmsmessaging -.-> zmsdldb;
    zmsdldb -.-> zmsbackend;

    %% Group Frontend Applications
    subgraph frontends [Frontend Apps - Vue]
        style frontends fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
        zmsadmin_vue
        zmsstatistic_vue
        zmscalldisplay_vue
        zmsticketprinter_vue
        zmscallcenter_vue
        zmscitizenview_vue
    end

    %% Group API Gateways
    subgraph gateways [API Gateways - Spring]
        style gateways fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
        internal_gateway
        citizen_gateway
    end

    %% Group Backend Services
    subgraph backend [Backend Services - Spring]
        style backend fill:#e8f5e8,stroke:#388e3c,stroke-width:2px
        zmsbackend
        zmscitizenbackend
        zmsmessaging
        zmsdldb
        other_eai
        mysql
    end

    %% RefArch System Boundary
    subgraph refarch_system [ZMS RefArch]
        style refarch_system fill:#f8f9fa,stroke:#6c757d,stroke-width:3px,stroke-dasharray: 5 5
        frontends
        gateways
        backend
    end

    %% Group External Systems
    subgraph external [External Systems]
        style external fill:#fff3e0,stroke:#f57c00,stroke-width:2px
        stadt_muenchen
        external_apis
        messaging_eai
    end

    %% Position external systems below RefArch
    refarch_system --> external;

    %% Styling
    classDef frontend fill:#e3f2fd,stroke:#1976d2,stroke-width:2px;
    classDef gateway fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px;
    classDef backend fill:#e8f5e8,stroke:#388e3c,stroke-width:2px;
    classDef external fill:#fff3e0,stroke:#f57c00,stroke-width:2px;

    class zmsadmin_vue,zmsstatistic_vue,zmscalldisplay_vue,zmsticketprinter_vue,zmscallcenter_vue,zmscitizenview_vue frontend;
    class internal_gateway,citizen_gateway gateway;
    class zmsbackend,zmscitizenbackend,zmsmessaging,zmsdldb,other_eai backend;
    class stadt_muenchen,external_apis,messaging_eai external;
```

### Wesentliche Architekturänderungen

- **Frontend-Modernisierung**: Alle Frontend-Module werden zu Vue.js-Anwendungen. Refactoring von `zmsadmin`, `zmsstatistic`, `zmsticketprinter` und `zmscalldisplay` auf Vue/Vuetify ersetzt `zmslayout` (heute gemeinsame BO-SCSS/JS-Hülle für `zmsadmin` und `zmsstatistic`) durch RefArch/Vuetify-Komponenten — analog zu `zmscitizenview`.
- **API-Gateway-Muster**: Getrennte Gateways für interne und bürgerorientierte Anwendungen
- **Backend-Refactoring**: Admin-Kern nach Spring Boot (`zmsbackend`); Bürger-API nach **`zmscitizenbackend`** — beide mit eigener Repository-Schicht auf dem **gemeinsamen MySQL-Schema** (kein HTTP-Hop zwischen Bürger- und Admin-Backend)
- **EAI-Dienste**: `zmsmessaging` und `zmsdldb` als eigene Spring-Boot-EAI-Dienste
- **Externe Integration**: `zmsdldb` übernimmt Stadt-München-Leistungen/-Standorte-Mapping
- **Microservices-Architektur**: Klare Zuständigkeit durch dedizierte Dienste

### Wesentliche Architekturänderungen (Detail)

- **Frontend-Modernisierung**: Alle Frontend-Module werden zu Vue.js-Anwendungen; `zmsadmin` und `zmsstatistic` hängen nicht mehr von `zmslayout` ab
- **API-Gateway-Muster**: Getrennte Gateways für interne und bürgerorientierte Anwendungen
- **Backend-Refactoring**: Admin-Kern konsolidiert in Spring Boot: `zmsapi`, `zmsdb`, `zmsclient`, `zmsentities`, `zmsslim`, `mellon` → **`zmsbackend`**
- **Citizen-Backend**: `zmscitizenapi` → **`zmscitizenbackend`**; entfällt `ZmsApiClientService` / `ZmsApiFacadeService`-HTTP zu `zmsapi` zugunsten direkter JPA/SQL-Queries auf dem gemeinsamen Schema
- **zmsmessaging**: Dedizierter EAI-Dienst für Benachrichtigungen
- **zmsdldb**: EAI-Dienst für Stadt-München-Datenintegration mit `zmsdldbmapper`; weitere Mapper für andere Städte möglich
- **Microservices-Architektur**: Klare Zuständigkeit durch dedizierte Dienste

### Architektur-Transformationen

| **Aspekt**              | **Ist**                                                                   | **Soll**                                 | **Nutzen**                                                |
| ----------------------- | ------------------------------------------------------------------------- | ---------------------------------------- | --------------------------------------------------------- |
| **Frontend**            | Gemischte PHP/Twig-Templates; `zmslayout` für `zmsadmin` / `zmsstatistic` | Vue.js-SPA-Anwendungen (Vuetify/RefArch) | Moderne UX; `zmslayout` entfällt mit Frontend-Refactoring |
| **API-Schicht**         | Direkte Service-Aufrufe                                                   | RefArch-API-Gateways                     | Zentralisierte Sicherheit, Monitoring                     |
| **Backend**             | PHP-Monolith                                                              | Spring-Boot-Microservices                | Bessere Skalierbarkeit, Wartbarkeit                       |
| **EAI**                 | Integriertes Messaging                                                    | Dedizierte EAI-Dienste                   | Klare Trennung                                            |
| **Externe Integration** | Direkter DB-Zugriff                                                       | Serviceorientierte Integration           | Bessere Daten-Governance                                  |

### Aufwandsschätzung

| **Komponente**                                                     | **Aufgabe**                                                          | **Schätzung** | **Schwierigkeit** |
| ------------------------------------------------------------------ | -------------------------------------------------------------------- | ------------- | ----------------- |
| `zmsdldbmapper`                                                    | Open-Source-Stellung                                                 | 4 Wochen      | Mittel            |
| `zmsdldbmapper`, `zmsdldb`                                         | Ein Modul nach Spring Boot EAI                                       | 8 Wochen      | Mittel            |
| `zmsmessaging`                                                     | Nach Spring Boot EAI                                                 | 4 Wochen      | Einfach           |
| `zmsdeployment`                                                    | Open-Source-Stellung                                                 | 8 Wochen      | Schwer            |
| `zmscallcenter`                                                    | Neue Vue/Vuetify-UI + API-Gateway mit SSO                            | 8 Wochen      | Einfach           |
| `zmscalldisplay`                                                   | Refactoring zu Vue/Vuetify + API-Gateway                             | 4 Wochen      | Einfach           |
| `zmsticketprinter`                                                 | Refactoring zu Vue/Vuetify + API-Gateway                             | 4 Wochen      | Einfach           |
| `zmsstatistic`                                                     | Refactoring zu Vue/Vuetify + API-Gateway mit SSO                     | 8 Wochen      | Mittel            |
| `zmsadmin`                                                         | Refactoring zu Vue/Vuetify + API-Gateway mit SSO                     | 9–12 Monate   | Sehr schwer       |
| `zmsdb`, `zmsentities`, `zmsapi`, `mellon`, `zmsclient`, `zmsslim` | Admin-Backend-Refactoring → **`zmsbackend`** (Spring Boot / RefArch) | 12–18 Monate  | Sehr schwer       |
| `zmscitizenapi`                                                    | Citizen-Backend-Refactoring → **`zmscitizenbackend`** (Spring Boot)  | 6–12 Monate   | Schwer            |

\*Die Roh-Schätzung für die Entwicklung umfasst kein UI/UX, Planung, Testing usw.

<hr/>

#### Verwandte Issues

- [ZMSKVR-685](https://jira.muenchen.de/browse/ZMSKVR-685) – Testautomatisierung Einrichtung
- [ZMSKVR-686](https://jira.muenchen.de/browse/ZMSKVR-686) – Testautomatisierung Umsetzung
- [ZMSKVR-795](https://jira.muenchen.de/browse/ZMSKVR-795) – CalendarView-Refactoring
- [#1427](https://github.com/it-at-m/eappointment/issues/1427) – Datenbank-Standardisierung
