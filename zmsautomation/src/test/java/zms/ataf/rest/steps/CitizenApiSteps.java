package zms.ataf.rest.steps;

import static io.restassured.RestAssured.given;

import java.time.Instant;
import java.time.format.DateTimeFormatter;
import java.util.Locale;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.stream.Collectors;

import org.assertj.core.api.Assertions;

import com.fasterxml.jackson.core.type.TypeReference;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.cucumber.java.After;
import io.cucumber.java.Before;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
import io.restassured.specification.RequestSpecification;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.helpers.CaptchaClient;
import zms.ataf.helpers.CitizenKeycloakTokenHelper;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmscitizenapi.AvailableAppointmentsResponse;
import zms.ataf.rest.dto.zmscitizenapi.AvailableCalendarResponse;
import zms.ataf.rest.dto.zmscitizenapi.Office;
import zms.ataf.rest.dto.zmscitizenapi.OfficeServiceRelation;
import zms.ataf.rest.dto.zmscitizenapi.ReserveAppointmentRequest;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;
import zms.ataf.rest.dto.zmscitizenapi.collections.OfficesAndServicesResponse;
import zms.ataf.ui.pages.citizenview.support.FuehrerscheinstelleScopeHints;
import zms.ataf.ui.pages.citizenview.support.RuppertstrasseWartezoneHints;

public class CitizenApiSteps {

    private static final DateTimeFormatter DATE_FORMAT = DateTimeFormatter.ISO_LOCAL_DATE;

    private Response response;
    private String baseUri;
    private AvailableCalendarResponse lastAvailableCalendarResponse;
    private String cachedCalendarOfficeIds;
    private List<Integer> cachedCalendarServiceIds;
    private List<Integer> cachedCalendarServiceCounts;
    private Integer cachedCalendarServiceId;
    private Integer cachedCalendarServiceCount;
    private String cachedCalendarCaptchaToken;
    private AvailableAppointmentsResponse lastAvailableAppointmentsResponse;
    private ThinnedProcess lastReserveProcess;
    private String confirmProcessId;
    private String confirmAuthKey;
    private int lastOfficeId;
    private int lastServiceId;
    private int lastServiceCount = 1;
    private String lastAppointmentDate;
    private String lastDisplayNumberBeforeCancel;
    private OfficesAndServicesResponse lastOfficesAndServicesResponse;
    private Integer rebookingSourceProcessId;
    private String rebookingSourceAuthKey;
    private String citizenAccessToken;
    /** Sent on available-calendar and reserve-appointment once a captcha step has set it. */
    private String captchaToken;
    private final Map<String, RememberedAppointment> rememberedAppointments = new java.util.LinkedHashMap<>();
    private List<ThinnedProcess> myAppointments = List.of();

