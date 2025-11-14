package base;

import static io.restassured.RestAssured.given;

import org.junit.jupiter.api.BeforeAll;

import config.TestConfig;
import io.restassured.RestAssured;
import io.restassured.builder.RequestSpecBuilder;
import io.restassured.filter.log.LogDetail;
import io.restassured.http.ContentType;
import io.restassured.specification.RequestSpecification;

/**
 * Base test class for API tests.
 * Provides common setup for REST-assured including base URI, authentication, and logging.
 */
public abstract class BaseApiTest {

    protected static RequestSpecification requestSpec;

    @BeforeAll
    static void setupRestAssured() {
        setupRestAssuredWithBaseUri(TestConfig.getBaseUri());
    }

    /**
     * Setup REST-assured with the given base URI.
     * Can be called by subclasses to use a different base URI.
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
}
