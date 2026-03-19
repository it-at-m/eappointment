package zms.ataf.rest.steps;

import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

import com.fasterxml.jackson.core.type.TypeReference;

import config.TestConfig;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmsapi.StatusResponse;

public class ZmsApiSteps {
    
    private Response response;
    private String baseUri;
    
    @Given("the ZMS API is available")
    public void theZmsApiIsAvailable() {
        baseUri = TestConfig.getBaseUri();
        
        given()
            .baseUri(baseUri)
        .when()
            .get("/status/")
        .then()
            .statusCode(200);
    }
    
    @Given("the ZMS API is available with logging {string}")
    public void theZmsApiIsAvailableWithLogging(String loggingEnabled) {
        baseUri = TestConfig.getBaseUri();
        // Logging configuration would be set here if needed
        theZmsApiIsAvailable();
    }
    
    @When("I request the status endpoint")
    public void iRequestTheStatusEndpoint() {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
        .when()
            .get("/status/");
        CommonApiSteps.setResponse(response);
    }
    
    
    @Then("the response should contain status information")
    public void theResponseShouldContainStatusInformation() {
        ApiResponse<StatusResponse> apiResponse = as(response,
            new TypeReference<ApiResponse<StatusResponse>>() {});
        
        org.assertj.core.api.Assertions.assertThat(apiResponse).isNotNull();
        org.assertj.core.api.Assertions.assertThat(apiResponse.getMeta()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(apiResponse.getMeta().getError()).isFalse();
        
        StatusResponse statusData = apiResponse.getData();
        org.assertj.core.api.Assertions.assertThat(statusData).isNotNull();
        org.assertj.core.api.Assertions.assertThat(statusData.getVersion()).isNotNull();
    }
    
    private <T> T as(Response response, TypeReference<T> typeReference) {
        try {
            com.fasterxml.jackson.databind.ObjectMapper mapper = new com.fasterxml.jackson.databind.ObjectMapper();
            String responseBody = response.asString();
            return mapper.readValue(responseBody, typeReference);
        } catch (Exception e) {
            String responseBody = response.asString();
            String errorMsg = String.format(
                "Failed to deserialize response. Status: %d, Response body (first 500 chars): %s",
                response.getStatusCode(),
                responseBody != null && responseBody.length() > 500
                    ? responseBody.substring(0, 500) + "..."
                    : responseBody
            );
            throw new RuntimeException(errorMsg, e);
        }
    }
}
