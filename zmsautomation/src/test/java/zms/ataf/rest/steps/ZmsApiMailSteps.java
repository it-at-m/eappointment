package zms.ataf.rest.steps;

import static io.restassured.RestAssured.*;

import java.util.List;
import java.util.Map;

import org.assertj.core.api.Assertions;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.cucumber.java.Before;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import io.restassured.response.Response;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.rest.dto.common.ApiResponse;
import zms.ataf.rest.dto.zmsapi.MailListItem;
import zms.ataf.rest.dto.zmsapi.MailProcessRef;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;

/**
 * Steps for zmsapi GET /mails/ (superuser X-Authkey) to fetch preconfirmation mail
 * and extract processId/authKey for the confirm-appointment step.
 * Shared by zmscitizenapi (process from reserve/preconfirm response) and zmscitizenview.
 * Finds the mail whose process id is the current booking. When the page has not stored a
 * process id yet, the contact email entered for this scenario selects that process.
 */
public class ZmsApiMailSteps {

    private static final ThreadLocal<String> CACHED_X_AUTH_KEY = new ThreadLocal<>();
    private String lastCancellationMailHtml;

    @Before
    public void clearMailAuthKey() {
        CACHED_X_AUTH_KEY.remove();
    }

    @When("I fetch the preconfirmation mail for the current process")
    public void iFetchThePreconfirmationMailForTheCurrentProcess() {
        String authKey = getOrLoginXAuthKey();
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        Integer processId = booking != null ? booking.getProcessId() : null;
        String contactEmail = CitizenApiSteps.getBookingContactEmail();
        ScenarioLogManager.getLogger().info(
            "zmsapi: fetching preconfirmation mail from GET /mails/ for process {} email {}",
            processId,
            contactEmail);
        Response response = null;
        List<MailListItem> mails = List.of();
        MailListItem match = null;
        for (int attempt = 1; attempt <= 6 && (match == null || match.process() == null); attempt++) {
            if (attempt > 1) {
                try {
                    Thread.sleep(2000L);
                } catch (InterruptedException interrupted) {
                    Thread.currentThread().interrupt();
                    throw new IllegalStateException("Interrupted while waiting for preconfirmation mail", interrupted);
                }
            }
            response = given()
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
            String body = response.asString();
            mails = parseMailList(response);
            if (processId != null) {
                ScenarioLogManager.getLogger().info("zmsapi: looking for mail matching process {}", processId);
                match = newestMailForProcess(mails, processId);
            } else if (contactEmail != null && !contactEmail.isBlank()) {
                ScenarioLogManager.getLogger().info("zmsapi: looking for mail whose body contains {}", contactEmail);
                match = newestMailContaining(body, mails, contactEmail);
            }
        }
        if (match == null || match.process() == null) {
            throw new IllegalStateException(
                "Preconfirmation mail not found for process " + processId + " email " + contactEmail
                    + " (GET /mails/ returned " + mails.size() + " mail(s)).");
        }
        String confirmProcessId = String.valueOf(match.process().id());
        String confirmAuthKey = match.process().authKey();
        CitizenApiSteps.setBookingConfirmCredentials(confirmProcessId, confirmAuthKey != null ? confirmAuthKey : "");
        if (booking == null || booking.getProcessId() == null || booking.getAuthKey() == null || booking.getAuthKey().isBlank()) {
            ThinnedProcess p = booking != null ? booking : new ThinnedProcess();
            p.setProcessId(match.process().id());
            p.setAuthKey(confirmAuthKey);
            CitizenApiSteps.setBookingProcess(p);
        }
        String confirmUrl = extractConfirmUrlFromMailResponse(response.asString(), match.process().id());
        if (confirmUrl != null) {
            CitizenApiSteps.setBookingConfirmUrl(confirmUrl);
            ScenarioLogManager.getLogger().info("zmsapi: confirm URL extracted from mail body for process {}", match.process().id());
        }
        ScenarioLogManager.getLogger().info("zmsapi: preconfirmation mail found for process {}, confirm credentials set for deep link", match.process().id());
    }

