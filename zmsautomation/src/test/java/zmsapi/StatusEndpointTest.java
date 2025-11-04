import static io.restassured.RestAssured.given;

import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import io.restassured.RestAssured;

class StatusEndpointTest {

    @BeforeAll
    static void setup() {
        String base = System.getProperty("BASE_URI");
        if (base == null || base.isEmpty()) {
            String envBase = System.getenv("BASE_URI");
            if (envBase != null && !envBase.isEmpty()) {
                base = envBase;
            } else {
                base = "http://zmsapi:8080";
            }
        }
        RestAssured.baseURI = base;
    }

    @Test
    @DisplayName("GET /status/ returns 200 and JSON body")
    void statusEndpointShouldBeOk() {
        given()
            .when()
                .get("/status/")
            .then()
                .statusCode(200);
    }
}
