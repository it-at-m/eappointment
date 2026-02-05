package zmsapi;

import static org.assertj.core.api.Assertions.assertThat;

import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import base.BaseApiTest;
import dto.common.ApiResponse;
import dto.zmsapi.StatusResponse;
import io.restassured.response.Response;

class StatusEndpointTest extends BaseApiTest {

    @Test
    @DisplayName("GET /status/ returns 200 and JSON body")
    void statusEndpointShouldBeOk() {
        Response response = givenRequest()
            .when()
                .get("/status/")
            .then()
                .statusCode(200)
                .extract()
                .response();

        // Deserialize wrapped response to DTO
        ApiResponse<StatusResponse> apiResponse = as(response,
            new com.fasterxml.jackson.core.type.TypeReference<ApiResponse<StatusResponse>>() {});

        // Assert wrapper structure
        assertThat(apiResponse).isNotNull();
        assertThat(apiResponse.getMeta()).isNotNull();
        assertThat(apiResponse.getMeta().getGenerated()).isNotNull();
        assertThat(apiResponse.getMeta().getServer()).isNotNull();
        assertThat(apiResponse.getMeta().getError()).isFalse();

        // Assert data structure
        StatusResponse statusData = apiResponse.getData();
        assertThat(statusData).isNotNull();
        
        // Version is always present in the status data
        assertThat(statusData.getVersion()).isNotNull();
        assertThat(statusData.getVersion().getMajor()).isNotNull();
        assertThat(statusData.getVersion().getMinor()).isNotNull();
        assertThat(statusData.getVersion().getPatch()).isNotNull();
        // Version fields are strings (patch can be like "00-muc33-patch7-55-g3e25da4c3")
        
        // Note: generated and server are in meta, not in the status data entity
        // The status entity contains: version, processes, database, mail, notification, sources
    }
}
