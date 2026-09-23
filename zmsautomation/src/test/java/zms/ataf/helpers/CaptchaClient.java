package zms.ataf.helpers;

import static io.restassured.RestAssured.given;

import java.nio.charset.StandardCharsets;
import java.util.Base64;
import java.util.Map;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

import org.altcha.altcha.v2.Altcha;
import org.altcha.altcha.v2.Altcha.Challenge;
import org.altcha.altcha.v2.Altcha.ChallengeParameters;
import org.altcha.altcha.v2.Altcha.Solution;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import io.restassured.response.Response;

/**
 * Solves the local CaptchaService challenge through the citizen API and can mint an already-expired
 * captcha JWT with the same secret the API uses.
 */
public final class CaptchaClient {

    private static final ObjectMapper MAPPER = new ObjectMapper();

    private CaptchaClient() {}

    public static String solve(String baseUri) {
        Response challengeResponse = given().baseUri(baseUri).when().get("/captcha-challenge/");
        challengeResponse.then().statusCode(200);
        String challengeJson = challengeResponse.asString();

        Challenge challenge = parseChallenge(challengeJson);
        Solution solution;
        try {
            solution = Altcha.solveChallenge(challenge, Altcha.kdf(challenge.parameters().algorithm()));
        } catch (Exception e) {
            throw new IllegalStateException("Could not solve captcha challenge", e);
        }

        long solveTime = solution.time() == null ? 0L : solution.time();
        String payloadJson = "{\"challenge\":" + challengeJson
                + ",\"solution\":{\"counter\":" + solution.counter()
                + ",\"derivedKey\":\"" + solution.derivedKey()
                + "\",\"time\":" + solveTime + "}}";
        String payload = Base64.getEncoder().encodeToString(payloadJson.getBytes(StandardCharsets.UTF_8));

        Response verifyResponse = given()
                .baseUri(baseUri)
                .contentType("application/json")
                .body(Map.of("payload", payload))
                .when()
                .post("/captcha-verify/");
        verifyResponse.then().statusCode(200);

        String token = verifyResponse.jsonPath().getString("token");
        if (token == null || token.isBlank()) {
            throw new IllegalStateException("captcha-verify did not return a token: " + verifyResponse.asString());
        }
        return token;
    }

    /** HS256 JWT whose {@code exp} is already in the past. Signed with {@code CAPTCHA_TOKEN_SECRET}. */
    public static String expiredToken() {
        String secret = System.getenv("CAPTCHA_TOKEN_SECRET");
        if (secret == null || secret.isBlank()) {
            throw new IllegalStateException("CAPTCHA_TOKEN_SECRET is not set; cannot sign an expired captcha token");
        }
        String header = base64Url("{\"alg\":\"HS256\",\"typ\":\"JWT\"}");
        String payload = base64Url("{\"ip\":\"127.0.0.1\",\"iat\":1,\"exp\":1}");
        String signingInput = header + "." + payload;
        try {
            Mac mac = Mac.getInstance("HmacSHA256");
            mac.init(new SecretKeySpec(secret.getBytes(StandardCharsets.UTF_8), "HmacSHA256"));
            String signature = Base64.getUrlEncoder().withoutPadding()
                    .encodeToString(mac.doFinal(signingInput.getBytes(StandardCharsets.UTF_8)));
            return signingInput + "." + signature;
        } catch (Exception e) {
            throw new IllegalStateException("Could not sign expired captcha token", e);
        }
    }

    private static String base64Url(String value) {
        return Base64.getUrlEncoder().withoutPadding()
                .encodeToString(value.getBytes(StandardCharsets.UTF_8));
    }

    private static Challenge parseChallenge(String json) {
        try {
            JsonNode root = MAPPER.readTree(json);
            JsonNode challenge = root.has("parameters") ? root : root.path("data");
            JsonNode params = challenge.path("parameters");
            ChallengeParameters parameters = new ChallengeParameters(
                    params.path("algorithm").asText(),
                    params.path("nonce").asText(),
                    params.path("salt").asText(),
                    params.path("cost").asInt(),
                    params.path("keyLength").asInt(),
                    params.path("keyPrefix").asText(),
                    textOrNull(params.get("keySignature")),
                    intOrNull(params.get("memoryCost")),
                    intOrNull(params.get("parallelism")),
                    longOrNull(params.get("expiresAt")),
                    null);
            String signature = textOrNull(challenge.get("signature"));
            return new Challenge(parameters, signature);
        } catch (Exception e) {
            throw new IllegalStateException("Could not read captcha challenge", e);
        }
    }

    private static String textOrNull(JsonNode node) {
        if (node == null || node.isNull() || node.isMissingNode()) {
            return null;
        }
        return node.asText();
    }

    private static Integer intOrNull(JsonNode node) {
        if (node == null || node.isNull() || node.isMissingNode() || !node.isNumber()) {
            return null;
        }
        return node.intValue();
    }

    private static Long longOrNull(JsonNode node) {
        if (node == null || node.isNull() || node.isMissingNode() || !node.isNumber()) {
            return null;
        }
        return node.longValue();
    }
}
