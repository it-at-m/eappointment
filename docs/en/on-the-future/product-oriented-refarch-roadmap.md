---
outline: deep
---

# Define Product-Oriented RefArch Roadmap: Modernize ZMS Architecture (3-5 Year Plan)

## Introduction

ZMS is evolving from a project-driven PHP stack into a long-lived, product-oriented platform. To support multi-year maintainability, faster delivery, and clearer ownership, we propose a reference architecture ([RefArch](https://refarch.oss.muenchen.de/)) that standardizes technology choices, runtime topology, and integration patterns across all modules.

https://refarch.oss.muenchen.de/

https://github.com/it-at-m/refarch
https://github.com/it-at-m/refarch-templates

This roadmap aligns business outcomes with technical execution: consolidating core capabilities into a single Spring Boot backend service (zmsbackend), introducing API Gateways for clear inbound boundaries, modernizing all frontends to Vue.js, and isolating EAI concerns (messaging, external data flows) as dedicated services. The end state reduces cognitive load, improves security and operability, and enables teams to scale work independently without fragile cross-coupling.

Key drivers:

- Product orientation and domain ownership
- Reduced tech debt and consistent developer experience
- Unified UX across internal and citizen-facing apps
- Stronger security posture at gateway boundaries
- Observability by default (logs, metrics, traces, SLOs)
- Predictable releases via automated testing and CI/CD
- Clear separation of concerns (core vs. EAI integrations)
- Long-term viability for 3–5 years with incremental migration paths
- Reusability by other cities and government entities

## Current Dependency Graph Architecture

`zmscitizenview` and `refarch-gateway` are built on top of `zmscitizenapi`, but they do not directly pull dependencies from it. Similarly, while `zmscitizenapi` sends requests to `zmsapi`, `zmsapi` is not a direct dependency of `zmscitizenapi`.

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

    %% Styling for the three modules
    classDef citizenapi fill:#e1f5fe,stroke:#01579b,stroke-width:2px;
    classDef gateway fill:#f3e5f5,stroke:#4a148c,stroke-width:2px;
    classDef citizenview fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px;

    class zmscitizenapi citizenapi;
    class refarch-gateway gateway;
    class zmscitizenview citizenview;
```

## Future Architecture (3-5 Years)

The following diagram shows the planned future architecture after refactoring to RefArch standards:

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
    zmscitizenapi[zmscitizenapi<br/>Spring Boot]
    zmsmessaging[zmsmessaging<br/>Spring Boot EAI]
    zmsdldb[zmsdldb<br/>Spring Boot EAI]
    other_eai[Other EAI<br/>Spring Boot EAI]

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
    citizen_gateway --> zmscitizenapi;

    %% Backend Service Dependencies
    zmscitizenapi --> zmsbackend;
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
        zmscitizenapi
        zmsmessaging
        zmsdldb
        other_eai
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
    class zmsbackend,zmscitizenapi,zmsmessaging,zmsdldb,other_eai backend;
    class stadt_muenchen,external_apis,messaging_eai external;
```

### Key Architectural Changes:

- **Frontend Modernization**: All frontend modules converted to Vue.js applications
- **API Gateway Pattern**: Separate gateways for internal and citizen-facing applications
- **Backend Refactoring**: Core services migrated to Spring Boot (zmsbackend)
- **EAI Services**: zmsmessaging and zmsdldb as dedicated Spring Boot EAI services
- **External Integration**: zmsdldb handles Stadt München services/locations mapping
- **Microservices Architecture**: Clear separation of concerns with dedicated services

### Key Architectural Changes:

- **Frontend Modernization**: All frontend modules converted to Vue.js applications
- **API Gateway Pattern**: Separate gateways for internal and citizen-facing applications
- **Backend Refactoring**: Core services migrated to Spring Boot consolidates: `zmsapi`, `zmsdb`, `zmsclient`, `zmsentities`, `zmsslim`, `mellon` -> (`zmsbackend`)
- **zmsmessaging**: Dedicated EAI service for notifications
- **zmsdldb**: EAI service for Stadt München data integration with `zmsdldbmapper`. Even possible to add other mappers for other cities.
- **Microservices Architecture**: Clear separation of concerns with dedicated services

### Key Architectural Transformations

| **Aspect**               | **Current State**        | **Target State**             | **Benefits**                        |
| ------------------------ | ------------------------ | ---------------------------- | ----------------------------------- |
| **Frontend**             | Mixed PHP/Twig templates | Vue.js SPA applications      | Modern UX, better maintainability   |
| **API Layer**            | Direct service calls     | RefArch API Gateways         | Centralized security, monitoring    |
| **Backend**              | PHP monolith             | Spring Boot microservices    | Better scalability, maintainability |
| **EAI**                  | Integrated messaging     | Dedicated EAI services       | Clear separation of concerns        |
| **External Integration** | Direct database access   | Service-oriented integration | Better data governance              |

### Implementation Effort Estimation

| **Component**                                                                       | **Task**                                      | **Estimation** | **Difficulty** |
| ----------------------------------------------------------------------------------- | --------------------------------------------- | -------------- | -------------- |
| `zmsdldbmapper`                                                                     | Open Source Stellung                          | 4 Weeks        | Medium         |
| `zmsdldbmapper`, `zmsdldb`                                                          | One Module to Spring Boot EAI                 | 8 Weeks        | Medium         |
| `zmsmessaging`                                                                      | To Spring Boot EAI                            | 4 Weeks        | Easy           |
| `zmsdeployment`                                                                     | Open Source Stellung                          | 8 Wochen       | Hard           |
| `zmscallcenter`                                                                     | Neue Vue/Vuetify UI + API Gateway mit SSO     | 8 Weeks        | Easy           |
| `zmscalldisplay`                                                                    | Refactor zu Vue/Vuetify + API Gateway         | 4 Weeks        | Easy           |
| `zmsticketprinter`                                                                  | Refactor zu Vue/Vuetify + API Gateway         | 4 Weeks        | Easy           |
| `zmsstatistic`                                                                      | Refactor zu Vue/Vuetify + API Gateway mit SSO | 8 Weeks        | Medium         |
| `zmsadmin`                                                                          | Refactor zu Vue/Vuetify + API Gateway mit SSO | 9–12 Months    | Very Hard      |
| `zmsdb`, `zmsentities`, `zmsapi`, `zmscitizenapi`, `mellon`, `zmsclient`, `zmsslim` | Backend-Refactor zu Spring Boot (RefArch)     | 18-24 Months   | Very Hard      |

\*The raw estimation for the development does not include UI/UX, Planning, Testing etc.

<hr/>

#### Related Issues

- [ZMSKVR-685](https://jira.muenchen.de/browse/ZMSKVR-685) - Test automation setup
- [ZMSKVR-686](https://jira.muenchen.de/browse/ZMSKVR-686) - Test automation implementation
- [ZMSKVR-795](https://jira.muenchen.de/browse/ZMSKVR-795) - CalendarView refactoring
- [#1427](https://github.com/it-at-m/eappointment/issues/1427) - Database standardization