    private static MailListItem newestMailForProcess(List<MailListItem> mails, Integer processId) {
        MailListItem match = null;
        for (MailListItem mail : mails) {
            MailProcessRef proc = mail.process();
            if (proc != null && processId.equals(proc.id())
                    && (match == null || (mail.id() != null && (match.id() == null || mail.id() > match.id())))) {
                match = mail;
            }
        }
        return match;
    }

    /** Mail whose HTML contains this scenario's contact address, so a parallel booking is not selected. */
    private static MailListItem newestMailContaining(String responseBody, List<MailListItem> mails, String contactEmail) {
        try {
            JsonNode data = new ObjectMapper().readTree(responseBody).path("data");
            if (!data.isArray()) {
                return null;
            }
            String needle = contactEmail.toLowerCase();
            MailListItem match = null;
            for (JsonNode mail : data) {
                if (!mailHtmlContains(mail, needle)) {
                    continue;
                }
                int id = mail.path("id").asInt(-1);
                for (MailListItem item : mails) {
                    if (item.id() != null && item.id() == id && item.process() != null && item.process().id() != null
                            && (match == null || match.id() == null || item.id() > match.id())) {
                        match = item;
                    }
                }
            }
            return match;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmsapi: could not match mail by contact email", e);
            return null;
        }
    }

    private static boolean mailHtmlContains(JsonNode mail, String needleLower) {
        JsonNode multipart = mail.path("multipart");
        if (!multipart.isArray()) {
            return false;
        }
        for (JsonNode part : multipart) {
            String content = part.path("content").asText("");
            if (content.toLowerCase().contains(needleLower)) {
                return true;
            }
        }
        return false;
    }

    /**
     * ZMSKVR-955 / ZMSKVR-965: logged-in (or already-confirmed) booking must not send the
     * activation mail whose HTML contains {@code appointment/confirm/}.
     */
    @Then("there should be no preconfirmation mail for the current process")
    public void thereShouldBeNoPreconfirmationMailForTheCurrentProcess() {
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        if (booking == null || booking.getProcessId() == null) {
            throw new IllegalStateException("No booking process; confirm the appointment first.");
        }
        Integer processId = booking.getProcessId();
        String authKey = getOrLoginXAuthKey();
        ScenarioLogManager.getLogger()
                .info("zmsapi: asserting no preconfirmation/activation mail for process {}", processId);
        Response response = given()
            .baseUri(TestConfig.getBaseUri())
            .header("X-Authkey", authKey)
            .queryParam("limit", 500)
        .when()
            .get("/mails/");
        CommonApiSteps.setResponse(response);
        Assertions.assertThat(response.getStatusCode())
                .as("GET /mails/ for activation-mail absence")
                .isEqualTo(200);
        String confirmUrl = extractConfirmUrlFromMailResponse(response.asString(), processId);
        Assertions.assertThat(confirmUrl)
                .as("process %s must not have an activation mail (appointment/confirm/)", processId)
                .isNull();
    }

    /** Second mail fetch: run after the appointment is confirmed. The confirmation mail (with link to /appointment/***) is only sent once the appointment is confirmed. */
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

    @When("I fetch the cancellation mail for the current process")
    public void iFetchTheCancellationMailForTheCurrentProcess() {
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        if (booking == null || booking.getProcessId() == null) {
            throw new IllegalStateException("No booking process available to fetch cancellation mail for.");
        }
        Integer processId = booking.getProcessId();
        String authKey = getOrLoginXAuthKey();
        ScenarioLogManager.getLogger().info(
                "zmsapi: fetching cancellation mail from GET /mails/ for process {}", processId);

        Response response = given()
                .baseUri(TestConfig.getBaseUri())
                .header("X-Authkey", authKey)
                .queryParam("limit", 500)
                .when()
                .get("/mails/");
        CommonApiSteps.setResponse(response);

        if (response.getStatusCode() != 200) {
            throw new IllegalStateException(
                    "Failed to fetch mails for cancellation mail. HTTP " + response.getStatusCode());
        }

        String html = extractCancellationMailHtmlFromMailResponse(response.asString(), processId);
        if (html == null || html.isBlank()) {
            throw new IllegalStateException("Cancellation mail not found or HTML content empty for process " + processId);
        }
        lastCancellationMailHtml = html;
    }

