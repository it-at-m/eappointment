import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import base.BaseCitizenApiTest;

class OfficesAndServicesEndpointTest extends BaseCitizenApiTest {

    @Test
    @DisplayName("GET /offices-and-services/ returns 200 and JSON body")
    void officesAndServicesEndpointShouldBeOk() {
        givenRequest()
            .when()
                .get("/offices-and-services/")
            .then()
                .statusCode(200);
    }
}
