package zms.ataf.helpers;

import static io.restassured.RestAssured.given;

import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import config.TestConfig;
import io.restassured.response.Response;

/**
 * Password-grant token for the public Keycloak client {@code dbs-fragments}
 * (zmscitizenview Bürger-Login). Used so ATAF can send {@code Authorization: Bearer}
 * on citizen API update/confirm the way the logged-in UI does.
 */
public final class CitizenKeycloakTokenHelper {

    private CitizenKeycloakTokenHelper() {
    }

    public static String fetchAccessToken() {
        String tokenUri = TestConfig.getKeycloakTokenUri();
        String username = TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
        String password = TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
        if (username == null || username.isBlank()) {
            username = "citizen";
        }
        if (password == null || password.isBlank()) {
            password = "vorschau";
        }

        ScenarioLogManager.getLogger().info(
            "Citizen Keycloak password grant at {} as user {}",
            tokenUri,
            username
        );

        Response response = given()
            .contentType("application/x-www-form-urlencoded")
            .formParam("grant_type", "password")
            .formParam("client_id", "dbs-fragments")
            .formParam("username", username)
            .formParam("password", password)
        .when()
            .post(tokenUri);

        response.then().statusCode(200);
        String accessToken = response.jsonPath().getString("access_token");
        if (accessToken == null || accessToken.isBlank()) {
            throw new IllegalStateException("Keycloak password grant returned no access_token for citizen user.");
        }
        return accessToken;
    }
}