    @Then("the cancellation mail should indicate the appointment was deleted with the word {word}")
    public void theCancellationMailShouldIndicateTheAppointmentWasDeletedWithTheWord(String expectedWord) {
        Assertions.assertThat(lastCancellationMailHtml)
                .as("cancellation mail HTML must have been fetched")
                .isNotBlank();
        String lower = lastCancellationMailHtml.toLowerCase();
        String expectedLower = expectedWord != null ? expectedWord.toLowerCase() : "";

        // 1) Straight match.
        boolean hasExpected = !expectedLower.isBlank() && lower.contains(expectedLower);

        // 2) Known encoding-mangled variant for German umlauts.
        // Example: "gelöscht" may appear as "gel?scht".
        boolean hasGeloeschtEncodingVariant =
                "gelöscht".equals(expectedLower) && (lower.contains("gel?scht") || java.util.regex.Pattern
                    .compile("wurde\\s+gel.*sch", java.util.regex.Pattern.DOTALL)
                    .matcher(lower)
                    .find());

        Assertions.assertThat(hasExpected || hasGeloeschtEncodingVariant)
                .as("cancellation mail HTML should include expected deletion word '%s' (or encoding variant)", expectedWord)
                .isTrue();
    }

    private String extractCancellationMailHtmlFromMailResponse(String responseBody, Integer processId) {
        try {
            JsonNode data = new ObjectMapper().readTree(responseBody).path("data");
            if (!data.isArray()) {
                return null;
            }
            // IMPORTANT: in our current test setup we do NOT delete old mails.
            // So multiple mails can exist for the same processId (reserve / preconfirm / confirm / cancel).
            // We must therefore pick the correct cancellation mail, not just "the first HTML part".
            String newestHtmlCandidate = null;
            int newestMailId = -1;

            String matchedAbgesagtHtml = null;
            int matchedAbgesagtMailId = -1;
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
                    if (content == null || content.isBlank()) {
                        continue;
                    }

                    // Always track the newest HTML content as a fallback.
                    if (mailId != -1 && mailId >= newestMailId) {
                        newestMailId = mailId;
                        newestHtmlCandidate = content;
                    }

                    // Prefer the actual cancellation mail by marker.
                    String lower = content.toLowerCase();
                    if (lower.contains("abgesagt")) {
                        if (mailId != -1 && mailId >= matchedAbgesagtMailId) {
                            matchedAbgesagtMailId = mailId;
                            matchedAbgesagtHtml = content;
                        }
                    }
                }
            }
            return matchedAbgesagtHtml != null ? matchedAbgesagtHtml : newestHtmlCandidate;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmsapi: could not extract cancellation mail html", e);
        }
        return null;
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
        String cachedXAuthKey = CACHED_X_AUTH_KEY.get();
        if (cachedXAuthKey != null && !cachedXAuthKey.isBlank()) {
            return cachedXAuthKey;
        }

        // Fallback for local/dev: obtain X-AuthKey by calling POST /workstation/login/
        // Use the system messenger account by default (same password as other system users).
        String username = TestPropertiesHelper.getPropertyAsString("zmsapiMailUserName", true, "_system_messenger");
        String password = TestPropertiesHelper.getPropertyAsString("zmsapiMailUserPassword", true, "vorschau");
        username = AccountCheckout.assignMessengerLogin(username);

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
            CACHED_X_AUTH_KEY.set(key);
            return key;
        } catch (Exception e) {
            throw new IllegalStateException("Failed to parse /workstation/login/ response for authkey", e);
        }
    }

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
