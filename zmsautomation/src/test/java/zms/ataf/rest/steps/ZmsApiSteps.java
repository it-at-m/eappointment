package zms.ataf.rest.steps;

import static io.restassured.RestAssured.given;

import java.time.DayOfWeek;
import java.time.LocalDate;
import java.util.Locale;

import org.assertj.core.api.Assertions;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ArrayNode;
import com.fasterxml.jackson.databind.node.ObjectNode;

import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.cucumber.java.Before;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
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
    private String ticketprinterHash;

    @Before
    public void resetProcessContext() {
        lastProcess = null;
        cachedXAuthKey = null;
        scenarioLoginUsername = null;
        scenarioLoginPassword = null;
        ticketprinterHash = null;
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
            objectNode.put("name", counter);
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
        }
    }

    @When("I reserve an appointment at scope {int} with service {string} and amendment {string} with the X-AuthKey")
    public void iReserveAnAppointmentAtScopeWithServiceAndAmendmentWithTheXAuthKey(
            int scopeId, String serviceName, String amendment) {
        String authKey = getOrLoginXAuthKey();
        JsonNode request = findScopeRequestByName(scopeId, serviceName, authKey);
        JsonNode freeProcess = fetchFirstFreeProcess(scopeId, request, authKey);
        ObjectNode process = freeProcess.deepCopy();

        String familyName = TestPropertiesHelper.getPropertyAsString("zmsapiAppointmentFamilyName", true, "Terminkunde");
        String email = TestPropertiesHelper.getPropertyAsString("zmsapiAppointmentEmail", true, "terminkunde@example.com");
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

        JsonNode reserved = parseDataNode(response);
        Assertions.assertThat(reserved).isNotNull();

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
        ScenarioLogManager.getLogger().info("Ticketprinter hash for scope {}: {}", scopeId, ticketprinterHash);
    }

    @Given("Spontankunden opening hours exist for scope {int} from {string} to {string}")
    public void spontankundenOpeningHoursExistForScope(int scopeId, String from, String to) {
        if (findOpeningHoursIds(scopeId, "ATAF-ZMSKVR-167").isEmpty()) {
            createSpontankundenOpeningHours(scopeId, from, to);
        }
    }

    @When("I delete Spontankunden opening hours for scope {int} with the X-AuthKey")
    public void iDeleteSpontankundenOpeningHoursForScope(int scopeId) {
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
            return new String[] { resolveWorkstationUsername(scenarioLoginUsername), password };
        }

        String username = TestPropertiesHelper.getPropertyAsString("zmsapiUserName", true);
        String password = TestPropertiesHelper.getPropertyAsString("zmsapiUserPassword", true);
        if (username.isBlank() || password.isBlank()) {
            throw new IllegalStateException(
                "Set testautomation.zmsapiUserName and testautomation.zmsapiUserPassword in testautomation.properties, "
                    + "or use 'Given the ZMS API workstation user is \"<role>\"' in the scenario");
        }
        return new String[] { resolveWorkstationUsername(username), password };
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

        for (JsonNode candidate : requests) {
            if (serviceName.equalsIgnoreCase(candidate.path("name").asText())) {
                return candidate;
            }
        }
        ScenarioLogManager.getLogger().warn(
            "Request '{}' not found for scope {}; using first available request", serviceName, scopeId);
        return requests.get(0);
    }

    private JsonNode fetchFirstFreeProcess(int scopeId, JsonNode request, String authKey) {
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
        return freeList.get(0);
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
