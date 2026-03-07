package zms.ataf.rest.steps;

import static io.restassured.RestAssured.*;

import java.util.List;

import com.fasterxml.jackson.databind.ObjectMapper;

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
 */
public class ZmsApiMailSteps {

    @When("I fetch the preconfirmation mail for the current process")
    public void iFetchThePreconfirmationMailForTheCurrentProcess() {
        String authKey = TestConfig.getZmsApiAuthKey();
        if (authKey == null || authKey.isEmpty()) {
            throw new IllegalStateException(
                "ZMSAPI_AUTH_KEY (or ZMSAPI_AUTH_KEY env) is required to fetch mails. Set it for tests that use preconfirmation mail.");
        }
        ThinnedProcess booking = CitizenApiSteps.getBookingProcess();
        if (booking == null) {
            throw new IllegalStateException("No current process. Reserve an appointment first.");
        }
        Integer processId = booking.getProcessId();
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
