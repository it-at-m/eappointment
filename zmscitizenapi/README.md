# ZMSCITIZENAPI

A REST-like interface that provides appointment booking and management capabilities for citizens. Part of the eAppointment system.

## Walkthrough
This pull request introduces a comprehensive new module called `zmscitizenapi` to the project, which provides a streamlined REST-like interface for citizen interactions with a queuing management system. The changes span multiple configuration files, middleware, controllers, services, models, and test cases, establishing a robust and secure API for appointment-related functionalities.

The ZMS Citizen API offers endpoints for:

* Discovering available services and offices
* Checking appointment availability
* Booking, confirming, and canceling appointments
* Managing appointment details

```mermaid
sequenceDiagram
    participant Client
    participant ZMSCitizenAPI
    participant ZMSApi
    
    Client->>ZMSCitizenAPI: Request available services
    ZMSCitizenAPI->>ZMSApi: Fetch services
    ZMSApi-->>ZMSCitizenAPI: Return services
    ZMSCitizenAPI-->>Client: Respond with services list

    Client->>ZMSCitizenAPI: Request available appointments
    ZMSCitizenAPI->>ZMSApi: Check appointment availability
    ZMSApi-->>ZMSCitizenAPI: Return available slots
    ZMSCitizenAPI-->>Client: Respond with available appointments
```

## Changes

| File | Change Summary |
|------|----------------|
| `.ddev/config.yaml` | Updated host HTTPS and web server ports from `59002`/`59001` to `8091`/`8090` |
| `.github/workflows/build-images.yaml` | Added `zmscitizenapi` module with PHP 8.0 |
| `.github/workflows/unit-tests.yaml` | Added `zmscitizenapi` module to matrix configuration |
| `.htaccess` | Added routing rules for `zmscitizenapi` module |
| `cli` | Added `zmscitizenapi` to modules list |


## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| **Core Configuration** |
| ZMS_API_URL | Backend API URL | https://localhost/terminvereinbarung/api/2 |
| **Cache & Maintenance** |
| CACHE_DIR | Cache storage directory | /cache |
| SOURCE_CACHE_TTL | Cache lifetime in seconds | 3600 |
| MAINTENANCE_MODE_ENABLED | Enable maintenance mode | false |
| **Logger Configuration** |
| LOGGER_MAX_REQUESTS | Maximum log requests per minute | 1000 |
| LOGGER_RESPONSE_LENGTH | Maximum response length to log in bytes | 1048576 (1MB) |
| LOGGER_STACK_LINES | Maximum stack trace lines to log | 20 |
| LOGGER_MESSAGE_SIZE | Maximum log message size in bytes | 8192 (8KB) |
| LOGGER_CACHE_TTL | Logger cache TTL in seconds | 60 |
| LOGGER_MAX_RETRIES | Maximum retry attempts | 3 |
| LOGGER_BACKOFF_MIN | Minimum backoff time in milliseconds | 100 |
| LOGGER_BACKOFF_MAX | Maximum backoff time in milliseconds | 1000 |
| LOGGER_LOCK_TIMEOUT | Lock timeout in seconds | 5 |
| **Captcha Configuration** |
| CAPTCHA_ENABLED | Enable captcha globally | false |
| CAPTCHA_TOKEN_SECRET | Secret key for signing and validating captcha token | "" |
| CAPTCHA_TOKEN_TTL | Captcha token TTL in seconds | 300 |
| ALTCHA_CAPTCHA_SITE_KEY | Altcha site key | "" |
| ALTCHA_CAPTCHA_SITE_SECRET | Altcha site secret | "" |
| ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE | Altcha challenge endpoint | https://captcha.muenchen.de/api/v1/captcha/challenge |
| ALTCHA_CAPTCHA_ENDPOINT_VERIFY | Altcha verification endpoint | https://captcha.muenchen.de/api/v1/captcha/verify |
| **Rate Limiting** |
| RATE_LIMIT_MAX_REQUESTS | Maximum requests per window | 60 |
| RATE_LIMIT_CACHE_TTL | Rate limit cache TTL in seconds | 60 |
| RATE_LIMIT_MAX_RETRIES | Maximum retry attempts | 3 |
| RATE_LIMIT_BACKOFF_MIN | Minimum backoff time in milliseconds | 10 |
| RATE_LIMIT_BACKOFF_MAX | Maximum backoff time in milliseconds | 50 |
| RATE_LIMIT_LOCK_TIMEOUT | Lock timeout in seconds | 1 |
| **Request Limits** |
| MAX_REQUEST_SIZE | Maximum request size in bytes | 10485760 (10MB) |
| MAX_STRING_LENGTH | Maximum string length in bytes | 32768 (32KB) |
| MAX_RECURSION_DEPTH | Maximum recursion depth | 10 |
| **Security Configuration** |
| IP_BLACKLIST | Blacklisted IPs/CIDR ranges (comma-separated) | "" |


