package zms.ataf.rest.steps;

import static io.restassured.RestAssured.given;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.time.DayOfWeek;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;
import java.util.concurrent.TimeUnit;

import org.assertj.core.api.Assertions;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ArrayNode;
import com.fasterxml.jackson.databind.node.ObjectNode;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.cucumber.datatable.DataTable;
import io.cucumber.java.After;
import io.cucumber.java.Before;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmsapi.StatusResponse;

public class ZmsApiSteps {

    private static final ObjectMapper MAPPER = new ObjectMapper();

    private Response response;
    private String baseUri;
    private String cachedXAuthKey;
    private String scenarioLoginUsername;
    private String scenarioLoginPassword;
    private JsonNode lastProcess;
    private final List<Integer> scenarioProcessIds = new ArrayList<>();
    private final Map<Integer, Integer> processScopeById = new HashMap<>();
    private final Map<String, String> clerkAuthKeys = new HashMap<>();
    private Response firstClerkCallResponse;
    private Response secondClerkCallResponse;
    private int workstationScopeId;
    private JsonNode departmentServices;
    private String ticketprinterHash;
    private Integer createdAvailabilityId;
    private Integer historyAvailabilityId;
    private int historyScopeId;
    private boolean lastProcessDeleted;
    /** ZMSKVR-42: unlock in @After if the scenario left a day locked. */
    private Integer dayClosureScopeId;
    private String dayClosureDate;
    private boolean dayClosureIsLocked;
    private String rememberedFreeAppointmentDate;
    private JsonNode rememberedFreeAppointmentRequest;
    /** Provider id before a locked scope update (ZMSKVR-1345). */
    private String rememberedScopeProviderId;
    private String rememberedScopeProviderSource;
    /** Warehouse ticketprinter count before finishing the current E-Kiosk walk-in (ZMSKVR-1126). */
    private Integer rememberedTicketprinterCount;
    /** ZMS-3212 / ZMS-3503: scope whose emergency may need cleanup. */
    private Integer emergencyScopeId;
    private Boolean emergencyEnabledBeforeScenario;
    /** ZMS-3213: display numbers in call order (oldest first) for Aufrufmonitor assertions. */
    private final List<String> callDisplayNumbersInCallOrder = new ArrayList<>();
    private final Map<String, JsonNode> callDisplayProcessesByLabel = new HashMap<>();
    /** Source walk-in Kundennummer before redirect (ZMS-3507 / ZMSALT-3541). */
    private String rememberedDisplayNumber;
    private Integer rememberedQueueNumber;

    @Before
    public void resetProcessContext() {
        lastProcess = null;
        scenarioProcessIds.clear();
        processScopeById.clear();
        clerkAuthKeys.clear();
        callDisplayNumbersInCallOrder.clear();
        callDisplayProcessesByLabel.clear();
        rememberedScopeProviderId = null;
        rememberedScopeProviderSource = null;
        rememberedTicketprinterCount = null;
        emergencyScopeId = null;
        emergencyEnabledBeforeScenario = null;
        rememberedDisplayNumber = null;
        rememberedQueueNumber = null;
        firstClerkCallResponse = null;
        secondClerkCallResponse = null;
        workstationScopeId = 0;
        departmentServices = null;
        cachedXAuthKey = null;
        scenarioLoginUsername = null;
        scenarioLoginPassword = null;
        ticketprinterHash = null;
        createdAvailabilityId = null;
        historyAvailabilityId = null;
        historyScopeId = 0;
        lastProcessDeleted = false;
        dayClosureScopeId = null;
        dayClosureDate = null;
        dayClosureIsLocked = false;
        rememberedFreeAppointmentDate = null;
        rememberedFreeAppointmentRequest = null;
    }

    @After
    public void deleteOpeningHoursLeftByTheScenario() {
        restoreEmergencyLeftByTheScenario();
        unlockDayClosureLeftByTheScenario();
        Integer id = createdAvailabilityId;
        createdAvailabilityId = null;
        if (id != null) {
            try {
                given()
                    .baseUri(apiBaseUri())
                    .header("X-AuthKey", getOrLoginXAuthKey())
                .when()
                    .delete("/availability/" + id + "/");
            } catch (RuntimeException e) {
                ScenarioLogManager.getLogger().warn("Could not delete opening hours {}: {}", id, e.getMessage());
            }
        }
        deleteScenarioProcesses();
        deleteLastProcessIfStillOpen();
    }

