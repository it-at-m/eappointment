package zms.ataf.rest.steps;

import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Map;

import com.fasterxml.jackson.core.type.TypeReference;

import config.TestConfig;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmscitizenapi.AvailableAppointmentsResponse;
import zms.ataf.rest.dto.zmscitizenapi.AvailableDaysResponse;
import zms.ataf.rest.dto.zmscitizenapi.ReserveAppointmentRequest;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;
import zms.ataf.rest.dto.zmscitizenapi.collections.OfficesAndServicesResponse;

public class CitizenApiSteps {

    private static final DateTimeFormatter DATE_FORMAT = DateTimeFormatter.ISO_LOCAL_DATE;

    private Response response;
    private String baseUri;
    private AvailableDaysResponse lastAvailableDaysResponse;
    private AvailableAppointmentsResponse lastAvailableAppointmentsResponse;
    private ThinnedProcess lastReserveProcess;
    private String confirmProcessId;
    private String confirmAuthKey;
    private int lastOfficeId;
    private int lastServiceId;
    private int lastServiceCount = 1;
    
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
        CommonApiSteps.setResponse(response);
    }

    @When("I request available days for office {int} and service {int}")
    public void iRequestAvailableDaysForOfficeAndService(int officeId, int serviceId) {
        iRequestAvailableDaysForOfficeAndService(officeId, serviceId, 1);
    }

    @When("I request available days for office {int} and service {int} with service count {int}")
    public void iRequestAvailableDaysForOfficeAndService(int officeId, int serviceId, int serviceCount) {
        lastOfficeId = officeId;
        lastServiceId = serviceId;
        lastServiceCount = serviceCount;
        String startDate = LocalDate.now().format(DATE_FORMAT);
        String endDate = LocalDate.now().plusMonths(6).format(DATE_FORMAT);
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .queryParam("officeId", String.valueOf(officeId))
            .queryParam("serviceId", String.valueOf(serviceId))
            .queryParam("startDate", startDate)
            .queryParam("endDate", endDate)
            .queryParam("serviceCount", String.valueOf(serviceCount))
        .when()
            .get("/available-days-by-office/");
        CommonApiSteps.setResponse(response);
        lastAvailableDaysResponse = parseDataResponse(response, AvailableDaysResponse.class);
    }

    @When("I request available appointments for the first available day")
    public void iRequestAvailableAppointmentsForTheFirstAvailableDay() {
        if (lastAvailableDaysResponse == null) {
            throw new IllegalStateException("Request available days first.");
        }
        String date = lastAvailableDaysResponse.getFirstAvailableDay();
        if (date == null) {
            throw new IllegalStateException("No available day in last response.");
        }
        iRequestAvailableAppointmentsForDateOfficeAndService(date, lastOfficeId, lastServiceId, lastServiceCount);
    }

    @When("I request available appointments for date {string}, office {int} and service {int}")
    public void iRequestAvailableAppointmentsForDateOfficeAndService(String date, int officeId, int serviceId) {
        iRequestAvailableAppointmentsForDateOfficeAndService(date, officeId, serviceId, 1);
    }

    @When("I request available appointments for date {string}, office {int} and service {int} with service count {int}")
    public void iRequestAvailableAppointmentsForDateOfficeAndService(String date, int officeId, int serviceId, int serviceCount) {
        lastOfficeId = officeId;
        lastServiceId = serviceId;
        lastServiceCount = serviceCount;
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .queryParam("date", date)
            .queryParam("officeId", String.valueOf(officeId))
            .queryParam("serviceId", String.valueOf(serviceId))
            .queryParam("serviceCount", String.valueOf(serviceCount))
        .when()
            .get("/available-appointments-by-office/");
        CommonApiSteps.setResponse(response);
        lastAvailableAppointmentsResponse = parseDataResponse(response, AvailableAppointmentsResponse.class);
    }

    @When("I reserve an appointment with the first available slot")
    public void iReserveAnAppointmentWithTheFirstAvailableSlot() {
        if (lastAvailableAppointmentsResponse == null) {
            throw new IllegalStateException("Request available appointments first (for date, office, service).");
        }
        Long timestamp = lastAvailableAppointmentsResponse.getFirstAppointmentTimestamp();
        if (timestamp == null) {
            throw new IllegalStateException("No appointment timestamps in last response.");
        }
        ReserveAppointmentRequest body = new ReserveAppointmentRequest();
        body.setTimestamp(timestamp);
        body.setOfficeId(lastOfficeId);
        body.setServiceId(List.of(lastServiceId));
        body.setServiceCount(List.of(lastServiceCount));
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(body)
        .when()
            .post("/reserve-appointment/");
        CommonApiSteps.setResponse(response);
        lastReserveProcess = parseDataResponse(response, ThinnedProcess.class);
        if (lastReserveProcess != null) {
            setLastReserveProcess(lastReserveProcess);
        }
    }

    @When("I preconfirm the appointment")
    public void iPreconfirmTheAppointment() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (process == null) {
            throw new IllegalStateException("Reserve an appointment first.");
        }
        Integer pid = process.getProcessId();
        String auth = process.getAuthKey();
        if (pid == null || auth == null) {
            throw new IllegalStateException("Last reserve response has no processId or authKey.");
        }
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(Map.of("processId", pid, "authKey", auth))
        .when()
            .post("/preconfirm-appointment/");
        CommonApiSteps.setResponse(response);
        ThinnedProcess updated = parseDataResponse(response, ThinnedProcess.class);
        if (updated != null) {
            lastReserveProcess = updated;
            setLastReserveProcess(updated);
        }
    }

    @When("I confirm the appointment")
    public void iConfirmTheAppointment() {
        String processId = getConfirmProcessId();
        String authKey = getConfirmAuthKey();
        if (processId == null || authKey == null) {
            throw new IllegalStateException("Fetch the preconfirmation mail first to get confirm credentials.");
        }
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(Map.of("processId", Integer.parseInt(processId), "authKey", authKey))
        .when()
            .post("/confirm-appointment/");
        CommonApiSteps.setResponse(response);
        ThinnedProcess confirmed = parseDataResponse(response, ThinnedProcess.class);
        if (confirmed != null) {
            lastReserveProcess = confirmed;
            setLastReserveProcess(confirmed);
        }
    }

    @When("I submit a booking request with valid data")
    public void iSubmitABookingRequestWithValidData() {
        iReserveAnAppointmentWithTheFirstAvailableSlot();
    }
    
    
    @Then("the response should contain a process id and auth key")
    public void theResponseShouldContainAProcessIdAndAuthKey() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getProcessId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getAuthKey()).isNotNull();
    }

    @Then("the appointment should be confirmed")
    public void theAppointmentShouldBeConfirmed() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getStatus()).isEqualTo("confirmed");
    }

    @Then("the appointment should be at office {int}")
    public void theAppointmentShouldBeAtOffice(int officeId) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getOfficeId())
            .as("Expected appointment to land at office %d", officeId)
            .isEqualTo(officeId);
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
        CommonApiSteps.setResponse(response);
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
    
    private <T> T parseDataResponse(Response response, Class<T> dataClass) {
        try {
            com.fasterxml.jackson.databind.ObjectMapper mapper = new com.fasterxml.jackson.databind.ObjectMapper();
            com.fasterxml.jackson.databind.JavaType type = mapper.getTypeFactory()
                .constructParametricType(ApiResponse.class, dataClass);
            ApiResponse<T> apiResponse = mapper.readValue(response.asString(), type);
            return apiResponse != null ? apiResponse.getData() : null;
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

    public AvailableDaysResponse getLastAvailableDaysResponse() {
        return lastAvailableDaysResponse;
    }

    public AvailableAppointmentsResponse getLastAvailableAppointmentsResponse() {
        return lastAvailableAppointmentsResponse;
    }

    /** Static booking context so other step classes (e.g. ZmsApiMailSteps) can read/write reserve and confirm state. */
    private static ThinnedProcess bookingProcess;
    private static String bookingConfirmProcessId;
    private static String bookingConfirmAuthKey;

    public static ThinnedProcess getBookingProcess() {
        return bookingProcess;
    }

    public static void setBookingProcess(ThinnedProcess process) {
        bookingProcess = process;
    }

    public static String getBookingConfirmProcessId() {
        return bookingConfirmProcessId;
    }

    public static String getBookingConfirmAuthKey() {
        return bookingConfirmAuthKey;
    }

    public static void setBookingConfirmCredentials(String processId, String authKey) {
        bookingConfirmProcessId = processId;
        bookingConfirmAuthKey = authKey;
    }

    public ThinnedProcess getLastReserveProcess() {
        return lastReserveProcess;
    }

    public void setLastReserveProcess(ThinnedProcess process) {
        this.lastReserveProcess = process;
        setBookingProcess(process);
    }

    public String getConfirmProcessId() {
        return confirmProcessId != null ? confirmProcessId : getBookingConfirmProcessId();
    }

    public String getConfirmAuthKey() {
        return confirmAuthKey != null ? confirmAuthKey : getBookingConfirmAuthKey();
    }

    public void setConfirmCredentials(String processId, String authKey) {
        this.confirmProcessId = processId;
        this.confirmAuthKey = authKey;
        setBookingConfirmCredentials(processId, authKey);
    }
}
