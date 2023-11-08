# Request Flows for a client with openId connection

The following explains the process of login an user into client zmsadmin via an open id connection. We distinguish between the login of a new user, the login of an existing user, the update of an access token with a refresh token, as well as the logout of the OIDC provider and the client itself.

```mermaid
sequenceDiagram
    actor User
    participant Client as Zmsadmin
    participant OIDC as OpenID Provider
    participant API as Zmsapi
    participant DB as Zmsdb    

    %% login sequence
    rect rgb(223, 255, 191)
    User ->> Client: Would like to log in using OIDC
        rect rgb(240, 240, 240)
            alt authKey not set and authorization code not existing
                Client -->>+ OIDC: login to the provider
                OIDC -->>+ Client: receive the authorization code after login to the provider
                Client -->>+ OIDC: requesting an access token by authorization code
                OIDC -->>- Client: get access token
                Client ->> API: send access token to api
                API -->> DB: write access token to session table
            else oidc state !== authKey
                Client --> Client: remove authKey from Cookies (Zmsclient)
            end
        end

    Client -->>+ OIDC: requesting owner data by access token
    OIDC -->>+ Client: send owner data to client
    Client ->> API: send post api call to /workstation/oauth/ with owner data
        rect rgb(240, 200, 200)
            break when state === null || x-Authkey !== state
                API --> Client: throw WorkstationAuthFailed exception
                Client --> Client: remove authKey from Cookies (Zmsclient)
            end
        end
        API -->> API: create Useraccount Entity from Zmsentities by oidc owner data
        rect rgb(240, 240, 240)
            alt useraccount exists
                API -->>+ DB: write login with x-Authkey as SessionID
            else useraccount new
                API -->>+ DB: write new user to db with random password and x-Auhtkey as SessionID
            end
        end
    DB -->>+ API: get workstation
    API ->> Client: return logged in workstation
        rect rgb(240, 240, 240)
            alt useraccount exists
                Client --> Client: redirect to /workstation/select/
            else useraccount new
                Client --> Client: redirect to index
                note over Client: A message is displayed that a system administrator must assign department assignments and user permissions 
            end
        end
    end

    %% refresh access token sequence
    rect rgb(255, 223, 191)
    User ->> Client: After successful login
        Client ->> API: requesting existing access token
        DB -->> API: read access token from session table
        API ->> Client: send existing access token to client
        rect rgb(240, 240, 240)
            alt access token has expired
                Client -->>+ OIDC: requesting a new access token by refresh token in existing access token
                OIDC -->>- Client: get new access token
                Client ->>+ API: delete expired token
                API -->>+ DB: remove access token from session table
                Client ->>+ API: send new token
                API -->>+ DB: write access token to session table
            else 
                rect rgb(230, 230, 250)
                    loop check access token 
                        Client->>Client: using valid access token until it expires
                    end
                    Note right of Client: refresh and access token lifespan can be set at the provider
                end
            end
        end
    end

    %% logout sequence
    rect rgb(201, 223, 255)
    User ->> Client: Would like to logout
        Client ->> API: Remove Session
        API -->> DB: delete session with access token data
        Client -->> OIDC: requesting logoutUrl with redirect_uri to client logout
        OIDC -->> Client: return logoutUrl
        Client -->> OIDC: logout from provider
        OIDC --> OIDC: logout and delete session
        OIDC -->> Client: redirect to transferred redirect_uri
        Client --> Client: redirect to /logout/
        Client ->> API: requesting to logout workstation   
        API -->> DB: logout workstation by removing x-Authkey
    end
    ```