## Appointment State Machine:

```mermaid
stateDiagram-v2
    [*] --> Reserved: reserve-appointment
    Reserved --> Preconfirmed: preconfirm-appointment
    Preconfirmed --> Confirmed: confirm-appointment
    Reserved --> Cancelled: cancel-appointment
    Preconfirmed --> Cancelled: cancel-appointment
    Confirmed --> Cancelled: cancel-appointment
    Reserved --> [*]: timeout
    Preconfirmed --> [*]: timeout
    Cancelled --> [*]
```

## Core Logic

### Domain Models
```mermaid
classDiagram

%% --------------------------------------------------
%% Entities
%% --------------------------------------------------
class ThinnedScope {
  <<Entity>>
  - int id
  - ThinnedProvider~0..1~ provider
  - string~0..1~ shortName
  - bool~0..1~ telephoneActivated
  - bool~0..1~ telephoneRequired
  - bool~0..1~ customTextfieldActivated
  - bool~0..1~ customTextfieldRequired
  - string~0..1~ customTextfieldLabel
  - bool~0..1~ captchaActivatedRequired
  - string~0..1~ displayInfo
  + toArray(): array
}

class ThinnedProvider {
  <<Entity>>
  - int~0..1~ id
  - string~0..1~ name
  - float~0..1~ lat
  - float~0..1~ lon
  - string~0..1~ source
  - ThinnedContact~0..1~ contact
  + toArray(): array
}

class ThinnedContact {
  <<Entity>>
  - string~0..1~ city
  - string~0..1~ country
  - string~0..1~ name
  - string~0..1~ postalCode
  - string~0..1~ region
  - string~0..1~ street
  - string~0..1~ streetNumber
  + toArray(): array
}

class Office {
  <<Entity>>
  - int id
  - string name
  - array~0..1~ address
  - array~0..1~ geo
  - ThinnedScope~0..1~ scope
  + toArray(): array
}

class Service {
  <<Entity>>
  - int id
  - string name
  - int~0..1~ maxQuantity
  - Combinable~0..1~ combinable
  + toArray(): array
}

class Combinable {
  <<Entity>>
  - array combinations
  + getCombinations(): array
}

class OfficeServiceRelation {
  <<Entity>>
  - int officeId
  - int serviceId
  - int slots
  + toArray(): array
}

class ThinnedProcess {
  <<Entity>>
  - int processId
  - int~0..1~ timestamp
  - string~0..1~ authKey
  - string~0..1~ familyName
  - string~0..1~ customTextfield
  - string~0..1~ email
  - string~0..1~ telephone
  - string~0..1~ officeName
  - int~0..1~ officeId
  - ThinnedScope~0..1~ scope
  - array subRequestCounts
  - int~0..1~ serviceId
  - int~0..1~ serviceCount
  - string~0..1~ status
  + toArray(): array
}

class AvailableAppointments {
  <<Entity>>
  - array appointmentTimestamps
  + toArray(): array
}

class AvailableDays {
  <<Entity>>
  - array availableDays
  + toArray(): array
}

class ProcessFreeSlots {
  <<Entity>>
  - array~0..1~ appointmentTimestamps
  + toArray(): array
}

%% --------------------------------------------------
%% Collections
%% --------------------------------------------------
class OfficeList {
  <<Entity>>
  - Office[] offices
  + toArray(): array
}

class ServiceList {
  <<Entity>>
  - Service[] services
  + toArray(): array
}

class ThinnedScopeList {
  <<Entity>>
  - ThinnedScope[] scopes
  + toArray(): array
}

class OfficeServiceRelationList {
  <<Entity>>
  - OfficeServiceRelation[] relations
  + toArray(): array
}

class OfficeServiceAndRelationList {
  <<Entity>>
  - OfficeList offices
  - ServiceList services
  - OfficeServiceRelationList relations
  + toArray(): array
}

%% --------------------------------------------------
%% Relationships
%% --------------------------------------------------

ThinnedScope --> "0..1" ThinnedProvider : provider
ThinnedProvider --> "0..1" ThinnedContact : contact
Office --> "0..1" ThinnedScope : scope
Service --> "0..1" Combinable : combinable

OfficeServiceRelationList --> "*" OfficeServiceRelation : relations
OfficeServiceRelation --> "1" Office
OfficeServiceRelation --> "1" Service

OfficeServiceAndRelationList --> "1" OfficeList : offices
OfficeServiceAndRelationList --> "1" ServiceList : services
OfficeServiceAndRelationList --> "1" OfficeServiceRelationList : relations

OfficeList --> "*" Office : offices
ServiceList --> "*" Service : services
ThinnedScopeList --> "*" ThinnedScope : scopes

ThinnedProcess --> "0..1" ThinnedScope : scope
```

