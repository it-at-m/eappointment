package zms.ataf.rest.steps;

import static io.restassured.RestAssured.given;
import org.assertj.core.api.Assertions;

import java.time.LocalDate;

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
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmsapi.StatusResponse;

public class ZmsApiSteps {

    private static final ObjectMapper MAPPER = new ObjectMapper();

    private Response response;
    private String baseUri;
    private static String cachedXAuthKey;
    private JsonNode lastProcess;

    @Before
    public void resetProcessContext() {
        lastProcess = null;
        cachedXAuthKey = null;
    }
    
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
    
    @When("I request the status endpoint")
    public void iRequestTheStatusEndpoint() {
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
        .when()
            .get("/status/");
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

    @When("I make a POST request to {string} with valid id and password")
    public void iMakeAPostRequestToWithValidIdAndPassword(String endpoint) {
        String[] credentials = workstationLoginCredentials();

        cachedXAuthKey = null;
        response = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .contentType("application/json")
            .body(java.util.Map.of("id", credentials[0], "password", credentials[1]))
        .when()
            .post(endpoint);
        CommonApiSteps.setResponse(response);

        if (response.getStatusCode() < 200 || response.getStatusCode() >= 300) {
            return;
        }
        String key = extractAuthKeyFromBody(response.asString());
        if (key == null || key.isBlank()) {
            throw new IllegalStateException("Login succeeded but response did not contain data.authkey/authKey");
        }
        cachedXAuthKey = key;
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

        // Login via /workstation/login/ and extract authkey/authKey from response.
        String[] credentials = workstationLoginCredentials();

        Response loginResponse = given()
            .baseUri(baseUri != null ? baseUri : TestConfig.getBaseUri())
            .contentType("application/json")
            .body(java.util.Map.of("id", credentials[0], "password", credentials[1]))
        .when()
            .post("/workstation/login/");

        CommonApiSteps.setResponse(loginResponse);

        String body = loginResponse.asString();
        ScenarioLogManager.getLogger().info(String.format(
            "ZMS API /workstation/login/ (auto X-AuthKey) status=%d body=%s",
            loginResponse.getStatusCode(),
            body.length() > 500 ? body.substring(0, 500) + "..." : body
        ));

        if (loginResponse.getStatusCode() < 200 || loginResponse.getStatusCode() >= 300) {
            throw new IllegalStateException(
                "Unable to auto-login to obtain X-AuthKey. Ensure zmsapiUserName/zmsapiUserPassword can login to /workstation/login/. HTTP "
                    + loginResponse.getStatusCode());
        }

        String key = extractAuthKeyFromBody(body);
        if (key == null || key.isBlank()) {
            throw new IllegalStateException("Login succeeded but response did not contain data.authkey/authKey");
        }
        cachedXAuthKey = key;
        return key;
    }

    private String[] workstationLoginCredentials() {
        String username = TestPropertiesHelper.getPropertyAsString("zmsapiUserName", true);
        String password = TestPropertiesHelper.getPropertyAsString("zmsapiUserPassword", true);
        if (username.isBlank() || password.isBlank()) {
            throw new IllegalStateException(
                "Set testautomation.zmsapiUserName and testautomation.zmsapiUserPassword in testautomation.properties "
                    + "(see V22__add_role_test_users.sql)");
        }
        return new String[] { username, password };
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
        LocalDate today = LocalDate.now();
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
