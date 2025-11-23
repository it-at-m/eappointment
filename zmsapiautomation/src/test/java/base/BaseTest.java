package base;

import static io.restassured.RestAssured.given;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;

import config.TestConfig;
import io.restassured.RestAssured;
import io.restassured.builder.RequestSpecBuilder;
import io.restassured.config.ObjectMapperConfig;
import io.restassured.config.RestAssuredConfig;
import io.restassured.filter.log.LogDetail;
import io.restassured.http.ContentType;
import io.restassured.mapper.ObjectMapperType;
import io.restassured.response.Response;
import io.restassured.specification.RequestSpecification;

/**
 * Common base class for all API tests.
 * Provides shared functionality for REST-assured setup, authentication, and response deserialization.
 */
public abstract class BaseTest {

    protected static RequestSpecification requestSpec;

    /**
     * Setup REST-assured with the given base URI.
     * This method should be called by subclasses in their @BeforeAll setup methods.
     *
     * @param baseUri Base URI for API requests
     */
    protected static void setupRestAssuredWithBaseUri(String baseUri) {
        // Configure base URI
        RestAssured.baseURI = baseUri;

        // Build request specification with common settings
        RequestSpecBuilder specBuilder = new RequestSpecBuilder()
            .setBaseUri(baseUri)
            .setContentType(ContentType.JSON)
            .setAccept(ContentType.JSON);

        // Add authentication if token is provided
        String authToken = TestConfig.getAuthToken();
        if (authToken != null && !authToken.isEmpty()) {
            specBuilder.addHeader("Authorization", "Bearer " + authToken);
        }

        // Enable logging if configured
        if (TestConfig.isLoggingEnabled()) {
            specBuilder.log(LogDetail.ALL);
        }

        // Configure Jackson for JSON deserialization
        RestAssuredConfig config = RestAssuredConfig.config()
            .objectMapperConfig(new ObjectMapperConfig(ObjectMapperType.JACKSON_2));
        specBuilder.setConfig(config);

        // Note: Timeout configuration is available via TestConfig.getRequestTimeout()
        // but REST-assured 5.x timeout API may need to be configured differently
        // For now, using default REST-assured timeouts

        requestSpec = specBuilder.build();
    }

    /**
     * Get a request specification builder with common settings.
     * Can be further customized in test methods.
     *
     * @return RequestSpecification with base configuration
     */
    protected RequestSpecification givenRequest() {
        return given().spec(requestSpec);
    }

    /**
     * Deserialize a REST-assured response to a generic type.
     * Useful for deserializing wrapped responses like ApiResponse&lt;T&gt;.
     *
     * @param <T> The type to deserialize to
     * @param response The REST-assured response
     * @param typeReference The TypeReference for the target type
     * @return Deserialized object of type T
     */
    protected <T> T as(Response response, TypeReference<T> typeReference) {
        try {
            ObjectMapper mapper = new ObjectMapper();
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
}