### Controller-Service-Model Architecture**
```mermaid
graph TB
    subgraph "Controller Layer"
        C[Controllers]
        C1[AppointmentController]
        C2[OfficeController]
        C3[ServiceController]
        C4[ScopeController]
        C --> C1 & C2 & C3 & C4
    end
    
    subgraph "Service Layer"
        S[Services]
        S1[AppointmentService]
        S2[OfficeService]
        S3[ServiceService]
        S4[ScopeService]
        S5[ValidationService]
        S6[MapperService]
        S --> S1 & S2 & S3 & S4 & S5 & S6
    end
    
    subgraph "Model Layer"
        M[Models]
        M1[Entities]
        M2[Collections]
        M --> M1 & M2
    end
    
    C1 --> S1
    C2 --> S2
    C3 --> S3
    C4 --> S4
    S1 & S2 & S3 & S4 --> S5
    S1 & S2 & S3 & S4 --> S6
    S1 & S2 & S3 & S4 --> M
```

```mermaid
graph TB
    subgraph "ZMS Citizen API"
        Controllers[Controllers]
        ZAF[ZmsApiFacadeService]
        ZAC[ZmsApiClientService]
        Cache[(PSR-16 Cache)]
        
        subgraph "Core Services"
            Map[MapperService]
            Val[ValidationService]
            Err[ExceptionService]
            Log[LoggerService]
        end
        
        Controllers --> ZAF
        ZAF --> ZAC
        ZAF --> Map
        ZAF --> Val
        ZAF --> Err
        ZAC --> Cache
        ZAC --> Log
        ZAC --> Err
    end
    
    subgraph "ZMS API"
        API[API Endpoints]
        
        subgraph "Resources"
            Cal[/calendar/]
            Pro[/process/]
            Src[/source/]
            Mail[/mail/]
        end
        
        API --> Cal
        API --> Pro
        API --> Src
        API --> Mail
    end
    
    ZAC -.->|HTTP| API
    
    classDef service fill:#f9f,stroke:#333,stroke-width:2px
    classDef core fill:#bbf,stroke:#333,stroke-width:2px
    classDef api fill:#bfb,stroke:#333,stroke-width:2px
    classDef storage fill:#fbb,stroke:#333,stroke-width:2px
    
    class ZAF,ZAC service
    class Map,Val,Err,Log core
    class API,Cal,Pro,Src,Mail api
    class Cache storage
    
    %% Data Flow Annotations
    linkStyle 0 stroke:#333,stroke-width:2px
    linkStyle 1,2,3,4 stroke:#666,stroke-width:1px
    linkStyle 5,6,7 stroke:#666,stroke-width:1px
    linkStyle 8 stroke:#0f0,stroke-width:3px,stroke-dasharray: 5 5
    
    %% Notes
    note1[ZmsApiFacadeService provides high-level interface]
    note2[ZmsApiClientService handles HTTP communication]
    note3[PSR-16 Cache for source data]
    note4[Core services for mapping, validation & errors]
    
    note1 --- ZAF
    note2 --- ZAC
    note3 --- Cache
    note4 --- Map
```

