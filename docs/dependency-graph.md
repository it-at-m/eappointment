# Dependency Graph

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

## Frontend vs Backend Modules

### Frontend

- `zmscitizenview`: Vue3 citizen-facing booking frontend built on [RefArch](https://refarch.oss.muenchen.de).
- `refarch-gateway`: frontend gateway/BFF layer used by `zmscitizenview`.
- `zmsadmin`: administration UI module (with backend/API integration).
- `zmsstatistic`: statistics/reporting UI module (with backend/API integration).
- `zmscalldisplay`: call display UI module.
- `zmsticketprinter`: ticket printer UI/runtime module.

`zmscitizenview` follows the RefArch reference architecture patterns and uses `refarch-gateway` as its gateway layer.
This means requests from `zmscitizenview` are routed through `refarch-gateway` before they reach `zmscitizenapi`.
For gateway behavior and security/routing details, see the RefArch API Gateway docs: [RefArch API Gateway](https://refarch.oss.muenchen.de/gateway.html).

### Backend APIs and Core Services

- `zmscitizenapi`: API layer for citizen booking flows, mapping backend entities into thinned frontend DTOs.
- `zmsapi`: core backend API for process, queue, appointment, and administration flows.
- `zmsdb`: database access/query layer for providers/requests/processes.
- `zmsdldb`: importer/transformer for external DLDB/SADB sources.
- `zmsclient`: HTTP/API client abstractions used between modules.
- `zmsslim`: shared Slim framework layer/helpers.
- `zmsmessaging`: messaging/notification backend module.
- `mellon`: shared base/library dependency used by multiple backend modules.

### Shared Across Frontend-Facing and Backend PHP Modules

- `zmsentities`: shared domain/entity model used by both frontend-facing PHP modules and backend PHP modules.
