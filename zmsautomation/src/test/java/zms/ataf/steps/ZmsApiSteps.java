package zms.ataf.steps;

import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

import com.fasterxml.jackson.core.type.TypeReference;

import config.TestConfig;
import dto.common.ApiResponse;
import dto.zmsapi.StatusResponse;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;

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
    
    @When("I request available appointments for scope {int}")
    public void iRequestAvailableAppointments(int scopeId) {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .pathParam("scopeId", scopeId)
        .when()
            .get("/scope/{scopeId}/availability/");
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
    
    @Then("the response should contain available slots")
    public void theResponseShouldContainSlots() {
        response.then()
            .body("data", not(empty()));
    }
    
    @When("I request scope information for scope {int}")
    public void iRequestScopeInformation(int scopeId) {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .pathParam("scopeId", scopeId)
        .when()
            .get("/scope/{scopeId}/");
        CommonApiSteps.setResponse(response);
    }
    
    @Then("the response should contain scope details")
    public void theResponseShouldContainScopeDetails() {
        response.then()
            .body("data", notNullValue());
    }
    
    @When("I request an invalid endpoint {string}")
    public void iRequestAnInvalidEndpoint(String endpoint) {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
        .when()
            .get(endpoint);
        CommonApiSteps.setResponse(response);
    }
    
    @When("I send a {string} request to {string}")
    public void iSendARequest(String method, String endpoint) {
        baseUri = baseUri != null ? baseUri : TestConfig.getBaseUri();
        switch (method.toUpperCase()) {
            case "DELETE":
                response = given()
                    .baseUri(baseUri)
                .when()
                    .delete(endpoint);
                break;
            case "PUT":
                response = given()
                    .baseUri(baseUri)
                .when()
                    .put(endpoint);
                break;
            case "PATCH":
                response = given()
                    .baseUri(baseUri)
                .when()
                    .patch(endpoint);
                break;
            default:
                throw new IllegalArgumentException("Unsupported HTTP method: " + method);
        }
        CommonApiSteps.setResponse(response);
    }
    
    @When("I request availability without scope ID")
    public void iRequestAvailabilityWithoutScopeId() {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
        .when()
            .get("/scope//availability/");
        CommonApiSteps.setResponse(response);
    }
    
    @When("I submit an invalid request body to {string}")
    public void iSubmitAnInvalidRequestBody(String endpoint) {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .contentType("application/json")
            .body("{\"invalid\": \"data\"}")
        .when()
            .post(endpoint);
        CommonApiSteps.setResponse(response);
    }
    
    @Then("the response should contain an error message")
    public void theResponseShouldContainAnErrorMessage() {
        response.then()
            .body(not(empty()));
    }
    
    @Then("the response should contain validation errors")
    public void theResponseShouldContainValidationErrors() {
        response.then()
            .body(not(empty()));
    }
    
    @Then("the response should contain {string}")
    public void theResponseShouldContain(String expectedContent) {
        switch (expectedContent.toLowerCase()) {
            case "available slots":
                theResponseShouldContainSlots();
                break;
            case "error message":
                theResponseShouldContainAnErrorMessage();
                break;
            case "empty array":
                response.then()
                    .body("data", empty());
                break;
            default:
                response.then()
                    .body(not(empty()));
        }
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