Key aspects of the architecture:

1. **Layered Design**:
   - Controllers interact only with ZmsApiFacadeService
   - ZmsApiFacadeService orchestrates operations using core services
   - ZmsApiClientService handles raw API communication

2. **Core Services**:
   - MapperService: Transforms between API and domain models
   - ValidationService: Validates requests and responses
   - ExceptionService: Centralizes error handling
   - LoggerService: Manages logging

3. **Caching Strategy**:
   - Source data cached for 1 hour
   - Cache implemented using PSR-16 interface
   - Cache key pattern: `source_{source_name}`

4. **API Communication**:
   - Configurable API URL via environment variable
   - HTTP client abstraction through App::$http
   - Support for GET, POST, DELETE methods

5. **Error Handling**:
   - Consistent error mapping across layers
   - Domain-specific exceptions
   - Standardized error responses

## Security

### Middleware Security Models

```mermaid
classDiagram
    class RequestHandlerInterface {
        <<interface>>
        +handle(request: ServerRequestInterface): ResponseInterface
    }

    class MiddlewareInterface {
        <<interface>>
        +process(request: ServerRequestInterface, handler: RequestHandlerInterface): ResponseInterface
    }
    
    class LoggerService {
        +logError(exception: Throwable, request: ServerRequestInterface)
        +logInfo(message: string, context: array)
    }

    class MaintenanceMiddleware {
        -HTTP_UNAVAILABLE: int = 503
        -ERROR_UNAVAILABLE: string = 'serviceUnavailable'
        +__invoke(request: ServerRequestInterface, next: RequestHandlerInterface): ResponseInterface|array
    }

    class RequestLoggingMiddleware {
        -logger: LoggerService
        +process()
    }

    class SecurityHeadersMiddleware {
        -securityHeaders: array
        -logger: LoggerService
        +process()
    }

    class RateLimitingMiddleware {
        -MAX_REQUESTS: int
        -TIME_WINDOW: int
        -cache: CacheInterface
        -logger: LoggerService
        +process()
        -checkAndIncrementLimit(key: string): bool
    }

    class RequestSanitizerMiddleware {
        -MAX_RECURSION_DEPTH: int
        -logger: LoggerService
        +process()
        -sanitizeRequest(request: ServerRequestInterface): ServerRequestInterface
    }

    class RequestSizeLimitMiddleware {
        -MAX_SIZE: int
        -logger: LoggerService
        +process()
    }

    class IpFilterMiddleware {
        -logger: LoggerService
        +process()
        -isIpInRange(ip: string, range: string): bool
    }

    class ErrorMessages {
        <<static>>
        +get(key: string): array
    }

    RequestHandlerInterface <|.. MaintenanceMiddleware
    MiddlewareInterface <|.. RequestLoggingMiddleware
    MiddlewareInterface <|.. SecurityHeadersMiddleware
    MiddlewareInterface <|.. RateLimitingMiddleware
    MiddlewareInterface <|.. RequestSanitizerMiddleware
    MiddlewareInterface <|.. RequestSizeLimitMiddleware
    MiddlewareInterface <|.. IpFilterMiddleware

    RequestLoggingMiddleware --> LoggerService
    SecurityHeadersMiddleware --> LoggerService
    RateLimitingMiddleware --> LoggerService
    RequestSanitizerMiddleware --> LoggerService
    RequestSizeLimitMiddleware --> LoggerService
    IpFilterMiddleware --> LoggerService

    RateLimitingMiddleware --> "1" CacheInterface

    MaintenanceMiddleware ..> ErrorMessages
    RequestLoggingMiddleware ..> ErrorMessages
    SecurityHeadersMiddleware ..> ErrorMessages
    RateLimitingMiddleware ..> ErrorMessages
    RequestSizeLimitMiddleware ..> ErrorMessages
    IpFilterMiddleware ..> ErrorMessages

    note for RequestHandlerInterface "PSR-15 Handler Interface"
    note for MiddlewareInterface "PSR-15 Middleware Interface"
    note for LoggerService "Centralized Logging"
    note for ErrorMessages "Error Message Registry"
    note for MaintenanceMiddleware "Uses __invoke pattern\nReturns array for maintenance mode\nor delegates to next handler"
```

