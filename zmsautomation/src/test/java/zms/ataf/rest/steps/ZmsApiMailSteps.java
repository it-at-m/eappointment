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
 * Shared by zmscitizenapi (process from reserve/preconfirm response) and zmscitizenview.
 * When process is set: finds mail by process id. When process is null (citizenview only):
 * uses most recent mail from GET /mails/ with process id so confirm step can proceed.
 */
public class ZmsApiMailSteps {

    private static String cachedXAuthKey;

    @When("I fetch the preconfirmation mail for the current process")
    public void iFetchThePreconfirmationMailForTheCurrentProcess() {
        String authKey = getOrLoginXAuthKey();
        ScenarioLogManager.getLogger().info("zmsapi: fetching preconfirmation mail from GET /mails/");
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
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        MailListItem match = null;
        if (booking != null) {
            Integer processId = booking.getProcessId();
            ScenarioLogManager.getLogger().info("zmsapi: looking for newest mail (max id) matching process {}", processId);
            for (MailListItem mail : mails) {
                MailProcessRef proc = mail.getProcess();
                if (proc != null && processId.equals(proc.getId())) {
                    if (match == null || (mail.getId() != null && (match.getId() == null || mail.getId() > match.getId()))) {
                        match = mail;
                    }
                }
            }
        }
        if (match == null && !mails.isEmpty()) {
            ScenarioLogManager.getLogger().info("zmsapi: no booking process (citizenview fallback); using newest mail (max id) with process id");
            for (MailListItem mail : mails) {
                MailProcessRef proc = mail.getProcess();
                if (proc != null && proc.getId() != null && proc.getAuthKey() != null && !proc.getAuthKey().isBlank()) {
                    if (match == null || (mail.getId() != null && (match.getId() == null || mail.getId() > match.getId()))) {
                        match = mail;
                    }
                }
            }
        }
        if (match == null || match.getProcess() == null) {
            throw new IllegalStateException(
                "Preconfirmation mail not found. Ensure preconfirm was called and mail is sent (GET /mails/ returned " + mails.size() + " mail(s)).");
        }
        String confirmProcessId = String.valueOf(match.getProcess().getId());
        String confirmAuthKey = match.getProcess().getAuthKey();
        CitizenApiSteps.setBookingConfirmCredentials(confirmProcessId, confirmAuthKey != null ? confirmAuthKey : "");
        if (booking == null) {
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(match.getProcess().getId());
            p.setAuthKey(confirmAuthKey);
            CitizenApiSteps.setBookingProcess(p);
        }
        String confirmUrl = extractConfirmUrlFromMailResponse(response.asString(), match.getProcess().getId());
        if (confirmUrl != null) {
            CitizenApiSteps.setBookingConfirmUrl(confirmUrl);
            ScenarioLogManager.getLogger().info("zmsapi: confirm URL extracted from mail body for process {}", match.getProcess().getId());
        }
        ScenarioLogManager.getLogger().info("zmsapi: preconfirmation mail found for process {}, confirm credentials set for deep link", match.getProcess().getId());
    }

    /** Second mail fetch: run after the user has opened the /appointment/confirm/*** link. The confirmation mail (with link to /appointment/***) is only sent once the appointment is confirmed. */
    @When("I fetch the confirmation mail for the current process")
    public void iFetchTheConfirmationMailForTheCurrentProcess() {
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        if (booking == null) {
            throw new IllegalStateException("No booking process; confirm the appointment first so the confirmation mail is sent.");
        }
        Integer processId = booking.getProcessId();
        String authKey = getOrLoginXAuthKey();
        ScenarioLogManager.getLogger().info("zmsapi: fetching confirmation mail (second GET /mails/, after confirm link opened) for process {}", processId);
        Response response = given()
            .baseUri(TestConfig.getBaseUri())
            .header("X-Authkey", authKey)
            .queryParam("limit", 500)
        .when()
            .get("/mails/");
        CommonApiSteps.setResponse(response);
        if (response.getStatusCode() != 200) {
            return;
        }
        String appointmentUrl = extractAppointmentViewUrlFromMailResponse(response.asString(), processId);
        if (appointmentUrl != null) {
            CitizenApiSteps.setBookingAppointmentUrl(appointmentUrl);
            ScenarioLogManager.getLogger().info("zmsapi: appointment view URL extracted from confirmation mail for process {} -> {}", processId, appointmentUrl);
        } else {
            ScenarioLogManager.getLogger().warn("zmsapi: no appointment view URL in any mail for process {} (check second mail has link to /appointment/ without /confirm/)", processId);
        }
    }