    private int parseIntOrFail(String value, String label) {
        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException nfe) {
            throw new AssertionError("Failed to parse integer for " + label + " from value \"" + value + "\"", nfe);
        }
    }
    
    /** Reset this thread's booking state and instance reserve state before each scenario. */
    @Before
    public void clearBookingStateBeforeScenario() {
        clearBookingState();
        lastReserveProcess = null;
        lastAvailableCalendarResponse = null;
        cachedCalendarOfficeIds = null;
        cachedCalendarServiceIds = null;
        cachedCalendarServiceCounts = null;
        cachedCalendarServiceId = null;
        cachedCalendarServiceCount = null;
        cachedCalendarCaptchaToken = null;
        captchaToken = null;
        lastAvailableAppointmentsResponse = null;
        lastAppointmentDate = null;
        lastOfficesAndServicesResponse = null;
        rebookingSourceProcessId = null;
        rebookingSourceAuthKey = null;
        citizenAccessToken = null;
        rememberedAppointments.clear();
        myAppointments = List.of();
    }

    /**
     * Best-effort cancel so a failed scenario does not leave a reserved slot in shared test calendars.
     * Runs before {@code WebDriverQuitHook} ({@code @After} order 9999). Skips already-deleted processes.
     */
    @After(order = 10000)
    public void cancelLeftoverAppointmentAfterScenario() {
        for (RememberedAppointment appointment : rememberedAppointments.values()) {
            cancelProcessQuietly(appointment.processId, appointment.authKey);
        }
        cancelLeftoverAppointmentQuietly();
    }

    /** Clear shared booking/confirm state (process, credentials, URLs). Call before each scenario to avoid cross-scenario leakage. */
    public static void clearBookingState() {
        booking().clear();
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
            Assertions.assertThat(apiResponse).isNotNull();
            Assertions.assertThat(apiResponse.getMeta()).isNotNull();
            Assertions.assertThat(apiResponse.getMeta().getError()).isFalse();
            officesAndServices = apiResponse.getData();
        }
        
        Assertions.assertThat(officesAndServices).isNotNull();
        Assertions.assertThat(officesAndServices.getOffices()).isNotNull();
        Assertions.assertThat(officesAndServices.getServices()).isNotNull();
        Assertions.assertThat(officesAndServices.getRelations()).isNotNull();
        lastOfficesAndServicesResponse = officesAndServices;
    }

    @Then("the offices and services response should not include service {int}")
    public void theOfficesAndServicesResponseShouldNotIncludeService(int serviceId) {
        Assertions.assertThat(lastOfficesAndServicesResponse)
            .as("Request offices-and-services first")
            .isNotNull();
        Assertions.assertThat(lastOfficesAndServicesResponse.getServices())
            .as("offices-and-services services")
            .isNotNull();
        boolean present = lastOfficesAndServicesResponse.getServices().stream()
            .anyMatch(service -> service != null && serviceId == (service.getId() == null ? -1 : service.getId()));
        Assertions.assertThat(present)
            .as("Service %d must stay out of the public offices-and-services catalog", serviceId)
            .isFalse();
    }

    @Then("office {int} should have sharedBookingOfficeIds {string}")
    public void officeShouldHaveSharedBookingOfficeIds(int officeId, String expectedIdsCsv) {
        Assertions.assertThat(lastOfficesAndServicesResponse)
            .as("Request offices-and-services first")
            .isNotNull();
        Office office = findOfficeById(lastOfficesAndServicesResponse, officeId);
        Assertions.assertThat(office)
            .as("Expected office %d in offices-and-services", officeId)
            .isNotNull();
        List<Integer> expected = parseOfficeIdsCsv(expectedIdsCsv);
        Assertions.assertThat(office.getSharedBookingOfficeIds())
            .as("office %d sharedBookingOfficeIds", officeId)
            .containsExactlyInAnyOrderElementsOf(expected);
    }

    @Then("office {int} should use a slot time of {int} minutes and service {int} should take {int} slot")
    public void officeShouldUseSlotTimeAndServiceShouldTakeSlots(
            int officeId, int slotTimeInMinutes, int serviceId, int slots) {
        Assertions.assertThat(lastOfficesAndServicesResponse)
            .as("Request offices-and-services first")
            .isNotNull();
        Office office = findOfficeById(lastOfficesAndServicesResponse, officeId);
        Assertions.assertThat(office)
            .as("Expected office %d in offices-and-services", officeId)
            .isNotNull();
        Assertions.assertThat(office.getSlotTimeInMinutes())
            .as("office %d slotTimeInMinutes", officeId)
            .isEqualTo(slotTimeInMinutes);
        OfficeServiceRelation relation = null;
        if (lastOfficesAndServicesResponse.getRelations() != null) {
            for (OfficeServiceRelation candidate : lastOfficesAndServicesResponse.getRelations()) {
                if (candidate != null
                        && officeId == (candidate.getOfficeId() == null ? -1 : candidate.getOfficeId())
                        && serviceId == (candidate.getServiceId() == null ? -1 : candidate.getServiceId())) {
                    relation = candidate;
                    break;
                }
            }
        }
        relation = java.util.Objects.requireNonNull(
                relation, "relation office " + officeId + " service " + serviceId);
        Assertions.assertThat(relation.getSlots())
            .as("service %d slots at office %d", serviceId, officeId)
            .isEqualTo(slots);
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
        lastAvailableCalendarResponse =
            fetchAvailableCalendar(List.of(officeId), serviceId, serviceCount);
    }

    @When("I request available days for offices {string} and service {int}")
    public void iRequestAvailableDaysForOfficesAndService(String officeIdsCsv, int serviceId) {
        iRequestAvailableDaysForOfficesAndService(officeIdsCsv, serviceId, 1);
    }

    @When("I request available days for offices {string} and service {int} with service count {int}")
    public void iRequestAvailableDaysForOfficesAndService(
            String officeIdsCsv, int serviceId, int serviceCount) {
        List<Integer> officeIds = parseOfficeIdsCsv(officeIdsCsv);
        Assertions.assertThat(officeIds).as("officeIds csv").isNotEmpty();
        lastOfficeId = officeIds.get(0);
        lastServiceId = serviceId;
        lastServiceCount = serviceCount;
        lastAvailableCalendarResponse = fetchAvailableCalendar(officeIds, serviceId, serviceCount);
    }

    @When("I request available days for office {int} and services {string}")
    public void iRequestAvailableDaysForOfficeAndServices(int officeId, String serviceIdsCsv) {
        iRequestAvailableDaysForOfficesAndServices(String.valueOf(officeId), serviceIdsCsv);
    }

    @When("I request available days for offices {string} and services {string}")
    public void iRequestAvailableDaysForOfficesAndServices(String officeIdsCsv, String serviceIdsCsv) {
        List<Integer> officeIds = parseOfficeIdsCsv(officeIdsCsv);
        List<Integer> serviceIds = parseOfficeIdsCsv(serviceIdsCsv);
        Assertions.assertThat(officeIds).as("officeIds csv").isNotEmpty();
        Assertions.assertThat(serviceIds).as("serviceIds csv").isNotEmpty();
        List<Integer> serviceCounts = serviceIds.stream().map(id -> 1).collect(Collectors.toList());
        lastOfficeId = officeIds.get(0);
        lastServiceId = serviceIds.get(0);
        lastServiceCount = serviceCounts.get(0);
        lastAvailableCalendarResponse = fetchAvailableCalendar(officeIds, serviceIds, serviceCounts);
    }

    @When("I request available days for office {int} and services {string} with service counts {string}")
    public void iRequestAvailableDaysForOfficeAndServicesWithCounts(
            int officeId, String serviceIdsCsv, String serviceCountsCsv) {
        List<Integer> serviceIds = parseOfficeIdsCsv(serviceIdsCsv);
        List<Integer> serviceCounts = parseOfficeIdsCsv(serviceCountsCsv);
        Assertions.assertThat(serviceIds).as("serviceIds csv").isNotEmpty();
        Assertions.assertThat(serviceCounts).as("serviceCounts csv").hasSameSizeAs(serviceIds);
        lastOfficeId = officeId;
        lastServiceId = serviceIds.get(0);
        lastServiceCount = serviceCounts.get(0);
        lastAvailableCalendarResponse = fetchAvailableCalendar(List.of(officeId), serviceIds, serviceCounts);
    }

    @Then("the available calendar should include a bookable day for office {int}")
    public void theAvailableCalendarShouldIncludeABookableDayForOffice(int officeId) {
        Assertions.assertThat(response.getStatusCode())
            .as("GET /available-calendar/")
            .isEqualTo(200);
        Assertions.assertThat(lastAvailableCalendarResponse)
            .as("Request available days first")
            .isNotNull();
        Assertions.assertThat(lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId))
            .as("Expected a bookable day with slots for office %d", officeId)
            .isNotBlank();
    }

    @Then("the available calendar should include no bookable day for office {int}")
    public void theAvailableCalendarShouldIncludeNoBookableDayForOffice(int officeId) {
        Assertions.assertThat(response.getStatusCode())
            .as("GET /available-calendar/")
            .isEqualTo(200);
        Assertions.assertThat(lastAvailableCalendarResponse)
            .as("Request available days first")
            .isNotNull();
        Assertions.assertThat(lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId))
            .as("Expected no bookable day for office %d when the appointment no longer fits", officeId)
            .isNull();
    }

    @Then("the available calendar should include appointments for offices {string}")
    public void theAvailableCalendarShouldIncludeAppointmentsForOffices(String officeIdsCsv) {
        int[] officeIds = parseOfficeIdsCsv(officeIdsCsv).stream().mapToInt(Integer::intValue).toArray();
        for (int attempt = 1; attempt <= 8; attempt++) {
            Assertions.assertThat(lastAvailableCalendarResponse)
                .as("Request available days first")
                .isNotNull();
            if (lastAvailableCalendarResponse.hasAppointmentsForAllOffices(officeIds)) {
                return;
            }
            if (cachedCalendarOfficeIds == null
                    || cachedCalendarServiceIds == null
                    || cachedCalendarServiceCounts == null) {
                break;
            }
            if (attempt < 8) {
                ScenarioLogManager.getLogger().info(String.format(
                    "Citizen API calendar is missing an office bucket for %s; requesting it again (%d/8)",
                    officeIdsCsv,
                    attempt));
                try {
                    Thread.sleep(2000);
                } catch (InterruptedException interrupted) {
                    Thread.currentThread().interrupt();
                    break;
                }
                lastAvailableCalendarResponse = fetchAvailableCalendar(
                    parseOfficeIdsCsv(cachedCalendarOfficeIds),
                    cachedCalendarServiceIds,
                    cachedCalendarServiceCounts);
            }
        }
        Assertions.assertThat(lastAvailableCalendarResponse.hasAppointmentsForAllOffices(officeIds))
            .as(
                "Expected available-calendar to include appointment buckets for offices %s (shared booking)",
                officeIdsCsv)
            .isTrue();
    }

    @Then("the available calendar should not include appointments for offices {string}")
    public void theAvailableCalendarShouldNotIncludeAppointmentsForOffices(String officeIdsCsv) {
        Assertions.assertThat(lastAvailableCalendarResponse)
            .as("Request available days first")
            .isNotNull();
        for (int officeId : parseOfficeIdsCsv(officeIdsCsv)) {
            Assertions.assertThat(lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId))
                .as(
                    "Expected available-calendar to have no appointment bucket for office %d",
                    officeId)
                .isNull();
        }
    }

    @Then("timestamps on each available calendar day should not be duplicated across offices")
    public void timestampsOnEachAvailableCalendarDayShouldNotBeDuplicatedAcrossOffices() {
        Assertions.assertThat(lastAvailableCalendarResponse)
            .as("Request available days first")
            .isNotNull();
        if (lastAvailableCalendarResponse.getAvailableDays() == null) {
            return;
        }
        for (AvailableCalendarResponse.CalendarDay day : lastAvailableCalendarResponse.getAvailableDays()) {
            if (day == null || day.getOffices() == null || day.getOffices().size() < 2) {
                continue;
            }
            Set<Long> seen = new HashSet<>();
            for (AvailableCalendarResponse.OfficeSlot office : day.getOffices()) {
                if (office == null || office.getAppointments() == null) {
                    continue;
                }
                for (Long ts : office.getAppointments()) {
                    if (ts == null) {
                        continue;
                    }
                    Assertions.assertThat(seen.add(ts))
                        .as(
                            "Duplicate timestamp %d on day %s across office buckets (round-robin should keep one winner)",
                            ts,
                            day.getDate())
                        .isTrue();
                }
            }
        }
    }

    @When("I request available appointments for the first available day")
    public void iRequestAvailableAppointmentsForTheFirstAvailableDay() {
        if (lastAvailableCalendarResponse == null) {
            throw new IllegalStateException("Request available days first.");
        }
        String date = lastAvailableCalendarResponse.getFirstAvailableDay();
        if (date == null) {
            throw new IllegalStateException("No available day in last response.");
        }
        if (cachedCalendarServiceIds != null
                && cachedCalendarServiceCounts != null
                && cachedCalendarServiceIds.size() > 1
                && cachedCalendarServiceIds.size() == cachedCalendarServiceCounts.size()) {
            iRequestAvailableAppointmentsForDateOfficeAndServices(
                    date, lastOfficeId, cachedCalendarServiceIds, cachedCalendarServiceCounts);
            return;
        }
        iRequestAvailableAppointmentsForDateOfficeAndService(date, lastOfficeId, lastServiceId, lastServiceCount);
    }

    @When("I request available appointments for the first available day for office {int}")
    public void iRequestAvailableAppointmentsForTheFirstAvailableDayForOffice(int officeId) {
        if (lastAvailableCalendarResponse == null) {
            throw new IllegalStateException("Request available days first.");
        }
        String date = lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId);
        if (date == null) {
            throw new IllegalStateException(
                "No available day with appointments for office " + officeId + " in last calendar response.");
        }
        iRequestAvailableAppointmentsForDateOfficeAndService(date, officeId, lastServiceId, lastServiceCount);
    }

    /**
     * ZMSKVR-88 / ZMSKVR-472: day after V19 Ruppertstraße range (V43) has one internet seat on Pass 172.
     */
    @When("I request available appointments for the single-seat Passkalender day for office {int} and service {int}")
    public void iRequestAvailableAppointmentsForTheSingleSeatPasskalenderDay(int officeId, int serviceId) {
        String date = BerlinTime.singleSeatDayAfterV19RuppertstrasseRange().format(DATE_FORMAT);
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API using single-seat Passkalender day %s (after V19 range) officeId=%d serviceId=%d",
            date,
            officeId,
            serviceId
        ));
        iRequestAvailableAppointmentsForDateOfficeAndService(date, officeId, serviceId, 1);
    }

    @When("I request available appointments for date {string}, office {int} and service {int}")
    public void iRequestAvailableAppointmentsForDateOfficeAndService(String date, int officeId, int serviceId) {
        iRequestAvailableAppointmentsForDateOfficeAndService(date, officeId, serviceId, 1);
    }

    @When("I request available appointments for date {string}, office {int} and service {int} with service count {int}")
    public void iRequestAvailableAppointmentsForDateOfficeAndService(
            String date, int officeId, int serviceId, int serviceCount) {
        iRequestAvailableAppointmentsForDateOfficeAndServices(
                date, officeId, List.of(serviceId), List.of(serviceCount));
    }

    private void iRequestAvailableAppointmentsForDateOfficeAndServices(
            String date, int officeId, List<Integer> serviceIds, List<Integer> serviceCounts) {
        lastOfficeId = officeId;
        lastServiceId = serviceIds.get(0);
        lastServiceCount = serviceCounts.get(0);
        lastAppointmentDate = date;

        // Free-slot timestamps are only inlined for slotsStartDate..slotsEndDate (often "today").
        // Ask for this exact day so later bookable days (e.g. V43 single-seat) are hydrated.
        lastAvailableCalendarResponse =
            fetchAvailableCalendar(List.of(officeId), serviceIds, serviceCounts, date, date);

        AvailableAppointmentsResponse appointments =
            lastAvailableCalendarResponse.getAppointmentsForDayAndOffice(date, officeId);
        lastAvailableAppointmentsResponse = appointments;

        int officeCount = appointments.getOffices() != null ? appointments.getOffices().size() : 0;
        int timestampCount = appointments.futureAppointmentTimestamps().size();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API calendar slots for date=%s, officeId=%d, serviceIds=%s: %d office(s), %d timestamp(s)",
            date,
            officeId,
            serviceIds,
            officeCount,
            timestampCount
        ));
    }

    @When("I solve the captcha")
    public void iSolveTheCaptcha() {
        baseUri = baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri();
        captchaToken = CaptchaClient.solve(baseUri);
        ScenarioLogManager.getLogger().info("Citizen API captcha solved");
    }

    @When("I use an expired captcha token")
    public void iUseAnExpiredCaptchaToken() {
        captchaToken = CaptchaClient.expiredToken();
        ScenarioLogManager.getLogger().info("Citizen API using an expired captcha token");
    }

    @When("I clear the captcha token")
    public void iClearTheCaptchaToken() {
        captchaToken = null;
        ScenarioLogManager.getLogger().info("Citizen API captcha token cleared");
    }

    @Then("office {int} should require captcha")
    public void officeShouldRequireCaptcha(int officeId) {
        Assertions.assertThat(scopeValue(officeId, "captchaActivatedRequired"))
            .as("office %d scope.captchaActivatedRequired", officeId)
            .isEqualTo(true);
    }

    @Then("office {int} should not require captcha")
    public void officeShouldNotRequireCaptcha(int officeId) {
        Assertions.assertThat(Boolean.TRUE.equals(scopeValue(officeId, "captchaActivatedRequired")))
            .as("office %d scope.captchaActivatedRequired", officeId)
            .isFalse();
    }

    @Then("office {int} rebooking should be disabled")
    public void officeRebookingShouldBeDisabled(int officeId) {
        Assertions.assertThat(Boolean.TRUE.equals(scopeValue(officeId, "rebookingDisabled")))
            .as("office %d scope.rebookingDisabled", officeId)
            .isTrue();
    }

    @Then("office {int} rebooking should not be disabled")
    public void officeRebookingShouldNotBeDisabled(int officeId) {
        Assertions.assertThat(Boolean.TRUE.equals(scopeValue(officeId, "rebookingDisabled")))
            .as("office %d scope.rebookingDisabled", officeId)
            .isFalse();
    }

    @When("I reserve an appointment with the first available slot")
    public void iReserveAnAppointmentWithTheFirstAvailableSlot() {
        reserveFirstAvailableSlot(false, true);
    }

    /**
     * Book every free internet seat for the office/service (refetching the calendar between
     * reserves) so the UI sees a fully empty calendar. Fails when nothing was reserved or a
     * bookable day remains.
     */
    @When("I reserve every available appointment for office {int} and service {int}")
    public void iReserveEveryAvailableAppointmentForOfficeAndService(int officeId, int serviceId) {
        iRequestAvailableDaysForOfficeAndService(officeId, serviceId, 1);
        int reserved = 0;
        for (int attempt = 0; attempt < 40; attempt++) {
            if (lastAvailableCalendarResponse == null) {
                iRequestAvailableDaysForOfficeAndService(officeId, serviceId, 1);
            }
            String date =
                    lastAvailableCalendarResponse == null
                            ? null
                            : lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId);
            if (date == null) {
                break;
            }
            iRequestAvailableAppointmentsForDateOfficeAndService(date, officeId, serviceId, 1);
            List<Long> timestamps =
                    lastAvailableAppointmentsResponse == null
                            ? List.of()
                            : new ArrayList<>(lastAvailableAppointmentsResponse.futureAppointmentTimestamps());
            if (timestamps.isEmpty()) {
                iRequestAvailableDaysForOfficeAndService(officeId, serviceId, 1);
                String again =
                        lastAvailableCalendarResponse == null
                                ? null
                                : lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId);
                if (again == null) {
                    break;
                }
                iRequestAvailableAppointmentsForDateOfficeAndService(again, officeId, serviceId, 1);
                timestamps =
                        lastAvailableAppointmentsResponse == null
                                ? List.of()
                                : new ArrayList<>(
                                        lastAvailableAppointmentsResponse.futureAppointmentTimestamps());
                if (timestamps.isEmpty()) {
                    break;
                }
            }
            // Soft reserve: 404/noAppointment means the seat was taken (parallel browser) or gone.
            reserveFirstAvailableSlot(false, false);
            if (response == null || response.getStatusCode() != 200) {
                ScenarioLogManager.getLogger()
                        .info(
                                "Citizen API reserve-every stopped for office {} after status {}",
                                officeId,
                                response == null ? null : response.getStatusCode());
                iRequestAvailableDaysForOfficeAndService(officeId, serviceId, 1);
                if (lastAvailableCalendarResponse == null
                        || lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId) == null) {
                    break;
                }
                continue;
            }
            iRememberTheCurrentAppointmentAs("feuerwache-bulk-" + reserved);
            reserved++;
            iRequestAvailableDaysForOfficeAndService(officeId, serviceId, 1);
        }
        String remainingDay =
                lastAvailableCalendarResponse == null
                        ? null
                        : lastAvailableCalendarResponse.getFirstAvailableDayForOffice(officeId);
        if (reserved == 0) {
            Assertions.fail(
                    "Office %d: reserved nothing; bookable day is %s",
                    officeId,
                    remainingDay == null ? "absent (calendar was already empty)" : remainingDay);
        }
        Assertions.assertThat(remainingDay)
                .as(
                        "Office %d still has a bookable day after reserving %d appointment(s)",
                        officeId,
                        reserved)
                .isNull();
        ScenarioLogManager.getLogger()
                .info("Citizen API reserved {} appointment(s) for office {} service {}", reserved, officeId, serviceId);
    }

    @Then("office {int} infoForAllAppointments should contain {string}")
    public void officeInfoForAllAppointmentsShouldContain(int officeId, String fragment) {
        String needle = TestDataHelper.transformTestData(fragment);
        Object raw = scopeValue(officeId, "infoForAllAppointments");
        Assertions.assertThat(raw)
                .as("office %d infoForAllAppointments", officeId)
                .isNotNull();
        Assertions.assertThat(String.valueOf(raw))
                .as("office %d infoForAllAppointments", officeId)
                .contains(needle);
    }

    /**
     * ZMSKVR-1051 / ZMSKVR-1309: GET /appointment/ scope.hint + infoForAppointment belong to the
     * same Ruppertstraße Wartebereich (WB03 or WB04).
     */
    @Then("the appointment scope hint and infoForAppointment should match a Ruppertstraße Wartezone")
    public void theAppointmentScopeHintAndInfoForAppointmentShouldMatchARuppertstrasseWartezone() {
        String hint = response.jsonPath().getString("scope.hint");
        String info = response.jsonPath().getString("scope.infoForAppointment");
        Assertions.assertThat(hint).as("scope.hint").isNotBlank();
        Assertions.assertThat(info).as("scope.infoForAppointment").isNotBlank();
        String combined = hint + " " + info;
        String code = RuppertstrasseWartezoneHints.detectCode(combined);
        Assertions.assertThat(code)
                .as("scope.hint=%s infoForAppointment=%s", hint, info)
                .isNotNull();
        Assertions.assertThat(hint).contains(RuppertstrasseWartezoneHints.zoneFor(code));
        Assertions.assertThat(info).contains(RuppertstrasseWartezoneHints.hintFor(code));
        Assertions.assertThat(info)
                .doesNotContain(RuppertstrasseWartezoneHints.hintFor(RuppertstrasseWartezoneHints.otherCode(code)));
        Assertions.assertThat(info).contains("<a href=\"" + RuppertstrasseWartezoneHints.HINT_HREF + "\"");
        RuppertstrasseWartezoneHints.rememberApiWartezoneCode(code);
        ScenarioLogManager.getLogger()
                .info("Citizen API appointment matched Ruppertstraße Wartezone {}", code);
    }

    /**
     * ZMSKVR-924 / ZMSKVR-1019: GET /appointment/ scope.hint + infoForAppointment belong to the
     * same Führerscheinstelle Schalter (FS-A or FS-B).
     */
    @Then("the appointment scope hint and infoForAppointment should match a Führerscheinstelle Schalter")
    public void theAppointmentScopeHintAndInfoForAppointmentShouldMatchAFuehrerscheinstelleSchalter() {
        String hint = response.jsonPath().getString("scope.hint");
        String info = response.jsonPath().getString("scope.infoForAppointment");
        Assertions.assertThat(hint).as("scope.hint").isNotBlank();
        Assertions.assertThat(info).as("scope.infoForAppointment").isNotBlank();
        String code = FuehrerscheinstelleScopeHints.detectCode(hint + " " + info);
        Assertions.assertThat(code)
                .as("scope.hint=%s infoForAppointment=%s", hint, info)
                .isNotNull();
        Assertions.assertThat(hint).contains(FuehrerscheinstelleScopeHints.zoneFor(code));
        Assertions.assertThat(info).contains(FuehrerscheinstelleScopeHints.hintFor(code));
        Assertions.assertThat(info)
                .doesNotContain(
                        FuehrerscheinstelleScopeHints.hintFor(
                                FuehrerscheinstelleScopeHints.otherCode(code)));
        FuehrerscheinstelleScopeHints.rememberApiSchalterCode(code);
        ScenarioLogManager.getLogger()
                .info("Citizen API appointment matched Führerscheinstelle Schalter {}", code);
    }

    /** ZMSKVR-924 / ZMSKVR-1019: second same-timestamp reserve lands on the other Schalter. */
    @Then("the appointment scope should be the other Führerscheinstelle Schalter")
    public void theAppointmentScopeShouldBeTheOtherFuehrerscheinstelleSchalter() {
        String first = FuehrerscheinstelleScopeHints.rememberedApiSchalterCode();
        Assertions.assertThat(first)
                .as("first Schalter must be remembered before the second reserve")
                .isNotBlank();
        String expected = FuehrerscheinstelleScopeHints.otherCode(first);
        String hint = response.jsonPath().getString("scope.hint");
        String info = response.jsonPath().getString("scope.infoForAppointment");
        String code = FuehrerscheinstelleScopeHints.detectCode(hint + " " + info);
        Assertions.assertThat(code)
                .as("second reserve scope.hint=%s infoForAppointment=%s", hint, info)
                .isEqualTo(expected);
        Assertions.assertThat(hint)
                .as("second reserve scope.hint=%s", hint)
                .contains(FuehrerscheinstelleScopeHints.zoneFor(expected));
        Assertions.assertThat(info).contains(FuehrerscheinstelleScopeHints.hintFor(expected));
        FuehrerscheinstelleScopeHints.rememberApiSchalterCode(code);
        ScenarioLogManager.getLogger()
                .info("Citizen API second reserve switched Schalter {} → {}", first, code);
    }

    /** ZMSKVR-1051 / ZMSKVR-1309: second same-timestamp reserve lands on the other Wartebereich. */
    @Then("the appointment scope should be the other Ruppertstraße Wartezone")
    public void theAppointmentScopeShouldBeTheOtherRuppertstrasseWartezone() {
        String first = RuppertstrasseWartezoneHints.rememberedApiWartezoneCode();
        Assertions.assertThat(first)
                .as("first Wartezone must be remembered before the second reserve")
                .isNotBlank();
        String expected = RuppertstrasseWartezoneHints.otherCode(first);
        String hint = response.jsonPath().getString("scope.hint");
        String info = response.jsonPath().getString("scope.infoForAppointment");
        String code = RuppertstrasseWartezoneHints.detectCode(hint + " " + info);
        Assertions.assertThat(code)
                .as("second reserve scope.hint=%s infoForAppointment=%s", hint, info)
                .isEqualTo(expected);
        Assertions.assertThat(hint)
                .as("second reserve scope.hint=%s", hint)
                .contains(RuppertstrasseWartezoneHints.zoneFor(expected));
        Assertions.assertThat(info).contains(RuppertstrasseWartezoneHints.hintFor(expected));
        RuppertstrasseWartezoneHints.rememberApiWartezoneCode(code);
        ScenarioLogManager.getLogger()
                .info("Citizen API second reserve switched Wartezone {} → {}", first, code);
    }

    /**
     * ZMSKVR-1051 / ZMSKVR-1309: with one seat on WB03 and WB04, a second reserve of the same
     * timestamp succeeds on the other scope (unlike the single-seat Passkalender race).
     */
    @When("I reserve the same appointment slot again for the other Wartebereich")
    public void iReserveTheSameAppointmentSlotAgainForTheOtherWartebereich() {
        ThinnedProcess first = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (first == null || first.getTimestamp() == null || first.getTimestamp() <= 0) {
            throw new IllegalStateException("Reserve a slot first so the same timestamp can be reserved again.");
        }
        int officeId = first.getOfficeId() != null ? first.getOfficeId() : lastOfficeId;
        int serviceId = first.getServiceId() != null ? first.getServiceId() : lastServiceId;
        if (officeId <= 0 || serviceId <= 0) {
            throw new IllegalStateException("First reserve has no officeId/serviceId for the second Wartebereich.");
        }
        int serviceCount = lastServiceCount > 0 ? lastServiceCount : 1;
        long timestamp = first.getTimestamp();

        ReserveAppointmentRequest body = new ReserveAppointmentRequest();
        body.setTimestamp(timestamp);
        body.setOfficeId(officeId);
        body.setServiceId(List.of(serviceId));
        body.setServiceCount(List.of(serviceCount));
        if (captchaToken != null && !captchaToken.isBlank()) {
            body.setCaptchaToken(captchaToken);
        }

        ScenarioLogManager.getLogger().info(String.format(
                "Citizen API /reserve-appointment/ other Wartebereich timestamp=%d officeId=%d serviceId=%d",
                timestamp,
                officeId,
                serviceId));

        response = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
                .contentType("application/json")
                .body(body)
                .when()
                .post("/reserve-appointment/");
        CommonApiSteps.setResponse(response);

        String reserveBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
                "Citizen API /reserve-appointment/ other Wartebereich status=%d body=%s",
                response.getStatusCode(),
                reserveBody.length() > 1250 ? reserveBody.substring(0, 1250) + "..." : reserveBody));
        response.then().statusCode(200);

        ThinnedProcess reserved;
        try {
            reserved = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            reserved = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(reserved).as("second Wartebereich reserve").isNotNull();
        Assertions.assertThat(reserved.getProcessId()).isNotNull();
        Assertions.assertThat(reserved.getAuthKey()).isNotBlank();
        Assertions.assertThat(reserved.getProcessId()).isNotEqualTo(first.getProcessId());
        lastReserveProcess = reserved;
        setLastReserveProcess(reserved);
    }

    @When("I attempt to reserve an appointment with the first available slot")
    public void iAttemptToReserveAnAppointmentWithTheFirstAvailableSlot() {
        reserveFirstAvailableSlot(false, false);
    }

    @When("I reserve an appointment with the first available slot using the current appointment as source")
    public void iReserveAnAppointmentWithTheFirstAvailableSlotUsingTheCurrentAppointmentAsSource() {
        reserveFirstAvailableSlot(true, true);
    }

    @When("I attempt to reserve an appointment with the first available slot using the current appointment as source")
    public void iAttemptToReserveAnAppointmentWithTheFirstAvailableSlotUsingTheCurrentAppointmentAsSource() {
        reserveFirstAvailableSlot(true, false);
    }

    private void reserveFirstAvailableSlot(boolean useCurrentAppointmentAsSource, boolean expectSuccess) {
        if (lastAvailableAppointmentsResponse == null) {
            throw new IllegalStateException("Request available appointments first (for date, office, service).");
        }
        List<Long> timestamps = new ArrayList<>(
            lastAvailableAppointmentsResponse.futureAppointmentTimestamps());
        for (int day = 0; timestamps.isEmpty() && day < 3; day++) {
            if (!loadNextCalendarDayWithSlots()) {
                break;
            }
            timestamps = new ArrayList<>(lastAvailableAppointmentsResponse.futureAppointmentTimestamps());
        }
        if (timestamps.isEmpty()) {
            ScenarioLogManager.getLogger().error("No appointment timestamps found in lastAvailableAppointmentsResponse "
                + "for officeId=" + lastOfficeId + ", serviceId=" + lastServiceId);
            throw new IllegalStateException("No appointment timestamps in last response.");
        }
        Integer sourceProcessId = null;
        String sourceAuthKey = null;
        if (useCurrentAppointmentAsSource) {
            ThinnedProcess source = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
            if (source == null || source.getProcessId() == null || source.getAuthKey() == null) {
                throw new IllegalStateException("Confirm an appointment first to use it as rebooking source.");
            }
            sourceProcessId = source.getProcessId();
            sourceAuthKey = source.getAuthKey();
            rebookingSourceProcessId = sourceProcessId;
            rebookingSourceAuthKey = sourceAuthKey;
            ScenarioLogManager.getLogger().info(String.format(
                "Citizen API rebooking reserve using source processId=%d", sourceProcessId
            ));
        }
        int refetches = 0;
        int sameSlotAttempts = 0;
        Response reserveResponse = null;
        for (int i = 0; i < timestamps.size(); ) {
            Long timestamp = timestamps.get(i);
            ReserveAppointmentRequest body = new ReserveAppointmentRequest();
            body.setTimestamp(timestamp);
            body.setOfficeId(lastOfficeId);
            List<Integer> reserveServiceIds = reserveServiceIds();
            List<Integer> reserveServiceCounts = reserveServiceCounts();
            body.setServiceId(reserveServiceIds);
            body.setServiceCount(reserveServiceCounts);
            if (useCurrentAppointmentAsSource) {
                body.setSourceProcessId(sourceProcessId);
                body.setSourceAuthKey(sourceAuthKey);
            }
            if (captchaToken != null && !captchaToken.isBlank()) {
                body.setCaptchaToken(captchaToken);
            }
            reserveResponse = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
                .contentType("application/json")
                .body(body)
            .when()
                .post("/reserve-appointment/");
            response = reserveResponse;
            CommonApiSteps.setResponse(reserveResponse);

            String reserveBody = response.asString();
            ScenarioLogManager.getLogger().info(String.format(
                "Citizen API /reserve-appointment/ timestamp=%d status=%d body=%s",
                timestamp,
                response.getStatusCode(),
                reserveBody.length() > 1250 ? reserveBody.substring(0, 1250) + "..." : reserveBody
            ));
            if (!expectSuccess || response.getStatusCode() == 200) {
                break;
            }
            if (reserveBody.contains("unknownError") && sameSlotAttempts < 2) {
                sameSlotAttempts++;
                ScenarioLogManager.getLogger().info(String.format(
                    "Citizen API slot timestamp=%d returned unknownError; retrying the same slot (%d/2)",
                    timestamp,
                    sameSlotAttempts
                ));
                continue;
            }
            sameSlotAttempts = 0;
            if (slotNoLongerAvailable(response) && i < timestamps.size() - 1) {
                ScenarioLogManager.getLogger().info(String.format(
                    "Citizen API slot timestamp=%d is reserved or booked; trying the next available slot",
                    timestamp
                ));
                i++;
                continue;
            }
            if (slotNoLongerAvailable(response) && refetches < 3) {
                refetches++;
                int added = appendFreshTimestamps(timestamps);
                ScenarioLogManager.getLogger().info(String.format(
                    "Citizen API slot timestamp=%d was the last known slot; refetched %d new timestamp(s) (%d/3)",
                    timestamp,
                    added,
                    refetches
                ));
                if (added > 0) {
                    i++;
                    continue;
                }
                if (loadNextCalendarDayWithSlots()) {
                    int fromNextDay = appendFreshTimestamps(timestamps);
                    if (fromNextDay > 0) {
                        i++;
                        continue;
                    }
                }
            }
            response = reserveResponse;
            CommonApiSteps.setResponse(reserveResponse);
            response.then().statusCode(200);
            i++;
        }
        response = reserveResponse;
        CommonApiSteps.setResponse(reserveResponse);
        if (!expectSuccess) {
            if (response.getStatusCode() == 200) {
                rememberReservationForCleanup(response);
            }
            return;
        }
        response.then().statusCode(200);

        // Reserve endpoint may return plain ThinnedProcess or an ApiResponse-wrapped payload
        ThinnedProcess reserved;
        try {
            reserved = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            reserved = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(reserved)
            .as("reserve-appointment response payload must deserialize")
            .isNotNull();
        Assertions.assertThat(reserved.getProcessId()).isNotNull();
        Assertions.assertThat(reserved.getAuthKey()).isNotNull();
        Assertions.assertThat(reserved.getOfficeId()).isEqualTo(lastOfficeId);
        // Skip `displayNumber` (it can vary across office/provider formatting) and skip `captchaToken`
        // (captcha is disabled for our test locations).
        Assertions.assertThat(reserved.getServiceId()).isNotNull();
        Assertions.assertThat(reserved.getTimestamp())
            .as("reserve-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(reserved.getTimestamp())
            .as("reserve-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);

        lastReserveProcess = reserved;
        if (lastReserveProcess != null) {
            setLastReserveProcess(lastReserveProcess);
        }
    }

    /**
     * ZMSKVR-88 / ZMSKVR-472: second citizen snatches the exact epoch the UI selected (before Weiter/reserve).
     * Prefer this over two browsers in CI — same race, one process to cancel afterwards.
     */
    @When("I reserve the remembered citizenview timeslot via the Citizen API for office {int} and service {int}")
    public void iReserveTheRememberedCitizenviewTimeslotViaTheCitizenApi(int officeId, int serviceId) {
        String raw = TestDataHelper.getTestData("citizenview_selected_slot_timestamp");
        if (raw == null || raw.isBlank()) {
            throw new IllegalStateException(
                    "Remember the selected citizenview timeslot first (citizenview_selected_slot_timestamp).");
        }
        long timestamp;
        try {
            timestamp = Long.parseLong(raw.trim());
        } catch (NumberFormatException e) {
            throw new IllegalStateException(
                    "citizenview_selected_slot_timestamp is not a long: \"" + raw + "\"", e);
        }
        if (timestamp <= 0) {
            throw new IllegalStateException("Remembered timeslot timestamp must be > 0, got " + timestamp);
        }
        reserveExactTimeslot(timestamp, officeId, serviceId, 1);
    }

    private void reserveExactTimeslot(long timestamp, int officeId, int serviceId, int serviceCount) {
        lastOfficeId = officeId;
        lastServiceId = serviceId;
        lastServiceCount = serviceCount;
        ReserveAppointmentRequest body = new ReserveAppointmentRequest();
        body.setTimestamp(timestamp);
        body.setOfficeId(officeId);
        body.setServiceId(List.of(serviceId));
        body.setServiceCount(List.of(serviceCount));
        if (captchaToken != null && !captchaToken.isBlank()) {
            body.setCaptchaToken(captchaToken);
        }

        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /reserve-appointment/ exact timestamp=%d officeId=%d serviceId=%d (UI race snatch)",
            timestamp,
            officeId,
            serviceId
        ));

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(body)
        .when()
            .post("/reserve-appointment/");
        CommonApiSteps.setResponse(response);

        String reserveBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /reserve-appointment/ timestamp=%d status=%d body=%s",
            timestamp,
            response.getStatusCode(),
            reserveBody.length() > 1250 ? reserveBody.substring(0, 1250) + "..." : reserveBody
        ));
        response.then().statusCode(200);

        ThinnedProcess reserved;
        try {
            reserved = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            reserved = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(reserved)
            .as("reserve-appointment response payload must deserialize")
            .isNotNull();
        Assertions.assertThat(reserved.getProcessId()).isNotNull();
        Assertions.assertThat(reserved.getAuthKey()).isNotNull();
        Assertions.assertThat(reserved.getOfficeId()).isEqualTo(officeId);
        Assertions.assertThat(reserved.getTimestamp())
            .as("reserved timestamp must match the remembered UI slot")
            .isEqualTo(timestamp);

        lastReserveProcess = reserved;
        setLastReserveProcess(lastReserveProcess);
    }

    /**
     * ZMSKVR-88 / ZMSKVR-472: second citizen tries the same timestamp the previous reserve already took.
     * Leaves {@code lastReserveProcess} untouched so cleanup still cancels the winning reservation.
     */
    @When("I attempt to reserve the same appointment slot again")
    public void iAttemptToReserveTheSameAppointmentSlotAgain() {
        ThinnedProcess first = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (first == null || first.getTimestamp() == null || first.getTimestamp() <= 0) {
            throw new IllegalStateException("Reserve a slot first so the same timestamp can be attempted again.");
        }
        int officeId = first.getOfficeId() != null ? first.getOfficeId() : lastOfficeId;
        int serviceId = first.getServiceId() != null ? first.getServiceId() : lastServiceId;
        if (officeId <= 0 || serviceId <= 0) {
            throw new IllegalStateException("First reserve has no officeId/serviceId for the duplicate attempt.");
        }
        int serviceCount = lastServiceCount > 0 ? lastServiceCount : 1;

        ReserveAppointmentRequest body = new ReserveAppointmentRequest();
        body.setTimestamp(first.getTimestamp());
        body.setOfficeId(officeId);
        body.setServiceId(List.of(serviceId));
        body.setServiceCount(List.of(serviceCount));
        if (captchaToken != null && !captchaToken.isBlank()) {
            body.setCaptchaToken(captchaToken);
        }

        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /reserve-appointment/ duplicate attempt timestamp=%d officeId=%d serviceId=%d",
            first.getTimestamp(),
            officeId,
            serviceId
        ));

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(body)
        .when()
            .post("/reserve-appointment/");
        CommonApiSteps.setResponse(response);

        String reserveBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /reserve-appointment/ duplicate status=%d body=%s",
            response.getStatusCode(),
            reserveBody.length() > 1250 ? reserveBody.substring(0, 1250) + "..." : reserveBody
        ));
    }

    /**
     * A failed reserve attempt can still return 200 and hold a slot. Keep that process so
     * {@link #cancelLeftoverAppointmentQuietly()} can cancel it, without changing the feature's assertions.
     */
    private void rememberReservationForCleanup(Response reserveResponse) {
        try {
            ThinnedProcess reserved = null;
            try {
                reserved = reserveResponse.as(ThinnedProcess.class);
            } catch (Exception ignored) {
                reserved = null;
            }
            if (reserved == null || reserved.getProcessId() == null || reserved.getAuthKey() == null) {
                reserved = parseDataResponse(reserveResponse, ThinnedProcess.class);
            }
            if (reserved != null && reserved.getProcessId() != null && reserved.getAuthKey() != null) {
                setLastReserveProcess(reserved);
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn(
                "Could not store an unexpected successful reservation for cleanup: " + e
            );
        }
    }

    /** Use the next calendar day that still has slots for the current office. */
    private boolean loadNextCalendarDayWithSlots() {
        if (lastAvailableCalendarResponse == null || lastAvailableCalendarResponse.getAvailableDays() == null) {
            return false;
        }
        String current = lastAppointmentDate;
        String nextDate = null;
        for (AvailableCalendarResponse.CalendarDay day : lastAvailableCalendarResponse.getAvailableDays()) {
            if (day == null || day.getDate() == null || day.getOffices() == null) {
                continue;
            }
            if (current != null && day.getDate().compareTo(current) <= 0) {
                continue;
            }
            for (AvailableCalendarResponse.OfficeSlot office : day.getOffices()) {
                if (office != null
                        && office.matchesOfficeId(lastOfficeId)
                        && office.getAppointments() != null
                        && !office.getAppointments().isEmpty()) {
                    nextDate = day.getDate();
                    break;
                }
            }
            if (nextDate != null) {
                break;
            }
        }
        if (nextDate == null) {
            return false;
        }
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API day %s has no free slot for office %d; using %s",
            current,
            lastOfficeId,
            nextDate));
        // Keep the original multi-service selection; a single-service refetch would
        // overwrite cachedCalendarServiceIds and shrink the later reserve.
        iRequestAvailableAppointmentsForDateOfficeAndServices(
            nextDate, lastOfficeId, reserveServiceIds(), reserveServiceCounts());
        return true;
    }

    /** Ask the calendar again and append timestamps this scenario has not tried yet. */
    private int appendFreshTimestamps(List<Long> timestamps) {
        AvailableCalendarResponse calendar =
            fetchAvailableCalendar(List.of(lastOfficeId), reserveServiceIds(), reserveServiceCounts());
        if (calendar == null || calendar.getAvailableDays() == null) {
            return 0;
        }
        long minFuture = System.currentTimeMillis() / 1000 + 60;
        int added = 0;
        for (AvailableCalendarResponse.CalendarDay day : calendar.getAvailableDays()) {
            if (day == null || day.getOffices() == null) {
                continue;
            }
            for (AvailableCalendarResponse.OfficeSlot office : day.getOffices()) {
                if (office == null || !office.matchesOfficeId(lastOfficeId) || office.getAppointments() == null) {
                    continue;
                }
                for (Long ts : office.getAppointments()) {
                    if (ts != null && ts > minFuture && !timestamps.contains(ts)) {
                        timestamps.add(ts);
                        added++;
                    }
                }
            }
        }
        return added;
    }

    /** Service ids for reserve / calendar retries — prefer the full multi-service selection. */
    private List<Integer> reserveServiceIds() {
        if (cachedCalendarServiceIds != null && !cachedCalendarServiceIds.isEmpty()) {
            return cachedCalendarServiceIds;
        }
        return List.of(lastServiceId);
    }

    private List<Integer> reserveServiceCounts() {
        List<Integer> ids = reserveServiceIds();
        if (cachedCalendarServiceCounts != null
                && cachedCalendarServiceCounts.size() == ids.size()) {
            return cachedCalendarServiceCounts;
        }
        if (ids.size() == 1) {
            return List.of(lastServiceCount);
        }
        List<Integer> counts = new ArrayList<>(ids.size());
        for (int i = 0; i < ids.size(); i++) {
            counts.add(1);
        }
        return counts;
    }

    /** Parallel scenarios share the calendar, so the first slot can already be reserved. */
    private static boolean slotNoLongerAvailable(Response reserveResponse) {
        String body = reserveResponse.asString();
        return body.contains("appointmentNotAvailable") || body.contains("unknownError");
    }

    @When("I preconfirm the appointment")
    public void iPreconfirmTheAppointment() {
        postPreconfirm(true);
    }

    @When("I attempt to preconfirm the appointment")
    public void iAttemptToPreconfirmTheAppointment() {
        postPreconfirm(false);
    }

    private void postPreconfirm(boolean expectSuccess) {
        ThinnedProcess process = requireCurrentProcess();
        Integer pid = process.getProcessId();
        String auth = process.getAuthKey();
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
            preconfirmBody.length() > 1250 ? preconfirmBody.substring(0, 1250) + "..." : preconfirmBody
        ));
        if (!expectSuccess) {
            return;
        }
        response.then().statusCode(200);

        ThinnedProcess updated;
        try {
            updated = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            updated = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(updated)
            .as("preconfirm-appointment response payload must deserialize")
            .isNotNull();
        Assertions.assertThat(updated.getProcessId()).isEqualTo(pid);
        Assertions.assertThat(updated.getAuthKey()).isEqualTo(auth);
        Assertions.assertThat(updated.getOfficeId()).isEqualTo(lastOfficeId);
        Assertions.assertThat(updated.getServiceId()).isEqualTo(process.getServiceId());
        Assertions.assertThat(updated.getTimestamp())
            .as("preconfirm-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(updated.getTimestamp())
            .as("preconfirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);

        lastReserveProcess = updated;
        setLastReserveProcess(updated);
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
            confirmBody.length() > 1250 ? confirmBody.substring(0, 1250) + "..." : confirmBody
        ));
        response.then().statusCode(200);

        ThinnedProcess confirmed;
        try {
            confirmed = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            confirmed = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(confirmed)
            .as("confirm-appointment response payload must deserialize")
            .isNotNull();
        Integer expectedPid = parseIntOrFail(processId, "processId");
        Assertions.assertThat(confirmed.getProcessId()).isEqualTo(expectedPid);
        Assertions.assertThat(confirmed.getAuthKey()).isEqualTo(authKey);
        // Prefer scenario requested office (via lastOfficeId), but fall back to the last preconfirmed payload.
        Integer expectedOfficeId = lastReserveProcess != null ? lastReserveProcess.getOfficeId() : lastOfficeId;
        Assertions.assertThat(confirmed.getOfficeId())
            .as("confirmed appointment should land at expected office")
            .isEqualTo(expectedOfficeId);
        Assertions.assertThat(confirmed.getServiceId()).isNotNull();
        Assertions.assertThat(confirmed.getTimestamp())
            .as("confirm-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(confirmed.getTimestamp())
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
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getProcessId()).isNotNull();
        Assertions.assertThat(process.getAuthKey()).isNotNull();
        Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        Assertions.assertThat(process.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(process.getTimestamp())
            .as("reserve-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        assertScopeProviderGeoPresent("reserve-appointment");
        // Skip `displayNumber` (varies) and `captchaToken` (captcha disabled in test data).
    }

    @When("I update the appointment with contact details and customTextfield {string}")
    public void iUpdateTheAppointmentWithContactDetailsAndCustomTextfield(String customTextfield) {
        postAppointmentUpdate(scenarioContactFamilyName(), scenarioContactEmail(), customTextfield != null ? customTextfield : "", true, false);
    }

    @When("I update the appointment with family name {string} and customTextfield {string}")
    public void iUpdateTheAppointmentWithFamilyNameAndCustomTextfield(String familyName, String customTextfield) {
        String email = familyName.toLowerCase(Locale.ROOT).replaceAll("[^a-z0-9]", "") + "@mailinator.com";
        postAppointmentUpdate(familyName, email, customTextfield != null ? customTextfield : "", true, false);
    }

    @When("I update the appointment with contact details and customTextfield {string} as the logged-in citizen")
    public void iUpdateTheAppointmentWithContactDetailsAndCustomTextfieldAsTheLoggedInCitizen(String customTextfield) {
        postAppointmentUpdate(scenarioContactFamilyName(), scenarioContactEmail(), customTextfield != null ? customTextfield : "", true, true);
    }

    @When("I update the appointment with contact details and telephone {string} as the logged-in citizen")
    public void iUpdateTheAppointmentWithContactDetailsAndTelephoneAsTheLoggedInCitizen(String telephone) {
        postAppointmentUpdate(scenarioContactFamilyName(), scenarioContactEmail(), "", telephone, true, true);
    }

    @When("I update the appointment with contact details without custom text")
    public void iUpdateTheAppointmentWithContactDetailsWithoutCustomText() {
        postAppointmentUpdate(scenarioContactFamilyName(), scenarioContactEmail(), "", true, false);
    }

    @When("I attempt to update the appointment changing familyName to {string}")
    public void iAttemptToUpdateTheAppointmentChangingFamilyNameTo(String familyName) {
        postAppointmentUpdate(familyName, scenarioContactEmail(), "", false, false);
    }

    @When("I attempt to update the appointment with email {string}")
    public void iAttemptToUpdateTheAppointmentWithEmail(String email) {
        postAppointmentUpdate(scenarioContactFamilyName(), email, "", false, false);
    }

    @When("I attempt to confirm the reserved appointment")
    public void iAttemptToConfirmTheReservedAppointment() {
        postConfirmFromCurrentProcess(false, false, false);
    }

    @When("I confirm the reserved appointment as the logged-in citizen")
    public void iConfirmTheReservedAppointmentAsTheLoggedInCitizen() {
        postConfirmFromCurrentProcess(true, true, false);
    }

    @When("I confirm the reserved appointment using the rebooking source")
    public void iConfirmTheReservedAppointmentUsingTheRebookingSource() {
        postConfirmFromCurrentProcess(true, false, true);
    }

    @When("I attempt to confirm the reserved appointment using the rebooking source")
    public void iAttemptToConfirmTheReservedAppointmentUsingTheRebookingSource() {
        postConfirmFromCurrentProcess(false, false, true);
    }

    @When("I remember the current appointment as {string}")
    public void iRememberTheCurrentAppointmentAs(String label) {
        ThinnedProcess process = requireCurrentProcess();
        RememberedAppointment previous = rememberedAppointments.get(label);
        Integer previousProcessId = previous == null ? null : previous.processId;
        rememberedAppointments.put(label, new RememberedAppointment(
                process.getProcessId(),
                process.getAuthKey(),
                previousProcessId));
        ScenarioLogManager.getLogger().info(String.format(
                "Remembered appointment \"%s\" as processId=%d (previous=%s)",
                label,
                process.getProcessId(),
                previousProcessId));
    }

    @When("I reserve an appointment with the first available slot using the remembered {string} appointment as source")
    public void iReserveUsingTheRememberedAppointmentAsSource(String label) {
        RememberedAppointment source = requireRemembered(label);
        ThinnedProcess stub = new ThinnedProcess();
        stub.setProcessId(source.processId);
        stub.setAuthKey(source.authKey);
        lastReserveProcess = stub;
        reserveFirstAvailableSlot(true, true);
    }

    @When("I request my appointments as the logged-in citizen")
    public void iRequestMyAppointmentsAsTheLoggedInCitizen() {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .header("Authorization", "Bearer " + requireCitizenAccessToken())
        .when()
            .get("/my-appointments/");
        CommonApiSteps.setResponse(response);
        response.then().statusCode(200);
        ThinnedProcess[] appointments = response.as(ThinnedProcess[].class);
        myAppointments = appointments == null ? List.of() : List.of(appointments);
        ScenarioLogManager.getLogger().info(String.format(
                "Citizen API /my-appointments/ status=%d appointmentCount=%d",
                response.getStatusCode(),
                myAppointments.size()));
    }

    @Then("my appointments include the remembered {string} appointment for service {int}")
    public void myAppointmentsIncludeTheRememberedAppointmentForService(String label, int serviceId) {
        RememberedAppointment remembered = requireRemembered(label);
        ThinnedProcess match = findMyAppointment(remembered.processId);
        Assertions.assertThat(match)
            .as("my appointments should include process %s", remembered.processId)
            .isNotNull();
        Assertions.assertThat(match.getServiceId()).isEqualTo(serviceId);
    }

    @Then("my appointments do not include the remembered {string} appointment")
    public void myAppointmentsDoNotIncludeTheRememberedAppointment(String label) {
        RememberedAppointment remembered = requireRemembered(label);
        Assertions.assertThat(findMyAppointment(remembered.processId))
            .as("my appointments should not include process %s", remembered.processId)
            .isNull();
    }

    @Then("the remembered {string} appointment was replaced")
    public void theRememberedAppointmentWasReplaced(String label) {
        RememberedAppointment remembered = requireRemembered(label);
        Assertions.assertThat(remembered.previousProcessId)
            .as("remember \"%s\" again after moving it", label)
            .isNotNull();
        Assertions.assertThat(remembered.processId).isNotEqualTo(remembered.previousProcessId);
        Assertions.assertThat(findMyAppointment(remembered.previousProcessId)).isNull();
        Assertions.assertThat(findMyAppointment(remembered.processId)).isNotNull();
    }

    @Then("the remembered {string} appointment is unchanged")
    public void theRememberedAppointmentIsUnchanged(String label) {
        RememberedAppointment remembered = requireRemembered(label);
        Assertions.assertThat(remembered.previousProcessId).isNull();
        Assertions.assertThat(findMyAppointment(remembered.processId)).isNotNull();
    }

    @When("I cancel the remembered {string} appointment")
    public void iCancelTheRememberedAppointment(String label) {
        RememberedAppointment remembered = requireRemembered(label);
        ScenarioLogManager.getLogger().info(String.format(
                "Citizen API /cancel-appointment/ for remembered \"%s\" processId=%d",
                label,
                remembered.processId));
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(Map.of("processId", remembered.processId, "authKey", remembered.authKey))
        .when()
            .post("/cancel-appointment/");
        CommonApiSteps.setResponse(response);
        response.then().statusCode(200);
        Integer currentProcessId = lastReserveProcess == null ? null : lastReserveProcess.getProcessId();
        if (currentProcessId != null && currentProcessId.intValue() == remembered.processId) {
            lastReserveProcess.setStatus("deleted");
            setLastReserveProcess(lastReserveProcess);
        }
    }

    private RememberedAppointment requireRemembered(String label) {
        RememberedAppointment remembered = rememberedAppointments.get(label);
        if (remembered == null) {
            throw new IllegalStateException("Remember the appointment \"" + label + "\" first.");
        }
        return remembered;
    }

    private ThinnedProcess findMyAppointment(Integer processId) {
        for (ThinnedProcess appointment : myAppointments) {
            if (appointment != null && processId.equals(appointment.getProcessId())) {
                return appointment;
            }
        }
        return null;
    }

    private void postAppointmentUpdate(
            String familyName,
            String email,
            String customTextfield,
            boolean expectSuccess,
            boolean asLoggedInCitizen) {
        postAppointmentUpdate(familyName, email, customTextfield, "", expectSuccess, asLoggedInCitizen);
    }

    private void postAppointmentUpdate(
            String familyName,
            String email,
            String customTextfield,
            String telephone,
            boolean expectSuccess,
            boolean asLoggedInCitizen) {
        ThinnedProcess process = requireCurrentProcess();
        Integer pid = process.getProcessId();
        String auth = process.getAuthKey();
        Map<String, Object> body = new java.util.LinkedHashMap<>();
        body.put("processId", pid);
        body.put("authKey", auth);
        body.put("familyName", familyName);
        body.put("email", email);
        body.put("telephone", telephone != null ? telephone : "");
        body.put("customTextfield", customTextfield != null ? customTextfield : "");
        body.put("customTextfield2", "");
        if (rebookingSourceProcessId != null && rebookingSourceAuthKey != null) {
            body.put("sourceProcessId", rebookingSourceProcessId);
            body.put("sourceAuthKey", rebookingSourceAuthKey);
        }
        RequestSpecification spec = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(body);
        if (asLoggedInCitizen) {
            spec = spec.header("Authorization", "Bearer " + requireCitizenAccessToken());
        }
        response = spec.when().post("/update-appointment/");
        CommonApiSteps.setResponse(response);

        String updateBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /update-appointment/ status=%d body=%s",
            response.getStatusCode(),
            updateBody.length() > 1250 ? updateBody.substring(0, 1250) + "..." : updateBody
        ));
        if (!expectSuccess) {
            return;
        }
        response.then().statusCode(200);

        ThinnedProcess updated;
        try {
            updated = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            updated = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(updated)
            .as("update-appointment response payload must deserialize")
            .isNotNull();
        Assertions.assertThat(updated.getProcessId()).isEqualTo(pid);
        Assertions.assertThat(updated.getAuthKey()).isEqualTo(auth);
        lastReserveProcess = updated;
        setLastReserveProcess(updated);
    }

    private void postConfirmFromCurrentProcess(
            boolean expectSuccess,
            boolean asLoggedInCitizen,
            boolean includeRebookingSource) {
        ThinnedProcess process = requireCurrentProcess();
        Integer pid = process.getProcessId();
        String auth = process.getAuthKey();
        Map<String, Object> body = new java.util.LinkedHashMap<>();
        body.put("processId", pid);
        body.put("authKey", auth);
        if (includeRebookingSource) {
            if (rebookingSourceProcessId == null || rebookingSourceAuthKey == null) {
                throw new IllegalStateException("Reserve with a source appointment first.");
            }
            body.put("sourceProcessId", rebookingSourceProcessId);
            body.put("sourceAuthKey", rebookingSourceAuthKey);
        }
        RequestSpecification spec = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(body);
        if (asLoggedInCitizen) {
            spec = spec.header("Authorization", "Bearer " + requireCitizenAccessToken());
        }
        response = spec.when().post("/confirm-appointment/");
        CommonApiSteps.setResponse(response);

        String confirmBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /confirm-appointment/ status=%d body=%s",
            response.getStatusCode(),
            confirmBody.length() > 1250 ? confirmBody.substring(0, 1250) + "..." : confirmBody
        ));
        if (!expectSuccess) {
            return;
        }
        response.then().statusCode(200);

        ThinnedProcess confirmed;
        try {
            confirmed = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            confirmed = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(confirmed)
            .as("confirm-appointment response payload must deserialize")
            .isNotNull();
        Assertions.assertThat(confirmed.getProcessId()).isEqualTo(pid);
        Assertions.assertThat(confirmed.getAuthKey()).isEqualTo(auth);
        Integer expectedOfficeId = lastReserveProcess != null ? lastReserveProcess.getOfficeId() : lastOfficeId;
        Assertions.assertThat(confirmed.getOfficeId())
            .as("confirmed appointment should land at expected office")
            .isEqualTo(expectedOfficeId);
        Assertions.assertThat(confirmed.getServiceId()).isNotNull();
        Assertions.assertThat(confirmed.getTimestamp())
            .as("confirm-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(confirmed.getTimestamp())
            .as("confirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);

        lastReserveProcess = confirmed;
        setLastReserveProcess(confirmed);
    }

    private ThinnedProcess requireCurrentProcess() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (process == null) {
            throw new IllegalStateException("Reserve an appointment first.");
        }
        if (process.getProcessId() == null || process.getAuthKey() == null) {
            throw new IllegalStateException("Last reserve response has no processId or authKey.");
        }
        return process;
    }

    private String requireCitizenAccessToken() {
        if (citizenAccessToken == null || citizenAccessToken.isBlank()) {
            citizenAccessToken = CitizenKeycloakTokenHelper.fetchAccessToken();
        }
        return citizenAccessToken;
    }

    @Then("the response errors should include errorCode {string}")
    public void theResponseErrorsShouldIncludeErrorCode(String errorCode) {
        List<String> codes = response.jsonPath().getList("errors.errorCode", String.class);
        Assertions.assertThat(codes)
            .as("response errors.errorCode")
            .isNotNull()
            .contains(errorCode);
    }

    @Then("the appointment familyName should be {string}")
    public void theAppointmentFamilyNameShouldBe(String expected) {
        Assertions.assertThat(contactValue(currentAppointmentProcess().getFamilyName()))
            .as("appointment familyName")
            .isEqualTo(expected);
    }

    @Then("the appointment familyName should be the generated contact name")
    public void theAppointmentFamilyNameShouldBeTheGeneratedContactName() {
        Assertions.assertThat(contactValue(currentAppointmentProcess().getFamilyName()))
            .as("appointment familyName")
            .isEqualTo(scenarioContactFamilyName());
    }

    @Then("the appointment customTextfield should be {string}")
    public void theAppointmentCustomTextfieldShouldBe(String expected) {
        Assertions.assertThat(contactValue(currentAppointmentProcess().getCustomTextfield()))
            .as("appointment customTextfield")
            .isEqualTo(expected);
    }

    private ThinnedProcess currentAppointmentProcess() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        Assertions.assertThat(process)
            .as("current appointment process")
            .isNotNull();
        return process;
    }

    private static String contactValue(String value) {
        return value == null ? "" : value;
    }

    @Then("the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void theUpdateResponseShouldIncludeThinnedBookingProcess() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getProcessId()).isNotNull();
        Assertions.assertThat(process.getAuthKey()).isNotNull();
        Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        assertScopeProviderGeoPresent("update-appointment");
    }

    @Then("the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void thePreconfirmResponseShouldBePreconfirmedWithProcessAuthOfficeService() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getProcessId()).isNotNull();
        Assertions.assertThat(process.getAuthKey()).isNotNull();
        Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        Assertions.assertThat(process.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(process.getTimestamp())
            .as("preconfirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        assertScopeProviderGeoPresent("preconfirm-appointment");
        // Skip `displayNumber` and `captchaToken`.
    }

    @Then("the preconfirmation mail should provide confirm credentials")
    public void thePreconfirmationMailShouldProvideConfirmCredentials() {
        Assertions.assertThat(getBookingConfirmProcessId())
            .as("bookingConfirmProcessId must be set")
            .isNotBlank();
        Assertions.assertThat(getBookingConfirmAuthKey())
            .as("bookingConfirmAuthKey must be set")
            .isNotBlank();
    }

    @Then("the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void theConfirmResponseShouldBeConfirmedWithProcessAuthOfficeService() {
        response.then().statusCode(200);
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getProcessId()).isNotNull();
        Assertions.assertThat(process.getAuthKey()).isNotNull();
        Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
        Assertions.assertThat(process.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(process.getTimestamp())
            .as("confirm-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        assertScopeProviderGeoPresent("confirm-appointment");
        // Skip `displayNumber` (varies) and `captchaToken` (captcha disabled in test data).
    }

    /**
     * Assert scope.provider.lat and lon are present (non-null numbers) on the current response.
     * Soft-delete/cancel responses intentionally clear geo; do not use this helper there.
     */
    private void assertScopeProviderGeoPresent(String endpointLabel) {
        Object lat = response.jsonPath().get("scope.provider.lat");
        Object lon = response.jsonPath().get("scope.provider.lon");
        Assertions.assertThat(lat)
            .as("%s response must include scope.provider.lat", endpointLabel)
            .isNotNull()
            .isInstanceOf(Number.class);
        Assertions.assertThat(lon)
            .as("%s response must include scope.provider.lon", endpointLabel)
            .isNotNull()
            .isInstanceOf(Number.class);
    }

    @Then("the confirmation mail should provide an appointment view url")
    public void theConfirmationMailShouldProvideAnAppointmentViewUrl() {
        String url = getBookingAppointmentUrl();
        Assertions.assertThat(url)
            .as("bookingAppointmentUrl must be set from confirmation mail")
            .isNotBlank();
        Assertions.assertThat(url).contains("appointment/");
        Assertions.assertThat(url).doesNotContain("appointment/confirm/");
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

    @Then("the fetched appointment should report placeholder email {string}")
    public void theFetchedAppointmentShouldReportPlaceholderEmail(String email) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).as("fetched appointment").isNotNull();
        Assertions.assertThat(process.getEmail())
            .as("stored client email")
            .isEqualToIgnoringCase(email);
        Assertions.assertThat(process.getPlaceholderEmail())
            .as("configured placeholderEmail")
            .isEqualToIgnoringCase(email);
    }

    @Then("the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId")
    public void theAppointmentEndpointResponseShouldIncludeAThinnedBookingProcessWithProcessIdAuthOfficeAndServiceId() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getProcessId()).isNotNull();
        Assertions.assertThat(process.getAuthKey()).isNotBlank();
        Assertions.assertThat(process.getOfficeId()).isEqualTo(lastOfficeId);
        Assertions.assertThat(process.getServiceId()).isEqualTo(lastServiceId);
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
            cancelBody.length() > 1250 ? cancelBody.substring(0, 1250) + "..." : cancelBody
        ));

        // Basic sanity: 200 and a non-empty payload with a thinned process.
        response.then().statusCode(200);
        ThinnedProcess cancelled;
        try {
            cancelled = response.as(ThinnedProcess.class);
        } catch (Exception e) {
            cancelled = parseDataResponse(response, ThinnedProcess.class);
        }
        Assertions.assertThat(cancelled).isNotNull();
        Assertions.assertThat(cancelled.getProcessId()).isEqualTo(pid);
        // Avoid strict checks on officeId/authKey because cancel returns a "deleted" payload where
        // officeId can be 0 and authKey may change.
        // Keep serviceId invariant checks, which are stable across our test flow.
        if (process.getServiceId() != null) {
            Assertions.assertThat(cancelled.getServiceId())
                .as("cancel-appointment should keep same serviceId as prior appointment")
                .isEqualTo(process.getServiceId());
        }
        Assertions.assertThat(cancelled.getTimestamp())
            .as("cancel-appointment timestamp must exist")
            .isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(cancelled.getTimestamp())
            .as("cancel-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` and `captchaToken` assertions: display formatting varies by office/provider,
        // and captcha is disabled in our test data.
        lastReserveProcess = cancelled;
        setLastReserveProcess(cancelled);
    }

    /**
     * Cancel the current process if one exists and is not already deleted. Never fails the scenario:
     * used from {@link #cancelLeftoverAppointmentAfterScenario()} after both passing and failing runs.
     */
    public void cancelLeftoverAppointmentQuietly() {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : getBookingProcess();
        if (process == null || process.getProcessId() == null || process.getAuthKey() == null) {
            return;
        }
        if ("deleted".equalsIgnoreCase(process.getStatus())) {
            return;
        }
        if (cancelProcessQuietly(process.getProcessId(), process.getAuthKey())) {
            process.setStatus("deleted");
            lastReserveProcess = process;
            setLastReserveProcess(process);
        }
    }

    private boolean cancelProcessQuietly(Integer processId, String authKey) {
        if (processId == null || authKey == null || authKey.isBlank()) {
            return false;
        }
        try {
            Response cancelResponse = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
                .contentType("application/json")
                .body(Map.of("processId", processId, "authKey", authKey))
            .when()
                .post("/cancel-appointment/");
            ScenarioLogManager.getLogger().info(String.format(
                "Best-effort /cancel-appointment/ processId=%d http=%d",
                processId,
                cancelResponse.getStatusCode()
            ));
            return cancelResponse.getStatusCode() == 200;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn(
                String.format("Best-effort cancel failed for processId=%d: %s", processId, e)
            );
            return false;
        }
    }

    @When("I cancel the rebooking source appointment")
    public void iCancelTheRebookingSourceAppointment() {
        if (rebookingSourceProcessId == null || rebookingSourceAuthKey == null) {
            throw new IllegalStateException("Reserve with a source appointment first.");
        }
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /cancel-appointment/ for rebooking source processId=%d", rebookingSourceProcessId
        ));
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .contentType("application/json")
            .body(Map.of("processId", rebookingSourceProcessId, "authKey", rebookingSourceAuthKey))
        .when()
            .post("/cancel-appointment/");
        CommonApiSteps.setResponse(response);

        String cancelBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /cancel-appointment/ source status=%d body=%s",
            response.getStatusCode(),
            cancelBody.length() > 1250 ? cancelBody.substring(0, 1250) + "..." : cancelBody
        ));
        response.then().statusCode(200);
    }

    @Then("the cancel endpoint response should include a soft deleted thinned booking process")
    public void theCancelEndpointResponseShouldIncludeAThinnedBookingProcess() {
        response.then().statusCode(200);
        ThinnedProcess cancelled = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(cancelled).isNotNull();
        // Avoid strict checks on officeId/authKey because cancel returns a "deleted" payload where
        // officeId can be 0 and authKey may change.
        // Soft-delete contract checks: officeId=0 and key appointment details are nulled.
        Assertions.assertThat(response.jsonPath().getInt("officeId")).as("officeId must be 0 (soft delete)").isEqualTo(0);
        Assertions.assertThat((Object) response.jsonPath().get("officeName"))
            .as("officeName must be null")
            .isNull();

        Assertions.assertThat(response.jsonPath().getString("familyName"))
            .as("familyName must indicate cancellation")
            .isEqualTo("(abgesagt)");

        // top-level strings reset / preserved by cancel SQL (QUERY_CANCELED)
        Assertions.assertThat(response.jsonPath().getString("telephone")).as("telephone must be empty").isEqualTo("");
        // customTextfield / customTextfield2 are not cleared on cancel (see zmscitizenapi cancel fixtures)
        Assertions.assertThat(response.jsonPath().getString("captchaToken")).as("captchaToken must be empty in soft delete payload").isEqualTo("");

        // provider inside scope is nulled out (keeps provider.id=0/name="") in soft delete payload
        Assertions.assertThat(response.jsonPath().getInt("scope.provider.id"))
            .as("scope.provider.id must be 0")
            .isEqualTo(0);
        Assertions.assertThat(response.jsonPath().getString("scope.provider.name"))
            .as("scope.provider.name must be empty")
            .isEqualTo("");
        Assertions.assertThat((Object) response.jsonPath().get("scope.provider.displayName"))
            .as("scope.provider.displayName must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.provider.lat"))
            .as("scope.provider.lat must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.provider.lon"))
            .as("scope.provider.lon must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.provider.contact"))
            .as("scope.provider.contact must be null")
            .isNull();

        // scope is mostly nulled out for soft delete payload
        Assertions.assertThat(response.jsonPath().getString("scope.shortName"))
            .as("scope.shortName must be empty")
            .isEqualTo("");
        Assertions.assertThat(response.jsonPath().getString("scope.emailFrom"))
            .as("scope.emailFrom must be empty")
            .isEqualTo("");
        Assertions.assertThat((Object) response.jsonPath().get("scope.emailRequired"))
            .as("scope.emailRequired must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.telephoneActivated"))
            .as("scope.telephoneActivated must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.telephoneRequired"))
            .as("scope.telephoneRequired must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfieldActivated"))
            .as("scope.customTextfieldActivated must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfieldRequired"))
            .as("scope.customTextfieldRequired must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfieldLabel"))
            .as("scope.customTextfieldLabel must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfield2Activated"))
            .as("scope.customTextfield2Activated must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfield2Required"))
            .as("scope.customTextfield2Required must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.customTextfield2Label"))
            .as("scope.customTextfield2Label must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.captchaActivatedRequired"))
            .as("scope.captchaActivatedRequired must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.infoForAppointment"))
            .as("scope.infoForAppointment must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.infoForAllAppointments"))
            .as("scope.infoForAllAppointments must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.slotsPerAppointment"))
            .as("scope.slotsPerAppointment must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.appointmentsPerMail"))
            .as("scope.appointmentsPerMail must be null")
            .isNull();
        Assertions.assertThat(response.jsonPath().getString("scope.whitelistedMails"))
            .as("scope.whitelistedMails must be empty")
            .isEqualTo("");
        Assertions.assertThat((Object) response.jsonPath().get("scope.reservationDuration"))
            .as("scope.reservationDuration must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.activationDuration"))
            .as("scope.activationDuration must be null")
            .isNull();
        Assertions.assertThat((Object) response.jsonPath().get("scope.hint"))
            .as("scope.hint must be null")
            .isNull();

        Assertions.assertThat(cancelled.getTimestamp()).isNotNull();
        long nowEpochSeconds = Instant.now().getEpochSecond();
        Assertions.assertThat(cancelled.getTimestamp())
            .as("cancel-appointment timestamp must be >= now - skew")
            .isGreaterThanOrEqualTo(nowEpochSeconds - 120);
        // Skip `displayNumber` and `captchaToken`.
    }

    @Then("the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email")
    public void theCancelEndpointResponseShouldStillIncludeProcessIdEmailDisplayNumberAndServiceIdAndServiceName() {
        response.then().statusCode(200);

        Assertions.assertThat(response.jsonPath().getInt("processId"))
            .as("processId should still be set in soft delete payload")
            .isNotEqualTo(0);

        Assertions.assertThat(response.jsonPath().getString("email"))
            .as("email must still be present so deletion/cancellation email can be sent")
            .isNotBlank();

        ThinnedProcess cancelled = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(cancelled).isNotNull();

        String cancelledDisplayNumber = cancelled.getDisplayNumber();
        Assertions.assertThat(cancelledDisplayNumber)
            .as("displayNumber must still be present in soft delete payload")
            .isNotBlank();
        if (lastDisplayNumberBeforeCancel != null && !lastDisplayNumberBeforeCancel.isBlank()) {
            Assertions.assertThat(cancelledDisplayNumber)
                .as("displayNumber should be preserved during soft delete")
                .isEqualTo(lastDisplayNumberBeforeCancel);
        }

        Object scopeId = response.jsonPath().get("scope.id");
        Assertions.assertThat(scopeId)
            .as("scope.id must still be present for email office-name lookup")
            .isNotNull();
        Assertions.assertThat(scopeId)
            .as("scope.id must be numeric")
            .isInstanceOf(Number.class);
        Assertions.assertThat(((Number) scopeId).intValue())
            .as("scope.id must be a positive office lookup id")
            .isGreaterThan(0);

        Assertions.assertThat(cancelled.getServiceId())
            .as("cancel-appointment should keep same serviceId as prior appointment")
            .isEqualTo(lastServiceId);
        Assertions.assertThat(response.jsonPath().getString("serviceName"))
            .as("serviceName should still exist")
            .isNotBlank();
    }
    /* End Section: Sequential steps assertions for thinned booking process */

    /* Section: Non-sequential steps assertions for thinned booking process */
    @Then("the appointment should be at office {int}")
    public void theAppointmentShouldBeAtOffice(int officeId) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getOfficeId())
            .as("Expected appointment to land at office %d", officeId)
            .isEqualTo(officeId);
    }

    @Then("the appointment should be for service {int}")
    public void theAppointmentShouldBeForService(int serviceId) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.getServiceId())
            .as("Expected appointment to use service %d", serviceId)
            .isEqualTo(serviceId);
    }

    @Then("the appointment service title order should be {string}")
    public void theAppointmentServiceTitleOrderShouldBe(String orderedNamesCsv) {
        ThinnedProcess process =
                lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process).as("appointment process").isNotNull();
        List<String> expected =
                Arrays.stream(orderedNamesCsv.split(","))
                        .map(String::trim)
                        .filter(s -> !s.isEmpty())
                        .collect(Collectors.toList());
        Assertions.assertThat(expected).as("expected service title names").isNotEmpty();
        Assertions.assertThat(process.getServiceName())
                .as("main serviceName (first in booking order)")
                .isEqualTo(expected.get(0));
        List<String> actual = new ArrayList<>();
        actual.add(process.getServiceName());
        if (process.getSubRequestCounts() != null) {
            for (ThinnedProcess.SubRequestCount sub : process.getSubRequestCounts()) {
                if (sub != null && sub.getName() != null && !sub.getName().isBlank()) {
                    actual.add(sub.getName());
                }
            }
        }
        Assertions.assertThat(actual)
                .as("serviceName then subRequestCounts must keep booking order, not alphabetical")
                .containsExactlyElementsOf(expected);
        if (process.getIcsContent() != null && !process.getIcsContent().isBlank()) {
            String summary = icsSummary(process.getIcsContent());
            if (summary != null) {
                int previous = -1;
                for (String name : expected) {
                    int idx = summary.indexOf(name);
                    Assertions.assertThat(idx)
                            .as("ICS SUMMARY must list \"%s\" after earlier services. SUMMARY=%s", name, summary)
                            .isGreaterThan(previous);
                    previous = idx;
                }
            }
        }
    }

    /** Unfolded ICS SUMMARY value, or null when the property is absent. */
    private static String icsSummary(String icsContent) {
        String unfolded = icsContent.replaceAll("\\R[ \\t]", "");
        for (String line : unfolded.split("\\R")) {
            if (line.startsWith("SUMMARY:")) {
                return line.substring("SUMMARY:".length()).replace("\\n", "\n").replace("\\,", ",");
            }
        }
        return null;
    }

    @Then("the appointment status should be {string}")
    public void theAppointmentStatusShouldBe(String expectedStatus) {
        ThinnedProcess process = lastReserveProcess != null ? lastReserveProcess : parseDataResponse(response, ThinnedProcess.class);
        Assertions.assertThat(process)
            .as("expected a thinned process in order to assert appointment status")
            .isNotNull();
        Assertions.assertThat(process.getStatus())
            .as("appointment status should match")
            .isEqualTo(expectedStatus);
    }
    /* End Section: Non-sequential steps assertions for thinned booking process */

    /* Section: Response Parsing */
    private AvailableCalendarResponse fetchAvailableCalendar(
            List<Integer> officeIds, int serviceId, int serviceCount) {
        return fetchAvailableCalendar(officeIds, List.of(serviceId), List.of(serviceCount), null, null);
    }

    private AvailableCalendarResponse fetchAvailableCalendar(
            List<Integer> officeIds, List<Integer> serviceIds, List<Integer> serviceCounts) {
        return fetchAvailableCalendar(officeIds, serviceIds, serviceCounts, null, null);
    }

    private AvailableCalendarResponse fetchAvailableCalendar(
            List<Integer> officeIds,
            List<Integer> serviceIds,
            List<Integer> serviceCounts,
            String slotsStartDate,
            String slotsEndDate) {
        Assertions.assertThat(officeIds).as("officeIds").isNotEmpty();
        Assertions.assertThat(serviceIds).as("serviceIds").isNotEmpty();
        Assertions.assertThat(serviceCounts)
            .as("serviceCounts")
            .hasSameSizeAs(serviceIds);
        String officeIdsParam =
            officeIds.stream().map(String::valueOf).collect(Collectors.joining(","));
        String serviceIdsParam =
            serviceIds.stream().map(String::valueOf).collect(Collectors.joining(","));
        String serviceCountsParam =
            serviceCounts.stream().map(String::valueOf).collect(Collectors.joining(","));
        String startDate = BerlinTime.today().format(DATE_FORMAT);
        String endDate = BerlinTime.today().plusMonths(6).format(DATE_FORMAT);
        RequestSpecification calendarRequest = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getCitizenApiBaseUri())
            .queryParam("officeIds", officeIdsParam)
            .queryParam("serviceIds", serviceIdsParam)
            .queryParam("startDate", startDate)
            .queryParam("endDate", endDate)
            .queryParam("serviceCounts", serviceCountsParam);
        if (slotsStartDate != null && !slotsStartDate.isBlank()) {
            calendarRequest = calendarRequest.queryParam("slotsStartDate", slotsStartDate);
        }
        if (slotsEndDate != null && !slotsEndDate.isBlank()) {
            calendarRequest = calendarRequest.queryParam("slotsEndDate", slotsEndDate);
        }
        if (captchaToken != null && !captchaToken.isBlank()) {
            calendarRequest = calendarRequest.queryParam("captchaToken", captchaToken);
        }
        response = calendarRequest.when().get("/available-calendar/");
        CommonApiSteps.setResponse(response);

        String calendarBody = response.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "Citizen API /available-calendar/ (officeIds=%s, serviceIds=%s, slots=%s..%s) status=%d body=%s",
            officeIdsParam,
            serviceIdsParam,
            slotsStartDate != null ? slotsStartDate : "-",
            slotsEndDate != null ? slotsEndDate : "-",
            response.getStatusCode(),
            calendarBody.length() > 1250 ? calendarBody.substring(0, 1250) + "..." : calendarBody
        ));

        AvailableCalendarResponse calendar = null;
        if (response.getStatusCode() == 200) {
            try {
                calendar = response.as(AvailableCalendarResponse.class);
            } catch (Exception e) {
                calendar = parseDataResponse(response, AvailableCalendarResponse.class);
            }
            cachedCalendarOfficeIds = officeIdsParam;
            cachedCalendarServiceIds = List.copyOf(serviceIds);
            cachedCalendarServiceCounts = List.copyOf(serviceCounts);
            cachedCalendarServiceId = serviceIds.get(0);
            cachedCalendarServiceCount = serviceCounts.get(0);
            cachedCalendarCaptchaToken = captchaToken;
        } else {
            cachedCalendarOfficeIds = null;
            cachedCalendarServiceIds = null;
            cachedCalendarServiceCounts = null;
            cachedCalendarServiceId = null;
            cachedCalendarServiceCount = null;
            cachedCalendarCaptchaToken = null;
        }
        return calendar;
    }

    private List<Integer> parseOfficeIdsCsv(String officeIdsCsv) {
        List<Integer> ids = new ArrayList<>();
        if (officeIdsCsv == null || officeIdsCsv.isBlank()) {
            return ids;
        }
        for (String token : officeIdsCsv.split(",")) {
            String trimmed = token.trim();
            if (trimmed.isEmpty()) {
                continue;
            }
            ids.add(parseIntOrFail(trimmed, "officeId"));
        }
        return ids;
    }

    private Object scopeValue(int officeId, String field) {
        Assertions.assertThat(lastOfficesAndServicesResponse)
            .as("Request offices-and-services first")
            .isNotNull();
        Office office = findOfficeById(lastOfficesAndServicesResponse, officeId);
        Assertions.assertThat(office)
            .as("Expected office %d in offices-and-services", officeId)
            .isNotNull();
        Assertions.assertThat(office.getScope())
            .as("office %d scope", officeId)
            .isInstanceOf(Map.class);
        return ((Map<?, ?>) office.getScope()).get(field);
    }

    private Office findOfficeById(OfficesAndServicesResponse response, int officeId) {
        if (response == null || response.getOffices() == null) {
            return null;
        }
        for (Office office : response.getOffices()) {
            if (office != null && office.getId() != null && office.getId() == officeId) {
                return office;
            }
        }
        return null;
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
                    ? responseBody.substring(0, 1250) + "..."
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
                    ? responseBody.substring(0, 1250) + "..."
                    : responseBody
            );
            throw new RuntimeException(errorMsg, e);
        }
    }

    public AvailableCalendarResponse getLastAvailableCalendarResponse() {
        return lastAvailableCalendarResponse;
    }

    public AvailableAppointmentsResponse getLastAvailableAppointmentsResponse() {
        return lastAvailableAppointmentsResponse;
    }

    /** Booking context for this scenario's thread, shared with mail and confirm steps. */
    private static final class BookingContext {
        private ThinnedProcess process;
        private String confirmProcessId;
        private String confirmAuthKey;
        private String confirmUrl;
        private String appointmentUrl;
        private String contactFamilyName;
        private String contactEmail;

        private void clear() {
            process = null;
            confirmProcessId = null;
            confirmAuthKey = null;
            confirmUrl = null;
            appointmentUrl = null;
            contactFamilyName = null;
            contactEmail = null;
        }
    }

    /**
     * One generated name per scenario. Nachname is the family name, and the mailinator
     * address is that same full name, as on the citizen view Kontakt step.
     */
    private static void ensureScenarioContact() {
        BookingContext context = booking();
        if (context.contactFamilyName != null && !context.contactFamilyName.isBlank()) {
            return;
        }
        String fullName = RandomNameHelper.generateRandomName();
        String[] parts = RandomNameHelper.splitFullNameIntoFirstAndLast(fullName);
        context.contactFamilyName = parts[1];
        if (context.contactEmail == null || context.contactEmail.isBlank()) {
            setBookingContactEmail(RandomNameHelper.getEmailConformName(fullName) + "@mailinator.com");
        }
    }

    private static String scenarioContactFamilyName() {
        ensureScenarioContact();
        return booking().contactFamilyName;
    }

    /** One mailinator address per scenario, same generator the citizen view Kontakt step uses. */
    private static String scenarioContactEmail() {
        ensureScenarioContact();
        return booking().contactEmail;
    }

    private static final ThreadLocal<BookingContext> BOOKING = ThreadLocal.withInitial(BookingContext::new);

    private static BookingContext booking() {
        return BOOKING.get();
    }

    public static ThinnedProcess getBookingProcess() {
        return booking().process;
    }

    public static void setBookingProcess(ThinnedProcess process) {
        booking().process = process;
    }

    public static String getBookingContactEmail() {
        return booking().contactEmail;
    }

    public static void setBookingContactEmail(String email) {
        booking().contactEmail = email;
    }

    public static String getBookingConfirmProcessId() {
        return booking().confirmProcessId;
    }

    public static String getBookingConfirmAuthKey() {
        return booking().confirmAuthKey;
    }

    public static void setBookingConfirmCredentials(String processId, String authKey) {
        BookingContext context = booking();
        context.confirmProcessId = processId;
        context.confirmAuthKey = authKey;
    }

    public static String getBookingConfirmUrl() {
        return booking().confirmUrl;
    }

    public static void setBookingConfirmUrl(String url) {
        booking().confirmUrl = url;
    }

    public static String getBookingAppointmentUrl() {
        return booking().appointmentUrl;
    }

    public static void setBookingAppointmentUrl(String url) {
        booking().appointmentUrl = url;
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

    private static final class RememberedAppointment {
        private final int processId;
        private final String authKey;
        private final Integer previousProcessId;

        private RememberedAppointment(int processId, String authKey, Integer previousProcessId) {
            this.processId = processId;
            this.authKey = authKey;
            this.previousProcessId = previousProcessId;
        }
    }
}