### Security and Rate Limiting Flow**
```mermaid
sequenceDiagram
    participant C as Client
    participant M as Middleware Stack
    participant A as API
    
    C->>M: HTTP Request
    
    rect rgb(200, 200, 200)
        note right of M: Security Middleware
        M->>M: 1. IP Filter Check
        M->>M: 2. Request Size Check
        M->>M: 3. Rate Limit Check
        M->>M: 4. Request Sanitization
    end
    
    alt Security Check Failed
        M-->>C: Error Response
    else Security Check Passed
        M->>A: Forward Request
        A-->>C: Response
    end
```

### IP Rate Limiting

```mermaid
sequenceDiagram
    participant C as Client
    participant API as ZMSCitizenAPI
    
    C->>API: Request
    API->>API: Check Rate Limit
    alt Under Limit
        API-->>C: 200 OK + Headers
    else Over Limit
        API-->>C: 429 Too Many Requests
        Note over C,API: X-RateLimit-Limit: 60<br>X-RateLimit-Remaining: 0<br>X-RateLimit-Reset: timestamp
    end
```

Headers returned:
- `X-RateLimit-Limit`: Requests allowed per minute
- `X-RateLimit-Remaining`: Requests remaining in window
- `X-RateLimit-Reset`: Timestamp when limit resets

Response when rateLimitExceeded:
```json
{
  "errors": [
    {
      "errorCode": "rateLimitExceeded",
      "statusCode": 429,
      "errorMessage": "Rate limit exceeded. Please try again later."
    }
  ]
}
```

## Caching
The PSR-16 Simple Cache is the core caching interface in zmscitizenapi. What I labeled as "File System Cache" is actually just the storage backend for PSR-16, implemented using Symfony's FilesystemAdapter.

Here's how it works:

1. **PSR-16 Setup**:
   - Application initializes one PSR-16 cache instance using Symfony's FilesystemAdapter
   - Cache directory and lifetime (default 3600s) configurable via env vars
   - All services use this single cache instance through `App::$cache`

2. **How the three caches use PSR-16**:
   - **Rate Limiting Cache**:
     - Key pattern: `rate_limit_{md5(ip)}`
     - TTL: 60 seconds
     - Stores request counts per IP
     - The rate limit cache tracks request counts per IP address using a distributed locking mechanism to prevent race conditions. Each IP is allowed 60 requests per minute, with the counter auto-resetting after the TTL expires.
   
   - **Logger Cache**:
     - Key pattern: Uses counter key
     - TTL: 60 seconds
     - Tracks log rate limiting
     - The rate limit cache tracks request counts per IP address using a distributed locking mechanism to prevent race conditions. Each IP is allowed 60 requests per minute, with the counter auto-resetting after the TTL expires.
   
   - **DLDB Source Cache**:
     - Key pattern: `source_{source_name}`
     - TTL: 3600 seconds (1 hour)
     - Caches API responses
     - The DLDB source cache stores API responses for source data with a 1-hour TTL. 

