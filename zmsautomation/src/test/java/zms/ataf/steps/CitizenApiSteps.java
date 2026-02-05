package zms.ataf.steps;

import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

import com.fasterxml.jackson.core.type.TypeReference;

import ataf.rest.steps.BaseRestSteps;
import config.TestConfig;
import dto.common.ApiResponse;
import dto.zmscitizenapi.collections.OfficesAndServicesResponse;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;

public class CitizenApiSteps extends BaseRestSteps {
    
    private Response response;
    private String baseUri;
    
    @Given("the Citizen API is available")
    public void theCitizenApiIsAvailable() {
        baseUri = TestConfig.getCitizenApiBaseUri();
        
        given()
            .baseUri(baseUri)
        .when()
            .get("/offices-and-services/")
        .then()
            .statusCode(200);
    }
    
    @Given("I have selected a valid service and location")
    public void iHaveSelectedAValidServiceAndLocation() {
        // This step is a placeholder for test data setup
        // In a real scenario, this would set up test data or select specific service/location
        // For now, we'll assume the booking endpoint handles the validation
        baseUri = TestConfig.getCitizenApiBaseUri();
    }
    
    @When("I request the offices and services endpoint")
    public void iRequestTheOfficesAndServicesEndpoint() {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
        .when()
            .get("/offices-and-services/");
    }
    
    @When("I submit a booking request with valid data")
    public void iSubmitABookingRequestWithValidData() {
        // TODO: Implement booking request step
        // This will need to be implemented based on the actual booking API structure
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body("{}") // Placeholder - needs actual booking data
        .when()
            .post("/appointments/");
    }
    
    @Then("the response status code should be {int}")
    public void theResponseStatusCodeShouldBe(int statusCode) {
        response.then().statusCode(statusCode);
    }
    
    @Then("the response should contain offices and services")
    public void theResponseShouldContainOfficesAndServices() {
        // Try unwrapped first, then wrapped if that fails
        OfficesAndServicesResponse officesAndServices;
        try {
            officesAndServices = response.as(OfficesAndServicesResponse.class);
        } catch (Exception e) {
            // Fallback to wrapped response if unwrapped fails
            ApiResponse<OfficesAndServicesResponse> apiResponse = as(response,
                new TypeReference<ApiResponse<OfficesAndServicesResponse>>() {});
            org.assertj.core.api.Assertions.assertThat(apiResponse).isNotNull();
            org.assertj.core.api.Assertions.assertThat(apiResponse.getMeta()).isNotNull();
            org.assertj.core.api.Assertions.assertThat(apiResponse.getMeta().getError()).isFalse();
            officesAndServices = apiResponse.getData();
        }
        
        org.assertj.core.api.Assertions.assertThat(officesAndServices).isNotNull();
        org.assertj.core.api.Assertions.assertThat(officesAndServices.getOffices()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(officesAndServices.getServices()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(officesAndServices.getRelations()).isNotNull();
    }
    
    @Then("I should receive a confirmation number")
    public void iShouldReceiveAConfirmationNumber() {
        // TODO: Implement confirmation number validation
        // This will need to be implemented based on the actual booking response structure
        response.then()
            .body("confirmationNumber", notNullValue());
    }
    
    @Given("I have a valid appointment confirmation number")
    public void iHaveAValidAppointmentConfirmationNumber() {
        // TODO: This would typically set up test data or retrieve a valid confirmation number
        // For now, this is a placeholder that would be implemented based on actual API structure
        baseUri = TestConfig.getCitizenApiBaseUri();
    }
    
    @Given("I have an invalid appointment confirmation number")
    public void iHaveAnInvalidAppointmentConfirmationNumber() {
        // Placeholder for invalid confirmation number setup
        baseUri = TestConfig.getCitizenApiBaseUri();
    }
    
    @Given("I have a cancelled appointment confirmation number")
    public void iHaveACancelledAppointmentConfirmationNumber() {
        // Placeholder for cancelled appointment setup
        baseUri = TestConfig.getCitizenApiBaseUri();
    }
    
    @When("I submit a cancellation request")
    public void iSubmitACancellationRequest() {
        // TODO: Implement cancellation request
        // This will need to be implemented based on the actual cancellation API structure
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body("{\"confirmationNumber\": \"placeholder\"}") // Placeholder - needs actual confirmation number
        .when()
            .delete("/appointments/");
    }
    
    @Then("the appointment should be cancelled")
    public void theAppointmentShouldBeCancelled() {
        response.then()
            .body(not(empty()));
        // TODO: Add specific assertions for cancellation confirmation
    }
    
    @Then("the response should indicate the appointment was not found")
    public void theResponseShouldIndicateAppointmentNotFound() {
        response.then()
            .body(not(empty()));
        // TODO: Add specific assertions for not found error
    }
    
    @Then("the response should indicate the appointment is already cancelled")
    public void theResponseShouldIndicateAppointmentAlreadyCancelled() {
        response.then()
            .body(not(empty()));
        // TODO: Add specific assertions for already cancelled error
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
