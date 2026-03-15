package zms.ataf.rest.steps;

import static io.restassured.RestAssured.*;

import java.util.List;
import java.util.Map;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmsapi.MailListItem;
import zms.ataf.rest.dto.zmsapi.MailProcessRef;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;

/**
 * Steps for zmsapi GET /mails/ (superuser X-Authkey) to fetch preconfirmation mail
 * and extract processId/authKey for the confirm-appointment step.
 * Shared by zmscitizenapi (process from reserve/preconfirm response) and zmscitizenview
 * (process from sync or continueFromPreconfirmStep); always looks up mail by process id.
 */
public class ZmsApiMailSteps {

    private static String cachedXAuthKey;

    @When("I fetch the preconfirmation mail for the current process")
    public void iFetchThePreconfirmationMailForTheCurrentProcess() {
        String authKey = getOrLoginXAuthKey();
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        if (booking == null) {
            throw new IllegalStateException(
                "No current process. Set booking process first: API flow = reserve/preconfirm; citizenview = sync from localStorage or capture in continueFromPreconfirmStep.");
        }
        Integer processId = booking.getProcessId();
        ScenarioLogManager.getLogger().info("zmsapi: fetching preconfirmation mail from GET /mails/ for process {}", processId);
        Response response = given()
            .baseUri(TestConfig.getBaseUri())
            .header("X-Authkey", authKey)
            .queryParam("limit", 500)
        .when()
            .get("/mails/");
        CommonApiSteps.setResponse(response);
        int status = response.getStatusCode();
        ScenarioLogManager.getLogger().info("zmsapi: GET /mails/ status={} bodySize={}", status, response.getBody().asString().length());
        if (status != 200) {
            return;
        }
        List<MailListItem> mails = parseMailList(response);
        MailListItem match = null;
        for (MailListItem mail : mails) {
            MailProcessRef proc = mail.getProcess();
            if (proc != null && processId.equals(proc.getId())) {
                match = mail;
                break;
            }
        }
        if (match == null || match.getProcess() == null) {
            throw new IllegalStateException(
                "Preconfirmation mail not found for process " + processId + ". Ensure preconfirm was called and mail is sent.");
        }
        String confirmProcessId = String.valueOf(match.getProcess().getId());
        String confirmAuthKey = match.getProcess().getAuthKey();
        CitizenApiSteps.setBookingConfirmCredentials(confirmProcessId, confirmAuthKey != null ? confirmAuthKey : "");
        ScenarioLogManager.getLogger().info("zmsapi: preconfirmation mail found for process {}, confirm credentials set for deep link", processId);
    }

    private String getOrLoginXAuthKey() {
        if (cachedXAuthKey != null && !cachedXAuthKey.isBlank()) {
            return cachedXAuthKey;
        }

        // Fallback for local/dev: obtain X-AuthKey by calling POST /workstation/login/
        // Use the system messenger account by default (same password as other system users).
        String username = TestPropertiesHelper.getPropertyAsString("zmsapiMailUserName", true, "_system_messenger");
        String password = TestPropertiesHelper.getPropertyAsString("zmsapiMailUserPassword", true, "vorschau");

        Response loginResponse = given()
            .baseUri(TestConfig.getBaseUri())
            .contentType("application/json")
            .body(Map.of("id", username, "password", password))
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
                "Unable to auto-login to obtain X-AuthKey. Ensure "
                    + "zmsapiMailUserName/zmsapiMailUserPassword can login to /workstation/login/. HTTP " + loginResponse.getStatusCode());
        }

        try {
            ObjectMapper mapper = new ObjectMapper();
            JsonNode root = mapper.readTree(body);
            JsonNode authNode = root.path("data").path("authkey");
            String key = authNode.isMissingNode() || authNode.isNull() ? null : authNode.asText();
            if (key == null || key.isBlank()) {
                // some payloads might use "authKey"
                authNode = root.path("data").path("authKey");
                key = authNode.isMissingNode() || authNode.isNull() ? null : authNode.asText();
            }
            if (key == null || key.isBlank()) {
                throw new IllegalStateException("Login succeeded but response did not contain data.authkey");
            }
            cachedXAuthKey = key;
            return key;
        } catch (Exception e) {
            throw new IllegalStateException("Failed to parse /workstation/login/ response for authkey", e);
        }
    }

    @SuppressWarnings("unchecked")
    private List<MailListItem> parseMailList(Response response) {
        try {
            ObjectMapper mapper = new ObjectMapper();
            com.fasterxml.jackson.databind.JavaType listType = mapper.getTypeFactory()
                .constructCollectionType(List.class, MailListItem.class);
            com.fasterxml.jackson.databind.JavaType type = mapper.getTypeFactory()
                .constructParametricType(ApiResponse.class, listType);
            ApiResponse<List<MailListItem>> apiResponse = mapper.readValue(response.asString(), type);
            return apiResponse != null && apiResponse.getData() != null ? apiResponse.getData() : List.of();
        } catch (Exception e) {
            throw new RuntimeException("Failed to parse /mails/ response: " + e.getMessage(), e);
        }
    }
}