Each "cache" is really just a different usage pattern of the same PSR-16 interface, with its own key namespace and TTL, but all data is stored in the same filesystem backend.


```mermaid
graph TB
    subgraph "Application Initialization"
        Init[Application::initialize]
        CacheInit[initializeCache]
        ValidateDir[validateCacheDirectory]
        SetupCache[setupCache]
        Init --> CacheInit
        CacheInit --> ValidateDir
        ValidateDir --> SetupCache
    end

    classDef init fill:#f9f,stroke:#333,stroke-width:2px
    class Init,CacheInit,ValidateDir,SetupCache init
```

```mermaid
graph TB
    subgraph "Cache Interface"
        AppCache[App::$cache<br>PSR-16 Simple Cache]
        FSAdapter[Symfony FilesystemAdapter]
        AppCache --> FSAdapter
    end

    subgraph "Cache Consumers"
        subgraph "Rate Limiting"
            RateLimitMW[RateLimitingMiddleware]
            RateKey["Key: rate_limit_{md5(ip)}<br>TTL: 60s"]
            RateLimitMW --> RateKey
        end
        
        subgraph "Logger"
            LogService[LoggerService]
            LogKey["Key: counter<br>TTL: 60s"]
            LogService --> LogKey
        end
        
        subgraph "DLDB Source"
            ApiClient[ZmsApiClientService]
            DLDBKey["Key: source_{source_name}<br>TTL: 3600s"]
            ApiClient --> DLDBKey
        end
    end

    subgraph "Storage Layer"
        CacheDir["Cache Directory<br>/cache/"]
        Files["Cache Files<br>.php"]
        CacheDir --> Files
    end

    RateKey --> AppCache
    LogKey --> AppCache
    DLDBKey --> AppCache
    FSAdapter --> CacheDir

    classDef interface fill:#bbf,stroke:#333,stroke-width:2px
    classDef consumer fill:#bfb,stroke:#333,stroke-width:2px
    classDef storage fill:#fbb,stroke:#333,stroke-width:2px

    class AppCache,FSAdapter interface
    class RateLimitMW,LogService,ApiClient,RateKey,LogKey,DLDBKey consumer
    class CacheDir,Files storage
```

## Logger Service
```mermaid
flowchart TD
    subgraph Log Types
        A[LoggerService] --> B[Error Logs]
        A --> C[Warning Logs]
        A --> D[Info Logs]
        A --> E[Request Logs]
    end

    subgraph Error Logs
        B --> B1[Exception Details]
        B1 --> B2[Stack Trace]
        B2 --> B3[File & Line]
        B3 --> B4[Request Context]
    end

    subgraph Warning & Info Logs
        C & D --> CD1[Message]
        CD1 --> CD2[Context Array]
        CD2 --> CD3[Timestamp]
    end

    subgraph Request Logs
        E --> E1[HTTP Method]
        E1 --> E2[Path & Query]
        E2 --> E3[Status Code]
        E3 --> E4[Headers]
        E4 --> E5[Response Body]
        E5 --> E6[Client IP]
    end

    subgraph Processing
        F[Rate Limiting]
        G[Size Check]
        H[Sensitive Data Filter]
        I[JSON Encoding]
        J[Write to Syslog]
    end

    B4 & CD3 & E6 --> F
    F --> G
    G --> H
    H --> I
    I --> J
```

The diagram shows:
1. Four main log types (Error, Warning, Info, Request)
2. Data included in each log type
3. Common processing steps for all logs
4. Special handling for sensitive data and size limits