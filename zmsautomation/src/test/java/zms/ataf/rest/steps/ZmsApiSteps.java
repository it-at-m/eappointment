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
import java.util.Set;
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
    private int workstationScopeId;
    private JsonNode departmentServices;
    private String ticketprinterHash;
    private Integer createdAvailabilityId;
    private Integer historyAvailabilityId;
    private int historyScopeId;
    private boolean lastProcessDeleted;

    @Before
    public void resetProcessContext() {
        lastProcess = null;
        scenarioProcessIds.clear();
        processScopeById.clear();
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
    }

    @After
    public void deleteOpeningHoursLeftByTheScenario() {
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
        if (scopeId != null) {
            ensureWorkstationOnScope(scopeId);
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

    @When("I queue a walk-in at scope {int} with service {string} and name {string} with the X-AuthKey")
    public void iQueueAWalkInAtScopeWithServiceAndNameWithTheXAuthKey(
            int scopeId, String serviceName, String familyName) {
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
        iAmLoggedInToTheZmsApiAs("agent_queue");
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        JsonNode freeList = fetchFreeProcesses(scopeId, request, authKey);

        long now = Instant.now().getEpochSecond();
        long currentMinute = now - (now % 60);
        long earliest = (now % 60 > 40) ? currentMinute + 60 : currentMinute;
        long soon = now + 6 * 60;
        // Department 2 reminds 1440 minutes ahead, so a later slot today is still due.
        long reminderHorizon = now + 24 * 60 * 60;
        List<JsonNode> candidates = slotsBetween(freeList, earliest, soon);
        if (candidates.isEmpty()) {
            candidates = slotsBetween(freeList, earliest, reminderHorizon);
            if (!candidates.isEmpty()) {
                ScenarioLogManager.getLogger().info(
                        "No intern slot in the next minutes for scope {}; using the next slot inside the reminder window",
                        scopeId);
            }
        }
        Assertions.assertThat(candidates)
                .as("scope %d should have an intern slot between epoch %d and %d", scopeId, earliest, reminderHorizon)
                .isNotEmpty();

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
        Assertions.assertThat(reserved).as("reserved terminkunde at scope %d", scopeId).isNotNull();

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(reserved))
        .when()
            .post("/process/status/confirmed/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("POST /process/status/confirmed/ body=%s", truncate(response.asString(), 1000))
                .isEqualTo(200);
        JsonNode confirmed = parseDataNode(response);
        rememberProcess(confirmed);
        trackProcess(confirmed.path("id").asInt(), scopeId);

        long appointment = confirmed.path("appointments").path(0).path("date").asLong(0);
        Assertions.assertThat(appointment)
                .as("confirmed appointment time")
                .isBetween(earliest, reminderHorizon);
        TestDataHelper.setTestData("appointment_epoch", Long.toString(appointment));
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

    @When("I queue a walk-in at scope {int} with service {string}, name {string}, free text {string} and second free text {string} with the X-AuthKey")
    public void iQueueAWalkInWithFreeTextWithTheXAuthKey(
            int scopeId, String serviceName, String familyName, String freeText, String secondFreeText) {
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
        process.put("customTextfield", freeText);
        process.put("customTextfield2", secondFreeText);
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

    @When("I redirect the last process to scope {int} with the X-AuthKey")
    public void iRedirectTheLastProcessToScopeWithTheXAuthKey(int targetScopeId) {
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

        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .body(toJson(reserved))
        .when()
            .post("/process/status/confirmed/");
        CommonApiSteps.setResponse(response);
        rememberProcess(parseDataNode(response));
    }

    @When("I call the last process at the workstation with the X-AuthKey")
    public void iCallTheLastProcessAtTheWorkstationWithTheXAuthKey() {
        Assertions.assertThat(lastProcess)
            .as("Reserve an appointment before calling it")
            .isNotNull();

        replaceFutureAppointmentWithWalkIn();
        String authKey = getOrLoginXAuthKey();
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("allowClusterWideCall", true)
            .body(toJson(lastProcess))
        .when()
            .post("/workstation/process/called/");
        CommonApiSteps.setResponse(response);
        JsonNode workstation = parseDataNode(response);
        rememberProcess(workstation != null ? workstation.path("process") : null);
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
        if (findOpeningHoursIds(scopeId, "ATAF-ZMSKVR-167").isEmpty()) {
            createSpontankundenOpeningHours(scopeId, from, to);
        }
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
        String authKey = getOrLoginXAuthKey();
        java.util.List<Integer> ids = findOpeningHoursIds(scopeId, null);
        Assertions.assertThat(ids)
            .as("scope %d should have Spontankunden opening hours to delete", scopeId)
            .isNotEmpty();
        for (int id : ids) {
            response = given()
                .baseUri(apiBaseUri())
                .header("X-AuthKey", authKey)
            .when()
                .delete("/availability/" + id + "/");
            CommonApiSteps.setResponse(response);
            Assertions.assertThat(response.getStatusCode())
                .as("DELETE /availability/%d/", id)
                .isEqualTo(200);
        }
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
    }

    private java.util.List<Integer> findOpeningHoursIds(int scopeId, String description) {
        String authKey = getOrLoginXAuthKey();
        LocalDate today = BerlinTime.today();
        Response listResponse = given()
            .baseUri(apiBaseUri())
            .header("X-AuthKey", authKey)
            .queryParam("startDate", today.toString())
            .queryParam("endDate", today.toString())
        .when()
            .get("/scope/" + scopeId + "/availability/");
        if (listResponse.getStatusCode() != 200) {
            return java.util.List.of();
        }
        ArrayNode list = parseDataArray(listResponse);
        if (list == null) {
            return java.util.List.of();
        }
        java.util.List<Integer> ids = new java.util.ArrayList<>();
        for (JsonNode item : list) {
            if (!"openinghours".equals(item.path("type").asText()) || item.path("id").asInt() <= 0) {
                continue;
            }
            if (description != null && !description.equals(item.path("description").asText())) {
                continue;
            }
            ids.add(item.path("id").asInt());
        }
        return ids;
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
     */
    private void ensureWorkstationOnScope(int scopeId) {
        if (scopeId <= 0 || scopeId == workstationScopeId) {
            return;
        }
        iUpdateTheWorkstationWithScopeAndCounterWithTheXAuthKey(scopeId, "21");
        Assertions.assertThat(response.getStatusCode())
            .as("POST /workstation/ for scope %d body=%s", scopeId, truncate(response.asString(), 500))
            .isEqualTo(200);
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
        LocalDate today = BerlinTime.today();
        ObjectNode calendar = MAPPER.createObjectNode();
        ObjectNode firstDay = MAPPER.createObjectNode();
        firstDay.put("year", today.getYear());
        firstDay.put("month", today.getMonthValue());
        firstDay.put("day", today.getDayOfMonth());
        calendar.set("firstDay", firstDay);
        calendar.set("lastDay", firstDay.deepCopy());

        ArrayNode scopes = MAPPER.createArrayNode();
        ObjectNode scope = MAPPER.createObjectNode();
        scope.put("id", scopeId);
        scopes.add(scope);
        calendar.set("scopes", scopes);

        ArrayNode requests = MAPPER.createArrayNode();
        requests.add(request.deepCopy());
        calendar.set("requests", requests);

        Response freeResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .header("X-AuthKey", authKey)
            .contentType("application/json")
            .queryParam("slotType", "intern")
            .queryParam("slotsRequired", 0)
            .body(toJson(calendar))
        .when()
            .post("/process/status/free/");

        JsonNode freeList = parseDataArray(freeResponse);
        Assertions.assertThat(freeList)
            .as("POST /process/status/free/ for scope %d on %s", scopeId, today)
            .isNotNull()
            .isNotEmpty();
        return freeList;
    }

    private JsonNode fetchFreeProcessesLookingAhead(int scopeId, JsonNode request, String authKey) {
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
                if (offset > 0) {
                    ScenarioLogManager.getLogger().info(
                        "No intern slot left on {}; using {}", start, day);
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

    private void rememberProcess(JsonNode process) {
        if (process != null && !process.isMissingNode() && !process.isNull()) {
            lastProcess = process;
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