    private void restoreEmergencyLeftByTheScenario() {
        Integer scopeId = emergencyScopeId;
        if (scopeId == null) {
            return;
        }
        Boolean previousEnabled = emergencyEnabledBeforeScenario;
        try {
            Response stop = postOrDeleteEmergency(scopeId, false);
            if (stop.getStatusCode() == 401 || stop.getStatusCode() == 403) {
                cachedXAuthKey = null;
                iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "4");
                stop = postOrDeleteEmergency(scopeId, false);
            }
            Assertions.assertThat(stop.getStatusCode())
                    .as("teardown DELETE /scope/%d/emergency/ body=%s",
                            scopeId, truncate(stop.asString(), 500))
                    .isEqualTo(200);
            if (previousEnabled != null) {
                Response restore = setScopeEmergencyEnabled(scopeId, previousEnabled);
                if (restore.getStatusCode() == 401 || restore.getStatusCode() == 403) {
                    cachedXAuthKey = null;
                    iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "4");
                    restore = setScopeEmergencyEnabled(scopeId, previousEnabled);
                }
                Assertions.assertThat(restore.getStatusCode())
                        .as("teardown restore emergencyEnabled=%s for scope %d body=%s",
                                previousEnabled, scopeId, truncate(restore.asString(), 500))
                        .isEqualTo(200);
            }
            emergencyScopeId = null;
            emergencyEnabledBeforeScenario = null;
        } catch (RuntimeException | AssertionError e) {
            ScenarioLogManager.getLogger().error(
                    "Could not restore emergency state for scope {}: {}", scopeId, e.getMessage());
            throw e instanceof AssertionError
                    ? (AssertionError) e
                    : new AssertionError("Emergency teardown failed for scope " + scopeId, e);
        }
    }

    private void unlockDayClosureLeftByTheScenario() {
        // Unlock whenever scope/date were recorded — not only when dayClosureIsLocked is true.
        // A failed assertion after a successful lock must not skip teardown.
        if (dayClosureScopeId != null && dayClosureDate != null) {
            try {
                unlockDayClosureViaApiIfLocked(dayClosureScopeId, dayClosureDate);
                dayClosureIsLocked = false;
            } catch (RuntimeException | AssertionError e) {
                ScenarioLogManager.getLogger().warn(
                        "Could not unlock day closure for scope {} on {}: {}",
                        dayClosureScopeId,
                        dayClosureDate,
                        e.getMessage());
            }
        }
        unlockUiDayClosureLeftByTheScenario();
    }

    /**
     * ZMSKVR-42: UI lock leftovers (e.g. failed after month overview) are unlocked via API
     * using the recorded scope/date — not the current browser page. Scope/date alone are
     * enough: the banner assertion may have failed after the lock POST succeeded.
     * API-only scenarios (and threads whose TestData map was already cleared) must no-op.
     */
    private void unlockUiDayClosureLeftByTheScenario() {
        String scope = optionalTestData("day_closure_ui_scope");
        String date = optionalTestData("day_closure_ui_date");
        boolean flaggedLocked = "true".equals(optionalTestData("day_closure_ui_locked"));
        if (scope == null || date == null || scope.isBlank() || date.isBlank()) {
            if (flaggedLocked) {
                ScenarioLogManager.getLogger().warn(
                        "UI day closure flagged locked but day_closure_ui_scope/date missing; leaving flag set");
            }
            return;
        }
        try {
            unlockDayClosureViaApiIfLocked(Integer.parseInt(scope), date);
            TestDataHelper.setTestData("day_closure_ui_locked", "false");
        } catch (RuntimeException | AssertionError e) {
            ScenarioLogManager.getLogger().warn(
                    "Could not unlock UI day closure for scope {} on {}: {}",
                    scope,
                    date,
                    e.getMessage());
        }
    }

    /** Null-safe TestDataHelper read — getTestData NPEs when the scenario map is unset. */
    private static String optionalTestData(String key) {
        try {
            return TestDataHelper.getTestData(key);
        } catch (NullPointerException | IllegalStateException ignored) {
            return null;
        }
    }

    private void unlockDayClosureViaApiIfLocked(int scopeId, String date) {
        AccountCheckout.checkout("scope:" + scopeId + ":day-closure");
        iAmLoggedInToTheZmsApiAs("appointment_admin");
        if (!isDayClosureLocked(scopeId, date)) {
            ScenarioLogManager.getLogger().info(
                    "Day closure for scope {} on {} already unlocked", scopeId, date);
            return;
        }
        Response toggle = postDayClosureToggle(scopeId, date);
        Assertions.assertThat(toggle.getStatusCode())
                .as("cleanup toggle closure scope %d on %s body=%s",
                        scopeId, date, truncate(toggle.asString(), 500))
                .isEqualTo(200);
        JsonNode existing = parseDataNode(toggle).path("existing");
        Assertions.assertThat(existing.isBoolean())
                .as("cleanup toggle data.existing must be boolean")
                .isTrue();
        Assertions.assertThat(existing.asBoolean())
                .as("cleanup should leave scope %d on %s unlocked", scopeId, date)
                .isFalse();
    }

    /**
     * Ensure the day starts unlocked so a following toggle creates a lock owned by this scenario.
     * If another run left it locked, unlock without recording ownership for teardown restore.
     */
    @Given("the day closure for scope {int} on date {string} is unlocked with the X-AuthKey")
    public void theDayClosureForScopeOnDateIsUnlocked(int scopeId, String date) {
        AccountCheckout.checkout("scope:" + scopeId + ":day-closure");
        String resolved = resolveDayClosureDate(date);
        if (!isDayClosureLocked(scopeId, resolved)) {
            ScenarioLogManager.getLogger().info(
                    "Day closure for scope {} on {} already unlocked", scopeId, resolved);
            return;
        }
        ScenarioLogManager.getLogger().warn(
                "Day closure for scope {} on {} was locked; unlocking before the scenario owns a toggle",
                scopeId,
                resolved);
        Response toggle = postDayClosureToggle(scopeId, resolved);
        Assertions.assertThat(toggle.getStatusCode())
                .as("ensure-unlocked toggle scope %d on %s body=%s",
                        scopeId, resolved, truncate(toggle.asString(), 500))
                .isEqualTo(200);
        JsonNode existing = parseDataNode(toggle).path("existing");
        Assertions.assertThat(existing.isBoolean()).isTrue();
        Assertions.assertThat(existing.asBoolean())
                .as("ensure-unlocked should leave scope %d on %s unlocked", scopeId, resolved)
                .isFalse();
    }

    private boolean isDayClosureLocked(int scopeId, String date) {
        String authKey = getOrLoginXAuthKey();
        Response list = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
                .queryParam("scopeIds", Integer.toString(scopeId))
                .queryParam("dateFrom", date)
                .queryParam("dateUntil", date)
                .when()
                .get("/closure/");
        Assertions.assertThat(list.getStatusCode())
                .as("GET /closure/ scope %d %s body=%s", scopeId, date, truncate(list.asString(), 500))
                .isEqualTo(200);
        JsonNode data = parseDataNode(list);
        if (data == null) {
            return false;
        }
        if (data.isArray()) {
            for (JsonNode row : data) {
                if (scopeId == row.path("scopeId").asInt()
                        && date.equals(row.path("date").asText())) {
                    return true;
                }
            }
            return false;
        }
        JsonNode items = data.path("items");
        if (items.isArray()) {
            for (JsonNode row : items) {
                if (scopeId == row.path("scopeId").asInt()
                        && date.equals(row.path("date").asText())) {
                    return true;
                }
            }
        }
        return false;
    }

    private void deleteScenarioProcesses() {
        int lastId = lastProcess == null ? 0 : lastProcess.path("id").asInt();
        for (Integer processId : new ArrayList<>(scenarioProcessIds)) {
            boolean removed = deleteProcessById(processId, false);
            if (processId == lastId && removed) {
                lastProcessDeleted = true;
            }
        }
        scenarioProcessIds.clear();
    }

    /** @return true when the process is gone (deleted or already absent) */
    private boolean deleteProcessById(int processId, boolean assertSuccess) {
        if (processId <= 0) {
            return true;
        }
        Integer scopeId = processScopeById.get(processId);
        if (scopeId != null && !ensureWorkstationOnScope(scopeId, assertSuccess)) {
            return false;
        }
        try {
            Response cleanup = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", getOrLoginXAuthKey())
                .queryParam("initiator", "admin")
            .when()
                .delete("/process/" + processId + "/");
            int status = cleanup.getStatusCode();
            if (assertSuccess) {
                CommonApiSteps.setResponse(cleanup);
                response = cleanup;
                Assertions.assertThat(status)
                    .as("DELETE /process/%d/ body=%s", processId, truncate(cleanup.asString(), 1000))
                    .isEqualTo(200);
                return true;
            }
            if (status < 300 || status == 404) {
                return true;
            }
            ScenarioLogManager.getLogger().warn(
                "Could not delete process {}: HTTP {}", processId, status);
            return false;
        } catch (RuntimeException e) {
            if (assertSuccess) {
                throw e;
            }
            ScenarioLogManager.getLogger().warn("Could not delete process {}: {}", processId, e.getMessage());
            return false;
        }
    }

    private void deleteLastProcessIfStillOpen() {
        if (lastProcess == null || lastProcessDeleted) {
            return;
        }
        int processId = lastProcess.path("id").asInt();
        if (processId <= 0) {
            return;
        }
        try {
            Response cleanup = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", getOrLoginXAuthKey())
                .queryParam("initiator", "admin")
            .when()
                .delete("/process/" + processId + "/");
            if (cleanup.getStatusCode() >= 300 && cleanup.getStatusCode() != 404) {
                ScenarioLogManager.getLogger().warn(
                    "Could not delete process {}: HTTP {}", processId, cleanup.getStatusCode());
            }
        } catch (RuntimeException e) {
            ScenarioLogManager.getLogger().warn("Could not delete process {}: {}", processId, e.getMessage());
        }
    }
    
    @Given("the ZMS API is available")
    public void theZmsApiIsAvailable() {
        baseUri = TestConfig.getBaseUri();
        
        given()
            .baseUri(baseUri)
            .header("X-Token", TestConfig.getSecureToken())
        .when()
            .get("/status/")
        .then()
            .statusCode(200);
    }
    
    @When("I make a GET request to {string}")
    public void iMakeAGetRequestTo(String endpoint) {
        var request = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri());
        if ("/status/".equals(endpoint) || endpoint.startsWith("/status/?")) {
            request.header("X-Token", TestConfig.getSecureToken());
        }
        response = request
        .when()
            .get(endpoint);
        CommonApiSteps.setResponse(response);
    }

    @When("I make a GET request to {string} with the X-Token")
    public void iMakeAGetRequestToWithTheXToken(String endpoint) {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-Token", TestConfig.getSecureToken())
        .when()
            .get(endpoint);
        CommonApiSteps.setResponse(response);
    }

    @When("I make a GET request to {string} with the X-AuthKey")
    public void iMakeAGetRequestToWithTheXAuthKey(String endpoint) {
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
        .when()
            .get(endpoint);
        CommonApiSteps.setResponse(response);
    }

    @Given("the ZMS API workstation user is {string}")
    public void theZmsApiWorkstationUserIs(String usernameOrRole) {
        scenarioLoginUsername = usernameOrRole;
        scenarioLoginPassword = null;
        cachedXAuthKey = null;
    }

    @Given("I am logged in to the ZMS API as {string}")
    public void iAmLoggedInToTheZmsApiAs(String usernameOrRole) {
        theZmsApiWorkstationUserIs(usernameOrRole);
        cachedXAuthKey = loginAndExtractAuthKey(workstationLoginCredentials());
    }

    /**
     * Second concurrent workstation session in the same scenario. Uses
     * {@link AccountCheckout#assignAnotherWorkstationLogin} so the first clerk keeps a valid SessionID.
     */
    @Given("I am logged in to the ZMS API as another {string}")
    public void iAmLoggedInToTheZmsApiAsAnother(String usernameOrRole) {
        theZmsApiWorkstationUserIs(usernameOrRole);
        String password = scenarioLoginPassword != null && !scenarioLoginPassword.isBlank()
            ? scenarioLoginPassword
            : defaultWorkstationPassword();
        String login = AccountCheckout.assignAnotherWorkstationLogin(usernameOrRole);
        cachedXAuthKey = loginAndExtractAuthKey(
            new String[] { resolveWorkstationUsername(login), password });
    }

    @When("I make a POST request to {string} with valid id and password")
    public void iMakeAPostRequestToWithValidIdAndPassword(String endpoint) {
        postWorkstationLogin(endpoint);
    }

    @When("I make a POST request to {string} as {string}")
    public void iMakeAPostRequestToAs(String endpoint, String usernameOrRole) {
        theZmsApiWorkstationUserIs(usernameOrRole);
        postWorkstationLogin(endpoint);
    }

    @Then("the response meta should contain exception {string}")
    public void theResponseMetaShouldContainException(String expectedExceptionShortName) {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        if (response == null) {
            throw new IllegalStateException("No response available. Make sure an API call was made before asserting response contents.");
        }

        ApiResponse<?> apiResponse = as(response, new TypeReference<ApiResponse<Object>>() {});
        Assertions.assertThat(apiResponse).isNotNull();
        Assertions.assertThat(apiResponse.getMeta()).isNotNull();

        String exception = apiResponse.getMeta().getException();
        Assertions.assertThat(exception)
            .as("meta.exception should be present")
            .isNotBlank();

        Assertions.assertThat(exception)
            .as("meta.exception should contain expected exception short name")
            .contains(expectedExceptionShortName);
    }

    /**
     * ZMSKVR-533 / ZMSKVR-661 — POST /useraccount/ must reject ids outside
     * {@code ^[-a-z0-9._]+(@keycloak)?$}.
     */
    @When("I try to create a useraccount with invalid username {string} with the X-AuthKey")
    public void iTryToCreateAUseraccountWithInvalidUsernameWithTheXAuthKey(String username) {
        Integer departmentId = AccountCheckout.checkedOutDepartmentId();
        Assertions.assertThat(departmentId)
            .as("user_admin login must remember a department for the new account")
            .isNotNull()
            .isPositive();

        ObjectNode body = MAPPER.createObjectNode();
        body.put("id", username);
        body.put("password", "unittest");
        ArrayNode roles = MAPPER.createArrayNode();
        roles.add("agent_queue");
        body.set("roles", roles);
        ArrayNode departments = MAPPER.createArrayNode();
        ObjectNode department = MAPPER.createObjectNode();
        department.put("id", departmentId);
        departments.add(department);
        body.set("departments", departments);

        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/useraccount/");
        CommonApiSteps.setResponse(response);
    }

    @Then("the response should reject the username with the character restriction message")
    public void theResponseShouldRejectTheUsernameWithTheCharacterRestrictionMessage() {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        Assertions.assertThat(response).isNotNull();
        Assertions.assertThat(response.getStatusCode()).isEqualTo(400);
        String body = response.asString();
        Assertions.assertThat(body)
            .contains("SchemaValidation")
            .contains("Der Nutzername darf nur Kleinbuchstaben, Zahlen, Punkte, Unterstriche und Bindestriche enthalten");
    }

    @Then("the response should contain config information")
    public void theResponseShouldContainConfigInformation() {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        Assertions.assertThat(response).isNotNull();
        String body = response.asString();
        Assertions.assertThat(body).contains("config.json");
    }

    @Then("the response should contain workstation information")
    public void theResponseShouldContainWorkstationInformation() {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        Assertions.assertThat(response).isNotNull();
        String body = response.asString();
        Assertions.assertThat(body).contains("workstation.json");
    }

    @When("I update the workstation with scope {int} and counter {string} with the X-AuthKey")
    public void iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(int scopeId, String counter) {
        String authKey = getOrLoginXAuthKey();
        Response getResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("resolveReferences", 2)
        .when()
            .get("/workstation/");

        JsonNode workstation = parseDataNode(getResponse);
        if (workstation instanceof ObjectNode objectNode) {
            objectNode.put("name", AccountCheckout.workstationCounter(counter));
            JsonNode scopeNode = objectNode.path("scope");
            if (scopeNode instanceof ObjectNode scopeObject) {
                scopeObject.put("id", scopeId);
            } else {
                ObjectNode scope = MAPPER.createObjectNode();
                scope.put("id", scopeId);
                objectNode.set("scope", scope);
            }
            JsonNode useraccount = objectNode.path("useraccount");
            if (useraccount instanceof ObjectNode useraccountObject) {
                useraccountObject.remove("departments");
            }
        }

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(workstation))
        .when()
            .post("/workstation/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() != 200) {
            ScenarioLogManager.getLogger().error(
                "POST /workstation/ failed with {}: {}",
                response.getStatusCode(),
                truncate(response.asString(), 1000));
        } else {
            workstationScopeId = scopeId;
        }
    }

    /** ZMS-3212 / ZMS-3503: POST /scope/{id}/emergency/ */
    @When("I trigger the emergency for scope {int} with the X-AuthKey")
    public void iTriggerTheEmergencyForScopeWithTheXAuthKey(int scopeId) {
        rememberEmergencyScope(scopeId);
        response = postOrDeleteEmergency(scopeId, true);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("POST /scope/%d/emergency/ body=%s", scopeId, truncate(response.asString(), 800))
                .isEqualTo(200);
    }

    /** ZMS-3503: DELETE /scope/{id}/emergency/ */
    @When("I stop the emergency for scope {int} with the X-AuthKey")
    public void iStopTheEmergencyForScopeWithTheXAuthKey(int scopeId) {
        rememberEmergencyScope(scopeId);
        response = postOrDeleteEmergency(scopeId, false);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("DELETE /scope/%d/emergency/ body=%s", scopeId, truncate(response.asString(), 800))
                .isEqualTo(200);
    }

    @When("I set emergencyEnabled on scope {int} to {word} with the X-AuthKey")
    public void iSetEmergencyEnabledOnScopeToWithTheXAuthKey(int scopeId, String enabledWord) {
        boolean enabled = Boolean.parseBoolean(enabledWord);
        rememberEmergencyScope(scopeId);
        response = setScopeEmergencyEnabled(scopeId, enabled);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("POST /scope/%d/ emergencyEnabled=%s body=%s",
                        scopeId, enabled, truncate(response.asString(), 800))
                .isEqualTo(200);
    }

    @Then("scope {int} emergency activated flag should be {int} with the X-AuthKey")
    public void scopeEmergencyActivatedFlagShouldBeWithTheXAuthKey(int scopeId, int expected) {
        JsonNode emergency = fetchScopeEmergencyStatus(scopeId);
        Assertions.assertThat(emergency.path("activated").asInt(-1))
                .as("scope %d status.emergency.activated", scopeId)
                .isEqualTo(expected);
    }

    private void rememberEmergencyScope(int scopeId) {
        if (emergencyScopeId == null) {
            emergencyScopeId = scopeId;
            JsonNode scope = fetchScope(scopeId, 1);
            emergencyEnabledBeforeScenario = "1".equals(
                    scope.path("preferences").path("workstation").path("emergencyEnabled").asText("0"));
        }
    }

    private Response postOrDeleteEmergency(int scopeId, boolean trigger) {
        String authKey = getOrLoginXAuthKey();
        var spec = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .body("{}");
        if (trigger) {
            return spec.when().post("/scope/" + scopeId + "/emergency/");
        }
        return spec.when().delete("/scope/" + scopeId + "/emergency/");
    }

    private Response setScopeEmergencyEnabled(int scopeId, boolean enabled) {
        String authKey = getOrLoginXAuthKey();
        JsonNode scope = fetchScope(scopeId, 1);
        ObjectNode body = (ObjectNode) scope.deepCopy();
        ObjectNode preferences = body.has("preferences") && body.get("preferences").isObject()
                ? (ObjectNode) body.get("preferences")
                : body.putObject("preferences");
        ObjectNode workstation = preferences.has("workstation") && preferences.get("workstation").isObject()
                ? (ObjectNode) preferences.get("workstation")
                : preferences.putObject("workstation");
        workstation.put("emergencyEnabled", enabled ? "1" : "0");
        return given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .body(toJson(body))
                .when()
                .post("/scope/" + scopeId + "/");
    }

    private JsonNode fetchScope(int scopeId, int resolveReferences) {
        String authKey = getOrLoginXAuthKey();
        Response scopeResponse = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
                .queryParam("resolveReferences", resolveReferences)
                .when()
                .get("/scope/" + scopeId + "/");
        Assertions.assertThat(scopeResponse.getStatusCode())
                .as("GET /scope/%d/ body=%s", scopeId, truncate(scopeResponse.asString(), 800))
                .isEqualTo(200);
        JsonNode scope = parseDataNode(scopeResponse);
        Assertions.assertThat(scope).as("scope %d payload", scopeId).isNotNull();
        return scope;
    }

    private JsonNode fetchScopeEmergencyStatus(int scopeId) {
        JsonNode scope = fetchScope(scopeId, 1);
        return scope.path("status").path("emergency");
    }

    @When("I queue a walk-in at scope {int} with service {string} and name {string} with the X-AuthKey")
    public void iQueueAWalkInAtScopeWithServiceAndNameWithTheXAuthKey(
            int scopeId, String serviceName, String familyName) {
        queueWalkIn(scopeId, serviceName, familyName, null);
    }

    @When("I queue a walk-in at scope {int} with service {string}, name {string} and amendment {string} with the X-AuthKey")
    public void iQueueAWalkInAtScopeWithServiceNameAndAmendmentWithTheXAuthKey(
            int scopeId, String serviceName, String familyName, String amendment) {
        queueWalkIn(scopeId, serviceName, familyName, amendment);
    }

    private void queueWalkIn(int scopeId, String serviceName, String familyName, String amendment) {
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        ObjectNode appointment = MAPPER.createObjectNode();
        appointment.set("scope", scope.deepCopy());
        appointment.put("date", 0);
        ObjectNode client = MAPPER.createObjectNode();
        client.put("familyName", familyName);
        client.put("email", "zmskvr1564@example.com");
        client.put("surveyAccepted", 1);
        ObjectNode process = MAPPER.createObjectNode();
        process.put("status", "queued");
        if (amendment != null && !amendment.isBlank()) {
            process.put("amendment", amendment);
        }
        process.set("scope", scope);
        process.set("appointments", MAPPER.createArrayNode().add(appointment));
        process.set("requests", MAPPER.createArrayNode().add(request.deepCopy()));
        process.set("clients", MAPPER.createArrayNode().add(client));

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(process))
        .when()
            .post("/workstation/process/waitingnumber/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("POST /workstation/process/waitingnumber/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        rememberProcess(parseDataNode(response));
    }

    @When("for scope {int} and service {string} an appointment customer {string} is created at the next minute.")
    public void fuerStandortWirdEinTerminkundeZurNaechstenMinuteAngelegt(
            int scopeId, String serviceName, String familyName) {
        bookAppointmentCustomer(scopeId, serviceName, familyName, 0, false, true);
    }

    /**
     * Same intern booking as the next-minute step, but the slot stays far enough ahead that
     * a later citizen cancel is still in the future, and today may already be closed.
     */
    @When("for scope {int} and service {string} an appointment customer {string} is created at least {int} minutes ahead.")
    public void fuerStandortWirdEinTerminkundeMitVorlaufAngelegt(
            int scopeId, String serviceName, String familyName, int minutesAhead) {
        bookAppointmentCustomer(scopeId, serviceName, familyName, minutesAhead, true, true);
    }

    /**
     * ZMSKVR-405 / ZMSKVR-549: confirmed Terminkunde whose appointment day is after today
     * (ProcessNotCurrentDate for calledprocess / POST called).
     */
    @When("for scope {int} and service {string} an appointment customer {string} is created on a future calendar day.")
    public void fuerStandortWirdEinTerminkundeAnEinemZukuenftigenKalendertagAngelegt(
            int scopeId, String serviceName, String familyName) {
        bookAppointmentCustomerOnFutureDay(scopeId, serviceName, familyName, true);
    }

    /**
     * ZMSKVR-375: leave the process as reserved (same-day) so ProcessNotCallable applies
     * to both calledprocess and selectedprocess without ProcessNotCurrentDate winning first.
     * Prefer a slightly later slot so outline examples (reserved → preconfirmed → …) do not
     * starve the narrow next-minute / 6-minute free window (ProcessReserveFailed).
     */
    @When("for scope {int} and service {string} a reserved appointment customer {string} is created at the next minute.")
    public void fuerStandortWirdEinReservierterTerminkundeZurNaechstenMinuteAngelegt(
            int scopeId, String serviceName, String familyName) {
        bookAppointmentCustomer(scopeId, serviceName, familyName, 2, true, false);
    }

    /**
     * ZMSKVR-375: reserved → preconfirmed so GET /process/{id}/ returns ProcessNotCallable.
     * Stagger minutesAhead from the reserved fixture so sequential outline examples do not
     * collide on the same intern slot.
     */
    @When("for scope {int} and service {string} a preconfirmed appointment customer {string} is created at the next minute.")
    public void fuerStandortWirdEinVorbestaetigterTerminkundeZurNaechstenMinuteAngelegt(
            int scopeId, String serviceName, String familyName) {
        bookAppointmentCustomer(scopeId, serviceName, familyName, 4, true, false);
        Assertions.assertThat(lastProcess).as("reserved process before preconfirm").isNotNull();
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("resolveReferences", 2)
            .body(toJson(lastProcess))
        .when()
            .post("/process/status/preconfirmed/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("POST /process/status/preconfirmed/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        rememberProcess(parseDataNode(response));
        Assertions.assertThat(lastProcess.path("status").asText())
            .as("process status after preconfirm")
            .isEqualTo("preconfirmed");
    }

    /**
     * ZMSKVR-375: confirmed Terminkunde then citizen-cancel ({@code DELETE /process/{id}/{authKey}/})
     * so status is {@code deleted} (ProcessNotCallable). Admin quick-delete leaves {@code blocked},
     * which WorkstationProcessGet does not treat as ProcessNotCallable.
     */
    @When("for scope {int} and service {string} a deleted appointment customer {string} is created at the next minute.")
    public void fuerStandortWirdEinGeloeschterTerminkundeZurNaechstenMinuteAngelegt(
            int scopeId, String serviceName, String familyName) {
        // Prefer a slightly later slot so parallel outline examples do not starve next-minute capacity.
        bookAppointmentCustomer(scopeId, serviceName, familyName, 5, true, true);
        Assertions.assertThat(lastProcess).as("confirmed process before delete").isNotNull();
        int processId = lastProcess.path("id").asInt();
        String processAuthKey = lastProcess.path("authKey").asText();
        Assertions.assertThat(processId).as("process id").isPositive();
        Assertions.assertThat(processAuthKey).as("process authKey").isNotBlank();
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("initiator", "admin")
        .when()
            .delete("/process/" + processId + "/" + processAuthKey + "/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("DELETE /process/%d/{authKey}/ body=%s", processId, truncate(response.asString(), 1000))
            .isEqualTo(200);
        JsonNode deleted = parseDataNode(response);
        Assertions.assertThat(deleted).as("DELETE /process/{id}/{authKey}/ data").isNotNull();
        rememberProcess(deleted);
        lastProcessDeleted = true;
        Assertions.assertThat(lastProcess.path("status").asText())
            .as("process status after citizen-cancel delete")
            .isEqualTo("deleted");
    }

    /**
     * ZMSKVR-375: confirmed same-day Terminkunde successfully called (status called).
     * Calls via a second {@code ataf} workstation session so the process stays assigned to that
     * holder desk. The scenario's primary clerk (and the UI {@code ataf_superuser_*} login) then
     * hit ProcessNotCallable on GET / {@code ?calledprocess=} instead of reopening the live call panel.
     */
    @When("for scope {int} and service {string} a called appointment customer {string} is created at the next minute.")
    public void fuerStandortWirdEinAufgerufenerTerminkundeZurNaechstenMinuteAngelegt(
            int scopeId, String serviceName, String familyName) {
        bookAppointmentCustomer(scopeId, serviceName, familyName, 5, true, true);
        Assertions.assertThat(lastProcess).as("booked process before call").isNotNull();
        JsonNode booked = Objects.requireNonNull(lastProcess, "booked process before call");
        Assertions.assertThat(appointmentIsToday(booked))
            .as("called fixture needs a same-day appointment; process=%s",
                truncate(booked.toString(), 400))
            .isTrue();
        int processId = booked.path("id").asInt();
        String primaryAuthKey = getOrLoginXAuthKey();
        String primaryUsername = scenarioLoginUsername;
        String primaryPassword = scenarioLoginPassword;

        // Hold the call on a desk the UI / primary clerk never use (features use Platz 13).
        // Same desk as the UI clerk races Parallel ProcessNotCallable scenarios and can clear
        // the holder assignment so ?calledprocess= never shows ProcessNotCallable (Firefox flake).
        iAmLoggedInToTheZmsApiAsAnother("ataf");
        String holderAuthKey = getOrLoginXAuthKey();
        iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "97");
        Assertions.assertThat(response.getStatusCode())
            .as("holder workstation scope for call body=%s", truncate(response.asString(), 500))
            .isEqualTo(200);
        response = postProcessCalled(holderAuthKey, true, booked);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("POST /workstation/process/called/ (holder) body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        JsonNode assigned = refreshAssignedProcessFromWorkstation(holderAuthKey);
        Assertions.assertThat(assigned).as("holder assigned process after call").isNotNull();
        Assertions.assertThat(assigned.path("id").asInt())
            .as("holder workstation must hold process id=%d; body=%s",
                processId, truncate(response.asString(), 800))
            .isEqualTo(processId);
        Assertions.assertThat(assigned.path("status").asText())
            .as("process status after holder call")
            .isEqualTo("called");
        rememberProcess(assigned);

        // Restore the scenario's primary clerk session for later GET / UI steps.
        // Do not rebind primary to Platz 13 — that races the UI clerk on the same desk during
        // slow Firefox scope checkout and can drop the holder assignment on desk 97.
        cachedXAuthKey = primaryAuthKey;
        scenarioLoginUsername = primaryUsername;
        scenarioLoginPassword = primaryPassword;
    }

    /**
     * ZMSKVR-375: called then moved to processing (ProcessNotCallable on GET).
     */
    @When("for scope {int} and service {string} a processing appointment customer {string} is created at the next minute.")
    public void fuerStandortWirdEinInBearbeitungTerminkundeZurNaechstenMinuteAngelegt(
            int scopeId, String serviceName, String familyName) {
        fuerStandortWirdEinAufgerufenerTerminkundeZurNaechstenMinuteAngelegt(scopeId, serviceName, familyName);
        forceLastProcessStatus("processing");
        Assertions.assertThat(response.getStatusCode())
            .as("POST process status processing body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        Assertions.assertThat(lastProcess.path("status").asText())
            .as("process status after processing")
            .isEqualTo("processing");
    }

    @Then("the last process appointment is not today")
    public void theLastProcessAppointmentIsNotToday() {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(appointmentIsToday(lastProcess))
            .as("last process appointment must be on a future/past day for ProcessNotCurrentDate; process=%s",
                truncate(lastProcess.toString(), 400))
            .isFalse();
    }

    @When("I request the last process with the X-AuthKey")
    public void iRequestTheLastProcessWithTheXAuthKey() {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        int processId = lastProcess.path("id").asInt();
        Assertions.assertThat(processId).as("process id").isPositive();
        iRequestProcessWithTheXAuthKey(processId);
    }

    @When("I request process {int} with the X-AuthKey")
    public void iRequestProcessWithTheXAuthKey(int processId) {
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("resolveReferences", 1)
        .when()
            .get("/process/" + processId + "/");
        CommonApiSteps.setResponse(response);
        ScenarioLogManager.getLogger().info(
            "GET /process/{}/ status={} body={}",
            processId,
            response.getStatusCode(),
            truncate(response.asString(), 800));
    }

    /**
     * Attempts POST /workstation/process/called/ without replacing a future appointment with a walk-in.
     * Used for ProcessNotCurrentDate / MatchScope / reserved-call coverage (ZMSKVR-405 / 375).
     * {@code allowClusterWideCall=false} so WorkstationProcessMatchScopeFailed can surface on call.
     */
    @When("I try to call the last process at the workstation with the X-AuthKey")
    public void iTryToCallTheLastProcessAtTheWorkstationWithTheXAuthKey() {
        Assertions.assertThat(lastProcess)
            .as("Reserve an appointment before trying to call it")
            .isNotNull();
        String authKey = getOrLoginXAuthKey();
        response = postProcessCalled(authKey, false, lastProcess);
        CommonApiSteps.setResponse(response);
        ScenarioLogManager.getLogger().info(
            "POST /workstation/process/called/ (no walk-in fallback) status={} body={}",
            response.getStatusCode(),
            truncate(response.asString(), 800));
    }

    /**
     * Call a known-missing process id (no walk-in fallback) for ProcessNotFound on the call path.
     */
    @When("I try to call process {int} at the workstation with the X-AuthKey")
    public void iTryToCallProcessAtTheWorkstationWithTheXAuthKey(int processId) {
        String authKey = getOrLoginXAuthKey();
        ObjectNode body = MAPPER.createObjectNode();
        body.put("id", processId);
        body.put("authKey", "missing");
        response = postProcessCalled(authKey, false, body);
        CommonApiSteps.setResponse(response);
        ScenarioLogManager.getLogger().info(
            "POST /workstation/process/called/ processId={} (expect ProcessNotFound) status={} body={}",
            processId,
            response.getStatusCode(),
            truncate(response.asString(), 800));
    }

    /**
     * ZMSKVR-230: Admin Termindauer is {@code timeSlotCount × slotTimeInMinutes}
     * (see appointment form.twig). GET /scope/{id}/request/ exposes timeSlotCount from
     * the provider relation; slot time comes from the scope provider.
     */
    @Then("scope {int} request {string} should have duration {int} minutes")
    public void scopeRequestShouldHaveDuration(int scopeId, String serviceName, int minutes) {
        // Prefer the current session (feature logs in as ataf). Do not force agent_queue:
        // that account is only Behörde 40 and cannot GET other scopes.
        if (cachedXAuthKey == null || cachedXAuthKey.isBlank()) {
            iAmLoggedInToTheZmsApiAs("ataf");
        }
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        Assertions.assertThat(request.path("name").asText("").toLowerCase(Locale.ROOT))
                .as("scope %d request list should include %s", scopeId, serviceName)
                .contains(serviceName.toLowerCase(Locale.ROOT));
        int timeSlotCount = request.path("timeSlotCount").asInt(0);
        int slotTimeInMinutes = fetchScopeSlotTimeInMinutes(scopeId, authKey);
        int duration = timeSlotCount * slotTimeInMinutes;
        Assertions.assertThat(duration)
                .as("scope %d request %s duration (timeSlotCount=%d × slotTimeInMinutes=%d, name=%s)",
                        scopeId, serviceName, timeSlotCount, slotTimeInMinutes,
                        request.path("name").asText())
                .isEqualTo(minutes);
    }

    private int fetchScopeSlotTimeInMinutes(int scopeId, String authKey) {
        // resolveReferences=1 loads provider.data.slotTimeInMinutes (Admin Termindauer input).
        Response scopeResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("resolveReferences", 1)
        .when()
            .get("/scope/" + scopeId + "/");
        Assertions.assertThat(scopeResponse.getStatusCode())
            .as("GET /scope/%d/?resolveReferences=1 body=%s", scopeId, truncate(scopeResponse.asString(), 1000))
            .isEqualTo(200);
        JsonNode scope = parseDataNode(scopeResponse);
        Assertions.assertThat(scope)
            .as("GET /scope/%d/?resolveReferences=1 should return data", scopeId)
            .isNotNull();
        int slotTime = scope.path("provider").path("data").path("slotTimeInMinutes").asInt(0);
        Assertions.assertThat(slotTime)
            .as("scope %d provider.data.slotTimeInMinutes", scopeId)
            .isGreaterThan(0);
        return slotTime;
    }

    /**
     * ZMSKVR-1345 / ZMSKVR-1461: restrictedscope users cannot change provider/source on POST /scope/{id}/.
     */
    @When("I try to change the provider source of scope {int} to {string} with the X-AuthKey")
    public void iTryToChangeTheProviderSourceOfScopeToWithTheXAuthKey(int scopeId, String newSource) {
        String authKey = getOrLoginXAuthKey();
        Response getResponse = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("resolveReferences", 1)
        .when()
            .get("/scope/" + scopeId + "/");
        Assertions.assertThat(getResponse.getStatusCode())
            .as("GET /scope/%d/ before locked update body=%s", scopeId, truncate(getResponse.asString(), 1000))
            .isEqualTo(200);
        JsonNode scope = parseDataNode(getResponse);
        Assertions.assertThat(scope).as("scope %d payload", scopeId).isNotNull();
        rememberedScopeProviderId = scope.path("provider").path("id").asText("");
        rememberedScopeProviderSource = scope.path("provider").path("source").asText("");
        Assertions.assertThat(rememberedScopeProviderId)
            .as("scope %d must already have a provider id", scopeId)
            .isNotBlank();

        ObjectNode body = (ObjectNode) scope.deepCopy();
        ObjectNode provider = body.has("provider") && body.get("provider").isObject()
            ? (ObjectNode) body.get("provider")
            : body.putObject("provider");
        provider.put("id", 999999);
        provider.put("source", newSource);

        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/scope/" + scopeId + "/");
        CommonApiSteps.setResponse(response);
    }

    @Then("the scope provider source is still {string}")
    public void theScopeProviderSourceIsStill(String expectedSource) {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        JsonNode scope = parseDataNode(response);
        Assertions.assertThat(scope.path("provider").path("source").asText())
            .as("provider.source after locked update")
            .isEqualTo(expectedSource);
    }

    @Then("the scope provider id is unchanged")
    public void theScopeProviderIdIsUnchanged() {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        Assertions.assertThat(rememberedScopeProviderId)
            .as("remembered provider id from GET before update")
            .isNotBlank();
        JsonNode scope = parseDataNode(response);
        Assertions.assertThat(scope.path("provider").path("id").asText())
            .as("provider.id after locked update")
            .isEqualTo(rememberedScopeProviderId);
    }

    @Then("the owner name is {string}")
    public void theOwnerNameIs(String expectedName) {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        JsonNode owner = parseDataNode(response);
        Assertions.assertThat(owner.path("name").asText())
            .as("owner.name")
            .isEqualTo(expectedName);
    }

    /**
     * Placeholder glue for ZMSKVR-1460 (@ignore). Implement when the API rejects zero durations.
     */
    @When("I update scope {int} with activationDuration {int} with the X-AuthKey")
    public void iUpdateScopeWithActivationDurationWithTheXAuthKey(int scopeId, int activationDuration) {
        Assertions.fail("ZMSKVR-1460 not implemented: POST /scope/" + scopeId + "/ with activationDuration="
                + activationDuration);
    }

    @When("I update scope {int} with reservationDuration {int} with the X-AuthKey")
    public void iUpdateScopeWithReservationDurationWithTheXAuthKey(int scopeId, int reservationDuration) {
        Assertions.fail("ZMSKVR-1460 not implemented: POST /scope/" + scopeId + "/ with reservationDuration="
                + reservationDuration);
    }

    @When("I update scope {int} with processingTimeAverage {int} with the X-AuthKey")
    public void iUpdateScopeWithProcessingTimeAverageWithTheXAuthKey(int scopeId, int processingTimeAverage) {
        Assertions.fail("ZMSKVR-1460 not implemented: POST /scope/" + scopeId + "/ with processingTimeAverage="
                + processingTimeAverage);
    }

    /**
     * ZMSKVR-933 / ZMSKVR-934: provider.data.slotTimeInMinutes seeds Admin Terminabstand.
     */
    @Then("scope {int} provider data should have slotTimeInMinutes {int}")
    public void scopeProviderDataShouldHaveSlotTimeInMinutes(int scopeId, int expectedMinutes) {
        String authKey = getOrLoginXAuthKey();
        int slotTime = fetchScopeSlotTimeInMinutes(scopeId, authKey);
        Assertions.assertThat(slotTime)
            .as("scope %d provider.data.slotTimeInMinutes", scopeId)
            .isEqualTo(expectedMinutes);
    }

    /**
     * ZMSKVR-933 / ZMSKVR-934: live Öffnungszeiten grid from GET /scope/{id}/availability/.
     */
    @Then("scope {int} opening hours should include slotTimeInMinutes {int}")
    public void scopeOpeningHoursShouldIncludeSlotTimeInMinutes(int scopeId, int expectedMinutes) {
        String authKey = getOrLoginXAuthKey();
        LocalDate today = BerlinTime.today();
        LocalDate end = today.plusDays(21);
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("startDate", today.toString())
            .queryParam("endDate", end.toString())
        .when()
            .get("/scope/" + scopeId + "/availability/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("GET /scope/%d/availability/ body=%s", scopeId, truncate(response.asString(), 1000))
            .isEqualTo(200);
        ArrayNode list = parseDataArray(response);
        Assertions.assertThat(list)
            .as("GET /scope/%d/availability/ data", scopeId)
            .isNotNull();
        // Terminabstand lives on type=appointment availabilities (Öffnungszeiten for booking).
        // type=openinghours is a separate open/closed window and is often absent here.
        boolean found = false;
        for (JsonNode item : list) {
            String type = item.path("type").asText();
            if (!"appointment".equals(type) && !"openinghours".equals(type)) {
                continue;
            }
            if (item.path("slotTimeInMinutes").asInt(0) == expectedMinutes) {
                found = true;
                break;
            }
        }
        Assertions.assertThat(found)
            .as(
                    "scope %d availability should include slotTimeInMinutes %d; body=%s",
                    scopeId,
                    expectedMinutes,
                    truncate(response.asString(), 1500))
            .isTrue();
    }

    private void bookAppointmentCustomer(
            int scopeId, String serviceName, String familyName, int minutesAhead, boolean requireServiceName) {
        bookAppointmentCustomer(scopeId, serviceName, familyName, minutesAhead, requireServiceName, true);
    }

    private void bookAppointmentCustomer(
            int scopeId,
            String serviceName,
            String familyName,
            int minutesAhead,
            boolean requireServiceName,
            boolean confirm) {
        // Keep the current API session (and its workstation). A fresh agent_queue login here
        // would drop the clerk's scope and invalidate remembered clerk X-AuthKeys (401 / MatchScopeFailed).
        if (cachedXAuthKey == null || cachedXAuthKey.isBlank()) {
            iAmLoggedInToTheZmsApiAs("agent_queue");
        }
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        if (requireServiceName) {
            Assertions.assertThat(request.path("name").asText("").toLowerCase(Locale.ROOT))
                    .as("scope %d request list should include %s", scopeId, serviceName)
                    .contains(serviceName.toLowerCase(Locale.ROOT));
        }
        long now = Instant.now().getEpochSecond();
        long currentMinute = now - (now % 60);
        long earliest = (now % 60 > 40) ? currentMinute + 60 : currentMinute;
        if (minutesAhead > 0) {
            earliest = Math.max(earliest, now + minutesAhead * 60L);
        }
        long soon = now + 6 * 60;
        long endOfToday = BerlinTime.today().atStartOfDay(java.time.ZoneId.of("Europe/Berlin")).toEpochSecond()
                + 86_400L;
        // free/ is day-scoped: a nonempty today list can be only past slots. Skip those and look ahead.
        JsonNode freeList = minutesAhead > 0
                ? fetchFreeProcessesLookingAhead(scopeId, request, authKey, earliest)
                : fetchFreeProcessesAllowEmpty(scopeId, request, authKey);
        if (minutesAhead <= 0
                && (freeList == null
                        || freeList.isEmpty()
                        || slotsBetween(freeList, earliest, endOfToday).isEmpty())) {
            ScenarioLogManager.getLogger().info(
                    "No eligible intern slots left today on {} for scope {}; looking ahead",
                    BerlinTime.today(),
                    scopeId);
            freeList = fetchFreeProcessesLookingAhead(scopeId, request, authKey, earliest);
        }

        List<JsonNode> candidates = slotsBetween(freeList, earliest, Math.min(soon, endOfToday));
        if (candidates.isEmpty()) {
            candidates = slotsBetween(freeList, earliest, endOfToday);
            if (!candidates.isEmpty()) {
                ScenarioLogManager.getLogger().info(
                        "No intern slot in the next minutes for scope {}; using a later slot today",
                        scopeId);
            }
        }
        if (minutesAhead > 0 && candidates.isEmpty()) {
            long reminderHorizon = now + 24 * 60 * 60;
            candidates = slotsBetween(freeList, earliest, reminderHorizon);
            if (!candidates.isEmpty()) {
                ScenarioLogManager.getLogger().info(
                        "No intern slot today for scope {}; using the next slot inside the reminder window",
                        scopeId);
            }
        }
        Assertions.assertThat(candidates)
                .as(minutesAhead <= 0
                        ? "scope %d needs a callable same-day intern slot from epoch %d (Terminkunde collision)"
                        : "scope %d should have an intern slot from epoch %d",
                        scopeId,
                        earliest)
                .isNotEmpty();

        try {
            reserveAndOptionallyConfirm(
                    scopeId, familyName, request, authKey, candidates, confirm, earliest, endOfToday,
                    minutesAhead, now);
        } catch (IllegalStateException reserveFailed) {
            if (!reserveFailed.getMessage().contains("Failed to reserve process. Maybe someone was faster.")) {
                throw reserveFailed;
            }
            // Parallel API scenarios often race the same free-list snapshot; refresh and retry once.
            ScenarioLogManager.getLogger().warn(
                    "All {} free-slot candidates for scope {} were taken; refreshing free list once",
                    candidates.size(),
                    scopeId);
            JsonNode refreshed = minutesAhead > 0
                    ? fetchFreeProcessesLookingAhead(scopeId, request, authKey, earliest)
                    : fetchFreeProcessesLookingAhead(scopeId, request, authKey, earliest);
            List<JsonNode> retry = slotsBetween(refreshed, earliest, Math.min(soon, endOfToday));
            if (retry.isEmpty()) {
                retry = slotsBetween(refreshed, earliest, endOfToday);
            }
            if (minutesAhead > 0 && retry.isEmpty()) {
                retry = slotsBetween(refreshed, earliest, now + 24 * 60 * 60);
            }
            Assertions.assertThat(retry)
                    .as("scope %d still needs an intern slot after reserve collision from epoch %d",
                            scopeId, earliest)
                    .isNotEmpty();
            reserveAndOptionallyConfirm(
                    scopeId, familyName, request, authKey, retry, confirm, earliest, endOfToday,
                    minutesAhead, now);
        }
    }

    /** Book from tomorrow onward so ProcessNotCurrentDate applies to calledprocess. */
    private void bookAppointmentCustomerOnFutureDay(
            int scopeId, String serviceName, String familyName, boolean confirm) {
        if (cachedXAuthKey == null || cachedXAuthKey.isBlank()) {
            iAmLoggedInToTheZmsApiAs("ataf");
        }
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        Assertions.assertThat(request.path("name").asText("").toLowerCase(Locale.ROOT))
                .as("scope %d request list should include %s", scopeId, serviceName)
                .contains(serviceName.toLowerCase(Locale.ROOT));
        long startOfTomorrow = BerlinTime.today()
                .plusDays(1)
                .atStartOfDay(java.time.ZoneId.of("Europe/Berlin"))
                .toEpochSecond();
        JsonNode freeList = fetchFreeProcessesLookingAhead(scopeId, request, authKey, startOfTomorrow);
        List<JsonNode> candidates = slotsBetween(freeList, startOfTomorrow, startOfTomorrow + 14L * 86_400L);
        Assertions.assertThat(candidates)
                .as("scope %d needs an intern slot on a future calendar day from epoch %d", scopeId, startOfTomorrow)
                .isNotEmpty();
        reserveAndOptionallyConfirm(
                scopeId, familyName, request, authKey, candidates, confirm, startOfTomorrow, 0, 1, 0);
        Assertions.assertThat(appointmentIsToday(lastProcess))
                .as("future-day Terminkunde must not be today; process=%s",
                        truncate(lastProcess != null ? lastProcess.toString() : "", 400))
                .isFalse();
    }

    private void reserveAndOptionallyConfirm(
            int scopeId,
            String familyName,
            JsonNode request,
            String authKey,
            List<JsonNode> candidates,
            boolean confirm,
            long earliest,
            long endOfToday,
            int minutesAhead,
            long now) {
        JsonNode reserved = null;
        for (int i = 0; i < candidates.size(); i++) {
            ObjectNode process = (ObjectNode) candidates.get(i).deepCopy();
            process.set("requests", MAPPER.createArrayNode().add(request.deepCopy()));
            ObjectNode client = MAPPER.createObjectNode();
            client.put("familyName", familyName);
            client.put("email", "muster.wartende@example.com");
            client.put("surveyAccepted", 1);
            process.set("clients", MAPPER.createArrayNode().add(client));

            response = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .queryParam("slotType", "intern")
                .queryParam("clientkey", "")
                .queryParam("slotsRequired", 0)
                .body(toJson(process))
            .when()
                .post("/process/status/reserved/");
            CommonApiSteps.setResponse(response);
            if (response.getStatusCode() == 200) {
                reserved = parseDataNode(response);
                break;
            }
            boolean slotTaken = response.getStatusCode() == 404
                    && response.asString().contains("Failed to reserve process. Maybe someone was faster.");
            if (slotTaken && i < candidates.size() - 1) {
                continue;
            }
            throw new IllegalStateException(
                    "POST /process/status/reserved/ failed with " + response.getStatusCode() + ": "
                            + truncate(response.asString(), 1000));
        }
        if (reserved == null) {
            throw new IllegalStateException(
                    "POST /process/status/reserved/ returned no data for scope " + scopeId + ": "
                            + truncate(response.asString(), 500));
        }

        if (!confirm) {
            JsonNode reservedProcess = Objects.requireNonNull(reserved, "reserved terminkunde");
            rememberProcess(reservedProcess);
            trackProcess(reservedProcess.path("id").asInt(), scopeId);
            long appointment = reservedProcess.path("appointments").path(0).path("date").asLong(0);
            TestDataHelper.setTestData("appointment_epoch", Long.toString(appointment));
            TestDataHelper.setTestData("new_appointment_customer_name", familyName);
            ScenarioLogManager.getLogger().info(
                    "Reserved Terminkunde {} at epoch {} for scope {} (status={})",
                    familyName,
                    appointment,
                    scopeId,
                    reservedProcess.path("status").asText());
            return;
        }

        response = postConfirmedWithDeadlockRetry(authKey, reserved);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("POST /process/status/confirmed/ body=%s", truncate(response.asString(), 1000))
                .isEqualTo(200);
        JsonNode confirmed = parseDataNode(response);
        rememberProcess(confirmed);
        trackProcess(confirmed.path("id").asInt(), scopeId);

        long appointment = confirmed.path("appointments").path(0).path("date").asLong(0);
        if (minutesAhead <= 0 && endOfToday > 0) {
            Assertions.assertThat(appointmentIsToday(confirmed))
                    .as("Terminkunde collision needs a same-day appointment; got epoch %d", appointment)
                    .isTrue();
            Assertions.assertThat(appointment)
                    .as("confirmed same-day appointment time")
                    .isBetween(earliest, endOfToday);
        } else if (minutesAhead > 0 && now > 0 && endOfToday > 0) {
            long reminderHorizon = now + 24 * 60 * 60;
            Assertions.assertThat(appointment)
                    .as("confirmed appointment time")
                    .isBetween(earliest, reminderHorizon);
        }
        TestDataHelper.setTestData("appointment_epoch", Long.toString(appointment));
        TestDataHelper.setTestData("new_appointment_customer_name", familyName);
        ScenarioLogManager.getLogger().info(
                "Terminkunde {} booked at epoch {} for scope {}", familyName, appointment, scopeId);
    }

    @When("I send the confirmation mail for the current appointment")
    public void iSendTheConfirmationMailForTheCurrentAppointment() {
        Assertions.assertThat(lastProcess)
                .as("Book the appointment before sending the confirmation mail")
                .isNotNull();
        int processId = lastProcess.path("id").asInt();
        String processAuthKey = lastProcess.path("authKey").asText("");
        Assertions.assertThat(processId).as("process id").isPositive();
        Assertions.assertThat(processAuthKey).as("process authKey").isNotBlank();
        rememberExpectedDuration(lastProcess);
        String authKey = getOrLoginXAuthKey();
        response = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .body(toJson(lastProcess))
            .when()
                .post("/process/" + processId + "/" + processAuthKey + "/confirmation/mail/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("POST /process/%d/confirmation/mail/ body=%s", processId, truncate(response.asString(), 1000))
                .isEqualTo(200);
        TestDataHelper.setTestData("mail_process_id", Integer.toString(processId));
    }

    @When("I queue the reminder mails that are due")
    public void iQueueTheReminderMailsThatAreDue() {
        Path backend = Path.of(System.getProperty("user.dir")).resolve("../zmsbackend").normalize();
        if (!Files.isRegularFile(backend.resolve("bin/queueMailReminder"))) {
            backend = Path.of("/var/www/html/zmsbackend");
        }
        Path script = backend.resolve("bin/queueMailReminder").toAbsolutePath();
        Assertions.assertThat(Files.isRegularFile(script))
                .as("reminder queue script %s", script)
                .isTrue();
        ProcessBuilder builder = new ProcessBuilder(phpBinary(), script.toString(), "120", "--commit");
        builder.directory(backend.toFile());
        builder.environment().putIfAbsent("ZMS_ENV", "dev");
        builder.environment().put("ZMS_CRONROOT", "1");
        builder.redirectErrorStream(true);
        String logged = "";
        try {
            for (int attempt = 1; attempt <= 3; attempt++) {
                Process queued = builder.start();
                StringBuffer output = new StringBuffer();
                Thread reader = new Thread(() -> {
                    try {
                        output.append(new String(queued.getInputStream().readAllBytes(), StandardCharsets.UTF_8));
                    } catch (IOException io) {
                        output.append(io.getMessage());
                    }
                }, "queueMailReminder-output");
                reader.setDaemon(true);
                reader.start();
                boolean finished;
                try {
                    finished = queued.waitFor(90, TimeUnit.SECONDS);
                } catch (InterruptedException interrupted) {
                    queued.destroyForcibly();
                    Thread.currentThread().interrupt();
                    throw new IllegalStateException("Interrupted while queueing reminder mails", interrupted);
                }
                if (!finished) {
                    queued.destroyForcibly();
                }
                reader.join(2000L);
                logged = truncate(output.toString(), 1500);
                Assertions.assertThat(finished)
                        .as("queueMailReminder did not finish. output=%s", logged)
                        .isTrue();
                if (queued.exitValue() == 0) {
                    ScenarioLogManager.getLogger().info("queueMailReminder finished: {}", truncate(output.toString(), 500));
                    return;
                }
                boolean lostProcess = output.toString().contains("ProcessUpdateFailed");
                if (lostProcess && new ZmsApiMailSteps().currentProcessHasReminderMail()) {
                    ScenarioLogManager.getLogger().info(
                            "queueMailReminder stopped on another appointment; this reminder is already queued");
                    return;
                }
                if (lostProcess && attempt < 3) {
                    ScenarioLogManager.getLogger().info(
                            "queueMailReminder hit a deleted appointment, retry {}", attempt);
                    continue;
                }
                Assertions.assertThat(queued.exitValue())
                        .as("queueMailReminder output=%s", logged)
                        .isEqualTo(0);
            }
        } catch (InterruptedException interrupted) {
            Thread.currentThread().interrupt();
            throw new IllegalStateException("Interrupted while queueing reminder mails", interrupted);
        } catch (IOException io) {
            throw new IllegalStateException("Could not run queueMailReminder", io);
        }
    }

    private static String phpBinary() {
        if (Files.isExecutable(Path.of("/usr/local/bin/php"))) {
            return "/usr/local/bin/php";
        }
        return "/usr/bin/php";
    }

    private void rememberExpectedDuration(JsonNode process) {
        JsonNode appointment = process.path("appointments").path(0);
        int slotCount = appointment.path("slotCount").asInt(0);
        int slotMinutes = appointment.path("availability").path("slotTimeInMinutes").asInt(0);
        if (slotMinutes <= 0) {
            slotMinutes = process.path("scope").path("provider").path("data").path("slotTimeInMinutes").asInt(0);
        }
        String expectedMinutes = slotCount > 0 && slotMinutes > 0
                ? Integer.toString(slotCount * slotMinutes)
                : "";
        Assertions.assertThat(expectedMinutes)
                .as("expected duration must be computable from slotCount %s and slotTimeInMinutes %s",
                        slotCount, slotMinutes)
                .isNotBlank();
        TestDataHelper.setTestData("expected_duration_minutes", expectedMinutes);
        ScenarioLogManager.getLogger().info(
                "Estimated duration inputs slotCount={} slotTimeInMinutes={}", slotCount, slotMinutes);
    }

    @When("for scope {int} and service {string}, {int} waiting customers are created.")
    public void fuerStandortWerdenWartendeAngelegt(int scopeId, String serviceName, int count) {
        Assertions.assertThat(count).isBetween(1, 3);
        iAmLoggedInToTheZmsApiAs("agent_queue");
        iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "22");
        Assertions.assertThat(response.getStatusCode())
            .as("POST /workstation/ for scope %d body=%s", scopeId, truncate(response.asString(), 1000))
            .isEqualTo(200);
        String[] names = {"Muster Waiting Ada", "Muster Waiting Ben", "Muster Waiting Cora"};
        for (int i = 0; i < count; i++) {
            iQueueAWalkInAtScopeWithServiceAndNameWithTheXAuthKey(scopeId, serviceName, names[i]);
            int processId = lastProcess == null ? 0 : lastProcess.path("id").asInt();
            if (processId > 0 && !scenarioProcessIds.contains(processId)) {
                scenarioProcessIds.add(processId);
            }
        }
    }

    @When("the appointments created in this scenario are deleted.")
    public void dieInDiesemSzenarioAngelegtenTermineGeloeschtWerden() {
        iDeleteTheProcessesCreatedInThisScenarioWithTheXAuthKey();
    }

    @Then("the current process display number starts with {string} and is not {string}")
    public void theCurrentProcessDisplayNumberStartsWithAndIsNot(String prefix, String forbidden) {
        Assertions.assertThat(lastProcess)
                .as("Book an appointment before asserting displayNumber")
                .isNotNull();
        String displayNumber = lastProcess.path("displayNumber").asText("");
        int processId = lastProcess.path("id").asInt();
        ScenarioLogManager.getLogger().info(
                "process id={} displayNumber={}", processId, displayNumber);
        Assertions.assertThat(displayNumber)
                .as("displayNumber for process %d", processId)
                .isNotBlank()
                .startsWith(prefix)
                .isNotEqualTo(forbidden);
        Assertions.assertThat(displayNumber)
                .as("prefixed displayNumber must not be only the process id")
                .isNotEqualTo(Integer.toString(processId));
    }

    @Then("the current process display number equals the process id")
    public void theCurrentProcessDisplayNumberEqualsTheProcessId() {
        Assertions.assertThat(lastProcess)
                .as("Book an appointment before asserting displayNumber")
                .isNotNull();
        String displayNumber = lastProcess.path("displayNumber").asText("");
        int processId = lastProcess.path("id").asInt();
        ScenarioLogManager.getLogger().info(
                "process id={} displayNumber={}", processId, displayNumber);
        Assertions.assertThat(processId).as("process id").isPositive();
        Assertions.assertThat(displayNumber)
                .as("displayNumber without prefix equals process id")
                .isEqualTo(Integer.toString(processId));
    }

    @When("I queue a walk-in at scope {int} with service {string}, name {string}, free text {string} and second free text {string} with the X-AuthKey")
    public void iQueueAWalkInWithFreeTextWithTheXAuthKey(
            int scopeId, String serviceName, String familyName, String freeText, String secondFreeText) {
        queueWalkInWithPlainTextFields(scopeId, serviceName, familyName, null, freeText, secondFreeText);
    }

    @When("I queue a walk-in at scope {int} with service {string}, name {string}, amendment {string}, free text {string} and second free text {string} with the X-AuthKey")
    public void iQueueAWalkInWithAmendmentAndFreeTextWithTheXAuthKey(
            int scopeId,
            String serviceName,
            String familyName,
            String amendment,
            String freeText,
            String secondFreeText) {
        queueWalkInWithPlainTextFields(scopeId, serviceName, familyName, amendment, freeText, secondFreeText);
    }

    private void queueWalkInWithPlainTextFields(
            int scopeId,
            String serviceName,
            String familyName,
            String amendment,
            String freeText,
            String secondFreeText) {
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        ObjectNode appointment = MAPPER.createObjectNode();
        appointment.set("scope", scope.deepCopy());
        appointment.put("date", 0);
        ObjectNode client = MAPPER.createObjectNode();
        client.put("familyName", familyName);
        client.put("email", familyName.toLowerCase(Locale.ROOT).replaceAll("[^a-z0-9]", "") + "@mailinator.com");
        client.put("surveyAccepted", 1);
        ObjectNode process = MAPPER.createObjectNode();
        process.put("status", "queued");
        if (amendment != null && !amendment.isBlank()) {
            process.put("amendment", amendment);
        }
        process.put("customTextfield", freeText != null ? freeText : "");
        process.put("customTextfield2", secondFreeText != null ? secondFreeText : "");
        process.set("scope", scope);
        process.set("appointments", MAPPER.createArrayNode().add(appointment));
        process.set("requests", MAPPER.createArrayNode().add(request.deepCopy()));
        process.set("clients", MAPPER.createArrayNode().add(client));

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(process))
        .when()
            .post("/workstation/process/waitingnumber/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("POST /workstation/process/waitingnumber/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        JsonNode queued = parseDataNode(response);
        rememberProcess(queued);
        int processId = queued == null ? 0 : queued.path("id").asInt();
        if (processId > 0 && !scenarioProcessIds.contains(processId)) {
            scenarioProcessIds.add(processId);
        }
    }

    @Then("the last process amendment should be {string}")
    public void theLastProcessAmendmentShouldBe(String expected) {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(lastProcess.path("amendment").asText())
            .as("last process amendment; process=%s", truncate(lastProcess.toString(), 500))
            .isEqualTo(expected);
    }

    @Then("the last process customTextfield should be {string}")
    public void theLastProcessCustomTextfieldShouldBe(String expected) {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(lastProcess.path("customTextfield").asText())
            .as("last process customTextfield; process=%s", truncate(lastProcess.toString(), 500))
            .isEqualTo(expected);
    }

    @Then("the last process amendment should not contain {string}")
    public void theLastProcessAmendmentShouldNotContain(String forbidden) {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(lastProcess.path("amendment").asText())
            .as("last process amendment must not contain %s", forbidden)
            .doesNotContain(forbidden);
    }

    @Then("the last process customTextfield should not contain {string}")
    public void theLastProcessCustomTextfieldShouldNotContain(String forbidden) {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(lastProcess.path("customTextfield").asText())
            .as("last process customTextfield must not contain %s", forbidden)
            .doesNotContain(forbidden);
    }

    @When("I delete the processes created in this scenario with the X-AuthKey")
    public void iDeleteTheProcessesCreatedInThisScenarioWithTheXAuthKey() {
        Assertions.assertThat(scenarioProcessIds)
            .as("Queue the walk-ins before deleting them")
            .isNotEmpty();
        for (Integer processId : new ArrayList<>(scenarioProcessIds)) {
            deleteProcessById(processId, true);
        }
        scenarioProcessIds.clear();
        lastProcessDeleted = true;
    }

    @When("I reserve an appointment at scope {int} with service {string} and amendment {string} with the X-AuthKey")
    public void iReserveAnAppointmentAtScopeWithServiceAndAmendmentWithTheXAuthKey(
            int scopeId, String serviceName, String amendment) {
        String familyName = TestPropertiesHelper.getPropertyAsString("zmsapiAppointmentFamilyName", true, "Terminkunde");
        String email = TestPropertiesHelper.getPropertyAsString("zmsapiAppointmentEmail", true, "terminkunde@example.com");
        reserveConfirmedAppointment(scopeId, serviceName, familyName, email, amendment, true);
    }

    @When("I reserve an appointment at scope {int} with service {string}, name {string} and amendment {string} with the X-AuthKey")
    public void iReserveAnAppointmentAtScopeWithServiceNameAndAmendmentWithTheXAuthKey(
            int scopeId, String serviceName, String familyName, String amendment) {
        String email = familyName.toLowerCase(Locale.ROOT).replaceAll("[^a-z0-9]", "") + "@mailinator.com";
        reserveConfirmedAppointment(scopeId, serviceName, familyName, email, amendment, true);
    }

    @When("I remember the last process display number and queue number")
    public void iRememberTheLastProcessDisplayNumberAndQueueNumber() {
        Assertions.assertThat(lastProcess)
            .as("Queue or reserve a process before remembering its waiting number")
            .isNotNull();
        rememberedDisplayNumber = lastProcess.path("displayNumber").asText("");
        Assertions.assertThat(rememberedDisplayNumber)
            .as("displayNumber of process %s", lastProcess.path("id").asInt())
            .isNotBlank();
        rememberedQueueNumber = lastProcess.path("queue").path("number").asInt(0);
        Assertions.assertThat(rememberedQueueNumber)
            .as("queue.number of process %s", lastProcess.path("id").asInt())
            .isPositive();
    }

    @When("I redirect the last process to scope {int} with the X-AuthKey")
    public void iRedirectTheLastProcessToScopeWithTheXAuthKey(int targetScopeId) {
        redirectLastProcessToScope(targetScopeId, null);
    }

    @When("I redirect the last process to scope {int} with amendment {string} with the X-AuthKey")
    public void iRedirectTheLastProcessToScopeWithAmendmentWithTheXAuthKey(int targetScopeId, String amendment) {
        redirectLastProcessToScope(targetScopeId, amendment);
    }

    private void redirectLastProcessToScope(int targetScopeId, String amendment) {
        Assertions.assertThat(lastProcess)
            .as("Reserve an appointment before redirecting it")
            .isNotNull();
        int originalId = lastProcess.path("id").asInt();
        Assertions.assertThat(originalId).isPositive();
        trackProcess(originalId, scopeIdOf(lastProcess));

        ObjectNode body = (ObjectNode) lastProcess.deepCopy();
        if (body.path("scope").isObject()) {
            ((ObjectNode) body.get("scope")).put("id", targetScopeId);
        } else {
            body.set("scope", MAPPER.createObjectNode().put("id", targetScopeId));
        }
        JsonNode appointments = body.path("appointments");
        if (appointments.isArray() && appointments.size() > 0 && appointments.get(0).isObject()) {
            ObjectNode appointment = (ObjectNode) appointments.get(0);
            if (appointment.path("scope").isObject()) {
                ((ObjectNode) appointment.get("scope")).put("id", targetScopeId);
            }
        }
        if (amendment != null) {
            body.put("amendment", amendment);
        }

        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/process/status/redirect/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("POST /process/status/redirect/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        JsonNode created = parseDataNode(response);
        Assertions.assertThat(created).as("redirect response data").isNotNull();
        int createdId = created.path("id").asInt();
        // Redirect finishes the source appointment and clears its scope. That row can no longer be deleted.
        forgetProcess(originalId);
        trackProcess(createdId, targetScopeId);
        rememberProcess(created);
    }

    @Then("the last process display number matches the remembered value")
    public void theLastProcessDisplayNumberMatchesTheRememberedValue() {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(rememberedDisplayNumber)
            .as("Remember displayNumber before redirecting")
            .isNotBlank();
        Assertions.assertThat(lastProcess.path("displayNumber").asText(""))
            .as("displayNumber after redirect; process=%s", lastProcess.path("id").asInt())
            .isEqualTo(rememberedDisplayNumber);
    }

    @Then("the last process queue number matches the remembered value")
    public void theLastProcessQueueNumberMatchesTheRememberedValue() {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(rememberedQueueNumber)
            .as("Remember queue.number before redirecting")
            .isNotNull();
        Assertions.assertThat(lastProcess.path("queue").path("number").asInt(0))
            .as("queue.number after redirect; process=%s", lastProcess.path("id").asInt())
            .isEqualTo(rememberedQueueNumber);
    }

    @Then("the last process scope id is {int}")
    public void theLastProcessScopeIdIs(int scopeId) {
        Assertions.assertThat(lastProcess).as("last process").isNotNull();
        Assertions.assertThat(scopeIdOf(lastProcess))
            .as("scope.id after redirect; process=%s", lastProcess.path("id").asInt())
            .isEqualTo(scopeId);
    }

    @Then("the last process has priority {int}")
    public void theLastProcessHasPriority(int priority) {
        Assertions.assertThat(lastProcess)
            .as("Redirect the appointment before reading its priority")
            .isNotNull();
        Assertions.assertThat(lastProcess.path("priority").asInt())
            .as("priority of process %s", lastProcess.path("id").asInt())
            .isEqualTo(priority);
    }

    @When("I delete the last process with the X-AuthKey")
    public void iDeleteTheLastProcessWithTheXAuthKey() {
        Assertions.assertThat(lastProcess)
            .as("Reserve an appointment before deleting it")
            .isNotNull();
        int processId = lastProcess.path("id").asInt();
        Assertions.assertThat(processId).isPositive();
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("initiator", "admin")
        .when()
            .delete("/process/" + processId + "/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("DELETE /process/%d/ body=%s", processId, truncate(response.asString(), 1000))
            .isEqualTo(200);
        lastProcessDeleted = true;
    }

    @When("I search processes for {string} with the X-AuthKey")
    public void iSearchProcessesForWithTheXAuthKey(String query) {
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("query", query)
            .queryParam("resolveReferences", 1)
        .when()
            .get("/process/search/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("GET /process/search/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
    }

    @When("I request a process search for {string} with the X-AuthKey")
    public void iRequestAProcessSearchForWithTheXAuthKey(String query) {
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("query", query)
            .queryParam("resolveReferences", 1)
        .when()
            .get("/process/search/");
        CommonApiSteps.setResponse(response);
    }

    @Then("the workstation has no selected location")
    public void theWorkstationHasNoSelectedLocation() {
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("resolveReferences", 1)
        .when()
            .get("/workstation/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("GET /workstation/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        JsonNode workstation = parseDataNode(response);
        Assertions.assertThat(workstation)
            .as("GET /workstation/ data")
            .isNotNull();
        Assertions.assertThat(workstation.hasNonNull("id"))
            .as("GET /workstation/ data.id")
            .isTrue();
        Assertions.assertThat(workstation.path("useraccount").isObject())
            .as("GET /workstation/ data.useraccount")
            .isTrue();
        int scopeId = workstation.path("scope").path("id").asInt(0);
        Assertions.assertThat(scopeId)
            .as("GET /workstation/ selected a location")
            .isZero();
    }

    @Then("the process search result for {string} has appointment status {string} with booking and cancellation today")
    public void theProcessSearchResultHasAppointmentStatusWithBookingAndCancellationToday(
            String familyName, String appointmentStatus) {
        ArrayNode results = parseDataArray(response);
        Assertions.assertThat(results)
            .as("GET /process/search/ data for %s", familyName)
            .isNotNull();
        JsonNode match = null;
        for (JsonNode row : results) {
            if (familyName.equals(row.path("clients").path(0).path("familyName").asText())
                    && appointmentStatus.equals(row.path("appointmentStatus").asText())) {
                match = row;
                break;
            }
        }
        if (match == null) {
            throw new AssertionError(String.format(
                "search hit %s with status %s. Body=%s",
                familyName,
                appointmentStatus,
                truncate(response.asString(), 1500)));
        }
        long start = BerlinTime.today().atStartOfDay(java.time.ZoneId.of("Europe/Berlin")).toEpochSecond();
        long end = start + 86_400L;
        long booked = match.path("createTimestamp").asLong();
        long cancelled = match.path("finalizedAt").asLong();
        Assertions.assertThat(booked)
            .as("createTimestamp for %s", familyName)
            .isGreaterThanOrEqualTo(start)
            .isLessThan(end);
        Assertions.assertThat(cancelled)
            .as("finalizedAt for %s", familyName)
            .isGreaterThanOrEqualTo(start)
            .isLessThan(end);
    }

    @Then("the process search results include {string}")
    public void theProcessSearchResultsInclude(String familyName) {
        Assertions.assertThat(familyNamesInSearch())
            .as("GET /process/search/ body=%s", truncate(response.asString(), 1500))
            .contains(familyName);
    }

    @Then("the process search results do not include {string}")
    public void theProcessSearchResultsDoNotInclude(String familyName) {
        Assertions.assertThat(familyNamesInSearch())
            .as("GET /process/search/ body=%s", truncate(response.asString(), 1500))
            .doesNotContain(familyName);
    }

    @Then("the process search lists {string} before {string}")
    public void theProcessSearchListsBefore(String earlierName, String laterName) {
        List<String> names = familyNamesInSearch().stream()
            .filter(name -> name.startsWith("Muster John Doe"))
            .toList();
        Assertions.assertThat(names)
            .as("GET /process/search/ past appointments")
            .containsExactly(earlierName, laterName);
    }

    @Then("the process search lists these names:")
    public void theProcessSearchListsTheseNames(DataTable table) {
        List<String> expected = table.asLists().stream().map(row -> row.get(0)).toList();
        List<String> found = familyNamesInSearch().stream()
            .filter(expected::contains)
            .distinct()
            .toList();
        Assertions.assertThat(found)
            .as("GET /process/search/ names. Body=%s", truncate(response.asString(), 1500))
            .containsExactlyInAnyOrderElementsOf(expected);
    }

    @Then("the process {string} has appointment status {string} and a booking time")
    public void theProcessHasAppointmentStatusAndABookingTime(String familyName, String appointmentStatus) {
        JsonNode match = searchRow(familyName);
        Assertions.assertThat(match.path("appointmentStatus").asText())
            .as("appointmentStatus for %s", familyName)
            .isEqualTo(appointmentStatus);
        Assertions.assertThat(match.path("createTimestamp").asLong())
            .as("createTimestamp for %s", familyName)
            .isPositive();
    }

    @Then("the process {string} was called")
    public void theProcessWasCalled(String familyName) {
        Assertions.assertThat(searchRow(familyName).path("queue").path("callTime").asLong())
            .as("callTime for %s", familyName)
            .isPositive();
    }

    @Then("the process {string} was not called")
    public void theProcessWasNotCalled(String familyName) {
        Assertions.assertThat(searchRow(familyName).path("queue").path("callTime").asLong())
            .as("callTime for %s", familyName)
            .isZero();
    }

    private JsonNode searchRow(String familyName) {
        ArrayNode results = parseDataArray(response);
        Assertions.assertThat(results).as("GET /process/search/ data").isNotNull();
        for (JsonNode row : results) {
            if (familyName.equals(row.path("clients").path(0).path("familyName").asText())) {
                return row;
            }
        }
        throw new AssertionError(String.format(
            "search hit %s. Body=%s",
            familyName,
            truncate(response.asString(), 1500)));
    }

    private List<String> familyNamesInSearch() {
        ArrayNode results = parseDataArray(response);
        Assertions.assertThat(results).as("GET /process/search/ data").isNotNull();
        List<String> names = new ArrayList<>();
        for (JsonNode row : results) {
            names.add(row.path("clients").path(0).path("familyName").asText());
        }
        return names;
    }

    private void reserveConfirmedAppointment(
            int scopeId,
            String serviceName,
            String familyName,
            String email,
            String amendment,
            boolean lookAhead) {
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        JsonNode freeList = lookAhead
            ? fetchFreeProcessesLookingAhead(scopeId, request, authKey)
            : fetchFreeProcesses(scopeId, request, authKey);

        JsonNode reserved = null;
        for (int i = 0; i < freeList.size(); i++) {
            ObjectNode process = freeList.get(i).deepCopy();
            process.put("amendment", amendment);
            process.set("requests", MAPPER.createArrayNode().add(request.deepCopy()));

            ObjectNode client = MAPPER.createObjectNode();
            client.put("familyName", familyName);
            client.put("email", email);
            client.put("surveyAccepted", 1);
            ArrayNode clients = MAPPER.createArrayNode();
            clients.add(client);
            process.set("clients", clients);

            response = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .queryParam("slotType", "intern")
                .queryParam("clientkey", "")
                .queryParam("slotsRequired", 0)
                .body(toJson(process))
            .when()
                .post("/process/status/reserved/");
            CommonApiSteps.setResponse(response);
            if (response.getStatusCode() == 200) {
                reserved = parseDataNode(response);
                Assertions.assertThat(reserved)
                    .as("POST /process/status/reserved/ returned 200 without data: %s",
                        truncate(response.asString(), 1000))
                    .isNotNull();
                break;
            }
            boolean slotTaken = response.getStatusCode() == 404
                && response.asString().contains("Failed to reserve process. Maybe someone was faster.");
            if (slotTaken && i < freeList.size() - 1) {
                ScenarioLogManager.getLogger().info(
                    "Intern slot for scope {} was reserved or booked (status {}); trying the next free process",
                    scopeId,
                    response.getStatusCode());
                continue;
            }
            throw new IllegalStateException(
                "POST /process/status/reserved/ failed with " + response.getStatusCode() + ": "
                    + truncate(response.asString(), 1000));
        }
        Assertions.assertThat(reserved)
            .as("POST /process/status/reserved/ for scope %d", scopeId)
            .isNotNull();

        response = postConfirmedWithDeadlockRetry(authKey, reserved);
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    /**
     * Parallel confirms can deadlock on scope display-number updates. Same retry as finish.
     */
    private Response postConfirmedWithDeadlockRetry(String authKey, JsonNode body) {
        Response confirmed =
                given()
                        .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
                        .header("X-AuthKey", authKey)
                        .contentType("application/json")
                        .body(toJson(body))
                        .when()
                        .post("/process/status/confirmed/");
        if (confirmed.getStatusCode() == 500
                && confirmed.asString().toLowerCase(Locale.ROOT).contains("deadlock")) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "Confirm hit a MySQL deadlock; retrying once. body={}",
                            truncate(confirmed.asString(), 300));
            try {
                Thread.sleep(400);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
            }
            confirmed =
                    given()
                            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
                            .header("X-AuthKey", authKey)
                            .contentType("application/json")
                            .body(toJson(body))
                            .when()
                            .post("/process/status/confirmed/");
        }
        return confirmed;
    }

    @When("I call the last process at the workstation with the X-AuthKey")
    public void iCallTheLastProcessAtTheWorkstationWithTheXAuthKey() {
        iCallTheLastProcessAtTheWorkstationWithAllowClusterWideCall(true);
    }

    @When("I call the last process at the workstation with allowClusterWideCall {word}")
    public void iCallTheLastProcessAtTheWorkstationWithAllowClusterWideCall(String allowClusterWideCall) {
        iCallTheLastProcessAtTheWorkstationWithAllowClusterWideCall(Boolean.parseBoolean(allowClusterWideCall));
    }

    @When("I remember the current ZMS API login as clerk {string}")
    public void iRememberTheCurrentZmsApiLoginAsClerk(String clerk) {
        String authKey = getOrLoginXAuthKey();
        Assertions.assertThat(authKey)
            .as("Login before remembering clerk %s", clerk)
            .isNotBlank();
        clerkAuthKeys.put(clerk, authKey);
        ScenarioLogManager.getLogger().info("Remembered ZMS API clerk {} auth key", clerk);
    }

    @When("clerk {string} calls the last process with allowClusterWideCall {word}")
    public void clerkCallsTheLastProcessWithAllowClusterWideCall(String clerk, String allowClusterWideCall) {
        Assertions.assertThat(lastProcess)
            .as("Queue or reserve a process before calling it")
            .isNotNull();
        String authKey = clerkAuthKeys.get(clerk);
        Assertions.assertThat(authKey)
            .as("Remember clerk %s before calling", clerk)
            .isNotBlank();
        assertLastProcessCallableWithoutWalkInFallback();
        response = postProcessCalled(authKey, Boolean.parseBoolean(allowClusterWideCall), lastProcess);
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() == 200) {
            JsonNode workstation = parseDataNode(response);
            rememberProcess(workstation != null ? workstation.path("process") : null);
            cachedXAuthKey = authKey;
        }
    }

    @When("both clerks {string} and {string} call the last process concurrently with allowClusterWideCall {word}")
    public void bothClerksCallTheLastProcessConcurrently(
            String firstClerk, String secondClerk, String allowClusterWideCall) throws Exception {
        Assertions.assertThat(lastProcess)
            .as("Queue or reserve a process before calling it")
            .isNotNull();
        String firstKey = clerkAuthKeys.get(firstClerk);
        String secondKey = clerkAuthKeys.get(secondClerk);
        Assertions.assertThat(firstKey).as("Remember clerk %s", firstClerk).isNotBlank();
        Assertions.assertThat(secondKey).as("Remember clerk %s", secondClerk).isNotBlank();
        boolean allow = Boolean.parseBoolean(allowClusterWideCall);
        assertLastProcessCallableWithoutWalkInFallback();
        JsonNode processBody = lastProcess.deepCopy();

        CountDownLatch ready = new CountDownLatch(2);
        CountDownLatch go = new CountDownLatch(1);
        ExecutorService pool = Executors.newFixedThreadPool(2);
        try {
            Future<Response> firstFuture = pool.submit(() -> {
                ready.countDown();
                if (!go.await(30, TimeUnit.SECONDS)) {
                    throw new IllegalStateException("Timed out waiting to start concurrent call");
                }
                return postProcessCalled(firstKey, allow, processBody);
            });
            Future<Response> secondFuture = pool.submit(() -> {
                ready.countDown();
                if (!go.await(30, TimeUnit.SECONDS)) {
                    throw new IllegalStateException("Timed out waiting to start concurrent call");
                }
                return postProcessCalled(secondKey, allow, processBody);
            });
            Assertions.assertThat(ready.await(30, TimeUnit.SECONDS))
                .as("Both clerk call threads should be ready")
                .isTrue();
            go.countDown();
            firstClerkCallResponse = firstFuture.get(60, TimeUnit.SECONDS);
            secondClerkCallResponse = secondFuture.get(60, TimeUnit.SECONDS);
        } finally {
            pool.shutdownNow();
        }

        ScenarioLogManager.getLogger().info(
            "Concurrent call clerk {} status={} clerk {} status={}",
            firstClerk,
            firstClerkCallResponse.getStatusCode(),
            secondClerk,
            secondClerkCallResponse.getStatusCode());
        response = firstClerkCallResponse.getStatusCode() == 200
            ? firstClerkCallResponse
            : secondClerkCallResponse;
        CommonApiSteps.setResponse(response);
        if (response != null && response.getStatusCode() == 200) {
            JsonNode workstation = parseDataNode(response);
            rememberProcess(workstation != null ? workstation.path("process") : null);
            cachedXAuthKey = firstClerkCallResponse.getStatusCode() == 200 ? firstKey : secondKey;
        }
    }

    @Then("exactly one clerk call succeeded and the other failed with ProcessAlreadyCalled")
    public void exactlyOneClerkCallSucceededAndTheOtherFailedWithProcessAlreadyCalled() {
        Assertions.assertThat(firstClerkCallResponse)
            .as("Run a concurrent two-clerk call first")
            .isNotNull();
        Assertions.assertThat(secondClerkCallResponse)
            .as("Run a concurrent two-clerk call first")
            .isNotNull();

        int firstStatus = firstClerkCallResponse.getStatusCode();
        int secondStatus = secondClerkCallResponse.getStatusCode();
        Assertions.assertThat(List.of(firstStatus, secondStatus))
            .as("one call must be 200 and the other 404; got %d and %d (%s / %s)",
                firstStatus,
                secondStatus,
                truncate(firstClerkCallResponse.asString(), 300),
                truncate(secondClerkCallResponse.asString(), 300))
            .containsExactlyInAnyOrder(200, 404);

        Response failed = firstStatus == 404 ? firstClerkCallResponse : secondClerkCallResponse;
        response = failed;
        CommonApiSteps.setResponse(failed);
        theResponseMetaShouldContainException("ProcessAlreadyCalled");

        Response succeeded = firstStatus == 200 ? firstClerkCallResponse : secondClerkCallResponse;
        response = succeeded;
        CommonApiSteps.setResponse(succeeded);
        JsonNode workstation = parseDataNode(succeeded);
        rememberProcess(workstation != null ? workstation.path("process") : null);
    }

    private void iCallTheLastProcessAtTheWorkstationWithAllowClusterWideCall(boolean allowClusterWideCall) {
        Assertions.assertThat(lastProcess)
            .as("Reserve an appointment before calling it")
            .isNotNull();

        replaceFutureAppointmentWithWalkIn();
        String authKey = getOrLoginXAuthKey();
        response = postProcessCalled(authKey, allowClusterWideCall, lastProcess);
        CommonApiSteps.setResponse(response);
        JsonNode workstation = parseDataNode(response);
        rememberProcess(workstation != null ? workstation.path("process") : null);
    }

    private Response postProcessCalled(String authKey, boolean allowClusterWideCall, JsonNode process) {
        return given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("allowClusterWideCall", allowClusterWideCall)
            .body(toJson(process))
        .when()
            .post("/workstation/process/called/");
    }

    /**
     * Concurrent / second-clerk collision paths must keep the booked process (Terminkunde stays confirmed).
     * Do not silently replace a later-day appointment with a walk-in.
     */
    private void assertLastProcessCallableWithoutWalkInFallback() {
        Assertions.assertThat(lastProcess)
                .as("Queue or reserve a process before calling it")
                .isNotNull();
        String status = lastProcess.path("status").asText("");
        if ("queued".equals(status)) {
            return;
        }
        Assertions.assertThat(appointmentIsToday(lastProcess))
                .as("Collision call needs a same-day appointment or walk-in; got status=%s appointment=%s",
                        status,
                        lastProcess.path("appointments").path(0).path("date").asText())
                .isTrue();
    }

    /**
     * A confirmed slot on a later day cannot be called. Queue a walk-in for today and drop the future appointment.
     */
    private void replaceFutureAppointmentWithWalkIn() {
        if (appointmentIsToday(lastProcess)) {
            return;
        }
        int futureId = lastProcess.path("id").asInt();
        ObjectNode body = lastProcess.deepCopy();
        body.remove("id");
        body.remove("authKey");
        body.put("status", "queued");
        String authKey = getOrLoginXAuthKey();
        ScenarioLogManager.getLogger().info(
            "Appointment {} is not today; queueing a walk-in so it can be called", futureId);
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/workstation/process/waitingnumber/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("POST /workstation/process/waitingnumber/ body=%s", truncate(response.asString(), 1000))
            .isEqualTo(200);
        JsonNode queued = parseDataNode(response);
        Assertions.assertThat(queued).isNotNull();
        Response deleted = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("initiator", "admin")
        .when()
            .delete("/process/" + futureId + "/");
        Assertions.assertThat(deleted.getStatusCode())
            .as("DELETE /process/%d/ body=%s", futureId, truncate(deleted.asString(), 1000))
            .isEqualTo(200);
        rememberProcess(queued);
    }

    private boolean appointmentIsToday(JsonNode process) {
        long timestamp = process.path("appointments").path(0).path("date").asLong(0);
        if (timestamp <= 0) {
            return true;
        }
        long start = BerlinTime.today().atStartOfDay(java.time.ZoneId.of("Europe/Berlin")).toEpochSecond();
        return timestamp >= start && timestamp < start + 86_400L;
    }

    @When("I set the assigned process status to processing with the X-AuthKey")
    public void iSetTheAssignedProcessStatusToProcessingWithTheXAuthKey() {
        String authKey = getOrLoginXAuthKey();
        JsonNode process = refreshAssignedProcessFromWorkstation(authKey);
        Assertions.assertThat(process).isNotNull();

        ObjectNode body = process.deepCopy();
        body.put("status", "processing");
        body.putNull("parkedBy");

        int processId = body.path("id").asInt();
        String processAuthKey = body.path("authKey").asText();
        Assertions.assertThat(processId).isPositive();
        Assertions.assertThat(processAuthKey).isNotBlank();

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("initiator", "admin")
            .body(toJson(body))
        .when()
            .post("/process/" + processId + "/" + processAuthKey + "/");
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    @When("I request the department services for scope {int} with the X-AuthKey")
    public void iRequestTheDepartmentServicesForScopeWithTheXAuthKey(int scopeId) {
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
        .when()
            .get("/scope/" + scopeId + "/request/department/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("GET /scope/%d/request/department/ body=%s", scopeId, truncate(response.asString(), 1000))
            .isEqualTo(200);
        departmentServices = parseDataNode(response);
        Assertions.assertThat(departmentServices)
            .as("GET /scope/%d/request/department/ data", scopeId)
            .isNotNull();
    }

    @Then("the scope services should include {string}")
    public void theScopeServicesShouldInclude(String serviceName) {
        Assertions.assertThat(requestListContainsName(departmentServices.path("scopeRequests"), serviceName))
            .as("scopeRequests should include %s", serviceName)
            .isTrue();
    }

    @Then("the scope services should not include {string}")
    public void theScopeServicesShouldNotInclude(String serviceName) {
        Assertions.assertThat(requestListContainsName(departmentServices.path("scopeRequests"), serviceName))
            .as("scopeRequests should not include %s", serviceName)
            .isFalse();
    }

    @Then("the additional department services should include {string}")
    public void theAdditionalDepartmentServicesShouldInclude(String serviceName) {
        Assertions.assertThat(requestListContainsName(
                departmentServices.path("additionalDepartmentRequests"), serviceName))
            .as("additionalDepartmentRequests should include %s", serviceName)
            .isTrue();
    }

    @Then("the scope services and the additional department services should not overlap")
    public void theScopeServicesAndTheAdditionalDepartmentServicesShouldNotOverlap() {
        Set<String> scopeIds = requestIds(departmentServices.path("scopeRequests"));
        Set<String> additionalIds = requestIds(departmentServices.path("additionalDepartmentRequests"));
        scopeIds.retainAll(additionalIds);
        Assertions.assertThat(scopeIds)
            .as("services listed both on the scope and as additional department services")
            .isEmpty();
    }

    @When("I finish the assigned process including additional service {string} with the X-AuthKey")
    public void iFinishTheAssignedProcessIncludingAdditionalServiceWithTheXAuthKey(String serviceName) {
        Assertions.assertThat(departmentServices)
            .as("Request the department services before finishing with an additional service")
            .isNotNull();
        JsonNode extra = findRequestByName(departmentServices.path("additionalDepartmentRequests"), serviceName);
        Assertions.assertThat(extra)
            .as("additional department service %s", serviceName)
            .isNotNull();

        String authKey = getOrLoginXAuthKey();
        JsonNode process = refreshAssignedProcessFromWorkstation(authKey);
        Assertions.assertThat(process).isNotNull();

        ObjectNode body = process.deepCopy();
        body.put("status", "finished");
        JsonNode existingRequests = body.get("requests");
        ArrayNode requests;
        if (existingRequests != null && existingRequests.isArray()) {
            requests = (ArrayNode) existingRequests;
        } else {
            requests = MAPPER.createArrayNode();
            body.set("requests", requests);
        }
        if (!requestListContainsName(requests, serviceName)) {
            requests.add(extra.deepCopy());
        }

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/process/status/finished/");
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    @Then("the finished process requests should include {string}")
    public void theFinishedProcessRequestsShouldInclude(String serviceName) {
        JsonNode process = lastProcess != null ? lastProcess : parseDataNode(response);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(requestListContainsName(process.path("requests"), serviceName))
            .as("finished process requests should include %s. Body=%s",
                serviceName, truncate(response != null ? response.asString() : "", 1000))
            .isTrue();
    }

    @When("I finish the assigned process with the X-AuthKey")
    public void iFinishTheAssignedProcessWithTheXAuthKey() {
        String authKey = getOrLoginXAuthKey();
        JsonNode process = refreshAssignedProcessFromWorkstation(authKey);
        Assertions.assertThat(process).isNotNull();

        ObjectNode body = process.deepCopy();
        body.put("status", "finished");

        response = postFinished(authKey, body);
        if (response.getStatusCode() == 500 && response.asString().toLowerCase(Locale.ROOT).contains("deadlock")) {
            ScenarioLogManager.getLogger().warn(
                    "Finish hit a MySQL deadlock; retrying once. body={}",
                    truncate(response.asString(), 300));
            try {
                Thread.sleep(400);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
            }
            process = refreshAssignedProcessFromWorkstation(authKey);
            Assertions.assertThat(process).isNotNull();
            body = process.deepCopy();
            body.put("status", "finished");
            response = postFinished(authKey, body);
        }
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    private Response postFinished(String authKey, JsonNode body) {
        return given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/process/status/finished/");
    }

    @Then("the response should contain process information")
    public void theResponseShouldContainProcessInformation() {
        if (response == null) {
            response = CommonApiSteps.getResponse();
        }
        Assertions.assertThat(response).isNotNull();
        Assertions.assertThat(response.asString()).contains("process.json");
    }

    @Then("the process status should be {string}")
    public void theProcessStatusShouldBe(String expectedStatus) {
        JsonNode process = lastProcess != null ? lastProcess : parseDataNode(response);
        Assertions.assertThat(process).isNotNull();
        String status = process.path("status").asText();
        if (status.isBlank()) {
            status = process.path("queue").path("status").asText();
        }
        Assertions.assertThat(status).isEqualTo(expectedStatus);
    }

    @Then("the response should contain status information")
    public void theResponseShouldContainStatusInformation() {
        ApiResponse<StatusResponse> apiResponse = as(response,
            new TypeReference<ApiResponse<StatusResponse>>() {});
        
        Assertions.assertThat(apiResponse).isNotNull();
        Assertions.assertThat(apiResponse.getMeta()).isNotNull();
        Assertions.assertThat(apiResponse.getMeta().getError()).isFalse();
        
        StatusResponse statusData = apiResponse.getData();
        Assertions.assertThat(statusData).isNotNull();
        Assertions.assertThat(statusData.getVersion()).isNotNull();
    }

    @Given("I have a ticketprinter session for scope {int}")
    public void iHaveATicketprinterSessionForScope(int scopeId) {
        Response orgResponse = given()
            .baseUri(apiBaseUri())
            .queryParam("resolveReferences", 0)
        .when()
            .get("/scope/" + scopeId + "/organisation/");
        Assertions.assertThat(orgResponse.getStatusCode())
            .as("GET /scope/%d/organisation/", scopeId)
            .isEqualTo(200);
        int organisationId = parseDataNode(orgResponse).path("id").asInt();
        Assertions.assertThat(organisationId).isPositive();

        Response hashResponse = given()
            .baseUri(apiBaseUri())
        .when()
            .get("/organisation/" + organisationId + "/hash/");
        Assertions.assertThat(hashResponse.getStatusCode())
            .as("GET /organisation/%d/hash/", organisationId)
            .isEqualTo(200);
        ticketprinterHash = parseDataNode(hashResponse).path("hash").asText();
        Assertions.assertThat(ticketprinterHash)
            .as("organisation hash for ticketprinter")
            .isNotBlank();
        ScenarioLogManager.getLogger().info("Ticketprinter session established for scope {}", scopeId);
    }

    @Given("Spontankunden opening hours exist for scope {int} from {string} to {string}")
    public void spontankundenOpeningHoursExistForScope(int scopeId, String from, String to) {
        AccountCheckout.checkout("scope:" + scopeId + ":spontankunden");
        // Always create a scenario-owned row; never reuse or delete pre-existing ATAF-ZMSKVR-167.
        createSpontankundenOpeningHours(scopeId, from, to);
    }

    @When("I create opening hours for scope {int} through Sunday of the current week with the X-AuthKey")
    public void iCreateOpeningHoursThroughSundayOfTheCurrentWeek(int scopeId) {
        AccountCheckout.checkout("scope:" + scopeId + ":zmskvr-1672");
        String authKey = getOrLoginXAuthKey();
        LocalDate today = BerlinTime.today();
        LocalDate sunday = today.with(DayOfWeek.SUNDAY);
        long startEpoch = today.atTime(BerlinTime.now()).atZone(BerlinTime.ZONE).toEpochSecond();
        long endEpoch = sunday.atStartOfDay(BerlinTime.ZONE).toEpochSecond();
        String endTime = closingTimeOn(sunday);

        ObjectNode weekday = MAPPER.createObjectNode();
        for (DayOfWeek day : DayOfWeek.values()) {
            boolean selected = day == DayOfWeek.SUNDAY
                    || (day == DayOfWeek.SATURDAY && today.getDayOfWeek() != DayOfWeek.SUNDAY);
            weekday.put(day.name().toLowerCase(Locale.ROOT), selected ? 1 : 0);
        }

        ObjectNode availability = MAPPER.createObjectNode();
        availability.put("type", "openinghours");
        availability.put("kind", "default");
        availability.put("description", "ATAF-ZMSKVR-1672");
        availability.put("startDate", startEpoch);
        availability.put("endDate", endEpoch);
        availability.put("startTime", "08:00:00");
        availability.put("endTime", endTime);
        availability.put("slotTimeInMinutes", 10);
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        availability.set("scope", scope);
        availability.set("weekday", weekday);
        ObjectNode workstationCount = MAPPER.createObjectNode();
        workstationCount.put("intern", 0);
        workstationCount.put("public", 0);
        availability.set("workstationCount", workstationCount);

        ObjectNode body = MAPPER.createObjectNode();
        body.set("availabilityList", MAPPER.createArrayNode().add(availability));
        body.put("selectedDate", today.toString());

        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/availability/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() == 200) {
            ArrayNode created = parseDataArray(response);
            if (created != null && !created.isEmpty() && created.get(0).path("id").asInt() > 0) {
                createdAvailabilityId = created.get(0).path("id").asInt();
            }
        }
    }

    @Then("the response should not report a missing weekday")
    public void theResponseShouldNotReportAMissingWeekday() {
        String body = response.asString();
        Assertions.assertThat(body).doesNotContain("invalidWeekday");
        Assertions.assertThat(body).doesNotContain("kommen im gewählten Zeitraum nicht vor");
    }

    @When("I create opening hours for scope {int} with note {string} with the X-AuthKey")
    public void iCreateOpeningHoursWithNote(int scopeId, String note) {
        AccountCheckout.checkout("scope:" + scopeId + ":zmskvr-1583");
        historyScopeId = scopeId;
        response = postScopedOpeningHours(scopeId, null, note);
        CommonApiSteps.setResponse(response);
        rememberCreatedOpeningHours();
    }

    @When("I save the created opening hours again with note {string} with the X-AuthKey")
    public void iSaveTheCreatedOpeningHoursAgainWithNote(String note) {
        Assertions.assertThat(createdAvailabilityId)
            .as("opening hours id from POST /availability/")
            .isNotNull();
        response = postScopedOpeningHours(historyScopeId, createdAvailabilityId, note);
        CommonApiSteps.setResponse(response);
    }

    @When("I request the history of the created opening hours with the X-AuthKey")
    public void iRequestTheHistoryOfTheCreatedOpeningHours() {
        Assertions.assertThat(historyAvailabilityId)
            .as("opening hours id kept for the history request")
            .isNotNull();
        requestOpeningHoursHistory(historyScopeId, historyAvailabilityId);
    }

    @When("I request the opening-hours history for scope {int} with the X-AuthKey")
    public void iRequestTheOpeningHoursHistoryForScope(int scopeId) {
        requestOpeningHoursHistory(scopeId, null);
    }

    @Then("the opening-hours history contains action {string} and note {string}")
    public void theOpeningHoursHistoryContainsActionAndNote(String action, String note) {
        ArrayNode rows = parseDataArray(response);
        Assertions.assertThat(rows).as("GET /scope/{id}/availability/history/ data").isNotNull();
        JsonNode match = null;
        for (JsonNode row : rows) {
            if (action.equals(row.path("action").asText()) && note.equals(row.path("comment").asText())) {
                match = row;
                break;
            }
        }
        if (match == null) {
            Assertions.fail("history row " + action + " with note " + note + " is missing");
            return;
        }
        Assertions.assertThat(match.path("timeSlot").asText())
            .as("Zeitschlitz")
            .isEqualTo("00:10:00");
        Assertions.assertThat(match.path("changedAt").asText())
            .startsWith(BerlinTime.today().toString());
    }

    private void rememberCreatedOpeningHours() {
        if (response.getStatusCode() != 200) {
            return;
        }
        ArrayNode created = parseDataArray(response);
        if (created != null && !created.isEmpty() && created.get(0).path("id").asInt() > 0) {
            createdAvailabilityId = created.get(0).path("id").asInt();
            historyAvailabilityId = createdAvailabilityId;
        }
    }

    private void requestOpeningHoursHistory(int scopeId, Integer availabilityId) {
        var request = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", getOrLoginXAuthKey());
        if (availabilityId != null) {
            request = request.queryParam("availabilityId", availabilityId);
        }
        response = request
        .when()
            .get("/scope/" + scopeId + "/availability/history/");
        CommonApiSteps.setResponse(response);
    }

    private Response postScopedOpeningHours(int scopeId, Integer availabilityId, String note) {
        String authKey = getOrLoginXAuthKey();
        LocalDate today = BerlinTime.today();
        LocalDate sunday = today.with(DayOfWeek.SUNDAY);
        long startEpoch = today.atTime(BerlinTime.now()).atZone(BerlinTime.ZONE).toEpochSecond();
        long endEpoch = sunday.atStartOfDay(BerlinTime.ZONE).toEpochSecond();

        ObjectNode weekday = MAPPER.createObjectNode();
        for (DayOfWeek day : DayOfWeek.values()) {
            boolean selected = day == DayOfWeek.SUNDAY
                    || (day == DayOfWeek.SATURDAY && today.getDayOfWeek() != DayOfWeek.SUNDAY);
            weekday.put(day.name().toLowerCase(Locale.ROOT), selected ? 1 : 0);
        }

        ObjectNode availability = MAPPER.createObjectNode();
        if (availabilityId != null) {
            availability.put("id", availabilityId);
        }
        availability.put("type", "openinghours");
        availability.put("kind", "default");
        availability.put("description", note);
        availability.put("startDate", startEpoch);
        availability.put("endDate", endEpoch);
        availability.put("startTime", "08:00:00");
        availability.put("endTime", closingTimeOn(sunday));
        availability.put("slotTimeInMinutes", 10);
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        availability.set("scope", scope);
        availability.set("weekday", weekday);
        ObjectNode workstationCount = MAPPER.createObjectNode();
        workstationCount.put("intern", 1);
        workstationCount.put("public", 1);
        availability.set("workstationCount", workstationCount);

        ObjectNode body = MAPPER.createObjectNode();
        body.set("availabilityList", MAPPER.createArrayNode().add(availability));
        body.put("selectedDate", today.toString());

        return given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/availability/");
    }

    @When("I delete the opening hours created for the current week with the X-AuthKey")
    public void iDeleteTheOpeningHoursCreatedForTheCurrentWeek() {
        Assertions.assertThat(createdAvailabilityId)
            .as("opening hours id from POST /availability/")
            .isNotNull();
        int id = createdAvailabilityId;
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", getOrLoginXAuthKey())
        .when()
            .delete("/availability/" + id + "/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() == 200) {
            createdAvailabilityId = null;
        }
    }

    @When("I delete Spontankunden opening hours for scope {int} with the X-AuthKey")
    public void iDeleteSpontankundenOpeningHoursForScope(int scopeId) {
        AccountCheckout.checkout("scope:" + scopeId + ":spontankunden");
        Assertions.assertThat(createdAvailabilityId)
            .as("scope %d: scenario must own a created Spontankunden availability id to delete", scopeId)
            .isNotNull();
        int id = createdAvailabilityId;
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", getOrLoginXAuthKey())
        .when()
            .delete("/availability/" + id + "/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
            .as("DELETE /availability/%d/", id)
            .isEqualTo(200);
        createdAvailabilityId = null;
    }

    /**
     * ZMSKVR-42: remember the first future day with intern free slots so day-lock
     * can be asserted against a concrete date (Terminkunden).
     */
    @When("I remember a free appointment day for scope {int} and service {string} with the X-AuthKey")
    public void iRememberAFreeAppointmentDayForScopeAndService(int scopeId, String serviceName) {
        AccountCheckout.checkout("scope:" + scopeId + ":day-closure");
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        Assertions.assertThat(request.path("name").asText("").toLowerCase(Locale.ROOT))
                .as("scope %d request list should include %s", scopeId, serviceName)
                .contains(serviceName.toLowerCase(Locale.ROOT));
        long startOfTomorrow = BerlinTime.today()
                .plusDays(1)
                .atStartOfDay(BerlinTime.ZONE)
                .toEpochSecond();
        JsonNode freeList = fetchFreeProcessesLookingAhead(scopeId, request, authKey, startOfTomorrow);
        List<JsonNode> candidates = slotsBetween(freeList, startOfTomorrow, startOfTomorrow + 14L * 86_400L);
        Assertions.assertThat(candidates)
                .as("scope %d needs an intern slot on a future calendar day", scopeId)
                .isNotEmpty();
        long appointment = candidates.get(0).path("appointments").path(0).path("date").asLong();
        LocalDate day = Instant.ofEpochSecond(appointment).atZone(BerlinTime.ZONE).toLocalDate();
        rememberedFreeAppointmentDate = day.toString();
        rememberedFreeAppointmentRequest = request;
        TestDataHelper.setTestData("day_closure_date", rememberedFreeAppointmentDate);
        TestDataHelper.setTestData("day_closure_day", Integer.toString(day.getDayOfMonth()));
        ScenarioLogManager.getLogger().info(
                "Remembered free appointment day {} for scope {}", rememberedFreeAppointmentDate, scopeId);
    }

    @When("I toggle the day closure for scope {int} on the remembered free appointment day with the X-AuthKey")
    public void iToggleTheDayClosureForScopeOnTheRememberedFreeAppointmentDay(int scopeId) {
        Assertions.assertThat(rememberedFreeAppointmentDate)
                .as("Call 'I remember a free appointment day…' first")
                .isNotBlank();
        iToggleTheDayClosureForScopeOnDate(scopeId, rememberedFreeAppointmentDate);
    }

    @When("I toggle the day closure for scope {int} on the remembered day-closure date with the X-AuthKey")
    public void iToggleTheDayClosureForScopeOnTheRememberedDayClosureDate(int scopeId) {
        String date = TestDataHelper.getTestData("day_closure_date");
        Assertions.assertThat(date)
                .as("day_closure_date from a remember step")
                .isNotBlank();
        iToggleTheDayClosureForScopeOnDate(scopeId, date);
    }

    @When("I toggle the day closure for scope {int} on date {string} with the X-AuthKey")
    public void iToggleTheDayClosureForScopeOnDate(int scopeId, String date) {
        AccountCheckout.checkout("scope:" + scopeId + ":day-closure");
        String resolved = resolveDayClosureDate(date);
        response = postDayClosureToggle(scopeId, resolved);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("POST /scope/%d/availability/%s/closure/toggle/ body=%s",
                        scopeId, resolved, truncate(response.asString(), 800))
                .isEqualTo(200);
        JsonNode data = parseDataNode(response);
        Assertions.assertThat(data)
                .as("closure toggle response data")
                .isNotNull();
        JsonNode existingNode = data.get("existing");
        Assertions.assertThat(existingNode)
                .as("closure toggle data.existing")
                .isNotNull();
        Assertions.assertThat(existingNode.isBoolean())
                .as("closure toggle data.existing must be boolean, was %s", existingNode.getNodeType())
                .isTrue();
        boolean existing = existingNode.asBoolean();
        dayClosureScopeId = scopeId;
        dayClosureDate = resolved;
        dayClosureIsLocked = existing;
        TestDataHelper.setTestData("day_closure_existing", Boolean.toString(existing));
        ScenarioLogManager.getLogger().info(
                "Day closure for scope {} on {} existing={}", scopeId, resolved, existing);
    }

    @Then("the day closure existing flag should be {word}")
    public void theDayClosureExistingFlagShouldBe(String expected) {
        boolean want = Boolean.parseBoolean(expected);
        Assertions.assertThat(dayClosureIsLocked)
                .as("day closure existing flag after toggle")
                .isEqualTo(want);
        JsonNode data = parseDataNode(response);
        Assertions.assertThat(data)
                .as("closure toggle response data")
                .isNotNull();
        JsonNode existingNode = data.get("existing");
        Assertions.assertThat(existingNode)
                .as("API data.existing")
                .isNotNull();
        Assertions.assertThat(existingNode.asBoolean())
                .as("API data.existing")
                .isEqualTo(want);
    }

    @Then("scope {int} should have no free appointment slots on the remembered day with the X-AuthKey")
    public void scopeShouldHaveNoFreeAppointmentSlotsOnTheRememberedDay(int scopeId) {
        assertFreeSlotsOnRememberedDay(scopeId, false);
    }

    @Then("scope {int} should have free appointment slots on the remembered day with the X-AuthKey")
    public void scopeShouldHaveFreeAppointmentSlotsOnTheRememberedDay(int scopeId) {
        assertFreeSlotsOnRememberedDay(scopeId, true);
    }

    private void assertFreeSlotsOnRememberedDay(int scopeId, boolean expectSlots) {
        Assertions.assertThat(rememberedFreeAppointmentDate)
                .as("remembered free appointment day")
                .isNotBlank();
        Assertions.assertThat(rememberedFreeAppointmentRequest)
                .as("remembered free appointment request")
                .isNotNull();
        String authKey = getOrLoginXAuthKey();
        LocalDate day = LocalDate.parse(rememberedFreeAppointmentDate);
        Response freeResponse = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .queryParam("slotType", "intern")
                .queryParam("slotsRequired", 0)
                .body(toJson(freeProcessCalendar(scopeId, rememberedFreeAppointmentRequest, day)))
                .when()
                .post("/process/status/free/");
        Assertions.assertThat(freeResponse.getStatusCode())
                .as("POST /process/status/free/ for scope %d on %s body=%s",
                        scopeId, day, truncate(freeResponse.asString(), 800))
                .isEqualTo(200);
        JsonNode freeList = parseDataArray(freeResponse);
        Assertions.assertThat(freeList)
                .as("POST /process/status/free/ data array for scope %d on %s", scopeId, day)
                .isNotNull();
        long dayStart = day.atStartOfDay(BerlinTime.ZONE).toEpochSecond();
        long dayEnd = dayStart + 86_400L;
        List<JsonNode> onDay = slotsBetween(freeList, dayStart, dayEnd);
        if (expectSlots) {
            Assertions.assertThat(onDay)
                    .as("expected free intern slots on %s for scope %d", day, scopeId)
                    .isNotEmpty();
        } else {
            Assertions.assertThat(onDay)
                    .as("expected no free intern slots on locked day %s for scope %d", day, scopeId)
                    .isEmpty();
        }
    }

    private Response postDayClosureToggle(int scopeId, String date) {
        String authKey = getOrLoginXAuthKey();
        // AvailabilityClosureToggle rejects an empty JSON body.
        ObjectNode body = MAPPER.createObjectNode();
        body.put("id", 0);
        return given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .body(toJson(body))
                .when()
                .post("/scope/" + scopeId + "/availability/" + date + "/closure/toggle/");
    }

    private String resolveDayClosureDate(String date) {
        if (date == null || date.isBlank() || "<morgen>".equals(date) || "tomorrow".equalsIgnoreCase(date)) {
            return BerlinTime.today().plusDays(1).toString();
        }
        if ("<heute>".equals(date) || "today".equalsIgnoreCase(date)) {
            return BerlinTime.today().toString();
        }
        if ("<remembered>".equals(date) || "remembered".equalsIgnoreCase(date)) {
            Assertions.assertThat(rememberedFreeAppointmentDate).isNotBlank();
            return rememberedFreeAppointmentDate;
        }
        return TestDataHelper.transformTestData(date);
    }

    @When("I request a ticketprinter button list {string}")
    public void iRequestATicketprinterButtonList(String buttonList) {
        Assertions.assertThat(ticketprinterHash)
            .as("Call 'I have a ticketprinter session for scope …' first")
            .isNotBlank();
        ObjectNode body = MAPPER.createObjectNode();
        body.put("buttonlist", buttonList);
        body.put("hash", ticketprinterHash);
        response = given()
            .baseUri(apiBaseUri())
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/ticketprinter/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() != 200) {
            ScenarioLogManager.getLogger().error(
                "POST /ticketprinter/ failed with {}: {}",
                response.getStatusCode(),
                truncate(response.asString(), 1000));
        }
    }

    @When("I request a waiting number for scope {int}")
    public void iRequestAWaitingNumberForScope(int scopeId) {
        requestWaitingNumber(scopeId, null);
    }

    @When("I request a waiting number for scope {int} and request {int}")
    public void iRequestAWaitingNumberForScopeAndRequest(int scopeId, int requestId) {
        requestWaitingNumber(scopeId, requestId);
    }

    @Then("the ticketprinter button for scope {int} should be enabled")
    public void theTicketprinterButtonForScopeShouldBeEnabled(int scopeId) {
        JsonNode button = findScopeButton(scopeId);
        Assertions.assertThat(button)
            .as("scope button %d in POST /ticketprinter/ response", scopeId)
            .isNotNull();
        Assertions.assertThat(button.path("enabled").asBoolean()).isTrue();
    }

    @Then("the ticketprinter button for scope {int} should be disabled")
    public void theTicketprinterButtonForScopeShouldBeDisabled(int scopeId) {
        JsonNode button = findScopeButton(scopeId);
        Assertions.assertThat(button)
            .as("scope button %d in POST /ticketprinter/ response", scopeId)
            .isNotNull();
        Assertions.assertThat(button.path("enabled").asBoolean()).isFalse();
    }

    @Then("the ticketprinter response should not contain scope {int}")
    public void theTicketprinterResponseShouldNotContainScope(int scopeId) {
        Assertions.assertThat(findScopeButton(scopeId))
            .as("missing scope %d should be omitted from the button list", scopeId)
            .isNull();
    }

    @When("I request a call display for locations {string}")
    public void iRequestACallDisplayForLocations(String scopeList) {
        ArrayNode scopes = MAPPER.createArrayNode();
        for (String id : scopeList.split(",")) {
            String token = id.trim();
            try {
                scopes.add(MAPPER.createObjectNode().put("id", Integer.parseInt(token)));
            } catch (NumberFormatException e) {
                Assertions.fail("Location id must be a number, got: " + token, e);
            }
        }
        ObjectNode body = MAPPER.createObjectNode();
        body.set("scopes", scopes);
        response = given()
            .baseUri(apiBaseUri())
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/calldisplay/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() != 200) {
            ScenarioLogManager.getLogger().error(
                "POST /calldisplay/ failed with {}: {}",
                response.getStatusCode(),
                truncate(response.asString(), 1000));
        }
    }

    /**
     * ZMS-3213 / ZMSALT-3250: seed two called walk-ins for Aufrufmonitor UI order
     * (zmscalldisplay already sorts by callTime descending).
     */
    @When("I prepare two called walk-ins at scope {int} with service {string} for call display order with the X-AuthKey")
    public void iPrepareTwoCalledWalkInsForCallDisplayOrder(int scopeId, String serviceName)
            throws InterruptedException {
        callDisplayNumbersInCallOrder.clear();
        callDisplayProcessesByLabel.clear();

        iAmLoggedInToTheZmsApiAs("ataf");
        iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "22");
        Assertions.assertThat(response.getStatusCode()).as("first clerk workstation").isEqualTo(200);
        iRememberTheCurrentZmsApiLoginAsClerk("first");
        queueWalkIn(
                scopeId,
                serviceName,
                "Muster Calldisplay Older " + System.nanoTime(),
                null);
        JsonNode older = lastProcess;
        Assertions.assertThat(older).as("older walk-in").isNotNull();
        callDisplayProcessesByLabel.put("older", older.deepCopy());
        trackProcess(older.path("id").asInt(), scopeId);

        iAmLoggedInToTheZmsApiAsAnother("ataf");
        iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "23");
        Assertions.assertThat(response.getStatusCode()).as("second clerk workstation").isEqualTo(200);
        iRememberTheCurrentZmsApiLoginAsClerk("second");
        queueWalkIn(
                scopeId,
                serviceName,
                "Muster Calldisplay Newer " + System.nanoTime(),
                null);
        JsonNode newer = lastProcess;
        Assertions.assertThat(newer).as("newer walk-in").isNotNull();
        callDisplayProcessesByLabel.put("newer", newer.deepCopy());
        trackProcess(newer.path("id").asInt(), scopeId);

        callWalkInAsClerk("first", "older");
        Thread.sleep(1100);
        callWalkInAsClerk("second", "newer");

        TestDataHelper.setTestData(
                "calldisplay_older_number", callDisplayNumbersInCallOrder.get(0));
        TestDataHelper.setTestData(
                "calldisplay_newer_number", callDisplayNumbersInCallOrder.get(1));
        ScenarioLogManager.getLogger().info(
                "Call display order prepared older={} newer={}",
                callDisplayNumbersInCallOrder.get(0),
                callDisplayNumbersInCallOrder.get(1));
    }

    private void callWalkInAsClerk(String clerk, String label) {
        String authKey = clerkAuthKeys.get(clerk);
        Assertions.assertThat(authKey).as("Remember clerk %s before calling", clerk).isNotBlank();
        JsonNode process = callDisplayProcessesByLabel.get(label);
        Assertions.assertThat(process).as("Remember call-display process \"%s\"", label).isNotNull();
        response = postProcessCalled(authKey, true, process);
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("clerk %s call \"%s\" body=%s", clerk, label, truncate(response.asString(), 1000))
                .isEqualTo(200);
        JsonNode workstation = parseDataNode(response);
        JsonNode called = workstation != null ? workstation.path("process") : null;
        rememberProcess(called);
        String displayNumber = displayNumberOf(called != null ? called : process);
        Assertions.assertThat(displayNumber)
                .as("displayNumber for call-display \"%s\"", label)
                .isNotBlank();
        callDisplayNumbersInCallOrder.add(displayNumber);
        callDisplayProcessesByLabel.put(label, (called != null ? called : process).deepCopy());
        cachedXAuthKey = authKey;
    }

    private static String displayNumberOf(JsonNode process) {
        if (process == null) {
            return "";
        }
        String displayNumber = process.path("displayNumber").asText("");
        if (!displayNumber.isBlank()) {
            return displayNumber;
        }
        String queueNumber = process.path("queue").path("number").asText("");
        if (!queueNumber.isBlank()) {
            return queueNumber;
        }
        int id = process.path("id").asInt();
        return id > 0 ? Integer.toString(id) : "";
    }

    @Then("the call display response should include location {int}")
    public void theCallDisplayResponseShouldIncludeLocation(int scopeId) {
        Assertions.assertThat(calldisplayScopeIds())
            .as("POST /calldisplay/ scopes")
            .contains(scopeId);
    }

    @Then("the call display response should not include location {int}")
    public void theCallDisplayResponseShouldNotIncludeLocation(int scopeId) {
        Assertions.assertThat(calldisplayScopeIds())
            .as("missing location %d should be omitted", scopeId)
            .doesNotContain(scopeId);
    }

    private List<Integer> calldisplayScopeIds() {
        JsonNode scopes = parseDataNode(response).path("scopes");
        List<Integer> ids = new ArrayList<>();
        if (scopes.isArray()) {
            scopes.forEach(scope -> ids.add(scope.path("id").asInt()));
        }
        return ids;
    }

    @Then("the process should have a waiting number")
    public void theProcessShouldHaveAWaitingNumber() {
        JsonNode process = lastProcess != null ? lastProcess : parseDataNode(response);
        Assertions.assertThat(process).isNotNull();
        int number = process.path("queue").path("number").asInt();
        Assertions.assertThat(number)
            .as("process.queue.number")
            .isPositive();
        ScenarioLogManager.getLogger().info("Ticketprinter waiting number: {}", number);
    }

    @Then("the process should be flagged as ticketprinter")
    public void theProcessShouldBeFlaggedAsTicketprinter() {
        JsonNode process = lastProcess != null ? lastProcess : parseDataNode(response);
        Assertions.assertThat(process).isNotNull();
        Assertions.assertThat(process.path("isTicketprinter").asBoolean(false))
            .as("process.isTicketprinter body=%s", truncate(process.toString(), 500))
            .isTrue();
    }

    @When("I request the clientscope warehouse report for scope {int} for today with the X-AuthKey")
    public void iRequestTheClientscopeWarehouseReportForScopeForTodayWithTheXAuthKey(int scopeId) {
        String authKey = getOrLoginXAuthKey();
        String period = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("groupby", "day")
        .when()
            .get("/warehouse/clientscope/" + scopeId + "/" + period + "/");
        CommonApiSteps.setResponse(response);
        ScenarioLogManager.getLogger().info(
            "GET /warehouse/clientscope/{}/{} status={} body={}",
            scopeId,
            period,
            response.getStatusCode(),
            truncate(response.asString(), 800));
    }

    @When("I remember the clientscope ticketprinter appeared count for today")
    public void iRememberTheClientscopeTicketprinterAppearedCountForToday() {
        // Empty scopes return ReportNotFound until the first finish archives a row.
        rememberedTicketprinterCount = ticketprinterAppearedCountForTodayOrZero();
        TestDataHelper.setTestData(
            "ekiosk_ticketprinter_baseline", Integer.toString(rememberedTicketprinterCount));
        ScenarioLogManager.getLogger().info(
            "Remembered clientscope ticketprinter baseline for today: {}", rememberedTicketprinterCount);
    }

    @Then("the clientscope ticketprinter appeared count for today should be at least {int}")
    public void theClientscopeTicketprinterAppearedCountForTodayShouldBeAtLeast(int minimum) {
        int total = ticketprinterAppearedCountForToday(parseDataNode(response));
        String today = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
        ScenarioLogManager.getLogger().info(
            "clientscope ticketprinter for {} = {} (min {})", today, total, minimum);
        Assertions.assertThat(total)
            .as("ticketprinter appeared for %s", today)
            .isGreaterThanOrEqualTo(minimum);
    }

    @Then("the clientscope ticketprinter appeared count for today should be greater than the remembered count")
    public void theClientscopeTicketprinterAppearedCountForTodayShouldBeGreaterThanTheRememberedCount() {
        Assertions.assertThat(rememberedTicketprinterCount)
            .as("Call 'I remember the clientscope ticketprinter appeared count for today' first")
            .isNotNull();
        int total = ticketprinterAppearedCountForToday(parseDataNode(response));
        String today = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
        ScenarioLogManager.getLogger().info(
            "clientscope ticketprinter for {} = {} (baseline {})",
            today,
            total,
            rememberedTicketprinterCount);
        Assertions.assertThat(total)
            .as("ticketprinter appeared for %s must increase over baseline %s", today, rememberedTicketprinterCount)
            .isGreaterThan(rememberedTicketprinterCount);
    }

    /** Baseline helper: missing warehouse report means zero finished E-Kiosk walk-ins yet. */
    private int ticketprinterAppearedCountForTodayOrZero() {
        if (response == null) {
            return 0;
        }
        try {
            JsonNode root = MAPPER.readTree(response.asString());
            if (root.path("meta").path("error").asBoolean(false)) {
                return 0;
            }
        } catch (Exception ignored) {
            return 0;
        }
        JsonNode exchange = parseDataNode(response);
        if (exchange == null) {
            return 0;
        }
        int ticketprinterIndex = dictionaryIndex(exchange, "ticketprinter");
        if (ticketprinterIndex < 0) {
            return 0;
        }
        String today = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
        int dateIndex = dictionaryIndex(exchange, "date");
        if (dateIndex < 0) {
            return 0;
        }
        int total = 0;
        JsonNode data = exchange.path("data");
        if (data.isArray()) {
            for (JsonNode row : data) {
                if (!row.isArray() || row.size() <= Math.max(dateIndex, ticketprinterIndex)) {
                    continue;
                }
                if (today.equals(row.get(dateIndex).asText())) {
                    total += row.get(ticketprinterIndex).asInt(0);
                }
            }
        }
        return total;
    }

    private int ticketprinterAppearedCountForToday(JsonNode exchange) {
        Assertions.assertThat(exchange)
            .as("warehouse clientscope exchange body=%s", truncate(response != null ? response.asString() : "", 800))
            .isNotNull();
        int ticketprinterIndex = dictionaryIndex(exchange, "ticketprinter");
        Assertions.assertThat(ticketprinterIndex)
            .as("dictionary entry ticketprinter in %s", exchange.path("dictionary"))
            .isGreaterThanOrEqualTo(0);
        String today = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
        int dateIndex = dictionaryIndex(exchange, "date");
        Assertions.assertThat(dateIndex).as("dictionary entry date").isGreaterThanOrEqualTo(0);
        int total = 0;
        JsonNode data = exchange.path("data");
        if (data.isArray()) {
            for (JsonNode row : data) {
                if (!row.isArray() || row.size() <= Math.max(dateIndex, ticketprinterIndex)) {
                    continue;
                }
                if (today.equals(row.get(dateIndex).asText())) {
                    total += row.get(ticketprinterIndex).asInt(0);
                }
            }
        }
        return total;
    }

    private static int dictionaryIndex(JsonNode exchange, String name) {
        JsonNode dictionary = exchange.path("dictionary");
        if (!dictionary.isArray()) {
            return -1;
        }
        for (int i = 0; i < dictionary.size(); i++) {
            if (name.equals(dictionary.get(i).path("name").asText())) {
                return i;
            }
            if (name.equals(dictionary.get(i).path("variable").asText())) {
                return i;
            }
        }
        return -1;
    }

    private void requestWaitingNumber(int scopeId, Integer requestId) {
        Assertions.assertThat(ticketprinterHash)
            .as("Call 'I have a ticketprinter session for scope …' first")
            .isNotBlank();
        var request = given()
            .baseUri(apiBaseUri());
        if (requestId != null) {
            request = request.queryParam("requestId", requestId);
        }
        response = request
        .when()
            .get("/scope/" + scopeId + "/waitingnumber/" + ticketprinterHash + "/");
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    private void createSpontankundenOpeningHours(int scopeId, String from, String to) {
        if (from != null && from.startsWith("00:00")) {
            throw new IllegalArgumentException(
                "Spontankunden startTime cannot be 00:00:00; the API ignores midnight as empty opening hours");
        }
        String authKey = getOrLoginXAuthKey();
        LocalDate today = BerlinTime.today();
        long startEpoch = today.atStartOfDay(BerlinTime.ZONE).toEpochSecond();
        DayOfWeek todayWeekday = today.getDayOfWeek();

        ObjectNode weekday = MAPPER.createObjectNode();
        for (DayOfWeek day : DayOfWeek.values()) {
            weekday.put(day.name().toLowerCase(Locale.ROOT), day == todayWeekday ? 1 : 0);
        }

        ObjectNode availability = MAPPER.createObjectNode();
        availability.put("type", "openinghours");
        availability.put("kind", "default");
        availability.put("description", "ATAF-ZMSKVR-167");
        availability.put("startDate", startEpoch);
        availability.put("endDate", startEpoch);
        availability.put("startTime", normalizeClock(from));
        availability.put("endTime", normalizeClock(to));
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        availability.set("scope", scope);
        availability.set("weekday", weekday);
        ObjectNode workstationCount = MAPPER.createObjectNode();
        workstationCount.put("intern", 0);
        workstationCount.put("public", 0);
        availability.set("workstationCount", workstationCount);

        ObjectNode body = MAPPER.createObjectNode();
        body.set("availabilityList", MAPPER.createArrayNode().add(availability));
        body.put("selectedDate", today.toString());

        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(body))
        .when()
            .post("/availability/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() != 200) {
            throw new IllegalStateException(
                "POST /availability/ for Spontankunden hours on scope " + scopeId
                    + " failed with " + response.getStatusCode() + ": "
                    + truncate(response.asString(), 1000));
        }
        ArrayNode created = parseDataArray(response);
        if (created != null && !created.isEmpty() && created.get(0).path("id").asInt() > 0) {
            createdAvailabilityId = created.get(0).path("id").asInt();
        }
    }

    private JsonNode findScopeButton(int scopeId) {
        JsonNode data = parseDataNode(response);
        Assertions.assertThat(data).isNotNull();
        JsonNode buttons = data.path("buttons");
        if (!buttons.isArray()) {
            return null;
        }
        for (JsonNode button : buttons) {
            if ("scope".equals(button.path("type").asText())
                    && button.path("scope").path("id").asInt() == scopeId) {
                return button;
            }
        }
        return null;
    }

    /**
     * 17:00 while Sunday is still ahead. On a Sunday evening the end clock has to stay after now,
     * on a 10-minute grid so it divides by slotTimeInMinutes.
     */
    private static String closingTimeOn(LocalDate endDay) {
        LocalTime end = LocalTime.of(17, 0);
        if (!endDay.isAfter(BerlinTime.today())) {
            LocalTime now = BerlinTime.now().withSecond(0).withNano(0);
            if (!end.isAfter(now)) {
                int roundedMinute = ((now.getMinute() / 10) + 1) * 10;
                end = now.withMinute(0).plusMinutes(roundedMinute + 10L);
                if (!end.isAfter(now) || end.isAfter(LocalTime.of(23, 50))) {
                    end = LocalTime.of(23, 50);
                }
            }
        }
        return end.format(DateTimeFormatter.ofPattern("HH:mm:ss"));
    }

    private static String normalizeClock(String time) {
        return time != null && time.length() == 5 ? time + ":00" : time;
    }

    private String apiBaseUri() {
        return baseUri != null ? baseUri : TestConfig.getBaseUri();
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

    private String getOrLoginXAuthKey() {
        if (cachedXAuthKey != null && !cachedXAuthKey.isBlank()) {
            return cachedXAuthKey;
        }

        cachedXAuthKey = loginAndExtractAuthKey(workstationLoginCredentials());
        return cachedXAuthKey;
    }

    private void postWorkstationLogin(String endpoint) {
        cachedXAuthKey = null;
        response = executeWorkstationLogin(endpoint, workstationLoginCredentials());
        CommonApiSteps.setResponse(response);

        if (response.getStatusCode() < 200 || response.getStatusCode() >= 300) {
            return;
        }
        cachedXAuthKey = extractAuthKeyFromBody(response.asString());
        if (cachedXAuthKey == null || cachedXAuthKey.isBlank()) {
            throw new IllegalStateException("Login succeeded but response did not contain data.authkey/authKey");
        }
    }

    private String loginAndExtractAuthKey(String[] credentials) {
        Response loginResponse = executeWorkstationLogin("/workstation/login/", credentials);
        CommonApiSteps.setResponse(loginResponse);

        String body = loginResponse.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "ZMS API /workstation/login/ (auto X-AuthKey) status=%d",
            loginResponse.getStatusCode()
        ));

        if (loginResponse.getStatusCode() < 200 || loginResponse.getStatusCode() >= 300) {
            throw new IllegalStateException(
                "Unable to auto-login to obtain X-AuthKey for user "
                    + credentials[0]
                    + ". Ensure the user can login to /workstation/login/ (see V22__add_role_test_users.sql). HTTP "
                    + loginResponse.getStatusCode());
        }

        String key = extractAuthKeyFromBody(body);
        if (key == null || key.isBlank()) {
            throw new IllegalStateException("Login succeeded but response did not contain data.authkey/authKey");
        }
        return key;
    }

    private Response executeWorkstationLogin(String endpoint, String[] credentials) {
        return given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .contentType("application/json")
            .body(java.util.Map.of("id", credentials[0], "password", credentials[1]))
        .when()
            .post(endpoint);
    }

    private String[] workstationLoginCredentials() {
        if (scenarioLoginUsername != null && !scenarioLoginUsername.isBlank()) {
            String password = scenarioLoginPassword != null && !scenarioLoginPassword.isBlank()
                ? scenarioLoginPassword
                : defaultWorkstationPassword();
            return credentials(resolveWorkstationUsername(scenarioLoginUsername), password);
        }

        String username = TestPropertiesHelper.getPropertyAsString("zmsapiUserName", true);
        String password = TestPropertiesHelper.getPropertyAsString("zmsapiUserPassword", true);
        if (username.isBlank() || password.isBlank()) {
            throw new IllegalStateException(
                "Set testautomation.zmsapiUserName and testautomation.zmsapiUserPassword in testautomation.properties, "
                    + "or use 'Given the ZMS API workstation user is \"<role>\"' in the scenario");
        }
        return credentials(resolveWorkstationUsername(username), password);
    }

    private String[] credentials(String username, String password) {
        String login = AccountCheckout.assignWorkstationLogin(username);
        return new String[] { resolveWorkstationUsername(login), password };
    }

    private String defaultWorkstationPassword() {
        String password = TestPropertiesHelper.getPropertyAsString("zmsapiUserPassword", true, "vorschau");
        return password.isBlank() ? "vorschau" : password;
    }

    private String resolveWorkstationUsername(String usernameOrRole) {
        return usernameOrRole.endsWith("@keycloak") ? usernameOrRole : usernameOrRole + "@keycloak";
    }

    private String extractAuthKeyFromBody(String body) {
        try {
            JsonNode root = MAPPER.readTree(body);
            JsonNode data = root.path("data");
            JsonNode authNode = data.path("authkey");
            String key = authNode.isMissingNode() || authNode.isNull() ? null : authNode.asText();
            if (key == null || key.isBlank()) {
                authNode = data.path("authKey");
                key = authNode.isMissingNode() || authNode.isNull() ? null : authNode.asText();
            }
            return key;
        } catch (Exception e) {
            return null;
        }
    }

    private JsonNode findScopeRequestByName(int scopeId, String serviceName, String authKey) {
        Response requestResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
        .when()
            .get("/scope/" + scopeId + "/request/");
        JsonNode requests = parseDataArray(requestResponse);
        Assertions.assertThat(requests)
            .as("scope %d should expose at least one request", scopeId)
            .isNotNull()
            .isNotEmpty();

        JsonNode containsMatch = null;
        int containsLength = Integer.MAX_VALUE;
        for (JsonNode candidate : requests) {
            String name = candidate.path("name").asText();
            if (serviceName.equalsIgnoreCase(name)) {
                return candidate;
            }
            if (name.toLowerCase(Locale.ROOT).contains(serviceName.toLowerCase(Locale.ROOT))
                    && name.length() < containsLength) {
                containsMatch = candidate;
                containsLength = name.length();
            }
        }
        if (containsMatch != null) {
            ScenarioLogManager.getLogger().info(
                "Request '{}' matched '{}' for scope {}",
                serviceName,
                containsMatch.path("name").asText(),
                scopeId);
            return containsMatch;
        }
        ScenarioLogManager.getLogger().warn(
            "Request '{}' not found for scope {}; using first available request", serviceName, scopeId);
        return requests.get(0);
    }

    private void forgetProcess(int processId) {
        scenarioProcessIds.remove(Integer.valueOf(processId));
        processScopeById.remove(processId);
    }

    private void trackProcess(int processId, int scopeId) {
        if (processId <= 0) {
            return;
        }
        if (!scenarioProcessIds.contains(processId)) {
            scenarioProcessIds.add(processId);
        }
        if (scopeId > 0) {
            processScopeById.put(processId, scopeId);
        }
    }

    private int scopeIdOf(JsonNode process) {
        if (process == null) {
            return 0;
        }
        int scopeId = process.path("scope").path("id").asInt();
        if (scopeId > 0) {
            return scopeId;
        }
        JsonNode appointments = process.path("appointments");
        if (appointments.isArray() && appointments.size() > 0) {
            return appointments.get(0).path("scope").path("id").asInt();
        }
        return 0;
    }

    /**
     * Delete is allowed only for the workstation scope and the user's departments.
     * A forwarded appointment lives on the target scope, so the workstation has to follow it.
     * Soft mode (required=false) is for {@code @After} cleanup after a UI Keycloak login may have
     * invalidated the API X-AuthKey for the same ataf_superuser account.
     */
    private boolean ensureWorkstationOnScope(int scopeId, boolean required) {
        if (scopeId <= 0 || scopeId == workstationScopeId) {
            return true;
        }
        iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "21");
        if (response.getStatusCode() == 200) {
            return true;
        }
        // UI login as the same pool user can drop the API session; refresh once.
        cachedXAuthKey = null;
        try {
            getOrLoginXAuthKey();
            iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "21");
        } catch (RuntimeException e) {
            if (required) {
                throw e;
            }
            ScenarioLogManager.getLogger().warn(
                "Skip cleanup workstation switch to scope {}: {}", scopeId, e.getMessage());
            return false;
        }
        if (response.getStatusCode() == 200) {
            return true;
        }
        if (required) {
            Assertions.assertThat(response.getStatusCode())
                .as("POST /workstation/ for scope %d body=%s", scopeId, truncate(response.asString(), 500))
                .isEqualTo(200);
        }
        ScenarioLogManager.getLogger().warn(
            "Skip cleanup workstation switch to scope {}: HTTP {} body={}",
            scopeId,
            response.getStatusCode(),
            truncate(response.asString(), 300));
        return false;
    }

    private List<JsonNode> slotsBetween(JsonNode freeList, long fromInclusive, long toInclusive) {
        List<JsonNode> candidates = new ArrayList<>();
        if (freeList == null) {
            return candidates;
        }
        for (JsonNode process : freeList) {
            long date = process.path("appointments").path(0).path("date").asLong(0);
            if (date >= fromInclusive && date <= toInclusive) {
                candidates.add(process);
            }
        }
        candidates.sort(Comparator.comparingLong(
                process -> process.path("appointments").path(0).path("date").asLong()));
        return candidates;
    }

    private JsonNode fetchFreeProcesses(int scopeId, JsonNode request, String authKey) {
        JsonNode freeList = fetchFreeProcessesAllowEmpty(scopeId, request, authKey);
        Assertions.assertThat(freeList)
            .as("POST /process/status/free/ for scope %d on %s", scopeId, BerlinTime.today())
            .isNotNull()
            .isNotEmpty();
        return freeList;
    }

    private JsonNode fetchFreeProcessesAllowEmpty(int scopeId, JsonNode request, String authKey) {
        LocalDate today = BerlinTime.today();
        Response freeResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("slotType", "intern")
            .queryParam("slotsRequired", 0)
            .body(toJson(freeProcessCalendar(scopeId, request, today)))
        .when()
            .post("/process/status/free/");
        return parseDataArray(freeResponse);
    }

    private JsonNode fetchFreeProcessesLookingAhead(int scopeId, JsonNode request, String authKey) {
        return fetchFreeProcessesLookingAhead(scopeId, request, authKey, Instant.now().getEpochSecond());
    }

    private JsonNode fetchFreeProcessesLookingAhead(
            int scopeId, JsonNode request, String authKey, long earliestEpoch) {
        LocalDate start = BerlinTime.today();
        JsonNode lastEmpty = null;
        for (int offset = 0; offset < 8; offset++) {
            LocalDate day = start.plusDays(offset);
            Response freeResponse = given()
                .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
                .header("X-AuthKey", authKey)
                .contentType("application/json")
                .queryParam("slotType", "intern")
                .queryParam("slotsRequired", 0)
                .body(toJson(freeProcessCalendar(scopeId, request, day)))
            .when()
                .post("/process/status/free/");
            JsonNode freeList = parseDataArray(freeResponse);
            if (freeList != null && !freeList.isEmpty()) {
                long dayStart = day.atStartOfDay(java.time.ZoneId.of("Europe/Berlin")).toEpochSecond();
                long dayEnd = dayStart + 86_400L;
                long from = offset == 0 ? Math.max(earliestEpoch, dayStart) : dayStart;
                if (slotsBetween(freeList, from, dayEnd).isEmpty()) {
                    ScenarioLogManager.getLogger().info(
                            "Intern free list for {} on scope {} has no slot at/after epoch {}; skipping",
                            day,
                            scopeId,
                            from);
                    lastEmpty = freeList;
                    continue;
                }
                if (offset > 0) {
                    ScenarioLogManager.getLogger().info(
                        "No eligible intern slot left on {}; using {}", start, day);
                }
                return freeList;
            }
            lastEmpty = freeList;
        }
        Assertions.assertThat(lastEmpty)
            .as("POST /process/status/free/ for scope %d from %s", scopeId, start)
            .isNotNull()
            .isNotEmpty();
        return lastEmpty;
    }

    private ObjectNode freeProcessCalendar(int scopeId, JsonNode request, LocalDate day) {
        ObjectNode calendar = MAPPER.createObjectNode();
        ObjectNode dayNode = MAPPER.createObjectNode();
        dayNode.put("year", day.getYear());
        dayNode.put("month", day.getMonthValue());
        dayNode.put("day", day.getDayOfMonth());
        calendar.set("firstDay", dayNode);
        calendar.set("lastDay", dayNode.deepCopy());

        ArrayNode scopes = MAPPER.createArrayNode();
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        scopes.add(scope);
        calendar.set("scopes", scopes);

        ArrayNode requests = MAPPER.createArrayNode();
        requests.add(request.deepCopy());
        calendar.set("requests", requests);
        return calendar;
    }

    private JsonNode refreshAssignedProcessFromWorkstation(String authKey) {
        Response workstationResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("resolveReferences", 2)
        .when()
            .get("/workstation/");

        JsonNode workstation = parseDataNode(workstationResponse);
        JsonNode process = workstation != null ? workstation.path("process") : null;
        if (process != null && process.has("id") && process.path("id").asInt() > 0) {
            rememberProcess(process);
            return process;
        }
        return lastProcess;
    }

    /** Set lastProcess status without requiring a workstation assignment on the current clerk. */
    private void forceLastProcessStatus(String status) {
        Assertions.assertThat(lastProcess).as("last process before status force").isNotNull();
        JsonNode process = Objects.requireNonNull(lastProcess, "last process before status force");
        int processId = process.path("id").asInt();
        String processAuthKey = process.path("authKey").asText();
        Assertions.assertThat(processId).as("process id").isPositive();
        Assertions.assertThat(processAuthKey).as("process authKey").isNotBlank();
        ObjectNode body = process.deepCopy();
        body.put("status", status);
        body.putNull("parkedBy");
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("initiator", "admin")
            .body(toJson(body))
        .when()
            .post("/process/" + processId + "/" + processAuthKey + "/");
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    private void rememberProcess(JsonNode process) {
        if (process != null && !process.isMissingNode() && !process.isNull()) {
            lastProcess = process;
            int processId = process.path("id").asInt();
            if (processId > 0) {
                TestDataHelper.setTestData("zmsapi_last_process_id", Integer.toString(processId));
            }
        }
    }

    private String toJson(JsonNode node) {
        try {
            return MAPPER.writeValueAsString(node);
        } catch (Exception e) {
            throw new RuntimeException("Failed to serialize JSON body", e);
        }
    }

    private String truncate(String value, int maxLength) {
        if (value == null) {
            return "";
        }
        return value.length() > maxLength ? value.substring(0, maxLength) + "..." : value;
    }

    private boolean requestListContainsName(JsonNode requests, String serviceName) {
        return findRequestByName(requests, serviceName) != null;
    }

    private JsonNode findRequestByName(JsonNode requests, String serviceName) {
        if (requests == null || !requests.isArray()) {
            return null;
        }
        for (JsonNode request : requests) {
            if (serviceName.equals(request.path("name").asText())) {
                return request;
            }
        }
        return null;
    }

    private Set<String> requestIds(JsonNode requests) {
        Set<String> ids = new HashSet<>();
        if (requests == null || !requests.isArray()) {
            return ids;
        }
        for (JsonNode request : requests) {
            String id = request.path("id").asText();
            if (!id.isBlank()) {
                ids.add(id);
            }
        }
        return ids;
    }

    private JsonNode parseDataNode(Response apiResponse) {
        try {
            JsonNode root = MAPPER.readTree(apiResponse.asString());
            JsonNode data = root.path("data");
            return data.isMissingNode() || data.isNull() ? null : data;
        } catch (Exception e) {
            throw new RuntimeException("Failed to parse API response data", e);
        }
    }

    private ArrayNode parseDataArray(Response apiResponse) {
        JsonNode data = parseDataNode(apiResponse);
        if (data == null) {
            return null;
        }
        if (data.isArray()) {
            return (ArrayNode) data;
        }
        if (data.isObject()) {
            // workstation/process/called returns workstation with nested process
            JsonNode process = data.path("process");
            if (process.has("id")) {
                return MAPPER.createArrayNode().add(process);
            }
        }
        return null;
    }
}
