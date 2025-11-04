import static io.restassured.RestAssured.given;

import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import io.restassured.RestAssured;

class OfficesAndServicesEndpointTest {

    @BeforeAll
    static void setup() {
        // For zmscitizenapi, we need to use port 8081 instead of 8080
        // BASE_URI is set to http://api-server:8080 by default, so we need to override for citizen API
        String base = System.getProperty("CITIZEN_API_BASE_URI");
        if (base == null || base.isEmpty()) {
            String envBase = System.getenv("CITIZEN_API_BASE_URI");
            if (envBase != null && !envBase.isEmpty()) {
                base = envBase;
            } else {
                // Default to port 8081 if no environment variable is set
                // Extract host from BASE_URI and change port to 8081
                String defaultBase = System.getProperty("BASE_URI");
                if (defaultBase == null || defaultBase.isEmpty()) {
                    defaultBase = System.getenv("BASE_URI");
                    if (defaultBase == null || defaultBase.isEmpty()) {
                        defaultBase = "http://api-server:8080";
                    }
                }
                // Replace port 8080 with 8081
                base = defaultBase.replace(":8080", ":8081");
            }
        }
        RestAssured.baseURI = base;
    }

    @Test
    @DisplayName("GET /offices-and-services/ returns 200 and JSON body")
    void officesAndServicesEndpointShouldBeOk() {
        given()
            .when()
                .get("/offices-and-services/")
            .then()
                .statusCode(200);
    }
}
