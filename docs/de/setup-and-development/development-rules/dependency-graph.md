# Abhängigkeitsgraph

`zmscitizenview` und `refarch-gateway` setzen auf `zmscitizenapi` auf, ziehen aber keine direkten Abhängigkeiten von dort. Ebenso sendet `zmscitizenapi` Anfragen an `zmsapi`, doch `zmsapi` ist keine direkte Abhängigkeit von `zmscitizenapi`.

Der Graph zeigt zusätzlich die zur Laufzeit benötigten Dienste jedes Deployments:

- `eappointment-php-base` – vorgefertigte PHP-Laufzeit-Images für alle PHP-Module (siehe [PHP-Basis-Images](../php-base-images)).
- `Digital Citizen Service (DBS)` – Münchens Open-Source-Identitätsbroker für Bürger:innen für BundID, BayernID und Elster, eingebunden auf der `refarch-gateway`-Ebene (siehe [it-at-m/dbs](https://it-at-m.github.io/dbs/)).

**Lesart der Kanten**

- Durchgezogener Pfeil (`A --> B`): A hat B als Code-Abhängigkeit (Composer).
- Gestrichelter Pfeil (`A -.-> B`): Build-/Integrationsabhängigkeit. A wird auf B aufgebaut und gegen B deployt, zieht es aber nicht als Code-Abhängigkeit.
- Dicker Pfeil (`A ==> B`): Laufzeit-/Infrastruktur-Abhängigkeit. A spricht zur Laufzeit mit B, oder B stellt die Laufzeitumgebung von A bereit.

```mermaid
%%{init: {"flowchart": {"defaultRenderer": "elk"}} }%%
graph TD;
    %% Code-Abhängigkeiten (Composer)
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

    zmscitizenapi --> mellon & zmsslim & zmsclient & zmsentities;

    %% Build-/Integrationsabhängigkeiten (gestrichelt)
    zmscitizenapi -.-> zmsapi;
    refarch-gateway -.-> zmscitizenapi;
    zmscitizenview -.-> refarch-gateway;

    %% Laufzeit-/externe Dienste (dick)
    refarch-gateway ==>|Bürger-Identität| dbs;
    phpbase ==>|PHP-Laufzeit-Image| zms_modules;

    %% Subgraphen
    subgraph refarch [refarch]
        style refarch stroke-dasharray: 5
        refarch-gateway
        zmscitizenview
    end

    subgraph zms_modules [ZMS PHP-Module]
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

    subgraph runtime [Laufzeit / externe Dienste]
        style runtime stroke-dasharray: 2, 4
        dbs["Digital Citizen Service (DBS)<br>BundID · BayernID · Elster Broker"]
    end

    subgraph foundation [Infrastruktur-Fundament]
        style foundation stroke-dasharray: 2, 4
        phpbase["eappointment-php-base<br>PHP-Laufzeit-Images"]
    end

    %% Stilisierung
    classDef citizenapi fill:#e1f5fe,stroke:#01579b,stroke-width:2px;
    classDef gateway fill:#f3e5f5,stroke:#4a148c,stroke-width:2px;
    classDef citizenview fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px;
    classDef runtimeSvc fill:#fff3e0,stroke:#e65100,stroke-width:2px;
    classDef infra fill:#e3f2fd,stroke:#0277bd,stroke-width:3px;

    class zmscitizenapi citizenapi;
    class refarch-gateway gateway;
    class zmscitizenview citizenview;
    class dbs runtimeSvc;
    class phpbase infra;
```

## Frontend- vs. Backend-Module

### Frontend

- `zmscitizenview`: Vue3-Buchungsfrontend für Bürger:innen, basierend auf [RefArch](https://refarch.oss.muenchen.de).
- `refarch-gateway`: Frontend-Gateway-/BFF-Schicht, von `zmscitizenview` genutzt.
- `zmsadmin`: Verwaltungs-UI-Modul (mit Backend-/API-Anbindung).
- `zmsstatistic`: Statistik-/Reporting-UI-Modul (mit Backend-/API-Anbindung).
- `zmscalldisplay`: UI-Modul für die Aufrufanzeige.
- `zmsticketprinter`: UI-/Laufzeit-Modul für den Ticketdrucker.

`zmscitizenview` folgt den RefArch-Referenzarchitekturmustern und nutzt `refarch-gateway` als Gateway-Schicht.
Das bedeutet, Anfragen aus `zmscitizenview` werden zunächst über `refarch-gateway` geleitet, bevor sie `zmscitizenapi` erreichen.
Hinweise zu Gateway-Verhalten sowie Sicherheits-/Routing-Details siehe RefArch-API-Gateway-Dokumentation: [RefArch API Gateway](https://refarch.oss.muenchen.de/gateway.html).

### Backend-APIs und Kerndienste

- `zmscitizenapi`: API-Schicht für Bürgerbuchungs-Flows; bildet Backend-Entitäten auf schlanke Frontend-DTOs ab.
- `zmsapi`: Kern-Backend-API für Vorgangs-, Warteschlangen-, Termin- und Verwaltungs-Flows.
- `zmsdb`: Datenbankzugriffs-/Abfrageschicht für Anbieter/Anliegen/Vorgänge.
- `zmsdldb`: Importer/Transformer für externe DLDB-/SADB-Quellen.
- `zmsclient`: HTTP-/API-Client-Abstraktionen, modulübergreifend genutzt.
- `zmsslim`: Gemeinsame Slim-Framework-Schicht/-Helfer.
- `zmsmessaging`: Backend-Modul für Nachrichten/Benachrichtigungen.
- `mellon`: Gemeinsame Basis-/Bibliotheks-Abhängigkeit, von mehreren Backend-Modulen genutzt.

### Geteilt zwischen frontendnahen und Backend-PHP-Modulen

- `zmsentities`: Gemeinsames Domänen-/Entitätsmodell, das sowohl frontendnahe als auch Backend-PHP-Module nutzen.

### Laufzeitdienste und Infrastruktur

Diese werden nicht als Code-Abhängigkeiten gezogen, sind aber zur Build-/Laufzeit erforderlich.

- `eappointment-php-base`: Vorgefertigte PHP-Laufzeit-Images, auf denen jedes PHP-Modul läuft. Detaillierte Abhängigkeitsansicht: [PHP-Basis-Images](../php-base-images).
- `Digital Citizen Service (DBS)`: Münchens Open-Source-Identitätsbroker für Bürger:innen für BundID, BayernID und Elster, eingebunden auf der `refarch-gateway`-Ebene vor `zmscitizenapi` für den Bürger-Buchungsfluss. Siehe [it-at-m/dbs](https://it-at-m.github.io/dbs/).
