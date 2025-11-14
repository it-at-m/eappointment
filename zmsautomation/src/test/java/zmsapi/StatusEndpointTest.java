import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import base.BaseApiTest;

class StatusEndpointTest extends BaseApiTest {

    @Test
    @DisplayName("GET /status/ returns 200 and JSON body")
    void statusEndpointShouldBeOk() {
        givenRequest()
            .when()
                .get("/status/")
            .then()
                .statusCode(200);
    }
}
