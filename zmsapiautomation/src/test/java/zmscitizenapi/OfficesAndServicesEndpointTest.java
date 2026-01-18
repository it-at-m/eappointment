package zmscitizenapi;

import static org.assertj.core.api.Assertions.assertThat;

import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import base.BaseCitizenApiTest;
import dto.common.ApiResponse;
import dto.zmscitizenapi.collections.OfficesAndServicesResponse;
import io.restassured.response.Response;

class OfficesAndServicesEndpointTest extends BaseCitizenApiTest {

    @Test
    @DisplayName("GET /offices-and-services/ returns 200 and JSON body")
    void officesAndServicesEndpointShouldBeOk() {
        Response response = givenRequest()
            .when()
                .get("/offices-and-services/")
            .then()
                .statusCode(200)
                .extract()
                .response();

        // Citizen API returns data directly (not wrapped in meta/data)
        // Try unwrapped first, then wrapped if that fails
        OfficesAndServicesResponse officesAndServices;
        try {
            officesAndServices = response.as(OfficesAndServicesResponse.class);
        } catch (Exception e) {
            // Fallback to wrapped response if unwrapped fails
            ApiResponse<OfficesAndServicesResponse> apiResponse = as(response,
                new com.fasterxml.jackson.core.type.TypeReference<ApiResponse<OfficesAndServicesResponse>>() {});
            assertThat(apiResponse).isNotNull();
            assertThat(apiResponse.getMeta()).isNotNull();
            assertThat(apiResponse.getMeta().getError()).isFalse();
            officesAndServices = apiResponse.getData();
        }

        // Assert data structure
        assertThat(officesAndServices).isNotNull();
        assertThat(officesAndServices.getOffices()).isNotNull();
        assertThat(officesAndServices.getServices()).isNotNull();
        assertThat(officesAndServices.getRelations()).isNotNull();
    }
}