    /** Extract appointment view link from GET /mails/ for the given process. Uses mail with max id (newest) that contains the link. */
    private String extractAppointmentViewUrlFromMailResponse(String responseBody, Integer processId) {
        try {
            JsonNode data = new ObjectMapper().readTree(responseBody).path("data");
            if (!data.isArray()) {
                return null;
            }
            int maxMailId = -1;
            String foundUrl = null;
            for (JsonNode mail : data) {
                JsonNode proc = mail.path("process");
                if (proc.isMissingNode() || proc.path("id").asInt(-1) != processId) {
                    continue;
                }
                int mailId = mail.path("id").asInt(-1);
                JsonNode multipart = mail.path("multipart");
                if (!multipart.isArray() || multipart.isEmpty()) {
                    continue;
                }
                for (JsonNode part : multipart) {
                    if (!"text/html".equals(part.path("mime").asText(null))) {
                        continue;
                    }
                    String content = part.path("content").asText("");
                    String url = extractAppointmentViewUrlFromHtml(content);
                    if (url != null && mailId > maxMailId) {
                        maxMailId = mailId;
                        foundUrl = url;
                    }
                }
            }
            return foundUrl;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmsapi: could not extract appointment view URL from mail body", e);
        }
        return null;
    }

    /** Find first href containing appointment/ but not appointment/confirm/ (view link from confirmation mail). */
    private String extractAppointmentViewUrlFromHtml(String html) {
        if (html == null) {
            return null;
        }
        int fromIndex = 0;
        while (true) {
            int anchor = html.indexOf("appointment/", fromIndex);
            if (anchor < 0) {
                return null;
            }
            if (anchor + "appointment/".length() <= html.length() && html.startsWith("appointment/confirm/", anchor)) {
                fromIndex = anchor + 1;
                continue;
            }
            int start = html.lastIndexOf("href=", anchor);
            if (start < 0) {
                fromIndex = anchor + 1;
                continue;
            }
            start += 5;
            while (start < html.length() && (html.charAt(start) == ' ' || html.charAt(start) == '\t')) {
                start++;
            }
            if (start >= html.length()) {
                return null;
            }
            char quote = html.charAt(start);
            if (quote == '\\' && start + 1 < html.length()) {
                quote = html.charAt(start + 1);
                start++;
            }
            if (quote != '"' && quote != '\'') {
                fromIndex = anchor + 1;
                continue;
            }
            start++;
            int end = html.indexOf(quote, start);
            if (end < 0) {
                return null;
            }
            String url = html.substring(start, end).replace("&amp;", "&");
            if (url.contains("appointment/") && !url.contains("appointment/confirm/")) {
                return url;
            }
            fromIndex = anchor + 1;
        }
    }

    /** Extract confirmation link from GET /mails/ response: find mail with max id (newest) for process that contains href with appointment/confirm/. */
    private String extractConfirmUrlFromMailResponse(String responseBody, Integer processId) {
        try {
            JsonNode data = new ObjectMapper().readTree(responseBody).path("data");
            if (!data.isArray()) {
                return null;
            }
            int maxMailId = -1;
            String foundUrl = null;
            for (JsonNode mail : data) {
                JsonNode proc = mail.path("process");
                if (proc.isMissingNode() || proc.path("id").asInt(-1) != processId) {
                    continue;
                }
                int mailId = mail.path("id").asInt(-1);
                JsonNode multipart = mail.path("multipart");
                if (!multipart.isArray() || multipart.isEmpty()) {
                    ScenarioLogManager.getLogger().debug("zmsapi: mail processId={} has no multipart body", processId);
                    continue;
                }
                for (JsonNode part : multipart) {
                    if (!"text/html".equals(part.path("mime").asText(null))) {
                        continue;
                    }
                    String content = part.path("content").asText("");
                    String url = extractConfirmUrlFromHtml(content);
                    if (url != null && mailId > maxMailId) {
                        maxMailId = mailId;
                        foundUrl = url;
                    }
                    if (url == null) {
                        ScenarioLogManager.getLogger().debug("zmsapi: no confirm URL in text/html (length={}, contains appointment/confirm/={})",
                            content.length(), content.contains("appointment/confirm/"));
                    }
                }
            }
            return foundUrl;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmsapi: could not extract confirm URL from mail body", e);
        }
        return null;
    }

    /** Find first href containing appointment/confirm/ in HTML; tolerate different quote and escape styles. */
    private String extractConfirmUrlFromHtml(String html) {
        if (html == null) {
            return null;
        }
        int anchor = html.indexOf("appointment/confirm/");
        if (anchor < 0) {
            return null;
        }
        int start = html.lastIndexOf("href=", anchor);
        if (start < 0) {
            return null;
        }
        start += 5;
        while (start < html.length() && (html.charAt(start) == ' ' || html.charAt(start) == '\t')) {
            start++;
        }
        if (start >= html.length()) {
            return null;
        }
        char quote = html.charAt(start);
        if (quote == '\\' && start + 1 < html.length()) {
            quote = html.charAt(start + 1);
            start++;
        }
        if (quote != '"' && quote != '\'') {
            return null;
        }
        start++;
        int end = html.indexOf(quote, start);
        if (end < 0) {
            return null;
        }
        String url = html.substring(start, end).replace("&amp;", "&");
        return url.contains("appointment/confirm/") ? url : null;
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
