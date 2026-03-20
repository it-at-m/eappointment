package zms.ataf.rest.steps;

import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

import java.time.Instant;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Map;

import com.fasterxml.jackson.core.type.TypeReference;

import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.cucumber.java.Before;
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
    private String lastDisplayNumberBeforeCancel;

    private int parseIntOrFail(String value, String label) {
        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException nfe) {
            throw new AssertionError("Failed to parse integer for " + label + " from value \"" + value + "\"", nfe);
        }
    }
    
    /** Reset static booking state and instance reserve state before each scenario so each test uses its own process and mails. */
    @Before
    public void clearBookingStateBeforeScenario() {
        clearBookingState();
        lastReserveProcess = null;
    }

    /** Clear shared booking/confirm state (process, credentials, URLs). Call before each scenario to avoid cross-scenario leakage. */
    public static void clearBookingState() {
        bookingProcess = null;
        bookingConfirmProcessId = null;
        bookingConfirmAuthKey = null;
        bookingConfirmUrl = null;
        bookingAppointmentUrl = null;
    }

    /* Section: Sequential steps assertions for thinned booking process */
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

        String daysBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /available-days-by-office/ (officeId=%d, serviceId=%d) status=%d body=%s",
            officeId,
            serviceId,
            response.getStatusCode(),
            daysBody.length() > 500 ? daysBody.substring(0, 500) + "..." : daysBody
        ));

        // Citizen API may return either a plain AvailableDaysResponse payload
        // or an ApiResponse-wrapped payload. Try plain first, then wrapped.
        AvailableDaysResponse days;
        try {
            days = response.as(AvailableDaysResponse.class);
        } catch (Exception e) {
            days = parseDataResponse(response, AvailableDaysResponse.class);
        }
        lastAvailableDaysResponse = days;
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

        String appointmentsBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /available-appointments-by-office/ (date=%s, officeId=%d, serviceId=%d) status=%d body=%s",
            date,
            officeId,
            serviceId,
            response.getStatusCode(),
            appointmentsBody.length() > 500 ? appointmentsBody.substring(0, 500) + "..." : appointmentsBody
        ));

        // As with available-days, available-appointments endpoints may return either
        // a plain AvailableAppointmentsResponse payload or an ApiResponse-wrapped payload.
        AvailableAppointmentsResponse appointments;
        try {
            appointments = response.as(AvailableAppointmentsResponse.class);
        } catch (Exception e) {
            appointments = parseDataResponse(response, AvailableAppointmentsResponse.class);
        }
        lastAvailableAppointmentsResponse = appointments;
    }

    @When("I reserve an appointment with the first available slot")
    public void iReserveAnAppointmentWithTheFirstAvailableSlot() {
        if (lastAvailableAppointmentsResponse == null) {
            throw new IllegalStateException("Request available appointments first (for date, office, service).");
        }
        Long timestamp = lastAvailableAppointmentsResponse.getFirstFutureAppointmentTimestamp();
        if (timestamp == null) {
            ScenarioLogManager.getLogger().error("No appointment timestamps found in lastAvailableAppointmentsResponse "
                + "for officeId=" + lastOfficeId + ", serviceId=" + lastServiceId);
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

        String reserveBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /reserve-appointment/ status=%d body=%s",
            response.getStatusCode(),
            reserveBody.length() > 500 ? reserveBody.substring(0, 500) + "..." : reserveBody
        ));
        response.then().statusCode(200);

        // Reserve endpoint may return plain ThinnedProcess or an ApiResponse-wrapped payload
        ThinnedProcess reserved;
        try {
            reserved = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            reserved = parseDataResponse(response, ThinnedProcess.class);
        }
        org.assertj.core.api.Assertions.assertThat(reserved)
            .as("reserve-appointment response payload must deserialize")
            .isNotNull();
        org.assertj.core.api.Assertions.assertThat(reserved.getProcessId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(reserved.getAuthKey()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(reserved.getOfficeId()).isEqualTo(lastOfficeId);
        // Skip `displayNumber` (it can vary across office/provider formatting) and skip `captchaToken`
        // (captcha is disabled for our test locations).
        org.assertj.core.api.Assertions.assertThat(reserved.getServiceId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(reserved.getTimestamp())
            .as("reserve-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(reserved.getTimestamp())
            .as("reserve-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);

        lastReserveProcess = reserved;
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

        String preconfirmBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /preconfirm-appointment/ status=%d body=%s",
            response.getStatusCode(),
            preconfirmBody.length() > 500 ? preconfirmBody.substring(0, 500) + "..." : preconfirmBody
        ));
        response.then().statusCode(200);

        ThinnedProcess updated;
        try {
            updated = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            updated = parseDataResponse(response, ThinnedProcess.class);
        }
        org.assertj.core.api.Assertions.assertThat(updated)
            .as("preconfirm-appointment response payload must deserialize")
            .isNotNull();
        org.assertj.core.api.Assertions.assertThat(updated.getProcessId()).isEqualTo(pid);
        org.assertj.core.api.Assertions.assertThat(updated.getAuthKey()).isEqualTo(auth);
        org.assertj.core.api.Assertions.assertThat(updated.getOfficeId()).isEqualTo(lastOfficeId);
        org.assertj.core.api.Assertions.assertThat(updated.getServiceId()).isEqualTo(process.getServiceId());
        org.assertj.core.api.Assertions.assertThat(updated.getTimestamp())
            .as("preconfirm-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(updated.getTimestamp())
            .as("preconfirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);

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
            .body(Map.of("processId", parseIntOrFail(processId, "processId"), "authKey", authKey))
        .when()
            .post("/confirm-appointment/");
        CommonApiSteps.setResponse(response);

        String confirmBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /confirm-appointment/ status=%d body=%s",
            response.getStatusCode(),
            confirmBody.length() > 500 ? confirmBody.substring(0, 500) + "..." : confirmBody
        ));
        response.then().statusCode(200);

        ThinnedProcess confirmed;
        try {
            confirmed = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            confirmed = parseDataResponse(response, ThinnedProcess.class);
        }
        org.assertj.core.api.Assertions.assertThat(confirmed)
            .as("confirm-appointment response payload must deserialize")
            .isNotNull();
        Integer expectedPid = parseIntOrFail(processId, "processId");
        org.assertj.core.api.Assertions.assertThat(confirmed.getProcessId()).isEqualTo(expectedPid);
        org.assertj.core.api.Assertions.assertThat(confirmed.getAuthKey()).isEqualTo(authKey);
        // Prefer scenario requested office (via lastOfficeId), but fall back to the last preconfirmed payload.
        Integer expectedOfficeId = lastReserveProcess != null ? lastReserveProcess.getOfficeId() : lastOfficeId;
        org.assertj.core.api.Assertions.assertThat(confirmed.getOfficeId())
            .as("confirmed appointment should land at expected office")
            .isEqualTo(expectedOfficeId);
        org.assertj.core.api.Assertions.assertThat(confirmed.getServiceId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(confirmed.getTimestamp())
            .as("confirm-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(confirmed.getTimestamp())
            .as("confirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);

        if (confirmed != null) {
            lastReserveProcess = confirmed;
            setLastReserveProcess(confirmed);
        }
    }

    @When("I submit a booking request with valid data")
    public void iSubmitABookingRequestWithValidData() {
        iReserveAnAppointmentWithTheFirstAvailableSlot();
    }
    
    
    @Then("the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void theReserveResponseShouldBeReservedWithProcessAuthOfficeService() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getProcessId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getAuthKey()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        org.assertj.core.api.Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        org.assertj.core.api.Assertions.assertThat(process.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(process.getTimestamp())
            .as("reserve-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` (varies) and `captchaToken` (captcha disabled in test data).
    }

    @Then("the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void thePreconfirmResponseShouldBePreconfirmedWithProcessAuthOfficeService() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getProcessId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getAuthKey()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        org.assertj.core.api.Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        org.assertj.core.api.Assertions.assertThat(process.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(process.getTimestamp())
            .as("preconfirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` and `captchaToken`.
    }

    @Then("the preconfirmation mail should provide confirm credentials")
    public void thePreconfirmationMailShouldProvideConfirmCredentials() {
        org.assertj.core.api.Assertions.assertThat(getBookingConfirmProcessId())
            .as("bookingConfirmProcessId must be set")
            .isNotBlank();
        org.assertj.core.api.Assertions.assertThat(getBookingConfirmAuthKey())
            .as("bookingConfirmAuthKey must be set")
            .isNotBlank();
    }

    @Then("the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void theConfirmResponseShouldBeConfirmedWithProcessAuthOfficeService() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getProcessId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getAuthKey()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        org.assertj.core.api.Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        org.assertj.core.api.Assertions.assertThat(process.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(process.getTimestamp())
            .as("confirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` (varies) and `captchaToken` (captcha disabled in test data).
    }

    @Then("the confirmation mail should provide an appointment view url")
    public void theConfirmationMailShouldProvideAnAppointmentViewUrl() {
        String url = getBookingAppointmentUrl();
        org.assertj.core.api.Assertions.assertThat(url)
            .as("bookingAppointmentUrl must be set from confirmation mail")
            .isNotBlank();
        org.assertj.core.api.Assertions.assertThat(url).contains("appointment/");
        org.assertj.core.api.Assertions.assertThat(url).doesNotContain("appointment/confirm/");
    }

    @When("I fetch the appointment for the current process")
    public void iFetchTheAppointmentForTheCurrentProcess() {
        // Prefer the most recent process data, but fall back to the shared booking context.
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (process == null) {
            throw new IllegalStateException("No appointment process available. Confirm the appointment first.");
        }

        Integer pid = process.getProcessId();
        String auth = process.getAuthKey();
        if (pid == null || auth == null) {
            throw new IllegalStateException("Process for appointment lookup has no processId or authKey.");
        }

        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /appointment/ for processId=%d", pid
        ));

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .queryParam("processId", pid)
            .queryParam("authKey", auth)
        .when()
            .get("/appointment/");

        CommonApiSteps.setResponse(response);
        response.then().statusCode(200);

        // Store the /appointment/ payload so the subsequent Then steps can assert fields cleanly.
        ThinnedProcess appointment;
        try {
            appointment = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            appointment = parseDataResponse(response, ThinnedProcess.class);
        }
        if (appointment != null) {
            lastReserveProcess = appointment;
            setLastReserveProcess(appointment);
        }
    }

    @Then("the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void theAppointmentEndpointResponseShouldIncludeAThinnedBookingProcessWithProcessIdAuthOfficeAndServiceId() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getProcessId()).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getAuthKey()).isNotBlank();
        org.assertj.core.api.Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        org.assertj.core.api.Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
    }

    @Then("I cancel the appointment")
    public void iCancelTheAppointment() {
        // Prefer the most recent process data, but fall back to the shared booking context.
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (process == null) {
            throw new IllegalStateException("No appointment process available to delete. Reserve and confirm first.");
        }

        Integer pid = process.getProcessId();
        String auth = process.getAuthKey();
        if (pid == null || auth == null) {
            throw new IllegalStateException("Process for deletion has no processId or authKey.");
        }
        lastDisplayNumberBeforeCancel = process.getDisplayNumber();

        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /cancel-appointment/ for processId=%d", pid
        ));

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(Map.of("processId", pid, "authKey", auth))
        .when()
            .post("/cancel-appointment/");

        CommonApiSteps.setResponse(response);

        String cancelBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /cancel-appointment/ status=%d body=%s",
            response.getStatusCode(),
            cancelBody.length() > 500 ? cancelBody.substring(0, 500) + "..." : cancelBody
        ));

        // Basic sanity: 200 and a non-empty payload with a thinned process.
        response.then().statusCode(200);
        ThinnedProcess cancelled;
        try {
            cancelled = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            cancelled = parseDataResponse(response, ThinnedProcess.class);
        }
        org.assertj.core.api.Assertions.assertThat(cancelled).isNotNull();
        org.assertj.core.api.Assertions.assertThat(cancelled.getProcessId()).isEqualTo(pid);
        // Avoid strict checks on officeId/authKey because cancel returns a "deleted" payload where
        // officeId can be 0 and authKey may change.
        // Keep serviceId invariant checks, which are stable across our test flow.
        if (process.getServiceId() != null) {
            org.assertj.core.api.Assertions.assertThat(cancelled.getServiceId())
                .as("cancel-appointment should keep same serviceId as prior appointment")
                .isEqualTo(process.getServiceId());
        }
        org.assertj.core.api.Assertions.assertThat(cancelled.getTimestamp())
            .as("cancel-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(cancelled.getTimestamp())
            .as("cancel-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` and `captchaToken` assertions: display formatting varies by office/provider,
        // and captcha is disabled in our test data.
        lastReserveProcess = cancelled;
        setLastReserveProcess(cancelled);
    }

    @Then("the cancel endpoint response should include a soft deleted thinned booking process")
    public void theCancelEndpointResponseShouldIncludeAThinnedBookingProcess() {
        response.then().statusCode(200);
        ThinnedProcess cancelled = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(cancelled).isNotNull();
        // Avoid strict checks on officeId/authKey because cancel returns a "deleted" payload where
        // officeId can be 0 and authKey may change.
        // Soft-delete contract checks: officeId=0 and key appointment details are nulled.
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getInt("officeId")).as("officeId must be 0 (soft delete)").isEqualTo(0);
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("officeName"))
            .as("officeName must be null")
            .isNull();

        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("familyName"))
            .as("familyName must indicate cancellation")
            .isEqualTo("(abgesagt)");

        // top-level strings reset
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("telephone")).as("telephone must be empty").isEqualTo("");
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("customTextfield")).as("customTextfield must be empty").isEqualTo("");
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("customTextfield2")).as("customTextfield2 must be empty").isEqualTo("");
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("captchaToken")).as("captchaToken must be empty in soft delete payload").isEqualTo("");

        // provider inside scope is nulled out (keeps provider.id=0/name="") in soft delete payload
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getInt("scope.provider.id"))
            .as("scope.provider.id must be 0")
            .isEqualTo(0);
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("scope.provider.name"))
            .as("scope.provider.name must be empty")
            .isEqualTo("");
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.provider.displayName"))
            .as("scope.provider.displayName must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.provider.lat"))
            .as("scope.provider.lat must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.provider.lon"))
            .as("scope.provider.lon must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.provider.contact"))
            .as("scope.provider.contact must be null")
            .isNull();

        // scope is mostly nulled out for soft delete payload
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("scope.shortName"))
            .as("scope.shortName must be empty")
            .isEqualTo("");
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("scope.emailFrom"))
            .as("scope.emailFrom must be empty")
            .isEqualTo("");
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.emailRequired"))
            .as("scope.emailRequired must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.telephoneActivated"))
            .as("scope.telephoneActivated must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.telephoneRequired"))
            .as("scope.telephoneRequired must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfieldActivated"))
            .as("scope.customTextfieldActivated must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfieldRequired"))
            .as("scope.customTextfieldRequired must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfieldLabel"))
            .as("scope.customTextfieldLabel must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfield2Activated"))
            .as("scope.customTextfield2Activated must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfield2Required"))
            .as("scope.customTextfield2Required must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfield2Label"))
            .as("scope.customTextfield2Label must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.captchaActivatedRequired"))
            .as("scope.captchaActivatedRequired must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.infoForAppointment"))
            .as("scope.infoForAppointment must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.infoForAllAppointments"))
            .as("scope.infoForAllAppointments must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.slotsPerAppointment"))
            .as("scope.slotsPerAppointment must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.appointmentsPerMail"))
            .as("scope.appointmentsPerMail must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("scope.whitelistedMails"))
            .as("scope.whitelistedMails must be empty")
            .isEqualTo("");
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.reservationDuration"))
            .as("scope.reservationDuration must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.activationDuration"))
            .as("scope.activationDuration must be null")
            .isNull();
        org.assertj.core.api.Assertions.assertThat((Object) response.jsonPath().get("scope.hint"))
            .as("scope.hint must be null")
            .isNull();

        org.assertj.core.api.Assertions.assertThat(cancelled.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        org.assertj.core.api.Assertions.assertThat(cancelled.getTimestamp())
            .as("cancel-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` and `captchaToken`.
    }

    @Then("the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email")
    public void theCancelEndpointResponseShouldStillIncludeProcessIdEmailDisplayNumberAndServiceIdAndServiceName() {
        response.then().statusCode(200);

        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getInt("processId"))
            .as("processId should still be set in soft delete payload")
            .isNotEqualTo(0);

        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("email"))
            .as("email must still be present so deletion/cancellation email can be sent")
            .isNotBlank();

        ThinnedProcess cancelled = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(cancelled).isNotNull();

        String cancelledDisplayNumber = cancelled.getDisplayNumber();
        org.assertj.core.api.Assertions.assertThat(cancelledDisplayNumber)
            .as("displayNumber must still be present in soft delete payload")
            .isNotBlank();
        if (lastDisplayNumberBeforeCancel != null && !lastDisplayNumberBeforeCancel.isBlank()) {
            org.assertj.core.api.Assertions.assertThat(cancelledDisplayNumber)
                .as("displayNumber should be preserved during soft delete")
                .isEqualTo(lastDisplayNumberBeforeCancel);
        }

        Object scopeId = response.jsonPath().get("scope.id");
        org.assertj.core.api.Assertions.assertThat(scopeId)
            .as("scope.id must still be present for email office-name lookup")
            .isNotNull();
        org.assertj.core.api.Assertions.assertThat(scopeId)
            .as("scope.id must be numeric")
            .isInstanceOf(Number.class);
        org.assertj.core.api.Assertions.assertThat(((Number) scopeId).intValue())
            .as("scope.id must be a positive office lookup id")
            .isGreaterThan(0);

        org.assertj.core.api.Assertions.assertThat(cancelled.getServiceId())
            .as("cancel-appointment should keep same serviceId as prior appointment")
            .isEqualTo(lastServiceId);
        org.assertj.core.api.Assertions.assertThat(response.jsonPath().getString("serviceName"))
            .as("serviceName should still exist")
            .isNotBlank();
    }
    /* End Section: Sequential steps assertions for thinned booking process */

    /* Section: Non-sequential steps assertions for thinned booking process */
    @Then("the appointment should be at office {int}")
    public void theAppointmentShouldBeAtOffice(int officeId) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getOfficeId())
            .as("Expected appointment to land at office %d", officeId)
            .isEqualTo(officeId);
    }

    @Then("the appointment should be for service {int}")
    public void theAppointmentShouldBeForService(int serviceId) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process).isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getServiceId())
            .as("Expected appointment to use service %d", serviceId)
            .isEqualTo(serviceId);
    }

    @Then("the appointment status should be {string}")
    public void theAppointmentStatusShouldBe(String expectedStatus) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        org.assertj.core.api.Assertions.assertThat(process)
            .as("expected a thinned process in order to assert appointment status")
            .isNotNull();
        org.assertj.core.api.Assertions.assertThat(process.getStatus())
            .as("appointment status should match")
            .isEqualTo(expectedStatus);
    }
    /* End Section: Non-sequential steps assertions for thinned booking process */

    /* Section: Response Parsing */
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
    private static String bookingConfirmUrl;
    private static String bookingAppointmentUrl;

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

    public static String getBookingConfirmUrl() {
        return bookingConfirmUrl;
    }

    public static void setBookingConfirmUrl(String url) {
        bookingConfirmUrl = url;
    }

    public static String getBookingAppointmentUrl() {
        return bookingAppointmentUrl;
    }

    public static void setBookingAppointmentUrl(String url) {
        bookingAppointmentUrl = url;
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
